<?php
namespace WPCI\Includes;

class Logger {
	private $start_time;
	private $request_data;

	private $content_size = 0;

	public function __construct() {
		$this->start_time = microtime( true );
		
		// Avoid logging admin/logged-in users/AJAX/Cron
		if ( is_admin() || is_user_logged_in() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// Buffer output to get size and analyze content
		ob_start();
		
		$this->collect_request_data();
		
		add_action( 'shutdown', [ $this, 'finalize_log' ], 20 );
	}

	public function collect_request_data() {
		$uri = $_SERVER['REQUEST_URI'];
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		
		$bot_detector = new BotDetector();
		$bot_type = $bot_detector->identify_bot( $user_agent );

		// Ignore common static assets for HUMANS to save space, but log for BOTS for rendering analysis
		$is_asset = false;
		$protected_extensions = [ '.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.ttf', '.json', '.ico' ];
		foreach ( $protected_extensions as $ext ) {
			if ( strpos( strtolower( $uri ), $ext ) !== false ) {
				$is_asset = true;
				break;
			}
		}

		if ( $is_asset && ! $bot_type ) {
			return; // Don't log asset hits for humans
		}

		// If it's not a bot, we log it as 'Human' for Crawl vs Traffic analysis
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
			if ( ob_get_level() > 0 ) {
				ob_end_flush();
			}
			return;
		}

		$this->content_size = ob_get_length();
		if ( ob_get_level() > 0 ) {
			ob_end_flush();
		}

		global $wpdb;
		$table_name = Database::get_table_name();

		$response_time = microtime( true ) - $this->start_time;
		$status_code = http_response_code();

		// Determine Post Type
		$post_type = 'unknown';
		if ( is_singular() ) {
			$post_type = get_post_type();
		} elseif ( is_archive() ) {
			$post_type = 'archive';
		} elseif ( is_home() || is_front_page() ) {
			$post_type = 'homepage';
		} elseif ( is_search() ) {
			$post_type = 'search';
		}

		// Bot Verification
		$dns_verifier = new DnsVerifier();
		$is_verified = $dns_verifier->verify_bot( $this->request_data['bot_type'], $this->request_data['ip'] ) ? 1 : 0;

		$wpdb->insert(
			$table_name,
			[
				'url'               => $this->request_data['url'],
				'ip'                => $this->request_data['ip'],
				'user_agent'        => $this->request_data['user_agent'],
				'method'            => $this->request_data['method'],
				'status_code'       => $status_code,
				'response_time'     => $response_time,
				'referer'           => $this->request_data['referer'],
				'bot_type'          => $this->request_data['bot_type'],
				'post_type'         => $post_type,
				'content_length'    => $this->content_size,
				'is_parameterized'  => $this->request_data['is_parameterized'],
				'device_type'       => $this->request_data['device_type'],
				'is_sitemap_url'    => 0, // Will be updated by Sitemap Module later
				'is_verified_bot'   => $is_verified,
				'timestamp'         => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%s', '%d', '%f', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d', '%s' ]
		);
	}

	private function detect_device_type( $user_agent ) {
		$user_agent = strtolower( $user_agent );
		if ( strpos( $user_agent, 'mobile' ) !== false || strpos( $user_agent, 'smartphone' ) !== false || strpos( $user_agent, 'android' ) !== false || strpos( $user_agent, 'iphone' ) !== false ) {
			return 'Mobile';
		}
		return 'Desktop';
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
