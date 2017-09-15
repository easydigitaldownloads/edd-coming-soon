<?php
/**
 * Functions
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if it's a Custom Status download
 *
 * @param int $download_id 	Download Post ID
 *
 * @return boolean 			Whether Custom Status is active
 *
 * @since 1.0
 */
function edd_coming_soon_is_active( $download_id = 0 ) {
	global $post;

	if ( empty( $download_id ) && is_object( $post ) && isset( $post->ID ) )
		$download_id = $post->ID;

	if ( ! empty( $download_id ) )
		return (boolean) get_post_meta( $download_id, 'edd_coming_soon', true );

	return false;
}
