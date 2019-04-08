<?php

/**
 * The file that defines the Executable Command Interface.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/commands
 */

use Activecampaign_For_Woocommerce_Admin as Admin;
use Activecampaign_For_Woocommerce_Ecom_Customer_Repository as Ecom_Customer_Repository;
use Activecampaign_For_Woocommerce_Ecom_Order_Factory as Ecom_Order_Factory;
use Activecampaign_For_Woocommerce_Ecom_Order_Repository as Ecom_Order_Repository;
use Activecampaign_For_Woocommerce_User_Meta_Service as User_Meta_Service;
use AcVendor\GuzzleHttp\Exception\GuzzleException;

use AcVendor\Psr\Log\LoggerInterface;

/**
 * Send the cart and its products to ActiveCampaign for the given customer.
 *
 * @since      1.0.0
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/commands
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
class Activecampaign_For_Woocommerce_Update_Cart_Command implements Activecampaign_For_Woocommerce_Executable_Interface {

	/**
	 * The WC Cart
	 *
	 * @var WC_Cart
	 */
	private $cart;
	/**
	 * The WC Customer
	 *
	 * @var WC_Customer
	 */
	private $customer;
	/**
	 * The Ecom Order Factory
	 *
	 * @var Ecom_Order_Factory
	 */
	private $factory;
	/**
	 * The Ecom Order Repo
	 *
	 * @var Activecampaign_For_Woocommerce_Ecom_Order_Repository
	 */
	private $order_repository;
	/**
	 * The Ecom Customer Repo
	 *
	 * @var Activecampaign_For_Woocommerce_Ecom_Customer_Repository
	 */
	private $customer_repository;

	/**
	 * The Admin object
	 *
	 * @var Activecampaign_For_Woocommerce_Admin
	 */
	private $admin;

	/**
	 * The logger interface.
	 *
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * Activecampaign_For_Woocommerce_Update_Cart_Command constructor.
	 *
	 * @param WC_Cart|null                              $cart The WC Cart.
	 * @param WC_Customer|null                          $customer The WC Customer.
	 * @param Activecampaign_For_Woocommerce_Admin|null $admin The admin object.
	 * @param Ecom_Order_Factory                        $factory The Ecom Order Factory.
	 * @param Ecom_Order_Repository                     $order_repository The Ecom Order Repo.
	 * @param Ecom_Customer_Repository|null             $customer_repository The Ecom Customer Repo.
	 * @param LoggerInterface                           $logger The logger interface.
	 */
	public function __construct(
		WC_Cart $cart = null,
		WC_Customer $customer = null,
		Admin $admin,
		Ecom_Order_Factory $factory,
		Ecom_Order_Repository $order_repository,
		Ecom_Customer_Repository $customer_repository,
		LoggerInterface $logger = null
	) {
		$this->cart                = $cart;
		$this->customer            = $customer;
		$this->factory             = $factory;
		$this->order_repository    = $order_repository;
		$this->customer_repository = $customer_repository;
		$this->admin               = $admin;
		$this->logger              = $logger;
	}

	/**
	 * Initialize injections that are still null
	 */
	public function init() {
		// calling wc in the constructor causes an exception, since the object is not ready yet
		$this->cart     = $this->cart ?: wc()->cart;
		$this->customer = $this->customer ?: wc()->customer;
	}

	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter

	/**
	 * {@inheritDoc}
	 *
	 * @param mixed ...$args The array of parameters passed.
	 */
	public function execute( ...$args ) {
		$this->init();

		// If the customer is not logged in, there is nothing to do
		if ( ! ( $this->customer instanceof WC_Customer ) || $this->customer->get_email() === null ) {
			return;
		}

		// First, make sure we have the ID for the ActiveCampaign customer record
		if ( ! $this->verify_ac_customer_id( $this->customer->get_id() ) ) {
			$this->logger->info( 'Create and save cart id: Missing id for ActiveCampaign customer record.' );

			return;
		}

		// Create the order object
		$order = $this->factory->from_woocommerce( $this->cart, $this->customer );

		// If we already have an AC ID, then this is an update. Otherwise, it's a create.
		try {
			if ( $order->get_id() ) {
				$this->order_repository->update( $order );
			} else {
				/**
				 * The new order
				 *
				 * @var Activecampaign_For_Woocommerce_Ecom_Order $new_order
				 */
				$new_order = $this->order_repository->create( $order );
				User_Meta_Service::set_current_cart_ac_id( $this->customer->get_id(), $new_order->get_id() );
			}
		} catch ( \Exception $e ) {
			/**
			 * We have seen issues for a few users of this plugin where either the create or update call throws
			 * an exception, which ends up breaking their store. This try/catch is a stop-gap measure for now.
			 */

			$message     = $e->getMessage();
			$stack_trace = $e->getTrace();
			$this->logger->error( $message, [ 'stack trace' => $stack_trace ] );
		}
	}
	// phpcs:enable

	/**
	 * Try and find the AC customer ID in the local DB. If not found, create the customer
	 * in AC and save the ID the DB.
	 *
	 * @param int $user_id The WordPress User ID.
	 *
	 * @return bool
	 */
	private function verify_ac_customer_id( $user_id ) {
		// Nothing to do if we already have the AC customer ID.
		if ( ! empty( User_Meta_Service::get_current_user_ac_customer_id( $user_id ) ) ) {
			return true;
		}

		try {
			/**
			 * The customer object from hosted
			 *
			 * @var Activecampaign_For_Woocommerce_Ecom_Customer $customer_from_hosted
			 */
			$ecom_customer = $this->customer_repository->find_by_email_and_connection_id( $this->customer->get_email(), $this->admin->get_storage()['connection_id'] );
		} catch ( GuzzleException $e ) {
			$message     = $e->getMessage();
			$stack_trace = $e->getTrace();
			$this->logger->error( $message, [ 'stack trace' => $stack_trace ] );

			return false;
		} catch ( \Exception $e ) {
			$message     = $e->getMessage();
			$stack_trace = $e->getTrace();
			$this->logger->error( $message, [ 'stack trace' => $stack_trace ] );

			return false;
		}

		User_Meta_Service::set_current_user_ac_customer_id( $user_id, $ecom_customer->get_id() );

		return true;
	}
}
