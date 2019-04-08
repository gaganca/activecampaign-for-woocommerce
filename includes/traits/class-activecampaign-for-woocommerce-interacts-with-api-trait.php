<?php
/**
 * The file for the trait Activecampaign_For_Woocommerce_Interacts_With_Api.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/traits
 */

/**
 * Trait Activecampaign_For_Woocommerce_Interacts_With_Api
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes/traits
 */
trait Activecampaign_For_Woocommerce_Interacts_With_Api {
	/**
	 * Retrieves a resource via the API and uses the value to update a model.
	 *
	 * @param Activecampaign_For_Woocommerce_Api_Client           $client The Client class.
	 * @param Activecampaign_For_Woocommerce_Ecom_Model_Interface $model The model class.
	 * @param string                                              $id The id to find.
	 * @param callable                                            $response_massager A callable to alter the response body.
	 *
	 * @throws Activecampaign_For_Woocommerce_Resource_Not_Found_Exception Thrown when a 404 is returned.
	 */
	private function get_and_set_model_properties_from_api_by_id(
		Activecampaign_For_Woocommerce_Api_Client $client,
		Activecampaign_For_Woocommerce_Ecom_Model_Interface $model,
		$id,
		callable $response_massager = null
	) {
		try {
			$result = $client
				->get( self::RESOURCE_NAME_PLURAL, (string) $id )
				->execute();
		} catch ( AcVendor\GuzzleHttp\Exception\ClientException $e ) {
			throw new Activecampaign_For_Woocommerce_Resource_Not_Found_Exception(
				'The resource was not found.',
				[
					'resource' => self::RESOURCE_NAME,
					'found_by' => 'id',
					'value'    => $id,
				],
				$e->getResponse()->getStatusCode(),
				$e
			);
		}

		$resource_array = \AcVendor\GuzzleHttp\json_decode( $result->getBody(), true );

		if ( $response_massager ) {
			$resource_array = $response_massager( $resource_array );
		}

		$resource = $resource_array[ self::RESOURCE_NAME ];

		$model->set_properties_from_serialized_array( $resource );
	}

	/**
	 * Retrieves a resource via the API by an email address and uses the value to update a model.
	 *
	 * @param Activecampaign_For_Woocommerce_Api_Client           $client The Api client class.
	 * @param Activecampaign_For_Woocommerce_Ecom_Model_Interface $model The model class.
	 * @param string                                              $email The email address.
	 * @param callable                                            $response_massager A callable to alter the response body.
	 *
	 * @throws Activecampaign_For_Woocommerce_Resource_Not_Found_Exception Thrown when a 404 is returned.
	 */
	private function get_and_set_model_properties_from_api_by_email(
		Activecampaign_For_Woocommerce_Api_Client $client,
		Activecampaign_For_Woocommerce_Ecom_Model_Interface $model,
		$email,
		callable $response_massager = null
	) {
		$this->get_and_set_model_properties_from_api_by_filter(
			$client,
			$model,
			'email',
			$email,
			$response_massager
		);
	}

	/**
	 * Retrieves a resource via the API by an external id and uses the value to update a model.
	 *
	 * @param Activecampaign_For_Woocommerce_Api_Client           $client The Api client class.
	 * @param Activecampaign_For_Woocommerce_Ecom_Model_Interface $model The model class.
	 * @param string                                              $externalid The externalid.
	 * @param callable                                            $response_massager A callable to alter the response body.
	 *
	 * @throws Activecampaign_For_Woocommerce_Resource_Not_Found_Exception Thrown when a 404 is returned.
	 */
	private function get_and_set_model_properties_from_api_by_externalid(
		Activecampaign_For_Woocommerce_Api_Client $client,
		Activecampaign_For_Woocommerce_Ecom_Model_Interface $model,
		$externalid,
		callable $response_massager = null
	) {
		$this->get_and_set_model_properties_from_api_by_filter(
			$client,
			$model,
			'externalid',
			$externalid,
			$response_massager
		);
	}

	/**
	 * Retrieves a resource via the API with a filter and uses the value to update a model.
	 *
	 * @param Activecampaign_For_Woocommerce_Api_Client           $client The Api client class.
	 * @param Activecampaign_For_Woocommerce_Ecom_Model_Interface $model The model class.
	 * @param string                                              $filter_name The name of the filter.
	 * @param string                                              $filter_value The value of the filter.
	 * @param callable                                            $response_massager A callable to alter the response body.
	 *
	 * @throws Activecampaign_For_Woocommerce_Resource_Not_Found_Exception Thrown when a 404 is returned.
	 */
	private function get_and_set_model_properties_from_api_by_filter(
		Activecampaign_For_Woocommerce_Api_Client $client,
		Activecampaign_For_Woocommerce_Ecom_Model_Interface $model,
		$filter_name,
		$filter_value,
		callable $response_massager = null
	) {
		$resource = $this->get_result_set_from_api_by_filter( $client, $filter_name, $filter_value, $response_massager );

		if ( empty( $resource ) ) {
			throw new Activecampaign_For_Woocommerce_Resource_Not_Found_Exception();
		}

		$model->set_properties_from_serialized_array( $resource[0] );
	}

	/**
	 * Retrieves a resource via the API with a filter. May return multiple rows.
	 *
	 * @param Activecampaign_For_Woocommerce_Api_Client $client The Api client class.
	 * @param string                                    $filter_name The name of the filter.
	 * @param string                                    $filter_value The value of the filter.
	 * @param callable|null                             $response_massager A callable to alter the response body.
	 *
	 * @return array
	 * @throws Activecampaign_For_Woocommerce_Resource_Not_Found_Exception Thrown when a 404 is returned.
	 */
	private function get_result_set_from_api_by_filter(
		Activecampaign_For_Woocommerce_Api_Client $client,
		$filter_name,
		$filter_value,
		callable $response_massager = null
	) {
		$client->set_filters( [] );
		$client->with_body( '' );

		$result = $client
			->get( self::RESOURCE_NAME_PLURAL )
			->with_filter( $filter_name, $filter_value )
			->execute();

		$resources_array = AcVendor\GuzzleHttp\json_decode( $result->getBody(), true );

		if ( count( $resources_array[ self::RESOURCE_NAME_PLURAL ] ) < 1 ) {
			throw new Activecampaign_For_Woocommerce_Resource_Not_Found_Exception(
				'The resource was not found.',
				[
					'resource' => self::RESOURCE_NAME,
					'found_by' => $filter_name,
					'value'    => $filter_value,
				],
				404
			);
		}

		if ( $response_massager ) {
			$resources_array = $response_massager( $resources_array );
		}

		return $resources_array[ self::RESOURCE_NAME_PLURAL ];
	}

	/**
	 * Serializes a model and creates a remote resource via the API.
	 *
	 * @param Activecampaign_For_Woocommerce_Api_Client           $client The API Client class.
	 * @param Activecampaign_For_Woocommerce_Ecom_Model_Interface $model The model class.
	 * @param callable                                            $response_massager A callable to alter the response body.
	 *
	 * @throws Activecampaign_For_Woocommerce_Resource_Unprocessable_Exception Thrown when a 422 is returned.
	 */
	private function create_and_set_model_properties_from_api(
		Activecampaign_For_Woocommerce_Api_Client $client,
		Activecampaign_For_Woocommerce_Ecom_Model_Interface $model,
		callable $response_massager = null
	) {
		$client->set_filters( [] );

		$resource = $model->serialize_to_array();

		$body = [
			self::RESOURCE_NAME => $resource,
		];

		$body_as_string = AcVendor\GuzzleHttp\json_encode( $body );

		try {
			$result = $client
				->post( self::RESOURCE_NAME_PLURAL )
				->with_body( $body_as_string )
				->execute();
		} catch ( AcVendor\GuzzleHttp\Exception\ClientException $e ) {
			throw new Activecampaign_For_Woocommerce_Resource_Unprocessable_Exception(
				'The resource was unprocessable.',
				[
					'resource' => self::RESOURCE_NAME,
					'context'  => $body_as_string,
					'response' => $e->getResponse()->getBody(),
					$e->getTraceAsString(),
				],
				$e->getResponse()->getStatusCode(),
				$e
			);
		}

		$resource_array = AcVendor\GuzzleHttp\json_decode( $result->getBody(), true );

		if ( $response_massager ) {
			$resource_array = $response_massager( $resource_array );
		}

		$resource = $resource_array[ self::RESOURCE_NAME ];

		$model->set_properties_from_serialized_array( $resource );
	}

	/**
	 * Serializes a model and updates a remote resource via the API.
	 *
	 * @param Activecampaign_For_Woocommerce_Api_Client           $client The API Client class.
	 * @param Activecampaign_For_Woocommerce_Ecom_Model_Interface $model The model class.
	 * @param callable                                            $response_massager A callable to alter the response body.
	 *
	 * @throws Activecampaign_For_Woocommerce_Resource_Not_Found_Exception Thrown when a 404 is returned.
	 * @throws Activecampaign_For_Woocommerce_Resource_Unprocessable_Exception Thrown when a 422 is returned.
	 */
	private function update_and_set_model_properties_from_api(
		Activecampaign_For_Woocommerce_Api_Client $client,
		Activecampaign_For_Woocommerce_Ecom_Model_Interface $model,
		callable $response_massager = null
	) {
		$client->set_filters( [] );

		$resource = $model->serialize_to_array();

		$body = [
			self::RESOURCE_NAME => $resource,
		];

		$body_as_string = AcVendor\GuzzleHttp\json_encode( $body );

		try {
			$result = $client
				->put( self::RESOURCE_NAME_PLURAL, $model->get_id() )
				->with_body( $body_as_string )
				->execute();
		} catch ( AcVendor\GuzzleHttp\Exception\ClientException $e ) {
			if ( $e->getCode() === 404 ) {
				throw new Activecampaign_For_Woocommerce_Resource_Not_Found_Exception(
					'The resource was not found.',
					[
						'resource' => self::RESOURCE_NAME,
						'found_by' => 'id',
						'value'    => $model->get_id(),
					],
					$e->getResponse()->getStatusCode(),
					$e
				);
			} else {
				throw new Activecampaign_For_Woocommerce_Resource_Unprocessable_Exception(
					'The resource was unprocessable.',
					[
						'resource' => self::RESOURCE_NAME,
						'context'  => $body_as_string,
						'response' => $e->getResponse()->getBody(),
					],
					$e->getResponse()->getStatusCode(),
					$e
				);
			}
		}

		$resource_array = AcVendor\GuzzleHttp\json_decode( $result->getBody(), true );

		if ( $response_massager ) {
			$resource_array = $response_massager( $resource_array );
		}

		$resource = $resource_array[ self::RESOURCE_NAME ];

		$model->set_properties_from_serialized_array( $resource );
	}
}
