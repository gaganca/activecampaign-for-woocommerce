<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/admin
 */

use Activecampaign_For_Woocommerce_Admin_Settings_Updated_Event as Admin_Settings_Updated;
use Activecampaign_For_Woocommerce_Admin_Settings_Validator as Validator;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/admin
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
class Activecampaign_For_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The response array that will be returned.
	 *
	 * @var array The array.
	 */
	private $response = [];

	/**
	 * The class that handles validating options changes.
	 *
	 * @var Validator The validator class.
	 */
	private $validator;

	/**
	 * The event class to be triggered after a successful options update.
	 *
	 * @var Activecampaign_For_Woocommerce_Admin_Settings_Updated_Event The event class.
	 */
	private $event;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param string                 $plugin_name The name of this plugin.
	 * @param string                 $version     The version of this plugin.
	 * @param Validator              $validator   The validator for the admin options.
	 * @param Admin_Settings_Updated $event       The admin settings updated event class.
	 */
	public function __construct( $plugin_name, $version, Validator $validator, Admin_Settings_Updated $event ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->validator   = $validator;
		$this->event       = $event;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/activecampaign-for-woocommerce-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/activecampaign-for-woocommerce-admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);
	}

	/**
	 * Register the page for the admin section
	 *
	 * @since    1.0.0
	 */
	public function add_admin_page() {
		add_options_page(
			'ActiveCampaign for WooCommerce',
			'ActiveCampaign for WooCommerce',
			'manage_options',
			ACTIVECAMPAIGN_FOR_WOOCOMMERCE_PLUGIN_NAME_SNAKE,
			array(
				$this,
				'fetch_admin_page',
			)
		);
	}

	/**
	 * This function adds to our plugin listing on the plugin page a link to our settings page.
	 *
	 * @param array $links The existing links being passed in.
	 *
	 * @return array
	 */
	public function add_plugin_settings_link( $links ) {
		$html_raw = '<a href="%s" aria-label="%s">%s</a>';

		$html = sprintf(
			$html_raw,
			admin_url( 'options-general.php?page=' . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_PLUGIN_NAME_SNAKE ),
			esc_attr__(
				'View ActiveCampaign settings',
				ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN
			),
			esc_html__( 'Settings', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN )
		);

		$action_links = [
			$html,
		];

		return array_merge( $action_links, $links );
	}

	/**
	 * Fetch the PHP template file that is used for the admin page
	 *
	 * @since    1.0.0
	 */
	public function fetch_admin_page() {
		require_once plugin_dir_path( __FILE__ )
					 . 'partials/activecampaign-for-woocommerce-admin-display.php';
	}

	/**
	 * Radio options for "How long after a cart is abandoned should ActiveCampaign trigger automations?"
	 * How long should we wait until we determine a cart is abandoned?
	 * These options let the user decide.
	 */
	public function get_ab_cart_wait_options() {
		$options = wp_json_encode(
			[
				// value     // label
				'1'  => esc_html__( '1 hour (recommended)', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN ),
				'6'  => esc_html__( '6 hours', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN ),
				'10' => esc_html__( '10 hours', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN ),
				'24' => esc_html__( '24 hours', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN ),
			]
		);

		return $options;
	}

	/**
	 * Radio options for "Checkbox display options"
	 */
	public function get_checkbox_display_options() {
		$options = wp_json_encode(
			[
				// value                          // label
				'visible_checked_by_default'   => esc_html__(
					'Visible, checked by default',
					ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN
				),
				'visible_unchecked_by_default' => esc_html__(
					'Visible, unchecked by default',
					ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN
				),
				'not_visible'                  => esc_html__(
					'Not visible',
					ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN
				),
			]
		);

		return $options;
	}

	/**
	 * Returns an encoded array of existing notices to be displayed on page-load.
	 *
	 * Once displayed, these notifications are then removed so they don't constantly build up in the
	 * UI.
	 *
	 * @return string
	 */
	public function get_admin_notices() {
		$storage = $this->get_storage();

		$notifications = isset( $storage['notifications'] ) ? $storage['notifications'] : [];

		$this->update_storage(
			[
				'notifications' => [],
			]
		);

		return wp_json_encode( $notifications );
	}

	/**
	 * Handles the form submission for the settings page,
	 * then redirects back to the plugin page.
	 */
	public function handle_settings_post() {
		if ( ! $this->validate_request_nonce() ) {
			wp_send_json_error( $this->get_response(), 403 );
		}

		$post_data = $this->extract_post_data();

		$this->update_options( $post_data );

		if ( $this->response_has_errors() ) {
			wp_send_json_error( $this->get_response(), 422 );
		}

		$this->push_response_notice(
			$this->format_response_message( 'Settings successfully updated!', 'success' )
		);

		wp_send_json_success( $this->get_response() );
	}

	/**
	 * Returns the options values in the DB.
	 *
	 * @return array
	 */
	public function get_options() {
		return get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_DB_OPTION_NAME );
	}

	/**
	 * Updates the settings options in the DB.
	 *
	 * @param array $data An array of data that will be serialized into the DB.
	 *
	 * @return array
	 * @throws Exception When the container is missing definitions.
	 */
	public function update_options( $data ) {
		$current_settings = get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_DB_OPTION_NAME );

		$this->validate_options_update( $data, $current_settings );

		if ( $this->response_has_errors() ) {
			return $this->response;
		}

		$api_url_changing = $this->api_url_is_changing( $data, $current_settings );

		if ( $current_settings ) {
			$data = array_merge( $current_settings, $data );
		}

		update_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_DB_OPTION_NAME, $data );

		$this->event->trigger(
			[
				'api_url_changed' => $api_url_changing,
			]
		);

		return $this->get_options();
	}

	/**
	 * Returns the storage values in the DB.
	 *
	 * @return array
	 */
	public function get_storage() {
		return get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_DB_STORAGE_NAME );
	}

	/**
	 * Updates the storage values in the DB.
	 *
	 * @param array $data An array of data that will be serialized into the DB.
	 *
	 * @return bool
	 */
	public function update_storage( $data ) {
		$current_settings = get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_DB_STORAGE_NAME );

		if ( $current_settings ) {
			$data = array_merge( $current_settings, $data );
		}

		update_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_DB_STORAGE_NAME, $data );

		return true;
	}

	/**
	 * Allows an event listener/async process to store a notification to be displayed
	 * on the next settings page load.
	 *
	 * @param string $message The message to be translated and escaped for display.
	 * @param string $level   The level of severity of the message.
	 */
	public function add_async_processing_notification( $message, $level = 'info' ) {
		$current_storage = $this->get_storage();

		if ( ! isset( $current_storage['notifications'] ) ) {
			$current_storage['notifications'] = [];
		}

		$notifications = $current_storage['notifications'];

		$notifications[] = $this->format_response_message( $message, $level );

		$this->update_storage(
			[
				'notifications' => $notifications,
			]
		);
	}

	/**
	 * Validates the request nonce for the settings form to ensure valid requests.
	 *
	 * @return bool
	 */
	private function validate_request_nonce() {
		/**
		 * Validate the nonce created for this specific form action.
		 * The nonce input is generated in the template using the wp_nonce_field().
		 */
		$valid = wp_verify_nonce(
			$_POST['activecampaign_for_woocommerce_settings_nonce_field'],
			'activecampaign_for_woocommerce_settings_form'
		);

		if ( ! $valid ) {
			$this->push_response_error(
				$this->format_response_message( 'Form nonce is invalid.', 'error' )
			);
		}

		return $valid;
	}

	/**
	 * Extracts from the $_POST superglobal an array of sanitized data.
	 *
	 * Before sanitizing the data, certain key/value pairs from the array are
	 * removed. This is because CSRF values are currently in the POST body
	 * and we do not want to persist them to the DB.
	 *
	 * @return array
	 */
	private function extract_post_data() {
		$post_data = $_POST;

		/**
		 * Unset all the form fields that don't need to be persisted in the DB.
		 */
		unset( $post_data['action'] );
		unset( $post_data['activecampaign_for_woocommerce_settings_nonce_field'] );
		unset( $post_data['_wp_http_referer'] );

		/**
		 * Map through all values sent in and sanitize them.
		 */
		$post_data = array_map(
			function ( $entry ) {
				return sanitize_text_field( $entry );
			}, $post_data
		);

		return $post_data;
	}

	/**
	 * Translates and sanitizes error/notice messages into an associative array.
	 *
	 * This will be returned as part of a response to be displayed as a notice in the
	 * admin section of the site.
	 *
	 * @param string $message The message that will be translated and returned.
	 * @param string $level   The notice level (e.g. info, success...).
	 *
	 * @return array
	 */
	private function format_response_message( $message, $level = 'info' ) {
		// phpcs:disable
		return [
			'level'   => sanitize_text_field( $level ),
			'message' => esc_html__(
				$message,
				ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN
			),
		];
		// phpcs:enable
	}

	/**
	 * Adds to the array of response errors a new error.
	 *
	 * @param array $error The error associative array containing the error message and level.
	 */
	private function push_response_error( $error ) {
		if ( ! isset( $this->response['errors'] ) ) {
			$this->response['errors'] = [];
		}

		$this->response['errors'][] = $error;
	}

	/**
	 * Adds to the array of response notices a new notice.
	 *
	 * @param array $notice The notice associative array containing the notice message and level.
	 */
	private function push_response_notice( $notice ) {
		if ( ! isset( $this->response['notices'] ) ) {
			$this->response['notices'] = [];
		}

		$this->response['notices'][] = $notice;
	}

	/**
	 * Returns an array of the response array with notices and errors
	 * merged with the current state of the options array.
	 *
	 * @return array
	 */
	private function get_response() {
		return array_merge( $this->response, $this->get_options() ?: [] );
	}

	/**
	 * Checks whether or not the current response contains errors.
	 *
	 * @return bool
	 */
	private function response_has_errors() {
		return isset( $this->response['errors'] ) &&
			   count( $this->response['errors'] ) > 0;
	}

	/**
	 * Validates the new data for the options table.
	 *
	 * @param array $new_data     The array of data to be updated.
	 * @param array $current_data The existing data for the options.
	 */
	private function validate_options_update( $new_data, $current_data ) {
		$errors = $this->validator->validate( $new_data, $current_data );

		if ( ! empty( $errors ) ) {
			foreach ( $errors as $error ) {
				$this->push_response_error(
					$this->format_response_message(
						$error,
						'error'
					)
				);
			}
		}
	}

	/**
	 * Checks if the API Url setting is changing.
	 *
	 * @param array $new_data     An array of new data to be saved.
	 * @param array $current_data An array of data that's already saved.
	 *
	 * @return bool
	 */
	private function api_url_is_changing( $new_data, $current_data ) {
		return ( isset( $new_data['api_url'] ) && isset( $current_data['api_url'] ) ) && // both are set
			   $new_data['api_url'] !== $current_data['api_url'];                        // and changing
	}
}
