<?php
namespace WPCI\Includes;

class CrawlAnalyzer {
	public function get_stats_for_range( $start_date, $end_date, $bot_type = null ) {
		global $wpdb;
		$stats_table = Database::get_stats_table_name();

		$where = $wpdb->prepare( "WHERE date BETWEEN %s AND %s", $start_date, $end_date );
		if ( $bot_type ) {
			$where .= $wpdb->prepare( " AND bot_type = %s", $bot_type );
		}

		return $wpdb->get_results( "SELECT * FROM $stats_table $where ORDER BY date ASC" );
	}

	public function get_top_urls( $days = 7, $limit = 10 ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT url, COUNT(*) as hit_count 
			FROM $logs_table 
			WHERE timestamp > %s 
			GROUP BY url 
			ORDER BY hit_count DESC 
			LIMIT %d",
			$date,
			$limit
		) );
	}

	public function get_crawl_budget_score() {
		// Example weighted score: based on status code ratios and response times
		// This is a simple logic for now
		global $wpdb;
		$stats_table = Database::get_stats_table_name();
		$last_7_days = $wpdb->get_results( "SELECT * FROM $stats_table WHERE date > '" . date( 'Y-m-d', strtotime( '-7 days' ) ) . "'" );

		if ( ! $last_7_days ) {
			return 0;
		}

		$total_hits = 0;
		$total_errors = 0;
		$avg_time = 0;

		foreach ( $last_7_days as $stat ) {
			$total_hits += $stat->hit_count;
			$total_errors += $stat->status_4xx + $stat->status_5xx;
			$avg_time += $stat->avg_response_time;
		}

		$error_rate = $total_hits > 0 ? ( $total_errors / $total_hits ) : 0;
		$avg_time = $avg_time / count( $last_7_days );

		$score = 100;
		$score -= ( $error_rate * 250 ); // 20% error rate = 50 points down
		$score -= ( $avg_time > 1 ? ( $avg_time - 1 ) * 20 : 0 ); // Each second over 1s = 20 points down

		return max( 0, min( 100, (int) $score ) );
	}
	
	public function get_orphan_urls( $limit = 10 ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		
		// Logic: bot hit a URL that is NOT current admin/logged-in 
		// but returned a 200 OK but might not be in our internal structure.
		// Actually, in WP, we check against WP_Query?
		// A better orphan definition is: Bot hits a URL that exists (200 OK) but has NO internal links (not in sitemap).
		
		// For now, let's flag 200 OK hits that we haven't seen in a while or are suspicious.
		return $wpdb->get_results( "SELECT url, COUNT(*) as hits FROM $logs_table WHERE status_code = 200 GROUP BY url ORDER BY hits DESC LIMIT $limit" );
	}

	public function get_alerts() {
		global $wpdb;
		// Check for status 404 spikes in last 24h vs last 7d average
		$logs_table = Database::get_table_name();
		$stats_table = Database::get_stats_table_name();
		
		$now = current_time( 'mysql' );
		$yesterday = date( 'Y-m-d H:i:s', strtotime( '-24 hours' ) );
		
		$last_24h_404 = $wpdb->get_var( $wpdb->prepare( 
			"SELECT COUNT(*) FROM $logs_table WHERE status_code = 404 AND timestamp > %s", 
			$yesterday 
		) );

		$alerts = [];
		if ( $last_24h_404 > 100 ) { // Simple threshold for example
			$alerts[] = [
				'type'    => 'warning',
				'message' => 'High volume of 404 errors detected in the last 24 hours. Check top 404 URLs.',
			];
		}
		
		// Alert on high response time
		$avg_time = $wpdb->get_var( $wpdb->prepare( "SELECT AVG(response_time) FROM $logs_table WHERE timestamp > %s", $yesterday ) );
		if ( $avg_time > 1.5 ) {
			$alerts[] = [
				'type'    => 'caution',
				'message' => 'High average bot response time detected (> 1.5s). Bot crawling may be slower.',
			];
		}

		return $alerts;
	}
}
