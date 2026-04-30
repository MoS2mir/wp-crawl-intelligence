<?php
namespace WPCI\Includes;

class AdminDashboard {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_post_wpci_export_csv', [ $this, 'handle_csv_export' ] );
	}

	public function add_menu_page() {
		add_menu_page(
			'WP Crawl Intelligence',
			'Crawl Intel',
			'manage_options',
			'wpci_dashboard',
			[ $this, 'render_dashboard' ],
			'dashicons-chart-area',
			30
		);
	}

	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_wpci_dashboard' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wpci_admin_css', WPCI_URL . 'assets/css/wpci-admin.css', [], WPCI_VERSION );
		wp_enqueue_script( 'wpci_admin_js', WPCI_URL . 'assets/js/wpci-admin.js', [], WPCI_VERSION, true );
		
		$analyzer = new CrawlAnalyzer();
		
		// Chart data preparation: last 14 days (Crawl vs Traffic)
		$stats = $analyzer->get_crawl_vs_traffic();

		$labels = [];
		$botHits = [];
		$humanHits = [];

		foreach ( $stats as $row ) {
			$labels[] = date( 'M j', strtotime( $row->date ) );
			$botHits[] = (int) $row->bot_hits;
			$humanHits[] = (int) $row->human_hits;
		}

		wp_localize_script( 'wpci_admin_js', 'wpciData', [
			'labels'     => $labels,
			'botHits'    => $botHits,
			'humanHits'  => $humanHits,
		] );
	}

	public function render_dashboard() {
		$analyzer = new CrawlAnalyzer();
		$sitemap_analyzer = new SitemapAnalyzer();

		// Core Stats
		$score = $analyzer->get_crawl_budget_score();
		$top_urls = $analyzer->get_top_urls( 7, 10 );
		$alerts = $analyzer->get_alerts();

		// Feature Data
		$unloved_pages = $sitemap_analyzer->get_unloved_pages( 30 ); // Feature 1
		$parameter_waste = $analyzer->get_parameter_waste(); // Feature 2
		$redirect_chains = $analyzer->get_redirect_chains(); // Feature 3
		$budget_by_type = $analyzer->get_budget_by_post_type(); // Feature 4
		$soft_404s = $analyzer->get_soft_404_candidates(); // Feature 5
		$latency_heatmap = $analyzer->get_latency_heatmap(); // Feature 6
		$mobile_parity = $analyzer->get_mobile_parity_stats(); // Feature 8
		$robots_suggestions = $analyzer->get_robots_txt_suggestions(); // Feature 9
		$discovery_speed = $analyzer->get_discovery_speed(); // Feature 10

		// New Advanced Features
		$bot_sessions = $analyzer->get_bot_sessions(); // Feature 3: Crawl Path
		$crawl_capacity = $analyzer->get_crawl_capacity_estimate(); // Feature 2: Capacity
		$ai_recommendations = $analyzer->get_ai_recommendations(); // Feature 8: AI Advisor
		$crawl_roi = $analyzer->get_crawl_roi_stats(); // Feature 7: ROI
		$budget_simulator = $analyzer->get_crawl_budget_simulator(); // Feature 7: Simulator
		$crawl_vs_traffic = $analyzer->get_crawl_vs_traffic(); // Feature 7: vs Traffic
		$resource_ratio = $analyzer->get_resource_to_page_ratio(); // Feature 6: Rendering Monitor

		include WPCI_PATH . 'includes/views/dashboard.php';
	}

	public function handle_csv_export() {
		check_admin_referer( 'wpci_export_csv', '_wpnonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		global $wpdb;
		$table_name = Database::get_table_name();
		$logs = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT 5000", ARRAY_A );

		if ( ! $logs ) {
			wp_die( 'No data to export' );
		}

		$filename = 'wpci_bot_logs_' . date( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array_keys( $logs[0] ) );

		foreach ( $logs as $row ) {
			fputcsv( $output, $row );
		}

		fclose( $output );
		exit;
	}
}
