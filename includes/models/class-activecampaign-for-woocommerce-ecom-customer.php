<?php

/**
 * The file for the EcomCustomer Model
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
 * The model class for the Ecom Customer
 *
 * @since      1.0.0
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/models
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
class Activecampaign_For_Woocommerce_Ecom_Customer implements Ecom_Model, Has_Id, Has_Email {
	use Api_Serializable;

	/**
	 * The API mappings for the API_Serializable trait.
	 *
	 * @var array
	 */
	public $api_mappings = [
		'connectionid' => 'connectionid',
		'externalid'   => 'externalid',
		'email'        => 'email',
		'id'           => 'id',
	];

	/**
	 * The connection id.
	 *
	 * @var string
	 */
	private $connectionid;

	/**
	 * The external id.
	 *
	 * @var string
	 */
	private $externalid;

	/**
	 * The email address.
	 *
	 * @var string
	 */
	private $email;

	/**
	 * The id.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Returns a connection id.
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
}
