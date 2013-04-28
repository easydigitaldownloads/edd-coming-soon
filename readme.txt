=== Easy Digital Downloads - Coming Soon and Custom Statuses ===
Contributors: sumobi, sc0ttkclark
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EFUPMPEZPGW7L
Tags: easy digital downloads, digital downloads, e-downloads, edd, coming soon, sumobi
Requires at least: 3.3
Tested up to: 3.6
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows Coming Soon or Custom Status text instead of normal pricing for downloads in Easy Digital Downloads.

== Description ==

This plugin requires [Easy Digital Downloads](http://wordpress.org/extend/plugins/easy-digital-downloads/ "Easy Digital Downloads"). It allows downloads to be "Coming Soon" or have Custom Status text, and prevents them from being added to the cart. It does a few things:

1. Adds a checkbox to the download configuration so you can set the download to Coming Soon / Custom Status.
1. Adds a text field to the download configuration so you can set the text to show (default "Coming Soon").
1. Replaces the download's price in the pricing admin column with "Coming Soon" or the Custom Status text.
1. Displays "Coming Soon" or the Custom Status text instead of the price when using the [downloads] shortcode, and anywhere else where the edd_price() function has been called.
1. Prevents the coming soon download from being purchased. The plugin will remove the purchase button and stop the download from being added to cart via the edd_action. Eg ?edd_action=add_to_cart&download_id=XXX

= Filtering the coming soon text =

Paste this into your functions.php and modify the text below:

    function themename_coming_soon_text() {
	    return 'Available Soon';
    }
    add_filter( 'edd_cs_coming_soon_text', 'themename_coming_soon_text' );


== Installation ==

1. Unpack the entire contents of this plugin zip file into your `wp-content/plugins/` folder locally
1. Upload to your site
1. Navigate to `wp-admin/plugins.php` on your site (your WP Admin plugin page)
1. Activate this plugin

OR you can just install it with WordPress by going to Plugins >> Add New >> and type this plugin's name

Then proceed to mark downloads as "Coming Soon".

== Screenshots ==

1. Download Configuration metabox with new coming soon checkbox option

== Changelog ==

= 1.1 =
* Added the ability to set custom text per download, default remains "Coming Soon"

= 1.0 =
* Initial release