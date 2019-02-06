<?php

/**
 * Provides interface to logging
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce_Logger
 * @subpackage Activecampaign_For_Woocommerce/includes
 */

/**
 * Logger object
 *
 * @package    Activecampaign_For_Woocommerce_Logger
 * @subpackage Activecampaign_For_Woocommerce/includes
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
class Activecampaign_For_Woocommerce_Logger {

	/**
	 * Instance of the WooCommerce logger.
	 *
	 * @var WC_Logger
	 */
	private $logger;

	/**
	 * The logger context parameter.
	 *
	 * @var array
	 */
	private $context;

	/**
	 * Logger constructor.
	 */
	public function __construct() {
		$this->logger  = function_exists( 'wc_get_logger' ) ? wc_get_logger() : null;
		$this->context = array( 'source' => ACTIVECAMPAIGN_FOR_WOOCOMMERCE_PLUGIN_NAME_KEBAB );
	}

	/**
	 * Logger debug method.
	 *
	 * @param string $message The log message.
	 */
	public function debug( $message ) {
		$this->logger->log( 'debug', $message, $this->context );
	}
}
