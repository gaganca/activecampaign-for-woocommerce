=== Plugin Name ===
Contributors: acteamintegrations, bartboy011
Tags: marketing, ecommerce, woocommerce, email, activecampaign, abandoned cart
Requires at least: 4.7
Tested up to: 5.0.3
Stable tag: 1.2.2
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ActiveCampaign for WooCommerce enables you to create abandoned cart automations and send emails to your contacts who abandon carts.

== Description ==

ActiveCampaign for WooCommerce automatically syncs your customers and their purchase data into ActiveCampaign, including abandoned carts and whether or not the customer opted-in to marketing.

ActiveCampaign for WooCommerce gives you the power to:
- Sync all customers and their purchase data into ActiveCampaign in real time
- Configure how long until a cart should be considered abandoned
- Provide an opt-in checkbox on your checkout form for customers to opt-in to marketing
- Configure what the opt-in checkbox says and if it's checked by default
- Trigger automations when a customer abandons a cart

== Installation ==

Before You Start
- Our plugin requires you to have the WooCommerce plugin installed and activated in WordPress.
- Your hosting environment should meet WooCommerce's minimum requirements, including PHP 7.0 or greater.

Installation
1. In your ActiveCampaign account, navigate to Settings.
2. Click the Integrations tab.
3. If your WooCommerce store is already listed here, skip to step 7. Otherwise, continue to step 4.
4. Click the "Add Integration" button.
5. Enter the URL of your WooCommerce site.
6. Follow the connection process that appears in WooCommerce.
7. In your WooCommerce store, install the "ActiveCampaign for WooCommerce" plugin and activate it.
8. Navigate to the plugin settings page (Settings > ActiveCampaign for WooCommerce)
9. Enter your ActiveCampaign API URL and API Key in the provided boxes.
10. Click "Update Settings".

== Changelog ==

= 1.2.2 =
* Prevent vendor package collisions with other plugins
* Increased error logging for easier debugging

= 1.2.0 =
* Accepts Marketing for Guests
* Local setup and readme updates

= 1.1.0 =
* Added support for guest abandoned carts

= 1.0.3 =
* Prevent edgecase where updating the Abandoned Cart time causes an exception

= 1.0.2 =
* Allow Woocommerce API calls to work when Wordpress is behind a load balancer
* Fixed a bug where abandoned cart functionality would not work if an item had no categories

= 1.0.1 =
* Prevent exceptions from breaking WooCommerce cart functionality

= 1.0.0 =
* Initial Release
