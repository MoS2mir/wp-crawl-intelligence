<?php
namespace WPCI\Includes;

class Security {
	public function __construct() {
		add_action( 'init', [ $this, 'enforce_firewall' ], 1 );
	}

	/**
	 * Feature 11: Fake Bot Firewall
	 */
	public function enforce_firewall() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$bot_detector = new BotDetector();
		$bot_type = $bot_detector->identify_bot( $user_agent );

		if ( ! $bot_type || ! in_array( $bot_type, [ 'Googlebot', 'Bingbot' ] ) ) {
			return;
		}

		$cache_key = ( 'Googlebot' === $bot_type ) ? 'wpci_verify_google_' : 'wpci_verify_bing_';
		$ip = $this->get_ip();
		$verification_status = get_transient( $cache_key . $ip );

		// Only block if explicitly identified as fake (status 0 in cache)
		// If status is false (not in cache), we don't block yet to avoid false positives
		if ( 0 === $verification_status && false !== $verification_status ) {
			if ( apply_filters( 'wpci_enable_firewall', false ) ) {
				status_header( 403 );
				exit( 'Access Denied: Fake Search Bot Detected' );
			}
		}
	}

	private function get_ip() {
		$ip_headers = [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' ];
		foreach ( $ip_headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = $_SERVER[ $header ];
				if ( $header === 'HTTP_X_FORWARDED_FOR' ) {
					$ip = explode( ',', $ip )[0];
				}
				return trim( $ip );
			}
		}
		return '0.0.0.0';
	}
}
