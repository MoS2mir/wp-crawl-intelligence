<?php
namespace WPCI\Includes;

class Logger {
	private $start_time;
	private $request_data;

	private $content_size = 0;

	public function __construct() {
		$this->start_time = microtime( true );
		
		// 4. Kill Switch
		if ( get_option( 'wpci_kill_switch', false ) ) {
			return;
		}

		// 9. Logger Early Exits
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// 3. WooCommerce Exclusions (Improved)
		if ( $this->is_woocommerce_protected() ) {
			return;
		}

		// Check for Ignored IPs
		$ignored_ips = get_option( 'wpci_ignored_ips', '' );
		if ( ! empty( $ignored_ips ) ) {
			$current_ip = $this->get_ip();
			$ignored_array = array_map( 'trim', explode( ',', $ignored_ips ) );
			if ( in_array( $current_ip, $ignored_array ) ) {
				return;
			}
		}

		// Optimization: Identify bot early
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$bot_detector = new BotDetector();
		$bot_type = $bot_detector->identify_bot( $user_agent );
		
		$enable_human_logs = get_option( 'wpci_enable_human_logs', true );
		if ( ! $bot_type && ! $enable_human_logs ) {
			return;
		}

		// 2. Stop using ob_start() for default requests
		// 10. No blocking operations in request thread
		$this->collect_request_data( $bot_type );
		
		add_action( 'shutdown', [ $this, 'finalize_log' ], 20 );
	}

	private function is_woocommerce_protected() {
		if ( ! function_exists( 'is_woocommerce' ) ) {
			return false;
		}
		
		// If WooCommerce is loaded, check for sensitive pages
		if ( is_cart() || is_checkout() || is_account_page() ) {
			return true;
		}

		// Additional check for URL patterns if helper functions aren't ready
		$uri = $_SERVER['REQUEST_URI'];
		$protected_paths = [ '/cart/', '/checkout/', '/my-account/' ];
		foreach ( $protected_paths as $path ) {
			if ( str_contains( $uri, $path ) ) {
				return true;
			}
		}

		return false;
	}

	public function collect_request_data( $pre_identified_bot = null ) {
		$uri = $_SERVER['REQUEST_URI'];
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		
		$bot_type = $pre_identified_bot;

		// Ignore common static assets for HUMANS
		$is_asset = false;
		$protected_extensions = [ '.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.ttf', '.json', '.ico' ];
		foreach ( $protected_extensions as $ext ) {
			if ( str_contains( strtolower( $uri ), $ext ) ) {
				$is_asset = true;
				break;
			}
		}

		if ( $is_asset && ! $bot_type ) {
			return; 
		}

		if ( ! $bot_type ) {
			$bot_type = 'Human';
		}

		$this->request_data = [
			'url'             => home_url( parse_url( $uri, PHP_URL_PATH ) ),
			'full_url'        => home_url( $uri ),
			'ip'              => $this->get_ip(),
			'user_agent'      => $user_agent,
			'method'          => $_SERVER['REQUEST_METHOD'],
			'referer'         => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
			'bot_type'        => $bot_type,
			'is_parameterized'=> ! empty( $_GET ) ? 1 : 0,
			'device_type'     => $this->detect_device_type( $user_agent ),
		];
	}

	public function finalize_log() {
		if ( ! $this->request_data ) {
			return;
		}

		global $wpdb;
		$buffer_table = Database::get_buffer_table_name();

		$response_time = microtime( true ) - $this->start_time;
		$status_code = http_response_code();

		// Determine Post Type efficiently
		$post_type = 'unknown';
		if ( function_exists( 'get_post_type' ) && is_singular() ) {
			$post_type = get_post_type();
		}

		// 6. Queue-based system (Write to buffer table first)
		// 8. No heavy operations (DNS verification is now deferred)
		$log_data = [
			'url'               => $this->request_data['url'],
			'url_hash'          => md5( $this->request_data['url'] ), // 7. Hash-based lookup
			'ip'                => $this->request_data['ip'],
			'user_agent'        => $this->request_data['user_agent'],
			'method'            => $this->request_data['method'],
			'status_code'       => $status_code,
			'response_time'     => $response_time,
			'referer'           => $this->request_data['referer'],
			'bot_type'          => $this->request_data['bot_type'],
			'post_type'         => $post_type,
			'content_length'    => 0, // Removed ob_start dependency
			'is_parameterized'  => $this->request_data['is_parameterized'],
			'device_type'       => $this->request_data['device_type'],
			'is_verified_bot'   => 0, // Verification deferred
			'timestamp'         => current_time( 'mysql' ),
		];

		$wpdb->insert(
			$buffer_table,
			[ 'data' => json_encode( $log_data ) ],
			[ '%s' ]
		);
	}

	private function detect_device_type( $user_agent ) {
		$user_agent = strtolower( $user_agent );
		if ( str_contains( $user_agent, 'mobile' ) || str_contains( $user_agent, 'smartphone' ) || str_contains( $user_agent, 'android' ) || str_contains( $user_agent, 'iphone' ) ) {
			return 'Mobile';
		}
		return 'Desktop';
	}

	private function get_ip() {
		// 5. Improved IP detection (Cloudflare + Proxies)
		$ip_headers = [
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_CLIENT_IP',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR'
		];

		foreach ( $ip_headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = $_SERVER[ $header ];
				if ( $header === 'HTTP_X_FORWARDED_FOR' ) {
					$ips = explode( ',', $ip );
					$ip = trim( $ips[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}
		
		return '0.0.0.0';
	}
}
