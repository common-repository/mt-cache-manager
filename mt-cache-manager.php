<?php
/**
 * Plugin Name:       MT Cache Manager
 * Plugin URI:        https://motostorie.blog/
 * Description:       Cleans nginx's proxy cache whenever a post is edited/published.
 * Version:           1.1.1
 * Author:            camaran
 * Author URI:        https://profiles.wordpress.org/camaran/
 * Text Domain:       mt-cache-manager
 * Requires at least: 5.7
 * Tested up to:      5.9
 *
 * @link              https://motostorie.blog/
 *
 * @package           mt-cache-manager
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! defined( 'MT_CACHE_MANAGER_BASEURL' ) ) {
	define( 'MT_CACHE_MANAGER_BASEURL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'MT_CACHE_MANAGER_BASENAME' ) ) {
	define( 'MT_CACHE_MANAGER_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'MT_CACHE_MANAGER_BASEPATH' ) ) {
	define( 'MT_CACHE_MANAGER_BASEPATH', plugin_dir_path( __FILE__ ) );
}

function activate_mt_cache_manager() {
	require_once MT_CACHE_MANAGER_BASEPATH . 'includes/class-mt-cache-manager-activator.php';
	MT_Cache_Manager_Activator::activate();
}
function deactivate_mt_cache_manager() {
	require_once MT_CACHE_MANAGER_BASEPATH . 'includes/class-mt-cache-manager-deactivator.php';
	MT_Cache_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mt_cache_manager' );
register_deactivation_hook( __FILE__, 'deactivate_mt_cache_manager' );
require MT_CACHE_MANAGER_BASEPATH . 'includes/class-mt-cache-manager.php';

function run_mt_cache_manager() {

	global $mt_cache_manager;

	$mt_cache_manager = new MT_Cache_Manager();
	$mt_cache_manager->run();
	
	// Load WP-CLI command.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {

		require_once MT_CACHE_MANAGER_BASEPATH . 'includes/class-mt-cache-manager-wp-cli-command.php';
		\WP_CLI::add_command( 'mt-cache-manager', 'MT_Cache_Manager_WP_CLI_Command' );

	}	

}
run_mt_cache_manager();
