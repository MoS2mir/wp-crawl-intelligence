<?php
namespace WPCI\Includes;

class BotDetector {
	public function __construct() {
		// Nothing to init here, primarily utility
	}

	public function identify_bot( $user_agent ) {
		$user_agent = strtolower( $user_agent );
		$bots = [
			'googlebot'   => 'Googlebot',
			'bingbot'      => 'Bingbot',
			'yandexbot'   => 'YandexBot',
			'duckduckbot' => 'DuckDuckBot',
			'ahrefsbot'   => 'AhrefsBot',
			'semrushbot'  => 'SemrushBot',
			'mj12bot'     => 'MJ12bot',
		];

		foreach ( $bots as $key => $bot_name ) {
			if ( strpos( $user_agent, $key ) !== false ) {
				return $bot_name;
			}
		}

		return null;
	}

	public function needs_verification( $bot_type ) {
		$verifiable = [ 'Googlebot', 'Bingbot' ];
		return in_array( $bot_type, $verifiable, true );
	}
}
