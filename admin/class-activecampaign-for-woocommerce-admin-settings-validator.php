<?php

/**
 * Handles validating changes to the admin settings for this plugin.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/admin
 */

use Activecampaign_For_Woocommerce_Api_Client as Client;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Handles validating changes to the admin settings for this plugin.
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/admin
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
class Activecampaign_For_Woocommerce_Admin_Settings_Validator {
	/**
	 * The API Client to use for API Key and URL Validation.
	 *
	 * @var Activecampaign_For_Woocommerce_Api_Client The Api Client.
	 */
	private $client;

	/**
	 * An array that will errors placed into it to be returned from the validation.
	 *
	 * @var array The errors array.
	 */
	private $errors;

	/**
	 * Activecampaign_For_Woocommerce_Admin_Settings_Validator constructor.
	 *
	 * @param Activecampaign_For_Woocommerce_Api_Client $client The API Client.
	 */
	public function __construct( Client $client ) {
		$this->client = $client;
	}

	/**
	 * Validates the new data for the options table.
	 *
	 * @param array $new_data     The array of data to be updated.
	 * @param array $current_data The existing data for the options.
	 *
	 * @return array
	 */
	public function validate( $new_data, $current_data ) {
		$this->errors = [];

		$this->validate_accepts_marketing_checkbox_text( $new_data, $current_data );

		$this->validate_api_key( $new_data, $current_data );

		$this->validate_api_url( $new_data, $current_data );

		$this->validate_ab_cart_wait_time( $new_data, $current_data );

		$this->validate_changing_api_details( $new_data, $current_data );

		return $this->errors;
	}

	/**
	 * Validates the accepts marketing checkbox text.
	 *
	 * @param array $new_data     The array of new data.
	 * @param array $current_data The array of existing data.
	 */
	private function validate_accepts_marketing_checkbox_text( $new_data, $current_data ) {
		/**
		 * If the Optin Checkbox Text isn't set in the DB nor the request, or the user is trying to set it
		 * to an empty string.
		 */
		if (
			( ! isset( $new_data['optin_checkbox_text'] ) && ! isset( $current_data['optin_checkbox_text'] ) ) ||
			( isset( $new_data['optin_checkbox_text'] ) && '' === $new_data['optin_checkbox_text'] )
		) {
			$this->errors[] = 'Oops! Looks like you didn&#39;t provide any checkbox text.';
		}
	}

	/**
	 * Validates the api key text.
	 *
	 * @param array $new_data     The array of new data.
	 * @param array $current_data The array of existing data.
	 */
	private function validate_api_key( $new_data, $current_data ) {
		/**
		 * If the API Key isn't set in the DB nor the request, or the user is trying to set it
		 * to an empty string.
		 */
		if (
			( ! isset( $new_data['api_key'] ) && ! isset( $current_data['api_key'] ) ) ||
			( isset( $new_data['api_key'] ) && '' === $new_data['api_key'] )
		) {
			$this->errors[] = 'The API Key is required and cannot be an empty string.';
		}
	}

	/**
	 * Validates the api url text.
	 *
	 * @param array $new_data     The array of new data.
	 * @param array $current_data The array of existing data.
	 */
	private function validate_api_url( $new_data, $current_data ) {
		/**
		 * If the API URL isn't set in the DB nor the request, or the user is trying to set it
		 * to an empty string.
		 */
		if (
			( ! isset( $new_data['api_url'] ) && ! isset( $current_data['api_url'] ) ) ||
			( isset( $new_data['api_url'] ) && '' === $new_data['api_url'] )
		) {
			$this->errors[] = 'The API URL is required and cannot be an empty string.';
		}
	}

	/**
	 * Validates the ab cart wait time.
	 *
	 * @param array $new_data     The array of new data.
	 * @param array $current_data The array of existing data.
	 */
	private function validate_ab_cart_wait_time( $new_data, $current_data ) {
		/**
		 * If the Abandoned Cart Wait Time isn't set in the DB nor the request, or the user is trying to set it
		 * to an empty string.
		 */
		if (
			( ! isset( $new_data['abcart_wait'] ) && ! isset( $current_data['abcart_wait'] ) ) ||
			( isset( $new_data['abcart_wait'] ) && '' === $new_data['abcart_wait'] )
		) {
			$this->errors[] = 'The Abandoned Cart Wait Time is required and cannot be an empty string.';
		}
	}

	/**
	 * Validates that the changing api credentials still work.
	 *
	 * @param array $new_data     The array of new data.
	 * @param array $current_data The array of existing data.
	 */
	private function validate_changing_api_details( $new_data, $current_data ) {
		$api_url_set_first_time = isset( $new_data['api_url'] ) && ! isset( $current_data['api_url'] );
		$api_key_set_first_time = isset( $new_data['api_key'] ) && ! isset( $current_data['api_key'] );

		$api_url_changing = ( isset( $new_data['api_url'] ) && isset( $current_data['api_url'] ) ) && // both are set
							$new_data['api_url'] !== $current_data['api_url'];                        // and changing

		$api_key_changing = ( isset( $new_data['api_key'] ) && isset( $current_data['api_key'] ) ) && // both are set
							$new_data['api_key'] !== $current_data['api_key'];                        // and changing

		$both_api_key_and_url_set = isset( $new_data['api_url'] ) && isset( $new_data['api_key'] );

		/**
		 * If the API Url/Key is being set for the first time, or is changing.
		 */
		if (
			(
				$api_url_set_first_time ||
				$api_key_set_first_time ||
				$api_url_changing ||
				$api_key_changing
			) &&
			$both_api_key_and_url_set
		) {
			$this->client->set_api_key( $new_data['api_key'] );
			$this->client->set_api_uri( $new_data['api_url'] );

			$this->client->configure_client();

			try {
				$this->client->get( 'connections' )
							 ->execute();
			} catch ( ClientException $e ) {
				$this->errors[] = 'Either the API Url or API Key is invalid.';
			} catch ( GuzzleException $e ) {
				$this->errors[] = 'Something went wrong while authenticating, please try again.';
			} catch ( \Exception $e ) {
				$this->errors[] = 'Something went wrong while authenticating, please try again.';
			}
		}
	}
}
