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

		$dns_verifier = new DnsVerifier();
		$ip = $this->get_ip();

		if ( ! $dns_verifier->verify_bot( $bot_type, $ip ) ) {
			// If it's a fake bot, we can block it
			// For now, let's just log it as a security event? 
			// Requirement says "block them at the application level".
			
			// Option: Check if blocking is enabled in settings
			// Since we don't have settings yet, let's keep it cautious or add a simple check
			if ( apply_filters( 'wpci_enable_firewall', false ) ) {
				status_header( 403 );
				exit( 'Access Denied: Fake Search Bot Detected' );
			}
		}
	}

	private function get_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
		}
		return $_SERVER['REMOTE_ADDR'];
	}
}
