<?php

/**
 * @link              http://www.sprako.nl/wordpress
 * @since             1.0.0
 * @package           Resource_Allocator
 *
 * @wordpress-plugin
 * Plugin Name:       Resource Allocator
 * Plugin URI:        www.sprako.nl/wordpress/resource-allocator
 * Description:       Allows the users of your site to allocate resources easily by the hour or day. Show the allocations using a simple shortcode.
 * Version:           1.0.0
 * Author:            Eric Sprangers
 * Author URI:        http://www.sprako.nl/wordpress
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       resourceallocator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-resource-allocator-activator.php
 */
function activate_resource_allocator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-resource-allocator-activator.php';
	Resource_Allocator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-resource-allocator-deactivator.php
 */
function deactivate_resource_allocator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-resource-allocator-deactivator.php';
	Resource_Allocator_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_resource_allocator' );
register_deactivation_hook( __FILE__, 'deactivate_resource_allocator' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-resource-allocator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_resource_allocator() {

	$plugin = new Resource_Allocator();
	$plugin->run();

}
run_resource_allocator();
