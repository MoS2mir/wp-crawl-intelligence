<?php
/**
 * Uninstall Plugin File
 * 
 * This file is called when the plugin is deleted.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Table cleanup
$table_logs = $wpdb->prefix . 'wpci_logs';
$table_stats = $wpdb->prefix . 'wpci_stats';

$wpdb->query( "DROP TABLE IF EXISTS $table_logs" );
$wpdb->query( "DROP TABLE IF EXISTS $table_stats" );

// Options cleanup
delete_option( 'wpci_version' );
delete_option( 'wpci_settings' );

// Transients cleanup
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpci_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wpci_%'" );
