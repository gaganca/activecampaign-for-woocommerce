<?php

/**
 * The file that defines the Add_Accepts_Marketing_To_Customer_Meta_Command Class.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/commands
 */

use Activecampaign_For_Woocommerce_Executable_Interface as Executable;
use Activecampaign_For_Woocommerce_User_Meta_Service as User_Meta_Service;

use AcVendor\Psr\Log\LoggerInterface;

/**
 * The Add_Accepts_Marketing_To_Customer_Meta_Command Class.
 *
 * This command is called when a cart is transitioning to an order, allowing us to
 * take our persistent cart id and add it to the meta table for the order.
 *
 * @since      1.0.0
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/commands
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
class Activecampaign_For_Woocommerce_Add_Accepts_Marketing_To_Customer_Meta_Command implements Executable {

	/**
	 * The Logger interface.
	 *
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * Activecampaign_For_Woocommerce_Add_Accepts_Marketing_To_Customer_Meta_Command constructor.
	 *
	 * @param LoggerInterface $logger The Logger interface.
	 */
	public function __construct( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Executes the command.
	 *
	 * Checks if the user is logged in. If so, and there's a persistent cart,
	 * saves that cart id to the order meta table.
	 *
	 * If not, saves the accepts marketing value in the order meta table, so it can
	 * still be saved on the eventual customer that will be created for the order.
	 *
	 * @param mixed ...$args An array of arguments that may be passed in from the action/filter called.
	 *
	 * @return WC_Order
	 */
	public function execute( ...$args ) {
		/**
		 * An instance of the WC_Order class being passed through the filter.
		 *
		 * @var WC_Order $order
		 */
		$order = $args[0];

		if ( ! $this->nonce_is_valid() ) {
			$this->logger->debug( 'Invalid checkout nonce' );

			return $order;
		}

		$accepts_marketing = $this->extract_accepts_marketing_value();

		$id = $order->get_customer_id();

		// If a customer is logged in, set the value of accepts marketing on the user meta-data.
		// For guest checkouts set it on the order meta-data.
		if ( ! $id ) {
			$this->update_order_accepts_marketing( $order, $accepts_marketing );
		} else {
			$this->update_user_accepts_marketing( $id, $accepts_marketing );
		}

		return $order;
	}

	/**
	 * Validates that the nonce for the request is valid.
	 *
	 * @return bool
	 */
	private function nonce_is_valid() {
		return (bool) wp_verify_nonce(
			$_POST['woocommerce-process-checkout-nonce'],
			'woocommerce-process_checkout'
		);
	}

	/**
	 * Extracts the value of the accepts marketing checkbox.
	 *
	 * @return int
	 */
	private function extract_accepts_marketing_value() {
		return isset( $_POST['activecampaign_for_woocommerce_accepts_marketing'] ) ?
			(int) $_POST['activecampaign_for_woocommerce_accepts_marketing'] :
			0;
	}

	/**
	 * Updates the user meta for the customer with their preference of marketing.
	 *
	 * Additionally, triggers the customer updated action, which will fire off a
	 * customer updated webhook.
	 *
	 * @param int $id The id of the user to be updated.
	 * @param int $accepts_marketing The value of the meta field to be updated.
	 */
	private function update_user_accepts_marketing( $id, $accepts_marketing ) {
		User_Meta_Service::set_user_accepts_marketing( $id, $accepts_marketing );

		// phpcs:disable
		// The linter definitions don't like that we're invoking another plugin's actions
		do_action( 'woocommerce_update_customer', $id );
		// phpcs:enable
	}

	/**
	 * Update the order's metadata with the accepts marketing value so it is included in the webhook
	 *
	 * @param WC_Order $order The order.
	 * @param int      $accepts_marketing Value of the checkbox.
	 */
	private function update_order_accepts_marketing( $order, $accepts_marketing ) {
		$order->update_meta_data(
			User_Meta_Service::ACTIVECAMPAIGN_ACCEPTS_MARKETING,
			$accepts_marketing
		);
	}
}
