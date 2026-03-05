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

	/**
	 * Feature 4: "Crawl Budget Score" by Post Type
	 */
	public function get_budget_by_post_type( $days = 14 ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT post_type, COUNT(*) as hit_count, AVG(response_time) as avg_time, SUM(content_length) as total_weight
			FROM $logs_table 
			WHERE timestamp > %s 
			GROUP BY post_type 
			ORDER BY hit_count DESC",
			$date
		) );
	}

	/**
	 * Feature 2: Parameter Crawl Waste Optimizer
	 */
	public function get_parameter_waste( $days = 7, $limit = 10 ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT url, COUNT(*) as waste_hits, AVG(response_time) as avg_waste_time
			FROM $logs_table 
			WHERE is_parameterized = 1 AND timestamp > %s 
			GROUP BY url 
			HAVING waste_hits > 5
			ORDER BY waste_hits DESC 
			LIMIT %d",
			$date, $limit
		) );
	}

	/**
	 * Feature 3: Redirect Chain & "Hop" Monitor
	 */
	public function get_redirect_chains( $days = 7 ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

		// We look for bots that hit a 3xx status, then possibly identify patterns
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT url, status_code, COUNT(*) as redirect_count
			FROM $logs_table 
			WHERE status_code IN (301, 302, 307, 308) AND timestamp > %s 
			GROUP BY url, status_code
			ORDER BY redirect_count DESC 
			LIMIT 20",
			$date
		) );
	}

	/**
	 * Feature 5: Soft 404 Pattern Recognition
	 */
	public function get_soft_404_candidates( $days = 14 ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

		// Candidates: Status 200 but very low content length
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT url, content_length, bot_type, timestamp 
			FROM $logs_table 
			WHERE status_code = 200 AND content_length < 2048 AND post_type NOT IN ('unknown', 'homepage') AND timestamp > %s
			ORDER BY content_length ASC 
			LIMIT 50",
			$date
		) );
	}

	/**
	 * Feature 6: Bot Latency Heatmap (TTFB per Bot)
	 */
	public function get_latency_heatmap( $days = 7 ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT bot_type, 
				CASE 
					WHEN url LIKE '%/product/%' THEN 'Products'
					WHEN url LIKE '%/category/%' THEN 'Categories'
					WHEN url LIKE '%/tag/%' THEN 'Tags'
					ELSE 'Other'
				END as url_cluster,
				AVG(response_time) as avg_ttfb,
				COUNT(*) as sample_size
			FROM $logs_table 
			WHERE timestamp > %s 
			GROUP BY bot_type, url_cluster
			ORDER BY bot_type, avg_ttfb DESC",
			$date
		) );
	}

	/**
	 * Feature 8: Mobile-First Indexing Parity Check
	 */
	public function get_mobile_parity_stats( $days = 14 ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT bot_type, device_type, COUNT(*) as hit_count, AVG(response_time) as avg_time
			FROM $logs_table 
			WHERE bot_type IN ('Googlebot', 'Bingbot') AND timestamp > %s 
			GROUP BY bot_type, device_type",
			$date
		) );
	}

	/**
	 * Feature 9: Automatic "Robots.txt" Suggestion Engine
	 */
	public function get_robots_txt_suggestions() {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$date = date( 'Y-m-d H:i:s', strtotime( "-14 days" ) );

		// Identify high-frequency paths with query params or search/archives
		$patterns = $wpdb->get_results( $wpdb->prepare(
			"SELECT url, COUNT(*) as hits 
			FROM $logs_table 
			WHERE timestamp > %s AND (is_parameterized = 1 OR url LIKE '%/search/%' OR url LIKE '%/author/%')
			GROUP BY url 
			HAVING hits > 50
			ORDER BY hits DESC 
			LIMIT 5",
			$date
		) );

		$suggestions = [];
		foreach ( $patterns as $pattern ) {
			$path = parse_url( $pattern->url, PHP_URL_PATH );
			if ( $path ) {
				$suggestions[] = "Disallow: " . $path . " # (Hit $pattern->hits times by bots recently)";
			}
		}

		return $suggestions;
	}

	/**
	 * Feature 10: Interaction-to-Indexation Timeline (Discovery Speed)
	 * This is a bit complex as it needs to join with WP posts table
	 */
	public function get_discovery_speed( $limit = 10 ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$posts_table = $wpdb->prefix . 'posts';

		// Find first log timestamp vs post_date for recent posts
		return $wpdb->get_results(
			"SELECT p.ID, p.post_title, p.post_date, MIN(l.timestamp) as first_crawl,
			TIMESTAMPDIFF(HOUR, p.post_date, MIN(l.timestamp)) as discovery_delay_hours
			FROM $posts_table p
			JOIN $logs_table l ON l.url LIKE CONCAT('%', p.post_name, '%')
			WHERE p.post_status = 'publish' AND p.post_type IN ('post', 'product')
			AND p.post_date > '" . date( 'Y-m-d', strtotime( '-30 days' ) ) . "'
			GROUP BY p.ID
			HAVING first_crawl IS NOT NULL
			ORDER BY p.post_date DESC
			LIMIT $limit"
		);
	}

	/**
	 * Feature 3: Googlebot Crawl Path Reconstruction
	 * Groups hits by IP and Bot Type to see the sequence of navigation.
	 */
	public function get_bot_sessions( $limit = 5 ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$date = date( 'Y-m-d H:i:s', strtotime( '-24 hours' ) );

		// Get unique bot sessions (IP + Bot Type)
		$ips = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT ip FROM $logs_table WHERE timestamp > %s AND bot_type IS NOT NULL LIMIT %d",
			$date, $limit
		) );

		$sessions = [];
		foreach ( $ips as $ip ) {
			$path = $wpdb->get_results( $wpdb->prepare(
				"SELECT url, timestamp, status_code FROM $logs_table WHERE ip = %s AND timestamp > %s ORDER BY timestamp ASC",
				$ip, $date
			) );
			
			if ( $path ) {
				$sessions[] = [
					'ip'   => $ip,
					'bot'  => $wpdb->get_var( $wpdb->prepare( "SELECT bot_type FROM $logs_table WHERE ip = %s LIMIT 1", $ip ) ),
					'path' => $path
				];
			}
		}
		return $sessions;
	}

	/**
	 * Feature 4: Crawl Depth & Deep Page Detector
	 */
	public function get_crawl_depth_stats() {
		global $wpdb;
		$logs_table = Database::get_table_name();
		
		// Logic: Detect if the referer is from the same site and calculate hops
		// For simplicity, we flag pages that are rarely reached or are far from home
		return $wpdb->get_results(
			"SELECT url, COUNT(DISTINCT referer) as inward_links, COUNT(*) as bot_visits
			FROM $logs_table 
			WHERE referer LIKE '" . home_url() . "%'
			GROUP BY url 
			ORDER BY inward_links ASC 
			LIMIT 10"
		);
	}

	/**
	 * Feature 7: Indexation Probability Score
	 */
	public function get_indexation_probability( $url ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		
		$stats = $wpdb->get_row( $wpdb->prepare(
			"SELECT COUNT(*) as crawls, AVG(status_code) as avg_status, MAX(timestamp) as last_crawl
			FROM $logs_table WHERE url = %s",
			$url
		) );

		if ( ! $stats || $stats->crawls == 0 ) return 0;

		$score = 50; // Neutral starting point
		$score += ( $stats->crawls * 5 ); // More crawls = higher probability
		if ( $stats->avg_status == 200 ) $score += 20;
		
		$days_since = ( time() - strtotime( $stats->last_crawl ) ) / DAY_IN_SECONDS;
		$score -= ( $days_since * 2 ); // Decay over time

		return max( 0, min( 100, (int) $score ) );
	}

	/**
	 * Feature 2: Crawl Capacity Estimator
	 */
	public function get_crawl_capacity_estimate() {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$avg_time = $wpdb->get_var( "SELECT AVG(response_time) FROM $logs_table WHERE timestamp > '" . date( 'Y-m-d H:i:s', strtotime( '-24 hours' ) ) . "'" );

		if ( ! $avg_time ) return 1000; // Baseline

		// Estimate based on parallel crawl capacity
		// E.g., if response is 0.5s, one crawler thread can do 2 pages/sec = 172k pages/day
		// Realistically, bots limit themselves. We estimate a healthy capacity.
		$capacity = ( 1 / max( 0.1, $avg_time ) ) * 1000; // Simplified formula
		return (int) $capacity;
	}

	/**
	 * Feature 8: AI Technical SEO Advisor
	 */
	public function get_ai_recommendations() {
		$alerts = $this->get_alerts();
		$waste = $this->get_parameter_waste();
		$recs = [];

		if ( ! empty( $waste ) ) {
			$recs[] = [
				'problem' => "Googlebot is wasting budget on parameterized URLs (" . count($waste) . " patterns detected).",
				'solution' => "Configure URL Parameters in Search Console or Disallow '?' folders in robots.txt."
			];
		}

		foreach ( $alerts as $alert ) {
			if ( strpos( $alert['message'], 'dropped' ) !== false ) {
				$recs[] = [
					'problem' => "Significant Crawl Rate Drop detected.",
					'solution' => "Check for accidental 'noindex' or firewall blocks on your server."
				];
			}
		}
		
		return $recs;
	}

	/**
	 * Feature 7: Indexation Probability Score
	 */
	public function get_indexation_probability_score( $url ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		
		$stats = $wpdb->get_row( $wpdb->prepare(
			"SELECT COUNT(*) as crawls, MAX(timestamp) as last_crawl
			FROM $logs_table WHERE url = %s AND status_code = 200",
			$url
		) );

		if ( ! $stats || $stats->crawls == 0 ) return 0;

		$score = 40;
		$score += ( $stats->crawls * 5 ); // Frequency factor
		
		$days_since = ( time() - strtotime( $stats->last_crawl ) ) / DAY_IN_SECONDS;
		$score -= ( $days_since * 3 ); // Recency factor
		
		return max( 0, min( 100, (int) $score ) );
	}

	/**
	 * Feature 7: Crawl Frequency Predictor
	 */
	public function get_crawl_frequency_predictor( $url ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		
		$timestamps = $wpdb->get_col( $wpdb->prepare(
			"SELECT timestamp FROM $logs_table WHERE url = %s ORDER BY timestamp DESC LIMIT 5",
			$url
		) );

		if ( count( $timestamps ) < 2 ) return "Too little data";

		$intervals = [];
		for ( $i = 0; $i < count( $timestamps ) - 1; $i++ ) {
			$intervals[] = strtotime( $timestamps[$i] ) - strtotime( $timestamps[$i+1] );
		}

		$avg_interval = array_sum( $intervals ) / count( $intervals );
		$next_crawl = strtotime( $timestamps[0] ) + $avg_interval;

		return date( 'Y-m-d H:m', $next_crawl );
	}

	/**
	 * Feature 7: Crawl Budget Simulator
	 */
	public function get_crawl_budget_simulator( $pattern = '/tag/' ) {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$total_hits = $wpdb->get_var( "SELECT COUNT(*) FROM $logs_table" );
		$pattern_hits = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $logs_table WHERE url LIKE %s", '%' . $pattern . '%' ) );

		if ( ! $total_hits ) return 0;

		return [
			'saved_percentage' => round( ( $pattern_hits / $total_hits ) * 100, 1 ),
			'saved_units' => $pattern_hits
		];
	}

	/**
	 * Feature 7: Crawl ROI Analyzer
	 */
	public function get_crawl_roi_stats() {
		global $wpdb;
		$logs_table = Database::get_table_name();

		return $wpdb->get_results(
			"SELECT post_type, COUNT(*) as bot_hits,
			CASE 
				WHEN post_type = 'product' THEN 'High'
				WHEN post_type IN ('post', 'page') THEN 'Medium'
				ELSE 'Low'
			END as roi_value
			FROM $logs_table 
			GROUP BY post_type 
			ORDER BY bot_hits DESC"
		);
	}

	/**
	 * Feature 7: Crawl vs Traffic Analyzer
	 */
	public function get_crawl_vs_traffic() {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$date = date( 'Y-m-d H:i:s', strtotime( '-14 days' ) );

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT 
				DATE(timestamp) as date,
				SUM(CASE WHEN bot_type = 'Human' THEN 1 ELSE 0 END) as human_hits,
				SUM(CASE WHEN bot_type != 'Human' THEN 1 ELSE 0 END) as bot_hits
			FROM $logs_table 
			WHERE timestamp > %s
			GROUP BY DATE(timestamp)
			ORDER BY date ASC",
			$date
		) );
	}

	/**
	 * Feature 6: Rendering Monitor (Resource Ratio)
	 */
	public function get_resource_to_page_ratio() {
		global $wpdb;
		$logs_table = Database::get_table_name();
		
		// Logic: Ratio of static asset requests (.js, .css) to page requests (.html/root) per bot
		return $wpdb->get_results(
			"SELECT bot_type,
			SUM(CASE WHEN url LIKE '%.js' OR url LIKE '%.css' THEN 1 ELSE 0 END) as asset_hits,
			SUM(CASE WHEN url NOT LIKE '%.js' AND url NOT LIKE '%.css' AND url NOT LIKE '%.png' THEN 1 ELSE 0 END) as page_hits
			FROM $logs_table 
			WHERE bot_type != 'Human'
			GROUP BY bot_type"
		);
	}

	public function get_crawl_budget_score() {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$date = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );

		$stats = $wpdb->get_row( $wpdb->prepare(
			"SELECT 
				COUNT(*) as total_hits,
				SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_hits,
				AVG(response_time) as avg_time,
				SUM(CASE WHEN is_parameterized = 1 THEN 1 ELSE 0 END) as param_hits
			FROM $logs_table 
			WHERE timestamp > %s",
			$date
		) );

		if ( ! $stats || ! $stats->total_hits ) {
			return 0;
		}

		$score = 100;
		
		// Penalty for errors (each 1% error rate = -5 points)
		$error_rate = ( $stats->error_hits / $stats->total_hits ) * 100;
		$score -= ( $error_rate * 5 );

		// Penalty for slow response (each 100ms over 500ms = -5 points)
		if ( $stats->avg_time > 0.5 ) {
			$score -= ( ( $stats->avg_time - 0.5 ) * 500 ); // More realistic penalty
		}

		// Penalty for crawl waste (parameters) (each 10% waste = -10 points)
		$waste_rate = ( $stats->param_hits / $stats->total_hits ) * 100;
		$score -= ( $waste_rate );

		return max( 0, min( 100, (int) $score ) );
	}

	public function get_alerts() {
		global $wpdb;
		$logs_table = Database::get_table_name();
		$yesterday = date( 'Y-m-d H:i:s', strtotime( '-24 hours' ) );
		$prev_window = date( 'Y-m-d H:i:s', strtotime( '-48 hours' ) );

		$alerts = [];

		// Alert 1: Crawl Rate Drop (> 50%) - Feature 12
		$hits_24h = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $logs_table WHERE timestamp > %s AND bot_type != 'Human'", $yesterday ) );
		$hits_prev_24h = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $logs_table WHERE timestamp BETWEEN %s AND %s AND bot_type != 'Human'", $prev_window, $yesterday ) );

		if ( $hits_prev_24h > 100 && $hits_24h < ( $hits_prev_24h * 0.5 ) ) {
			$alerts[] = [
				'type'    => 'critical',
				'message' => 'Crawl rate dropped by more than 50% in the last 24 hours! Check for indexability issues.',
			];
		}

		// Alert 2: 5xx Spikes - Feature 12
		$status_5xx = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $logs_table WHERE status_code >= 500 AND timestamp > %s AND bot_type != 'Human'", $yesterday ) );
		if ( $status_5xx > 20 ) {
			$alerts[] = [
				'type'    => 'critical',
				'message' => "Sudden spike in 5xx server errors for bots ($status_5xx hits). Server may be overloaded or misconfigured.",
			];
		}

		// Alert 3: Fake Bot Detected - Feature 11
		$fake_bots = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $logs_table WHERE is_verified_bot = 0 AND bot_type IN ('Googlebot', 'Bingbot') AND timestamp > %s", $yesterday ) );
		if ( $fake_bots > 50 ) {
			$alerts[] = [
				'type'    => 'warning',
				'message' => "High volume of unverified 'Fake Bots' detected ($fake_bots hits). Possible scrapers spoofing User-Agents.",
			];
		}

		return $alerts;
	}
}
