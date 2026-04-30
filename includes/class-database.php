<?php
namespace WPCI\Includes;

class Database {
	public function __construct() {
		// Possibly handle updates or schema migrations here
	}

	public static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'wpci_logs';
		$buffer_table = $wpdb->prefix . 'wpci_buffer';

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			url text NOT NULL,
			url_hash char(32) NOT NULL,
			ip varchar(45) NOT NULL,
			user_agent text NOT NULL,
			method varchar(10) NOT NULL,
			status_code int(3) DEFAULT NULL,
			response_time decimal(10,5) DEFAULT NULL,
			referer text DEFAULT NULL,
			bot_type varchar(32) DEFAULT NULL,
			post_type varchar(32) DEFAULT NULL,
			content_length bigint(20) DEFAULT NULL,
			is_parameterized tinyint(1) DEFAULT 0,
			device_type varchar(16) DEFAULT NULL,
			is_sitemap_url tinyint(1) DEFAULT 0,
			is_verified_bot tinyint(1) DEFAULT 0,
			timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY url_hash (url_hash),
			KEY ip (ip),
			KEY bot_type (bot_type),
			KEY timestamp (timestamp)
		) $charset_collate;";

		$sql_buffer = "CREATE TABLE $buffer_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			data longtext NOT NULL,
			timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		// Table for raw stats aggregation
		$stats_table = $wpdb->prefix . 'wpci_stats';
		$sql_stats = "CREATE TABLE $stats_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			date date NOT NULL,
			bot_type varchar(32) NOT NULL,
			hit_count bigint(20) DEFAULT 0,
			avg_response_time decimal(10,5) DEFAULT 0,
			status_2xx bigint(20) DEFAULT 0,
			status_3xx bigint(20) DEFAULT 0,
			status_4xx bigint(20) DEFAULT 0,
			status_5xx bigint(20) DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY date_bot (date, bot_type)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		dbDelta( $sql_buffer );
		dbDelta( $sql_stats );
	}

	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wpci_logs';
	}

	public static function get_buffer_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wpci_buffer';
	}

	public static function get_stats_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wpci_stats';
	}
}
