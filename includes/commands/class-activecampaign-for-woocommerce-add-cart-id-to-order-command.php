<?php

/**
 * The file that defines the Add_Cart_Id_To_Order_Command Class.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/commands
 */

use Activecampaign_For_Woocommerce_Executable_Interface as Executable;
use Activecampaign_For_Woocommerce_User_Meta_Service as User_Meta_Service;
use Activecampaign_For_Woocommerce_Sync_Guest_Abandoned_Cart_Command as Sync_Guest_Abandoned_Cart_Command;

/**
 * The Add_Cart_Id_To_Order_Command Class.
 *
 * This command is called when a cart is transitioning to an order, allowing us to
 * take our persistent cart id and add it to the meta table for the order.
 *
 * @since      1.0.0
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/commands
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
class Activecampaign_For_Woocommerce_Add_Cart_Id_To_Order_Command implements Executable {
	/**
	 * Executes the command.
	 *
	 * Checks if the user is logged in. If so, and there's a persistent cart,
	 * saves that cart id to the order meta table.
	 *
	 * @param mixed ...$args An array of arguments that may be passed in from the action/filter called.
	 *
	 * @since 1.0.0
	 * @return WC_Order
	 */
	public function execute( ...$args ) {
		/**
		 * The WooCommerce Order object that's in-progress of being saved.
		 *
		 * @var WC_Order $order
		 */
		$order = $args[0];

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			$externalcheckoutid = Sync_Guest_Abandoned_Cart_Command::generate_externalcheckoutid(
				wc()->session->get_customer_id(),
				$order->get_billing_email()
			);

			$order->update_meta_data(
				ACTIVECAMPAIGN_FOR_WOOCOMMERCE_PERSISTANT_CART_ID_NAME,
				$externalcheckoutid
			);

			return $order;
		}

		/**
		 * Delete the local cache of Hosted's order/cart ID so it isn't used
		 * erroneously on the next order this user places.
		 */
		User_Meta_Service::delete_current_cart_ac_id( $user_id );

		$cart_id = User_Meta_Service::get_current_cart_id( $user_id );

		if ( ! $cart_id ) {
			return $order;
		}

		$order->update_meta_data( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_PERSISTANT_CART_ID_NAME, $cart_id );

		return $order;
	}
}
