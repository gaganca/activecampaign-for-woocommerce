<?php

/**
 * The file for the EcomOrder Model
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/models
 */

use Activecampaign_For_Woocommerce_Api_Serializable as Api_Serializable;
use Activecampaign_For_Woocommerce_Ecom_Model_Interface as Ecom_Model;
use Activecampaign_For_Woocommerce_Has_Id as Has_Id;
use Activecampaign_For_Woocommerce_Has_Email as Has_Email;

/**
 * The model class for the EcomOrder
 *
 * @since      1.0.0
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/models
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
class Activecampaign_For_Woocommerce_Ecom_Order implements Ecom_Model, Has_Id, Has_Email {
	use Api_Serializable {
		serialize_to_array as serialize_all_but_products_to_array;
		set_properties_from_serialized_array as set_all_but_products_as_properties_from_serialized_array;
	}

	/**
	 * The mappings for the Api_Serializable trait.
	 *
	 * @var array
	 */
	public $api_mappings = [
		'abandoned_date'        => 'abandonedDate',
		'connectionid'          => 'connectionid',
		'customerid'            => 'customerid',
		'currency'              => 'currency',
		'email'                 => 'email',
		'externalid'            => 'externalid',
		'externalcheckoutid'    => 'externalcheckoutid',
		'external_created_date' => 'externalCreatedDate',
		'external_updated_date' => 'externalUpdatedDate',
		'id'                    => 'id',
		'order_number'          => 'orderNumber',
		'source'                => 'source',
		'total_price'           => 'totalPrice',
		'total_products'        => 'totalProducts',
		'order_date'            => 'orderDate',
		'order_url'             => 'orderUrl',
	];

	/**
	 * The abandoned date.
	 *
	 * @var string
	 */
	private $abandoned_date;

	/**
	 * The connection id.
	 *
	 * @var string
	 */
	private $connectionid;

	/**
	 * The customer id.
	 *
	 * @var string
	 */
	private $customerid;

	/**
	 * The currency.
	 *
	 * @var string
	 */
	private $currency;

	/**
	 * The email.
	 *
	 * @var string
	 */
	private $email;

	/**
	 * The external id.
	 *
	 * @var string
	 */
	private $externalid;

	/**
	 * The external checkout id.
	 *
	 * @var string
	 */
	private $externalcheckoutid;

	/**
	 * The external created date.
	 *
	 * @var string
	 */
	private $external_created_date;

	/**
	 * The external updated date.
	 *
	 * @var string
	 */
	private $external_updated_date;

	/**
	 * The id.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * The order number.
	 *
	 * @var string
	 */
	private $order_number;

	/**
	 * An array of Order Products for this order.
	 *
	 * @var Activecampaign_For_Woocommerce_Ecom_Product[]
	 */
	private $order_products = [];

	/**
	 * The source (1 or 0).
	 *
	 * @var string
	 */
	private $source;

	/**
	 * The total price.
	 *
	 * @var string
	 */
	private $total_price;

	/**
	 * The total products.
	 *
	 * @var string
	 */
	private $total_products;

	/**
	 * The order date.
	 *
	 * @var string
	 */
	private $order_date;

	/**
	 * The order/cart url.
	 *
	 * @var string
	 */
	private $order_url;

	/**
	 * Returns the abandoned date.
	 *
	 * @return string
	 */
	public function get_abandoned_date() {
		return $this->abandoned_date;
	}

	/**
	 * Sets the abandoned date.
	 *
	 * @param string $abandoned_date The abandoned date.
	 */
	public function set_abandoned_date( $abandoned_date ) {
		$this->abandoned_date = $abandoned_date;
	}

	/**
	 * Returns the connection id.
	 *
	 * @return string
	 */
	public function get_connectionid() {
		return $this->connectionid;
	}

	/**
	 * Sets the connection id.
	 *
	 * @param string $connectionid The connection id.
	 */
	public function set_connectionid( $connectionid ) {
		$this->connectionid = $connectionid;
	}

	/**
	 * Returns the customer id.
	 *
	 * @return string
	 */
	public function get_customerid() {
		return $this->customerid;
	}

	/**
	 * Sets the customer id.
	 *
	 * @param string $customerid The customer id.
	 */
	public function set_customerid( $customerid ) {
		$this->customerid = $customerid;
	}

	/**
	 * Returns the currency.
	 *
	 * @return string
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * Sets the currency.
	 *
	 * @param string $currency The currency.
	 */
	public function set_currency( $currency ) {
		$this->currency = $currency;
	}

	/**
	 * Returns the email.
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Sets the email.
	 *
	 * @param string $email The email.
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Returns the external id.
	 *
	 * @return string
	 */
	public function get_externalid() {
		return $this->externalid;
	}

	/**
	 * Sets the external id.
	 *
	 * @param string $externalid The external id.
	 */
	public function set_externalid( $externalid ) {
		$this->externalid = $externalid;
	}

	/**
	 * Returns the external checkout id.
	 *
	 * @return string
	 */
	public function get_externalcheckoutid() {
		return $this->externalcheckoutid;
	}

	/**
	 * Sets the external checkout id.
	 *
	 * @param string $externalcheckoutid The external checkout id.
	 */
	public function set_externalcheckoutid( $externalcheckoutid ) {
		$this->externalcheckoutid = $externalcheckoutid;
	}

	/**
	 * Returns the external created date.
	 *
	 * @return string
	 */
	public function get_external_created_date() {
		return $this->external_created_date;
	}

	/**
	 * Sets the external created date.
	 *
	 * @param string $external_created_date The external created date.
	 */
	public function set_external_created_date( $external_created_date ) {
		$this->external_created_date = $external_created_date;
	}

	/**
	 * Returns the external updated date.
	 *
	 * @return string
	 */
	public function get_external_updated_date() {
		return $this->external_updated_date;
	}

	/**
	 * Sets the external updated date.
	 *
	 * @param string $external_updated_date The external updated date.
	 */
	public function set_external_updated_date( $external_updated_date ) {
		$this->external_updated_date = $external_updated_date;
	}

	/**
	 * Returns the id.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Sets the id.
	 *
	 * @param string $id The id.
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Returns the order number.
	 *
	 * @return string
	 */
	public function get_order_number() {
		return $this->order_number;
	}

	/**
	 * Sets the order number.
	 *
	 * @param string $order_number The order number.
	 */
	public function set_order_number( $order_number ) {
		$this->order_number = $order_number;
	}

	/**
	 * Returns the source (sync = 0 or webhook = 1).
	 *
	 * @return string
	 */
	public function get_source() {
		return $this->source;
	}

	/**
	 * Sets the source.
	 *
	 * @param string $source The source (1 or 0).
	 */
	public function set_source( $source ) {
		$this->source = $source;
	}

	/**
	 * Returns the total price.
	 *
	 * @return string
	 */
	public function get_total_price() {
		return $this->total_price;
	}

	/**
	 * Sets the total price.
	 *
	 * @param string $total_price The total price.
	 */
	public function set_total_price( $total_price ) {
		$this->total_price = (string) $total_price;
	}

	/**
	 * Set the order date
	 *
	 * @param string $date_time The order date.
	 */
	public function set_order_date( $date_time ) {
		$this->order_date = $date_time;
	}

	/**
	 * Get the order date
	 *
	 * @return string
	 */
	public function get_order_date() {
		return $this->order_date;
	}

	/**
	 * Set the order url.
	 *
	 * @param string $url The order url.
	 */
	public function set_order_url( $url ) {
		$this->order_url = $url;
	}

	/**
	 * Get the order url.
	 *
	 * @return string
	 */
	public function get_order_url() {
		return $this->order_url;
	}

	/**
	 * Returns the total products.
	 *
	 * @return string
	 */
	public function get_total_products() {
		return $this->total_products;
	}

	/**
	 * Sets the total products.
	 *
	 * @param string $total_products The total products.
	 */
	public function set_total_products( $total_products ) {
		$this->total_products = (string) $total_products;
	}

	/**
	 * Adds an Ecom product to the array of ecom products.
	 *
	 * @param Activecampaign_For_Woocommerce_Ecom_Product $order_product The ecom product to be added.
	 */
	public function push_order_product( Activecampaign_For_Woocommerce_Ecom_Product $order_product ) {
		$this->order_products[] = $order_product;
	}

	/**
	 * Returns the array of ecom products.
	 *
	 * @return Activecampaign_For_Woocommerce_Ecom_Product[]
	 */
	public function get_order_products() {
		return $this->order_products;
	}

	/**
	 * Sets the properties from a serialized array returned from the API.
	 *
	 * Calls the set properties method of the trait used in this class, but first
	 * massages the data due to how ecom order products are returned by the API.
	 *
	 * @param array $array The serialized array.
	 */
	public function set_properties_from_serialized_array( array $array ) {
		$this->set_all_but_products_as_properties_from_serialized_array( $array );

		foreach ( $array['orderProducts'] as $product ) {
			if ( ! isset( $product['orderid'] ) ) {
				$product['orderid'] = $this->get_id();
			}

			$order_product = new Activecampaign_For_Woocommerce_Ecom_Product();
			$order_product->set_properties_from_serialized_array( $product );
			$this->push_order_product( $order_product );
		}
	}

	/**
	 * Serializes the model to an associative array.
	 *
	 * Calls the serialize method of the trait used in this class, but first
	 * massages the data due to how ecom order products are expected by the API.
	 *
	 * @return array
	 */
	public function serialize_to_array() {
		$array = $this->serialize_all_but_products_to_array();

		$order_products = [];

		foreach ( $this->order_products as $order_product ) {
			$order_products[] = $order_product->serialize_to_array();
		}

		$array['orderProducts'] = $order_products;

		return $array;
	}
}
