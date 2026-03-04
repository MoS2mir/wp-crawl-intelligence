<?php
namespace WPCI\Includes;

class Logger {
	private $start_time;
	private $request_data;

	public function __construct() {
		$this->start_time = microtime( true );
		$this->collect_request_data();
		
		// Hook into shutdown to finalize logging
		add_action( 'shutdown', [ $this, 'finalize_log' ] );
	}

	public function collect_request_data() {
		// Avoid logging logged-in admins
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}

		// Option to disable asset logging (CSS, JS, Images)
		$uri = $_SERVER['REQUEST_URI'];
		$protected_extensions = [ '.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.ttf' ];
		foreach ( $protected_extensions as $ext ) {
			if ( strpos( $uri, $ext ) !== false ) {
				return;
			}
		}

		$bot_detector = new BotDetector();
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$bot_type = $bot_detector->identify_bot( $user_agent );

		// We log everything, but prioritize bots
		// The requirement said "Log all incoming HTTP requests", but focus on bots.
		// For high performance, we might want to ONLY log bots, or limit logging.
		// Let's log if it's a bot or if explicitly enabled.
		if ( ! $bot_type ) {
			return; // Only log known bots for performance, unless specified otherwise
		}

		$this->request_data = [
			'url'        => home_url( $uri ),
			'ip'         => $this->get_ip(),
			'user_agent' => $user_agent,
			'method'     => $_SERVER['REQUEST_METHOD'],
			'referer'    => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
			'bot_type'   => $bot_type,
		];
	}

	public function finalize_log() {
		if ( ! $this->request_data ) {
			return;
		}

		global $wpdb;
		$table_name = Database::get_table_name();

		$response_time = microtime( true ) - $this->start_time;
		$status_code = http_response_code();

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
				'is_verified_bot'   => $is_verified,
				'timestamp'         => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%s', '%d', '%f', '%s', '%s', '%d', '%s' ]
		);
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
