<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/admin/partials
 */

$activecampaign_for_woocommerce_settings = get_option( ACTIVECAMPAIGN_FOR_WOOCOMMERCE_DB_OPTION_NAME );
$activecampaign_for_woocommerce_settings = stripslashes_deep( $activecampaign_for_woocommerce_settings );

$activecampaign_for_woocommerce_api_url = '';
if ( isset( $activecampaign_for_woocommerce_settings['api_url'] ) ) {
	$activecampaign_for_woocommerce_api_url = $activecampaign_for_woocommerce_settings['api_url'];
}

$activecampaign_for_woocommerce_api_key = '';
if ( isset( $activecampaign_for_woocommerce_settings['api_key'] ) ) {
	$activecampaign_for_woocommerce_api_key = $activecampaign_for_woocommerce_settings['api_key'];
}

$activecampaign_for_woocommerce_abcart_wait = '1';
if ( isset( $activecampaign_for_woocommerce_settings['abcart_wait'] ) ) {
	$activecampaign_for_woocommerce_abcart_wait = $activecampaign_for_woocommerce_settings['abcart_wait'];
}

$activecampaign_for_woocommerce_optin_checkbox_text = esc_html__( 'Keep me up to date on news and exclusive offers', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN );
if ( isset( $activecampaign_for_woocommerce_settings['optin_checkbox_text'] ) ) {
	$activecampaign_for_woocommerce_optin_checkbox_text = $activecampaign_for_woocommerce_settings['optin_checkbox_text'];
}

$activecampaign_for_woocommerce_optin_checkbox_display_option = 'visible_checked_by_default';
if ( isset( $activecampaign_for_woocommerce_settings['checkbox_display_option'] ) ) {
	$activecampaign_for_woocommerce_optin_checkbox_display_option = $activecampaign_for_woocommerce_settings['checkbox_display_option'];
}
?>

<div id="activecampaign-for-woocommerce-app" data='{
	"abCartOptions": <?php echo esc_html( $this->get_ab_cart_wait_options() ); ?>,
	"checkboxDisplayOptions": <?php echo esc_html( $this->get_checkbox_display_options() ); ?>,
	"notices": <?php echo esc_html( $this->get_admin_notices() ); ?>,
	"api_url": "<?php echo esc_html( sanitize_text_field( $activecampaign_for_woocommerce_api_url ) ); ?>",
	"api_key": "<?php echo esc_html( sanitize_text_field( $activecampaign_for_woocommerce_api_key ) ); ?>",
	"abcart_wait": "<?php echo esc_html( sanitize_text_field( $activecampaign_for_woocommerce_abcart_wait ) ); ?>",
	"optin_checkbox_text": "<?php echo esc_html( sanitize_text_field( $activecampaign_for_woocommerce_optin_checkbox_text ) ); ?>",
	"checkbox_display_option": "<?php echo esc_html( sanitize_text_field( $activecampaign_for_woocommerce_optin_checkbox_display_option ) ); ?>",
	"loading": false
}'>

	<ac-notice v-for="notice in notices" :level="notice.level" :message="notice.message" :key="notice.message"></ac-notice>

	<h1>
		<?php
			esc_html_e( 'ActiveCampaign for WooCommerce Settings', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN );
		?>
	</h1>

	<b-tabs type="is-boxed" v-model="activeTab">

		<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="activecampaign-for-woocommerce-options-form">

			<input type="hidden" name="action" value="activecampaign_for_woocommerce_settings">

			<?php
				wp_nonce_field( 'activecampaign_for_woocommerce_settings_form', 'activecampaign_for_woocommerce_settings_nonce_field' );
			?>

			<b-tab-item id="activecampaign_connection" label="<?php esc_html_e( 'Connection', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN ); ?>">

				<h2>
					<?php
						esc_html_e( 'API Credentials', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN );
					?>
				</h2>
				<p>
					<?php
						esc_html_e( 'To find your ActiveCampaign API URL and API Key, log into your account and navigate to Settings > Developer > API Access.', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN );
					?>
				</p>

				<ac-input name="api_url" label="<?php esc_html_e( 'API URL', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN ); ?>" v-model="api_url"></ac-input>
				<ac-input name="api_key" label="<?php esc_html_e( 'API Key', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN ); ?>" v-model="api_key"></ac-input>

			</b-tab-item>

			<b-tab-item id="activecampaign_store" label="<?php esc_html_e( 'Store Settings', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN ); ?>">

				<h2>
					<?php
						esc_html_e( 'Abandoned Cart', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN );
					?>
				</h2>
				<p>
					<?php
						esc_html_e( 'How long should ActiveCampaign wait after a contact abandons a cart before triggering abandoned cart automations?', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN );
					?>
				</p>

				<ac-radio v-for='(label, value) in abCartOptions' :key='value' :label='label' :value='value' name='abcart_wait' :selected='abcart_wait'></ac-radio>

				<hr />

				<h2>
					<?php
						esc_html_e( 'Opt-in Checkbox', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN );
					?>
				</h2>
				<p>
					<?php
						esc_html_e( 'Configure what text should appear next to the opt-in checkbox, and whether that checkbox should be visible and checked by default.', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN );
					?>
				</p>

				<ac-input name="optin_checkbox_text" label="<?php esc_html_e( 'Checkbox text', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN ); ?>" v-model="optin_checkbox_text"></ac-input>

				<h3>
					<?php
						esc_html_e( 'Checkbox display options', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN );
					?>
				</h3>

				<ac-radio v-for='(label, value) in checkboxDisplayOptions' :key='value' :label='label' :value='value' name='checkbox_display_option' :selected='checkbox_display_option'></ac-radio>

			</b-tab-item>

			<ac-button @click="ajaxUpdateSettings" :loading="loading">
				<?php
					esc_html_e( 'Update Settings', ACTIVECAMPAIGN_FOR_WOOCOMMERCE_LOCALIZATION_DOMAIN );
				?>
			</ac-button>

		</form>

	</b-tabs>

</div>
