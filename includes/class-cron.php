<?php
namespace WPCI\Includes;

class Cron {
	public function __construct() {
		add_action( 'wpci_daily_aggregation', [ $this, 'aggregate_logs' ] );
		add_action( 'wpci_daily_cleanup', [ $this, 'cleanup_logs' ] );
	}

	public static function schedule_events() {
		if ( ! wp_next_scheduled( 'wpci_daily_aggregation' ) ) {
			wp_schedule_event( time(), 'daily', 'wpci_daily_aggregation' );
		}
		if ( ! wp_next_scheduled( 'wpci_daily_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'wpci_daily_cleanup' );
		}
	}

	public static function clear_scheduled_events() {
		wp_clear_scheduled_hook( 'wpci_daily_aggregation' );
		wp_clear_scheduled_hook( 'wpci_daily_cleanup' );
	}

	public function aggregate_logs() {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$stats_table = Database::get_stats_table_name();

		// Aggregate for yesterday, just in case today is still being populated
		$yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );

		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				bot_type, 
				COUNT(*) as hit_count, 
				AVG(response_time) as avg_response_time,
				SUM(CASE WHEN status_code BETWEEN 200 AND 299 THEN 1 ELSE 0 END) as status_2xx,
				SUM(CASE WHEN status_code BETWEEN 300 AND 399 THEN 1 ELSE 0 END) as status_3xx,
				SUM(CASE WHEN status_code BETWEEN 400 AND 499 THEN 1 ELSE 0 END) as status_4xx,
				SUM(CASE WHEN status_code BETWEEN 500 AND 599 THEN 1 ELSE 0 END) as status_5xx
			FROM $logs_table 
			WHERE DATE(timestamp) = %s 
			GROUP BY bot_type",
			$yesterday
		) );

		if ( $results ) {
			foreach ( $results as $row ) {
				$wpdb->replace(
					$stats_table,
					[
						'date'              => $yesterday,
						'bot_type'          => $row->bot_type,
						'hit_count'         => $row->hit_count,
						'avg_response_time' => $row->avg_response_time,
						'status_2xx'        => $row->status_2xx,
						'status_3xx'        => $row->status_3xx,
						'status_4xx'        => $row->status_4xx,
						'status_5xx'        => $row->status_5xx,
					],
					[ '%s', '%s', '%d', '%f', '%d', '%d', '%d', '%d' ]
				);
			}
		}
	}

	public function cleanup_logs() {
		global $wpdb;
		$logs_table = Database::get_table_name();

		// Default retention: 30 days
		$retention_days = 30;
		$date = date( 'Y-m-d', strtotime( "-$retention_days days" ) );

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM $logs_table WHERE timestamp < %s",
			$date
		) );
	}
}
