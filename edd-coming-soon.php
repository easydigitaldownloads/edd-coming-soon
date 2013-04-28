<?php
/*
Plugin Name: Easy Digital Downloads - Custom Status
Plugin URI: http://scottkclark.com/
Description: Allows "custom status" downloads (not available for purchase) in Easy Digital Downloads
Version: 1.0
Author: Scott Kingsley Clark (based on code from Andrew Munro)
Author URI: http://scottkclark.com
License: GPL-2.0+
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

// Plugin constants
if ( !defined( 'EDD_COMING_SOON' ) )
	define( 'EDD_COMING_SOON', '1.1' );

if ( !defined( 'EDD_COMING_SOON_URL' ) )
	define( 'EDD_COMING_SOON_URL', plugin_dir_url( __FILE__ ) );

if ( !defined( 'EDD_COMING_SOON_DIR' ) )
	define( 'EDD_COMING_SOON_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Internationalization
 *
 * @since 1.0
 */
function edd_coming_soon_textdomain () {
	load_plugin_textdomain( 'edd-coming-soon', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'edd_coming_soon_textdomain' );

/**
 * Check if it's a Custom Status download
 *
 * @param int $download_id Download Post ID
 *
 * @return boolean Whether Custom Status is active
 *
 * @since 1.0
 */
function edd_coming_soon_is_active ( $download_id = 0 ) {
	global $post;

	if ( empty( $download_id ) && is_object( $post ) && isset( $post->ID ) )
		$download_id = $post->ID;

	if ( !empty( $download_id ) )
		return (boolean) get_post_meta( $download_id, 'edd_coming_soon', true );

	return false;
}

/**
 * Render the Custom Status checkbox
 *
 * @param int $post_id Post ID
 *
 * @since 1.0
 */
function edd_coming_soon_render_option ( $post_id ) {
	$coming_soon = (boolean) get_post_meta( $post_id, 'edd_coming_soon', true );
	$coming_soon_text = get_post_meta( $post_id, 'edd_coming_soon_text', true );

	// Default
	$default_text = apply_filters( 'edd_cs_coming_soon_text', __( 'Coming Soon', 'edd-coming-soon' ) );
?>
	<p>
		<label for="edd_coming_soon">
			<input type="checkbox" name="edd_coming_soon" id="edd_coming_soon" value="1" <?php checked( true, $coming_soon ); ?> />
			<?php _e( 'Enable Coming Soon / Custom Status download', 'edd-coming-soon' ); ?>
		</label>
	</p>

	<p id="edd_coming_soon_container"<?php echo ( $coming_soon ? '' : ' style="display:none;"' ); ?>>
		<label for="edd_coming_soon_text">
			<input type="text" name="edd_coming_soon_text" id="edd_coming_soon_text" size="45" style="width:110px;" value="<?php echo esc_attr( $coming_soon_text ); ?>" />
			<?php echo sprintf( __( 'Custom Status text (default: <em>%s</em>)', 'edd-coming-soon' ), $default_text ); ?>
		</label>
	</p>
<?php
}
add_action( 'edd_meta_box_fields', 'edd_coming_soon_render_option', 10 );

/**
 * Hook into save filter and add the download image fields
 *
 * @param array $fields Array of fields to save for EDD
 *
 * @return array Array of fields to save for EDD
 *
 * @since 1.0
 */
function edd_coming_soon_metabox_fields_save ( $fields ) {
	$fields[] = 'edd_coming_soon';
	$fields[] = 'edd_coming_soon_text';

	return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_coming_soon_metabox_fields_save' );

/**
 * Hook into currency filter and if the download is a Custom Status download, return no currency
 *
 * @return string The currency code to be used
 *
 * @since 1.0
 */
function edd_coming_soon_edd_currency ( $currency ) {
	if ( edd_coming_soon_is_active() )
		return '';

	return $currency;
}
add_filter( 'edd_currency', 'edd_coming_soon_edd_currency' );

/**
 * Filter price function so it shows Custom Status text instead of price
 *
 * @param string $price Price text
 * @param int $download_id Download Post ID
 *
 * @return string Price text to be shown
 *
 * @since 1.0
 */
function edd_coming_soon_filter_price ( $price, $download_id ) {
	if ( edd_coming_soon_is_active( $download_id ) ) {
		// Default
		$coming_soon_text = apply_filters( 'edd_cs_coming_soon_text', __( 'Coming Soon', 'edd-coming-soon' ) );

		// Custom override
		$custom_text = get_post_meta( $download_id, 'edd_coming_soon_text', true );

		if ( 0 < strlen( $custom_text ) )
			$coming_soon_text = $custom_text;

		return apply_filters( 'edd_coming_soon_text', $coming_soon_text, $download_id );
	}

	return $price;
}
add_filter( 'edd_download_price', 'edd_coming_soon_filter_price', 10, 2 );

/**
 * Only show purchase form if it's not a Custom Status download
 *
 * @param string $purchase_form Form HTML
 * @param array $args Arguments for display
 *
 * @return string Form HTML
 *
 * @since 1.0
 */
function edd_coming_soon_purchase_download_form ( $purchase_form, $args ) {
	if ( edd_coming_soon_is_active( $args[ 'download_id' ] ) )
		return '';

	return $purchase_form;
}
add_filter( 'edd_purchase_download_form', 'edd_coming_soon_purchase_download_form', 10, 2 );

/**
 * Prevent download from being added to cart (free or priced) with ?edd_action=add_to_cart&download_id=XXX
 *
 * @param int $download_id Download Post ID
 *
 * @since 1.0
 */
function edd_coming_soon_pre_add_to_cart ( $download_id ) {
	if ( edd_coming_soon_is_active( $download_id ) ) {
		$add_text = apply_filters( 'edd_coming_soon_pre_add_to_cart', __( 'This download cannot be purchased', 'edd-coming-soon' ), $download_id );

		wp_die( $add_text, '', array( 'back_link' => true ) );
	}
}
add_action( 'edd_pre_add_to_cart', 'edd_coming_soon_pre_add_to_cart' );

function edd_coming_soon_admin_scripts ( $hook ) {
	global $post;

	if ( is_object( $post ) && $post->post_type != 'download' ) {
		return;
	}

	wp_enqueue_script( 'edd-cp-admin-scripts', EDD_COMING_SOON_URL . 'js/edd-coming-soon-admin.js', array( 'jquery' ), EDD_COMING_SOON );
}
add_action( 'admin_enqueue_scripts', 'edd_coming_soon_admin_scripts' );