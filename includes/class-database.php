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

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			url text NOT NULL,
			ip varchar(45) NOT NULL,
			user_agent text NOT NULL,
			method varchar(10) NOT NULL,
			status_code int(3) DEFAULT NULL,
			response_time decimal(10,5) DEFAULT NULL,
			referer text DEFAULT NULL,
			bot_type varchar(32) DEFAULT NULL,
			is_verified_bot tinyint(1) DEFAULT 0,
			timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY ip (ip),
			KEY bot_type (bot_type),
			KEY status_code (status_code),
			KEY timestamp (timestamp)
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
		dbDelta( $sql_stats );
	}

	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wpci_logs';
	}

	public static function get_stats_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'wpci_stats';
	}
}
