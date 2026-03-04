<?php
namespace WPCI\Includes;

class DnsVerifier {
	public function verify_google( $ip ) {
		$cache_key = 'wpci_verify_google_' . $ip;
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return (bool) $cached;
		}

		$hostname = gethostbyaddr( $ip );
		if ( ! $hostname || ! preg_match( '/\.google(bot)?\.com$/i', $hostname ) ) {
			set_transient( $cache_key, 0, DAY_IN_SECONDS );
			return false;
		}

		// Verify IP matches hostname
		$resolved_ip = gethostbyname( $hostname );
		$is_verified = ( $resolved_ip === $ip ) ? 1 : 0;
		set_transient( $cache_key, $is_verified, DAY_IN_SECONDS );

		return (bool) $is_verified;
	}

	public function verify_bing( $ip ) {
		$cache_key = 'wpci_verify_bing_' . $ip;
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return (bool) $cached;
		}

		$hostname = gethostbyaddr( $ip );
		if ( ! $hostname || ! preg_match( '/\.search\.msn\.com$/i', $hostname ) ) {
			set_transient( $cache_key, 0, DAY_IN_SECONDS );
			return false;
		}

		$resolved_ip = gethostbyname( $hostname );
		$is_verified = ( $resolved_ip === $ip ) ? 1 : 0;
		set_transient( $cache_key, $is_verified, DAY_IN_SECONDS );

		return (bool) $is_verified;
	}

	public function verify_bot( $bot_type, $ip ) {
		if ( 'Googlebot' === $bot_type ) {
			return $this->verify_google( $ip );
		} elseif ( 'Bingbot' === $bot_type ) {
			return $this->verify_bing( $ip );
		}
		// Default true for non-verifiable bots, as they are likely identified by UA
		// but we might add more later.
		return true;
	}
}
