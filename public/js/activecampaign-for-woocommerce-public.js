/**
 * Bootstrap the JS for the plugin
 *
 * @link       https://www.activecampaign.com/
 * @since      1.0.0
 *
 * @package    Activecampaign_For_Woocommerce
 * @subpackage Activecampaign_For_Woocommerce/public/js
 */

(function( $ ) {
	'use strict';

	// Holds the modified email address value
	var billing_email = '';

	// Holds the setTimeout executor
	var sync_guest_abandoned_cart_wait = null;

	/**
	 * Set a wait of a couple seconds so we don't
	 * continually kick off Ajax requests for every
	 * character typed after an email value is valid.
	 *
	 * For example, if I type a valid email: ac@test.com
	 * we'll kick off the Ajax request. If I go back and
	 * change the email to add a bunch more characters:
	 * ac-supportrequest@test.com
	 * ... we don't want 14 more Ajax requests to run immediately.
	 * Instead, we space out the requests so it gives the user
	 * time to fully type in the email address,
	 * but it should still be fast enough to capture an abandoned cart.
	 */
	function sync_guest_abandoned_cart_wait_set() {
		if ( sync_guest_abandoned_cart_wait ) {
			clearTimeout( sync_guest_abandoned_cart_wait );
		}

		sync_guest_abandoned_cart_wait = setTimeout(
			function() {
				sync_guest_abandoned_cart();
			},
			2000
		);
	}

	/**
	 * Kick off the Ajax request to sync the guest
	 * abandoned cart to the AC account.
	 */
	function sync_guest_abandoned_cart() {
		jQuery.ajax({
			type: 'post',
			dataType: 'json',
			url: public_vars.ajaxurl,
			data: {
				action: "activecampaign_for_woocommerce_cart_sync_guest",
				email: billing_email
			},
			success: function (response) {
			}
		});

		// Release the wait so it can be set again.
		sync_guest_abandoned_cart_wait = null;
	}

	$( document ).ready(function() {
		$( '.woocommerce-billing-fields #billing_email' ).keyup(function() {
			var billing_email_value = $( this ).val();

			var billing_email_val_not_empty = billing_email_value !== '';
			var billing_email_val_changed = billing_email_value !== billing_email;

			var billing_email_val_valid_email = false;
			var location_of_ampersand = billing_email_value.indexOf('@');
			var ampersand_exists = location_of_ampersand !== -1;
			var tld_dot_after_ampersand = billing_email_value.indexOf('.') > location_of_ampersand;
			var tld_dot_not_last_char = billing_email_value.lastIndexOf('.') < billing_email_value.length - 1;

			if ( ampersand_exists && tld_dot_after_ampersand && tld_dot_not_last_char ) {
				billing_email_val_valid_email = true;
			}

			if (
				billing_email_val_not_empty &&
				billing_email_val_changed &&
				billing_email_val_valid_email
			) {
				// The email value looks good - let's queue the request.
				sync_guest_abandoned_cart_wait_set();
			}

			billing_email = billing_email_value;
		});
	});

})( jQuery );
