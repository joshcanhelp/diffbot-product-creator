<?php
/**
 * Functions for looking up data.
 *
 * @since   0.1.0
 *
 * @package WordPress
 */

/**
 * Get post meta wrapper function.
 *
 * @param string $meta_key - Meta key to look up.
 * @param int    $pid      - Product ID for value.
 *
 * @return mixed
 */
function dbprod_meta( $meta_key, $pid = 0 ) {

	// Set the global post ID if none is provided.
	if ( empty( $pid ) ) {
		$pid = get_the_ID();
	}

	return get_post_meta( $pid, $meta_key, true );
}
