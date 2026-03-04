<?php
namespace WPCI\Includes;

class Loader {
	public function __construct() {
		$this->init_components();
	}

	private function init_components() {
		// Initialize Logger first to start tracking as early as possible
		new Logger();

		// Initialize Database and Tables (though we handle this on activation)
		new Database();

		// Bot Detection & Verification
		new BotDetector();
		new DnsVerifier();

		// Crawl Analysis & Stats
		new CrawlAnalyzer();

		// Admin & UI
		if ( is_admin() ) {
			new AdminDashboard();
		}

		// Background Tasks & Aggregation
		new Cron();
	}

	private function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		add_action( $hook, $callback, $priority, $accepted_args );
	}

	private function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		add_filter( $hook, $callback, $priority, $accepted_args );
	}
}
