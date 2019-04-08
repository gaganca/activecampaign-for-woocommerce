<?php

/**
 * The file that defines the Executable Command Interface.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.1.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/commands
 */

use Activecampaign_For_Woocommerce_Admin as Admin;
use Activecampaign_For_Woocommerce_Ecom_Customer as Ecom_Customer;
use Activecampaign_For_Woocommerce_Ecom_Customer_Repository as Ecom_Customer_Repository;
use Activecampaign_For_Woocommerce_Ecom_Order_Factory as Ecom_Order_Factory;
use Activecampaign_For_Woocommerce_Ecom_Order_Repository as Ecom_Order_Repository;
use Activecampaign_For_Woocommerce_User_Meta_Service as User_Meta_Service;
use Activecampaign_For_Woocommerce_Logger as Logger;
use AcVendor\GuzzleHttp\Exception\GuzzleException;

/**
 * Handles sending the guest customer and pending order to AC.
 * When the email input field on the checkout page is changed,
 * an Ajax request will run the execute method.
 *
 * @since      1.1.0
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/commands
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
class Activecampaign_For_Woocommerce_Sync_Guest_Abandoned_Cart_Command implements Activecampaign_For_Woocommerce_Executable_Interface {

	/**
	 * The WC Cart
	 *
	 * @var WC_Cart
	 */
	public $cart;

	/**
	 * The WC Customer
	 *
	 * @var WC_Customer
	 */
	public $customer;

	/**
	 * The Ecom Order Factory
	 *
	 * @var Ecom_Order_Factory
	 */
	public $factory;

	/**
	 * The Ecom Order Repo
	 *
	 * @var Activecampaign_For_Woocommerce_Ecom_Order_Repository
	 */
	public $order_repository;

	/**
	 * The Ecom Customer Repo
	 *
	 * @var Activecampaign_For_Woocommerce_Ecom_Customer_Repository
	 */
	public $customer_repository;

	/**
	 * The Admin object
	 *
	 * @var Activecampaign_For_Woocommerce_Admin
	 */
	private $admin;

	/**
	 * The guest customer email address
	 *
	 * @var string
	 */
	private $customer_email;

	/**
	 * The WooCommerce customer object
	 *
	 * @var WC_Customer
	 */
	private $customer_woo;

	/**
	 * The resulting existing or newly created AC ecom customer
	 *
	 * @var Ecom_Model
	 */
	private $customer_ac;

	/**
	 * Hash of the WooCommerce session ID plus the guest customer email.
	 * Used to identify an order as being created in a pending state.
	 *
	 * @var string
	 */
	private $external_checkout_id;

	/**
	 * The native ecom order object used to
	 * create or update an order in AC
	 *
	 * @var Ecom_Order
	 */
	private $ecom_order;

	/**
	 * The resulting existing or newly created AC ecom order
	 *
	 * @var Ecom_Model
	 */
	private $order_ac;

	/**
	 * Whether or not the AC order exists.
	 * Used to determine whether or not
	 * we want to update the AC order (PUT request).
	 *
	 * @var boolean
	 */
	private $order_ac_exists = false;

	/**
	 * Activecampaign_For_Woocommerce_Sync_Guest_Abandoned_Cart_Command constructor.
	 *
	 * @param WC_Cart|null                              $cart The WC Cart.
	 * @param WC_Customer|null                          $customer The WC Customer.
	 * @param WC_Session|null                           $wc_session The WC Session.
	 * @param Activecampaign_For_Woocommerce_Admin|null $admin The admin object.
	 * @param Ecom_Order_Factory                        $factory The Ecom Order Factory.
	 * @param Ecom_Order_Repository                     $order_repository The Ecom Order Repo.
	 * @param Ecom_Customer_Repository|null             $customer_repository The Ecom Customer Repo.
	 * @param Logger                                    $logger The ActiveCampaign WooCommerce logger.
	 */
	public function __construct(
		WC_Cart $cart = null,
		WC_Customer $customer = null,
		WC_Session $wc_session = null,
		Admin $admin,
		Ecom_Order_Factory $factory,
		Ecom_Order_Repository $order_repository,
		Ecom_Customer_Repository $customer_repository,
		Logger $logger = null
	) {
		$this->cart                = $cart;
		$this->customer            = $customer;
		$this->wc_session          = $wc_session;
		$this->admin               = $admin;
		$this->factory             = $factory;
		$this->order_repository    = $order_repository;
		$this->customer_repository = $customer_repository;
		$this->logger              = $logger;
	}

	/**
	 * Initialize injections that are still null
	 */
	public function init() {
		$this->cart       = $this->cart ?: wc()->cart;
		$this->customer   = $this->customer ?: wc()->customer;
		$this->wc_session = $this->wc_session ?: wc()->session;
		$this->logger     = $this->logger ?: new Logger();
	}

	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
	/**
	 * Execute this command.
	 *
	 * @param mixed ...$args The array of parameters passed.
	 * @return boolean Whether or not the command was successful
	 */
	public function execute( ...$args ) {
		$this->init();

		if (
			! $this->validate_request() ||
			! $this->setup_woocommerce_customer() ||
			! $this->find_or_create_ac_customer() ||
			! $this->setup_woocommerce_cart() ||
			! $this->setup_ecom_order()
		) {
			return false;
		}

		$find_or_create_ac_order = $this->find_or_create_ac_order();

		if ( 1 === $find_or_create_ac_order ) {
			// Existing order found in AC, try to update it
			return $this->update_ac_order();
		}

		if ( ! $find_or_create_ac_order ) {
			// 0 was returned, meaning some kind of exception
			return false;
		}

		/**
		 * If $find_or_create_ac_order === 2,
		 * it's a newly created order so return true
		 * since this is the final step.
		 */

		return true;
	}
	// phpcs:enable

	/**
	 * Generate the externalcheckoutid hash which
	 * is used to tie together pending and complete
	 * orders in Hosted (so we don't create duplicate orders).
	 *
	 * @param string $wc_session_hash The unique WooCommerce cart session ID.
	 * @param string $billing_email The guest customer's email address.
	 * @return string The hash used as the externalcheckoutid value
	 */
	public static function generate_externalcheckoutid( $wc_session_hash, $billing_email ) {
		return md5( $wc_session_hash . $billing_email );
	}

	/**
	 * Validate that the request has all necessary data
	 *
	 * @return bool Whether or not this job was successful
	 */
	private function validate_request() {
		if ( is_user_logged_in() ) {
			return false;
		}

		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'sync_guest_abandoned_cart_nonce' ) ) {
			$this->logger->debug( 'sync_guest_abandoned_cart_nonce failed' );
			return false;
		}

		$this->customer_email = sanitize_email( $_REQUEST['email'] );

		if ( ! $this->customer_email ) {
			$this->logger->debug( 'invalid customer email' );
			return false;
		}

		return true;
	}

	/**
	 * Set up the WooCommerce customer object
	 * with the customer's email address
	 *
	 * @return bool Whether or not this job was successful
	 */
	private function setup_woocommerce_customer() {
		// Obtain WooCommerce customer model
		$this->customer_woo = $this->customer;

		if ( ! ( $this->customer_woo instanceof WC_Customer ) ) {
			$this->logger->debug( 'customer_woo not an instance of WC_Customer' );
			return false;
		}

		$this->customer_woo->set_email( $this->customer_email );

		return true;
	}

	/**
	 * Lookup ecom customer record in AC. If it does not exist, create it.
	 *
	 * @return bool Whether or not this job was successful
	 */
	private function find_or_create_ac_customer() {
		$this->customer_ac = null;
		$connection_id     = $this->admin->get_storage()['connection_id'];

		try {
			// Try to find the customer in AC
			$this->customer_ac = $this->customer_repository->find_by_email_and_connection_id( $this->customer_email, $connection_id );
		} catch ( Activecampaign_For_Woocommerce_Resource_Not_Found_Exception $e ) {
			// Customer does not exist in AC yet
			// Set up AC customer model
			$new_customer = new Ecom_Customer();
			$new_customer->set_email( $this->customer_email );
			$new_customer->set_connectionid( $connection_id );

			try {
				// Try to create the new customer in AC
				$this->customer_ac = $this->customer_repository->create( $new_customer );
			} catch ( Exception $e ) {
				$this->logger->debug(
					'guest customer creation exception: ' . $e->getMessage()
				);
				return false;
			}
		} catch ( Exception $e ) {
			$this->logger->debug(
				'guest find customer exception: ' . $e->getMessage()
			);
			return false;
		}

		if ( ! $this->customer_ac ) {
			$this->logger->debug( 'invalid AC customer' );
			return false;
		}

		return true;
	}

	/**
	 * Set up the WooCommerce cart object with the checkout ID
	 *
	 * @return bool Whether or not this job was successful
	 */
	private function setup_woocommerce_cart() {
		if ( ! ( $this->cart instanceof WC_Cart ) ) {
			$this->logger->debug( 'cart not an instance of WC_Cart' );
			return false;
		}

		$this->external_checkout_id = self::generate_externalcheckoutid(
			$this->wc_session->get_customer_id(),
			$this->customer_email
		);

		return true;
	}

	/**
	 * Set up the ecom order with necessary data
	 *
	 * @return bool This job was successful
	 */
	private function setup_ecom_order() {
		// Create the order object
		$this->ecom_order = $this->factory->from_woocommerce( $this->cart, $this->customer_woo );

		$this->ecom_order->set_externalcheckoutid( $this->external_checkout_id );
		$this->ecom_order->set_customerid( $this->customer_ac->get_id() );

		return true;
	}

	/**
	 * Lookup ecom customer record in AC. If it does not exist, create it.
	 * The end goal is to have a valid AC order
	 * either already existing in AC or newly created.
	 *
	 * @return int Status on what happened:
	 *             0 = Failure of some kind
	 *             1 = Existing order found in AC
	 *             2 = New order successfully created in AC
	 */
	private function find_or_create_ac_order() {
		$this->order_ac = null;

		try {
			// Try to find the order by it's externalcheckoutid
			$this->order_ac = $this->order_repository->find_by_externalcheckoutid( $this->external_checkout_id );
		} catch ( Activecampaign_For_Woocommerce_Resource_Not_Found_Exception $e ) {
			// Order does not exist in AC yet
			try {
				// Try to create the new order in AC
				$this->order_ac = $this->order_repository->create( $this->ecom_order );
				return 2;
			} catch ( Exception $e ) {
				$this->logger->debug(
					'guest order creation exception: ' . $e->getMessage()
				);
				return 0;
			}
		} catch ( Exception $e ) {
			$this->logger->debug(
				'guest find order exception: ' . $e->getMessage()
			);
			return 0;
		}

		if ( ! $this->order_ac ) {
			$this->logger->debug( 'invalid AC order' );
			return 0;
		}

		return 1;
	}

	/**
	 * Update the existing ecom order in AC
	 *
	 * @return bool Whether or not this job was successful
	 */
	private function update_ac_order() {
		$this->ecom_order->set_id( $this->order_ac->get_id() );

		try {
			$this->order_repository->update( $this->ecom_order );
			return true;
		} catch ( Exception $e ) {
			$this->logger->debug(
				'guest order update exception: ' . $e->getMessage()
			);
			return false;
		}

		return true;
	}
}
