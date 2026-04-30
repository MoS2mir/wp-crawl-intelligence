<?php
namespace WPCI\Includes;

class DnsVerifier {
	public function verify_google( $ip ) {
		$cache_key = 'wpci_verify_google_' . $ip;
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return (bool) $cached;
		}

		// 1. Remove synchronous DNS lookups (gethostbyaddr/gethostbyname removed)
		// Instead, we return false and let the background process handle it
		return false;
	}

	public function verify_bing( $ip ) {
		$cache_key = 'wpci_verify_bing_' . $ip;
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return (bool) $cached;
		}

		return false;
	}

	public function verify_bot( $bot_type, $ip ) {
		if ( 'Googlebot' === $bot_type ) {
			return $this->verify_google( $ip );
		} elseif ( 'Bingbot' === $bot_type ) {
			return $this->verify_bing( $ip );
		}
		return true;
	}

	/**
	 * Background verification method to be called by Cron
	 */
	public function perform_background_verification( $ip, $bot_type ) {
		if ( 'Googlebot' === $bot_type ) {
			$hostname = gethostbyaddr( $ip );
			if ( $hostname && preg_match( '/\.google(bot)?\.com$/i', $hostname ) ) {
				$resolved_ip = gethostbyname( $hostname );
				$is_verified = ( $resolved_ip === $ip ) ? 1 : 0;
				set_transient( 'wpci_verify_google_' . $ip, $is_verified, DAY_IN_SECONDS );
				return (bool) $is_verified;
			}
		} elseif ( 'Bingbot' === $bot_type ) {
			$hostname = gethostbyaddr( $ip );
			if ( $hostname && preg_match( '/\.search\.msn\.com$/i', $hostname ) ) {
				$resolved_ip = gethostbyname( $hostname );
				$is_verified = ( $resolved_ip === $ip ) ? 1 : 0;
				set_transient( 'wpci_verify_bing_' . $ip, $is_verified, DAY_IN_SECONDS );
				return (bool) $is_verified;
			}
		}
		return false;
	}
}
