<?php
/**
 * Plugin Name: WP Crawl Intelligence
 * Description: Advanced real-time bot tracking, crawl budget analysis, and SEO intelligence for WordPress.
 * Version: 1.0.0
 * Author: Antigravity Architect
 * Text Domain: wp-crawl-intelligence
 * Requires PHP: 8.0
 * Requires at least: 5.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define( 'WPCI_VERSION', '1.0.0' );
define( 'WPCI_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPCI_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCI_BASENAME', plugin_basename( __FILE__ ) );

// Registration Hooks
function wpci_activate() {
	require_once WPCI_PATH . 'includes/class-database.php';
	\WPCI\Includes\Database::create_tables();
	
	require_once WPCI_PATH . 'includes/class-cron.php';
	\WPCI\Includes\Cron::schedule_events();
}
register_activation_hook( __FILE__, 'wpci_activate' );

function wpci_deactivate() {
	require_once WPCI_PATH . 'includes/class-cron.php';
	\WPCI\Includes\Cron::clear_scheduled_events();
}
register_deactivation_hook( __FILE__, 'wpci_deactivate' );

// Autoloader
spl_autoload_register( function ( $class ) {
	$prefix = 'WPCI\\';
	$base_dir = WPCI_PATH . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	
	// Convert namespace to file path: WPCI\Includes\Logger -> includes/class-logger.php
	$parts = explode( '\\', $relative_class );
	$file = 'class-' . strtolower( str_replace( '_', '-', end( $parts ) ) ) . '.php';
	
	// If it's in a sub-namespace, we'd need to handle subdirectories, 
	// but based on requested structure, everything is in includes/
	$path = $base_dir . $file;

	if ( file_exists( $path ) ) {
		require_once $path;
	}
} );

// Initialize Plugin
add_action( 'plugins_loaded', function() {
	new \WPCI\Includes\Loader();
} );
