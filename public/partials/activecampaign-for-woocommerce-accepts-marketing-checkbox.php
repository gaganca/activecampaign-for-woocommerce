<?php

/**
 * A checkbox to be rendered as part of the WooCommerce checkout form.
 *
 * Allows a customer to accept marketing or not.
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/public/partials
 */

/**
 * The parent instance of the Public class that is calling this template.
 *
 * @var Activecampaign_For_Woocommerce_Public $activecampaign_for_woocommerce_public_helper
 */
$activecampaign_for_woocommerce_public_helper = $this;

$activecampaign_for_woocommerce_is_checked = $activecampaign_for_woocommerce_public_helper->accepts_marketing_checkbox_is_checked();

$activecampaign_for_woocommerce_accepts_marketing_label = $activecampaign_for_woocommerce_public_helper->label_for_accepts_marketing_checkbox();

?>

<p class="form-row form-row-wide">
	<input
		id="activecampaign_for_woocommerce_accepts_marketing"
		class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
		type="checkbox"
		name="activecampaign_for_woocommerce_accepts_marketing"
		value="1"
		<?php
		if ( $activecampaign_for_woocommerce_is_checked ) {
			echo 'checked="checked"';
		}
		?>
	/>

	<label
		for="activecampaign_for_woocommerce_accepts_marketing"
		class="woocommerce-form__label woocommerce-form__label-for-checkbox inline"
	>
		<span>
			<?php
			echo esc_html( $activecampaign_for_woocommerce_accepts_marketing_label );
			?>
		</span>
	</label>
</p>

<div class="clear"></div>


