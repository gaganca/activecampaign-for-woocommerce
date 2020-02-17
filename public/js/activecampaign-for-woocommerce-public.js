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

	// Holds the modified email address, first and last name values
	var billing_email = '';
	var billing_first_name = '';
	var billing_last_name = '';
	
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

	// If this was stubbed in a test, use the stub
	if (typeof window.sync_guest_abandoned_cart_wait_set !== 'undefined') {
		var sync_guest_abandoned_cart_wait_set = window.sync_guest_abandoned_cart_wait_set;
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
				email: billing_email,
				first_name: billing_first_name,
				last_name: billing_last_name
			},
			success: function (response) {
			}
		});

		// Release the wait so it can be set again.
		sync_guest_abandoned_cart_wait = null;
	}
	
	/**
	*	Validate email, using the regex from ac_str_email_pattern
	* ac_global/functions/str.php in Hosted
	*/
	function validate_email(input_email) {
		var email_regex = /[\+_a-z0-9\u00a1-\uffff-'&=]+(?:\.[\+_a-z0-9\u00a1-\uffff-'&=]+)*\.{0,1}@[a-z0-9\u00a1-\uffff-]+(?:\.[a-z0-9\u00a1-\uffff-]+)*(?:\.[a-z]{2,})/; 
		return email_regex.test(String(input_email).toLowerCase());
	}

	$( document ).ready(function() {
		$( '.woocommerce-checkout #billing_email' ).keyup(function() {
			var $checkout = $(this).closest('.woocommerce-checkout');
			billing_first_name = $checkout.find("#billing_first_name").val();
			billing_last_name = $checkout.find("#billing_last_name").val();

			var billing_email_value = $( this ).val();

			var billing_email_val_not_empty = billing_email_value !== '';
			var billing_email_val_changed = billing_email_value !== billing_email;
			var billing_email_val_valid_email = validate_email(billing_email_value);

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
