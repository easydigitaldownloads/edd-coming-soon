=== Easy Digital Downloads - Coming Soon ===
Contributors: easydigitaldownloads, sumobi, sc0ttkclark, julien731
Tags: easy digital downloads, digital downloads, e-downloads, edd, coming soon, sumobi
Requires at least: 3.3
Tested up to: 4.8.1
Stable tag: 1.3.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows Coming Soon or Custom Status text instead of normal pricing for downloads in Easy Digital Downloads.

== Description ==

This plugin requires [Easy Digital Downloads](https://wordpress.org/plugins/easy-digital-downloads/ "Easy Digital Downloads"). It allows downloads to be "Coming Soon" or have Custom Status text, and prevents them from being added to the cart. It does a few things:

1. Adds a checkbox to the download configuration so you can set the download to Coming Soon / Custom Status.
1. Adds a text field to the download configuration so you can set the text to show (default "Coming Soon").
1. Adds "Coming Soon" or your custom status text underneath the price on the admin pricing column
1. Displays "Coming Soon" or the Custom Status text instead of the price when using the [downloads] shortcode, and anywhere else where the edd_price() function has been called.
1. Prevents the coming soon download from being purchased. The plugin will remove the purchase button and stop the download from being added to cart via the edd_action. Eg ?edd_action=add_to_cart&download_id=XXX
1. Allows customers to vote on a specific download. A download's votes are listed on the edit/publish page and on the admin dashboard

** Filter examples **

Example filter of how you can change the default coming soon text. Copy this function to your functions.php

    function edd_coming_soon_modify_default_status_text() {
	    return 'Not long now!';
    }
    add_filter( 'edd_cs_coming_soon_text', 'edd_coming_soon_modify_default_status_text' );


Example filter of how you can modify the markup of the coming soon text in the admin columns. Copy this function to your functions.php

    function edd_coming_soon_modify_admin_column_text( $custom_text ) {
	    return '<h2>' . $custom_text . '</h2>';
    }
    add_filter( 'edd_coming_soon_display_admin_text', 'edd_coming_soon_modify_admin_column_text' );


Example filter of how you can modify the markup of the coming soon text on the front end. Copy this function to your functions.php

    function edd_coming_soon_modify_text( $custom_text ) {
	    return '<h2>' . $custom_text . '</h2>';
    }
    add_filter( 'edd_coming_soon_display_text', 'edd_coming_soon_modify_text' );


Example filter of how you can modify the message that displays when someone tries to purchase a download that is coming soon.
This message can be tested by appending ?edd_action=add_to_cart&download_id=XXX to your URL, substituting XXX with your download ID

    function edd_coming_soon_modify_prevent_download_message( $download_id ) {
	    return __( 'This item cannot be purchased just yet, hang tight!', 'edd-coming-soon' );
    }
    add_filter( 'edd_coming_soon_pre_add_to_cart', 'edd_coming_soon_modify_prevent_download_message' );

== Installation ==

1. Unpack the entire contents of this plugin zip file into your `wp-content/plugins/` folder locally
1. Upload to your site
1. Navigate to `wp-admin/plugins.php` on your site (your WP Admin plugin page)
1. Activate this plugin

OR you can just install it with WordPress by going to Plugins >> Add New >> and type this plugin's name

Mark downloads as "Coming Soon" from the download configuration metabox. Optionally you can enter in custom text

== Frequently Asked Questions ==

= I'm not seeing the "Coming Soon" text on my download grid =

Some themes may have coded their own custom solution for displaying the downloads. Try switching to the default WordPress theme to see if you can see the text.

= I'm still seeing a price on my single download page =

It depends on how your theme displays the price on your single download page. If your theme uses the default EDD purchase form then this will be removed fine. However some themes might have their own function for displaying the price so you'll need to edit your theme to remove it. You can use the following condition to wrap blocks of code that shouldn't be displayed such as the price:

    <?php if ( ! edd_coming_soon_is_active() ) : ?>
         // the code you don't want to show when a download is set to coming soon
    <?php endif; ?>

== Screenshots ==

1. Easy Digital Download's download configuration metabox with the new coming soon option

1. The coming soon text is displayed underneath the price on the admin pricing columns

1. The download's price is removed from the standard download grid, and the coming soon text is shown

1. The download's coming soon text is shown after the content on the single download page. This can be removed

== Changelog ==

= 1.3.3 =
* New: Added a edd_coming_soon_vote_btn_classes filter hook
* New: Added activation class to check for the existence of Easy Digital Downloads when activating
* Tweak: Updated plugin information and readme
* Tweak: Allow $custom_text variable to be used with the edd_coming_soon_display_admin_text filter hook
* Tweak: Allow $custom_text variable to be used with the edd_coming_soon_display_text filter hook
* Tweak: The coming soon notice now uses EDD's native edd_after_download_content hook
* Fix: Fixed an instance where the vote count code appear on the front-end of the website

= 1.3.2 =
* Fix: Various PHP notices

= 1.3.1 =
* New: edd_coming_soon_voting_enabled() function to check whether a download has voting enabled
* New: edd_coming_soon_get_votes() function to get the total votes for a download
* New: French translation, props fxbenard
* Tweak: Creates the "_edd_coming_soon_votes" meta key on save if voting is enabled for a download and it hasn't received any votes

= 1.3 =
* Fix: Moved the plugin's options to the "download settings" metabox
* New: Voting feature. Users can now express their interest in downloads that are marked as coming soon.
* New: Dashboard widget for showing how many votes coming soon downloads have
* New: [edd_cs_vote] shortcode for allowing a user to vote on a download from any page

= 1.2 =
* Fix: Coming soon text not displaying on front-end
* New: Coming soon text is now shown underneath the price in the admin columns
* New: Added example filters

= 1.1 =
* Added the ability to set custom text per download, default remains "Coming Soon". Thanks to @sc0ttkclark

= 1.0 =
* Initial release
