<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes
 */

use Activecampaign_For_Woocommerce_Add_Cart_Id_To_Order_Command as Add_Cart_Id_To_Order;
use Activecampaign_For_Woocommerce_Add_Accepts_Marketing_To_Customer_Meta_Command as Add_Accepts_Marketing_To_Customer_Meta;
use Activecampaign_For_Woocommerce_Admin as Admin;
use Activecampaign_For_Woocommerce_Cart_Emptied_Event as Cart_Emptied;
use Activecampaign_For_Woocommerce_Cart_Updated_Event as Cart_Updated;
use Activecampaign_For_Woocommerce_Clear_User_Meta_Command as Clear_User_Meta_Command;
use Activecampaign_For_Woocommerce_Create_Or_Update_Connection_Option_Command as Create_Or_Update_Connection_Option_Command;
use Activecampaign_For_Woocommerce_Create_And_Save_Cart_Id_Command as Create_And_Save_Cart_Id;
use Activecampaign_For_Woocommerce_Delete_Cart_Id_Command as Delete_Cart_Id;
use Activecampaign_For_Woocommerce_I18n as I18n;
use Activecampaign_For_Woocommerce_Loader as Loader;
use Activecampaign_For_Woocommerce_Public as AC_Public;
use Activecampaign_For_Woocommerce_Set_Connection_Id_Cache_Command as Set_Connection_Id_Cache_Command;
use Activecampaign_For_Woocommerce_Update_Cart_Command as Update_Cart_Command;
use Activecampaign_For_Woocommerce_Sync_Guest_Abandoned_Cart_Command as Sync_Guest_Abandoned_Cart_Command;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/includes
 * @author     acteamintegrations <team-integrations@activecampaign.com>
 */
class Activecampaign_For_Woocommerce {
	/**
	 * The Admin class that handles all admin-facing code.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Admin $admin The admin class.
	 */
	private $admin;

	/**
	 * The Public class that handles all public-facing code.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      AC_Public $public The public class.
	 */
	private $public;
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Handles all internationalization.
	 *
	 * @var Activecampaign_For_Woocommerce_I18n $i18n The internationalization class.
	 */
	private $i18n;

	/**
	 * Used for triggering cart updated event.
	 *
	 * @var Cart_Updated The cart updated event class.
	 */
	private $cart_updated_event;

	/**
	 * Used for triggering cart emptied event.
	 *
	 * @var Cart_Emptied The cart emptied event class.
	 */
	private $cart_emptied_event;

	/**
	 * Handles setting the connection id cache.
	 *
	 * @var Set_Connection_Id_Cache_Command The set connection id command class.
	 */
	private $set_connection_id_cache_command;

	/**
	 * Handles creating or updating the connection ID in Hosted.
	 *
	 * @var Create_Or_Update_Connection_Option_Command The create or update connection option command.
	 */
	private $create_or_update_connection_option_command;

	/**
	 * Handles creating and then saving to the DB the persistent cart id.
	 *
	 * @var Create_And_Save_Cart_Id The create and save cart id command class.
	 */
	private $create_and_save_cart_id_command;

	/**
	 * Handles sending the cart and its products to AC for the given customer.
	 *
	 * @var Update_Cart_Command The update cart command class.
	 */
	private $update_cart_command;

	/**
	 * Handles deleting from the DB the persistent cart id.
	 *
	 * @var Delete_Cart_Id The delete cart id command class.
	 */
	private $delete_cart_id_command;

	/**
	 * Handles taking the persistent cart ID from the DB and adding it to the meta of the order.
	 *
	 * @var Add_Cart_Id_To_Order The add cart id to order command class.
	 */
	private $add_cart_id_to_order_command;

	/**
	 * Handles taking from the $_POST object the customers accepts marketing choice and adding
	 * it to the meta of the customer in the DB.
	 *
	 * @var Add_Accepts_Marketing_To_Customer_Meta The add accepts marketing meta to customer class.
	 */
	private $add_accepts_marketing_to_customer_meta_command;

	/**
	 * Handles clearing user meta if certain circumstances are met.
	 *
	 * @var Clear_User_Meta_Command The clear user meta command class.
	 */
	private $clear_user_meta_command;

	/**
	 * Handles sending the guest customer and pending order to AC.
	 *
	 * @var Sync_Guest_Abandoned_Cart_Command The sync guest abandoned cart command class.
	 */
	private $sync_guest_abandoned_cart_command;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 *
	 * @param string                                     $version                         The current version of the
	 *                                                                                    plugin.
	 * @param string                                     $plugin_name                     The kebab-case name of the
	 *                                                                                    plugin.
	 * @param Loader                                     $loader                          The loader class.
	 * @param Admin                                      $admin                           The admin class.
	 * @param AC_Public                                  $public                          The public class.
	 * @param I18n                                       $i18n                            The internationalization
	 *                                                                                    class.
	 * @param Cart_Updated                               $cart_updated_event              The cart update event class.
	 * @param Cart_Emptied                               $cart_emptied_event              The cart emptied event class.
	 * @param Set_Connection_Id_Cache_Command            $set_connection_id_cache_command The connection id cache
	 *                                                                                    command class.
	 * @param Create_Or_Update_Connection_Option_Command $c_or_u_co_command               The connection option command
	 *                                                                                    class.
	 * @param Create_And_Save_Cart_Id                    $create_and_save_cart_id_command The cart id command class.
	 * @param Update_Cart_Command                        $update_cart_command             The update cart command
	 *                                                                                    class.
	 * @param Delete_Cart_Id                             $delete_cart_id_command          The delete cart id
	 *                                                                                    command class.
	 * @param Add_Cart_Id_To_Order                       $add_cart_id_to_order_command    The add cart id to order
	 *                                                                                    command class.
	 * @param Add_Accepts_Marketing_To_Customer_Meta     $add_am_to_meta_command          The accepts marketing
	 *                                                                                    command class.
	 * @param Clear_User_Meta_Command                    $clear_user_meta_command         The clear user meta command
	 *                                                                                    class.
	 * @param Sync_Guest_Abandoned_Cart_Command          $sync_guest_abandoned_cart_command The sync guest abandoned
	 *                                                                                      cart command class.
	 *
	 * @since    1.0.0
	 */
	public function __construct(
		$version,
		$plugin_name,
		Loader $loader,
		Admin $admin,
		AC_Public $public,
		I18n $i18n,
		Cart_Updated $cart_updated_event,
		Cart_Emptied $cart_emptied_event,
		Set_Connection_Id_Cache_Command $set_connection_id_cache_command,
		Create_Or_Update_Connection_Option_Command $c_or_u_co_command,
		Create_And_Save_Cart_Id $create_and_save_cart_id_command,
		Update_Cart_Command $update_cart_command,
		Delete_Cart_Id $delete_cart_id_command,
		Add_Cart_Id_To_Order $add_cart_id_to_order_command,
		Add_Accepts_Marketing_To_Customer_Meta $add_am_to_meta_command,
		Clear_User_Meta_Command $clear_user_meta_command,
		Sync_Guest_Abandoned_Cart_Command $sync_guest_abandoned_cart_command
	) {
		$this->version     = $version;
		$this->plugin_name = $plugin_name;

		$this->loader                                     = $loader;
		$this->admin                                      = $admin;
		$this->public                                     = $public;
		$this->i18n                                       = $i18n;
		$this->cart_updated_event                         = $cart_updated_event;
		$this->cart_emptied_event                         = $cart_emptied_event;
		$this->set_connection_id_cache_command            = $set_connection_id_cache_command;
		$this->create_or_update_connection_option_command = $c_or_u_co_command;
		$this->create_and_save_cart_id_command            = $create_and_save_cart_id_command;
		$this->update_cart_command                        = $update_cart_command;
		$this->delete_cart_id_command                     = $delete_cart_id_command;
		$this->add_cart_id_to_order_command               = $add_cart_id_to_order_command;
		$this->add_accepts_marketing_to_customer_meta_command = $add_am_to_meta_command;
		$this->clear_user_meta_command                        = $clear_user_meta_command;
		$this->sync_guest_abandoned_cart_command              = $sync_guest_abandoned_cart_command;
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Activecampaign_For_Woocommerce_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @throws Exception Thrown when Container definitions are missing.
	 */
	private function set_locale() {
		$this->loader->add_action( 'plugins_loaded', $this->i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register Events to be executed on different actions.
	 *
	 * @throws Exception Thrown when Container definitions are missing.
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_event_hooks() {
		// Cart actions
		$this->loader->add_filter(
			'woocommerce_update_cart_action_cart_updated',
			$this->cart_updated_event,
			'trigger'
		);

		$this->loader->add_action(
			'woocommerce_add_to_cart',
			$this->cart_updated_event,
			'trigger'
		);

		$this->loader->add_action(
			'woocommerce_cart_item_removed',
			$this->cart_updated_event,
			'trigger'
		);

		$this->loader->add_action(
			'woocommerce_checkout_update_order_meta',
			$this->cart_emptied_event,
			'trigger'
		);

		$this->loader->add_action(
			'woocommerce_cart_emptied',
			$this->cart_emptied_event,
			'trigger'
		);
	}

	/**
	 * Register Commands to be executed on different actions.
	 *
	 * @throws Exception Thrown when Container definitions are missing.
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_command_hooks() {
		$this->define_admin_commands();

		/**
		 * If the site admin has not yet configured their plugin, bail out before
		 * registering any public commands since they will not work without the
		 * plugin being configured.
		 */
		if ( ! $this->admin->get_options() ) {
			return;
		}

		$this->define_public_commands();
	}

	/**
	 * Registers commands related to the admin portion of the WordPress site with
	 * action hooks.
	 *
	 * @since 1.2.1
	 * @access private
	 */
	private function define_admin_commands() {
		/**
		 * By including priority 1, we ensure that the connection id caching occurs
		 * before the create or update command.
		 */
		$this->loader->add_action(
			'activecampaign_for_woocommerce_admin_settings_updated',
			$this->set_connection_id_cache_command,
			'execute',
			1
		);

		$this->loader->add_action(
			'activecampaign_for_woocommerce_admin_settings_updated',
			$this->create_or_update_connection_option_command,
			'execute'
		);

		$this->loader->add_filter(
			'activecampaign_for_woocommerce_admin_settings_updated',
			$this->clear_user_meta_command,
			'execute'
		);
	}

	/**
	 * Registers commands related to the public-facing portion of the WordPress site with
	 * action hooks.
	 *
	 * @since 1.2.1
	 * @access private
	 */
	private function define_public_commands() {
		$this->loader->add_action(
			'activecampaign_for_woocommerce_cart_updated',
			$this->create_and_save_cart_id_command,
			'execute'
		);

		$this->loader->add_action(
			'activecampaign_for_woocommerce_cart_updated',
			$this->update_cart_command,
			'execute'
		);

		$this->loader->add_action(
			'activecampaign_for_woocommerce_cart_emptied',
			$this->delete_cart_id_command,
			'execute'
		);

		$this->loader->add_filter(
			'woocommerce_checkout_create_order',
			$this->add_cart_id_to_order_command,
			'execute'
		);

		$this->loader->add_filter(
			'woocommerce_checkout_create_order',
			$this->add_accepts_marketing_to_customer_meta_command,
			'execute'
		);
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @throws Exception Thrown when Container definitions are missing.
	 */
	private function define_admin_hooks() {
		$this->loader->add_action(
			'admin_enqueue_scripts',
			$this->admin,
			'enqueue_styles'
		);

		$this->loader->add_action(
			'admin_enqueue_scripts',
			$this->admin,
			'enqueue_scripts'
		);

		$this->loader->add_action(
			'admin_menu',
			$this->admin,
			'add_admin_page'
		);

		$this->loader->add_action(
			'admin_post_' . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_PLUGIN_NAME_SNAKE . '_settings',
			$this->admin,
			'handle_settings_post'
		);

		$this->loader->add_filter(
			'plugin_action_links_' . ACTIVECAMPAIGN_FOR_WOOCOMMERCE_PLUGIN_BASE_NAME,
			$this->admin,
			'add_plugin_settings_link'
		);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @throws Exception Thrown when Container definitions are missing.
	 */
	private function define_public_hooks() {
		/**
		 * If the site admin has not yet configured their plugin, bail out before
		 * registering any public commands since they will not work without the
		 * plugin being configured.
		 */
		if ( ! $this->admin->get_options() ) {
			return;
		}

		$this->loader->add_action(
			'wp_enqueue_scripts',
			$this->public,
			'enqueue_styles'
		);

		$this->loader->add_action(
			'wp_enqueue_scripts',
			$this->public,
			'enqueue_scripts'
		);

		$this->loader->add_action(
			'woocommerce_after_checkout_billing_form',
			$this->public,
			'handle_woocommerce_checkout_form'
		);

		$this->loader->add_action(
			'wp_ajax_activecampaign_for_woocommerce_cart_sync_guest',
			$this->sync_guest_abandoned_cart_command,
			'execute'
		);

		$this->loader->add_action(
			'wp_ajax_nopriv_activecampaign_for_woocommerce_cart_sync_guest',
			$this->sync_guest_abandoned_cart_command,
			'execute'
		);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @throws Exception Thrown when Container definitions are missing.
	 */
	public function run() {
		$this->set_locale();
		$this->define_event_hooks();
		$this->define_command_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Activecampaign_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
