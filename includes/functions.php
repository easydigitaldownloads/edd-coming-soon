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

/**
 * Check if a download has voting enabled.
 *
 * @since   1.3.1
 * @return  boolean
 */
function edd_coming_soon_voting_enabled( $download_id = 0 ) {

	if ( ! $download_id ) {
		return;
	}

	$voting_enabled = get_post_meta( $download_id , 'edd_cs_vote_enable', true );

	if ( $voting_enabled ) {
		return true;
	}

	return false;
}
