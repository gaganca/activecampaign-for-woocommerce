<?php

/**
 * The plugin configuration file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.activecampaign.com/
 * @since             1.0.0
 * @package           Activecampaign_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       ActiveCampaign for WooCommerce
 * Plugin URI:        https://www.activecampaign.com/
 * Description:       Add Abandoned Cart functionality to your WooCommerce store using ActiveCampaign.
 * Version:           1.2.1
 * Author:            ActiveCampaign
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       activecampaign-for-woocommerce
 * Domain Path:       /languages
 */

use DI\Container;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Fix usage of is_ssl()
// See https://codex.wordpress.org/Function_Reference/is_ssl
if (
	! isset( $_SERVER['HTTPS'] )
	&& isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] )
	&& 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO']
) {
	$_SERVER['HTTPS'] = 'on';
}

require_once __DIR__ . '/includes/config/activecampaign-for-woocommerce-bootstrap.php';

/**
 * There are some WordPress core functions that require knowing the full path to this file, referred to
 * as the 'plugin base name'. We define this here so that we can properly access the value elsewhere.
 */
if ( ! defined( 'ACTIVECAMPAIGN_FOR_WOOCOMMERCE_PLUGIN_BASE_NAME' ) ) {
	define( 'ACTIVECAMPAIGN_FOR_WOOCOMMERCE_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activecampaign-for-woocommerce-activator.php
 *
 * @since 1.0.0
 */
function activecampaign_for_woocommerce_activate() {
	$activator = activecampaign_for_woocommerce_build_container()->get( Activecampaign_For_Woocommerce_Activator::class );
	$activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-activecampaign-for-woocommerce-deactivator.php
 *
 * @since 1.0.0
 */
function activecampaign_for_woocommerce_deactivate() {
	$deactivator = new Activecampaign_For_Woocommerce_Deactivator();
	$deactivator->deactivate();
}

register_activation_hook( __FILE__, 'activecampaign_for_woocommerce_activate' );
register_deactivation_hook( __FILE__, 'activecampaign_for_woocommerce_deactivate' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 *
 * @param Container $container The PHP DI container instance.
 */
function activecampaign_for_woocommerce_run( Container $container ) {
	$plugin = $container->get( Activecampaign_For_Woocommerce::class );
	$plugin->run();
}

/**
 * PHPUnit Autoloads this file. In doing so, it actually runs the run() function.
 * This causes a pollution of the global scope, registering actions and etc. The PHPUnit
 * bootstrap file sets TESTING=1 to the environment to disable this.
 */
if ( ! getenv( 'TESTING' ) ) {
	activecampaign_for_woocommerce_run( activecampaign_for_woocommerce_build_container() );
}
