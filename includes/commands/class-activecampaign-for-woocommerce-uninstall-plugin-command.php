<?php

/**
 * The file that defines the Uninstall_Plugin_Command Class.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/commands
 */

use Activecampaign_For_Woocommerce_Executable_Interface as Executable;
use Activecampaign_For_Woocommerce_User_Meta_Service as User_Meta_Service;

/**
 * The Uninstall_Plugin_Command Class.
 *
 * This command is called when uninstalling the plugin and handled erasing all plugin-specific data.
 *
 * @since      1.0.0
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/commands
 * @author     Joshua Bartlett <jbartlett@activecampaign.com>
 */
class Activecampaign_For_Woocommerce_Uninstall_Plugin_Command implements Executable {
	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
	/**
	 * Executes the command.
	 *
	 * Erases all plugin specific data from the database.
	 *
	 * @param mixed ...$args An array of arguments that may be passed in from the action/filter called.
	 *
	 * @since 1.0.0
	 */
	public function execute( ...$args ) {
		delete_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_DB_OPTION_NAME );
		delete_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_DB_STORAGE_NAME );

		User_Meta_Service::delete_all_user_meta();
	}
	// phpcs:enable
}
