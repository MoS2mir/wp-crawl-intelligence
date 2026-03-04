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
		
		// Chart data preparation: last 14 days
		$start_date = date( 'Y-m-d', strtotime( '-14 days' ) );
		$end_date = date( 'Y-m-d' );
		$stats = $analyzer->get_stats_for_range( $start_date, $end_date );

		$labels = [];
		$hit_counts = [];
		$response_times = [];
		
		foreach ( $stats as $stat ) {
			if ( ! in_array( $stat->date, $labels ) ) {
				$labels[] = $stat->date;
				$hit_counts[ $stat->date ] = 0;
				$response_times[ $stat->date ] = 0;
				$count[ $stat->date ] = 0;
			}
			$hit_counts[ $stat->date ] += $stat->hit_count;
			$response_times[ $stat->date ] += $stat->avg_response_time;
			$count[ $stat->date ] += 1;
		}

		$formatted_response_times = [];
		foreach ( $response_times as $date => $time ) {
			$formatted_response_times[] = ( $count[ $date ] > 0 ) ? ( $time / $count[ $date ] ) : 0;
		}

		wp_localize_script( 'wpci_admin_js', 'wpciData', [
			'labels'         => $labels,
			'hitCounts'      => array_values( $hit_counts ),
			'responseTimes'  => array_values( $formatted_response_times ),
		] );
	}

	public function render_dashboard() {
		$analyzer = new CrawlAnalyzer();
		$score = $analyzer->get_crawl_budget_score();
		$top_urls = $analyzer->get_top_urls( 7, 10 );
		$alerts = $analyzer->get_alerts();

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
