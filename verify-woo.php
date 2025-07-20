<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://parsamirzaie.com
 * @since             1.0.0
 * @package           Verify_Woo
 *
 * @wordpress-plugin
 * Plugin Name:       VerifyWoo
 * Plugin URI:        https://#
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Parsa Mirzaie
 * Author URI:        https://parsamirzaie.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       verify-woo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'VERIFY_WOO_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-verify-woo-activator.php
 */
function activate_verify_woo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-verify-woo-activator.php';
	Verify_Woo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-verify-woo-deactivator.php
 */
function deactivate_verify_woo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-verify-woo-deactivator.php';
	Verify_Woo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_verify_woo' );
register_deactivation_hook( __FILE__, 'deactivate_verify_woo' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-verify-woo.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_verify_woo() {

	$plugin = new Verify_Woo();
	$plugin->run();
}
run_verify_woo();
