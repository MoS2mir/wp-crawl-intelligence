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

		// Bot Detection, Verification & Security
		new BotDetector();
		new DnsVerifier();
		new Security(); // Feature 11: Fake Bot Firewall

		// Crawl Analysis & Stats
		new CrawlAnalyzer();
		new SitemapAnalyzer(); // Feature 1: Sitemap Analysis

		// Admin & UI
		if ( is_admin() ) {
			new AdminDashboard();
		}

		// Background Tasks & Notifications
		new Cron();
		new AlertSystem(); // Feature 12: Admin Alerts
	}

	private function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		add_action( $hook, $callback, $priority, $accepted_args );
	}

	private function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		add_filter( $hook, $callback, $priority, $accepted_args );
	}
}
