<?php
/**
 * Functions for working with the product meta fields.
 *
 * @since   0.1.0
 *
 * @package WordPress
 */

/**
 * Core set of meta fields
 *
 * @return array
 */
function dbprod_custom_meta_fields() {

	return array(
		'dbprod_offerPrice'   => array(
			'label'       => __( 'Offer Price', 'diffbotproducts' ),
			'description' => '',
			'type'        => 'text',
			'post_col'    => true,
		),
		'dbprod_regularPrice' => array(
			'label'       => __( 'Regular Price', 'diffbotproducts' ),
			'description' => '',
			'type'        => 'text',
			'post_col'    => true,
		),
		'dbprod_pageUrl'      => array(
			'label'       => __( 'Product Page URL', 'diffbotproducts' ),
			'description' => '',
			'type'        => 'url',
			'post_col'    => false,
		),
	);
}


/**
 * Do not display diffbot-product meta fields in the custom fields UI
 * Hooked to: is_protected_meta
 *
 * @param bool   $protected - Original protected value.
 * @param string $meta_key - Meta key to protect or not.
 *
 * @return bool
 */
function dbprod_protect_meta( $protected, $meta_key ) {
	$meta_fields = dbprod_custom_meta_fields();

	if ( array_key_exists( $meta_key, $meta_fields ) ) {
		return true;
	}

	return $protected;
}
add_filter( 'is_protected_meta', 'dbprod_protect_meta', 10, 2 );


/**
 * Reacts to a global $_POST containing core meta fields
 *
 * @param integer $pid  - Product ID.
 * @param array   $data - POST data to process.
 */
function dbprod_save_posted_meta( $pid, array $data ) {

	foreach ( dbprod_custom_meta_fields() as $meta_key => $meta_info ) {

		// Sanitize based on field type.
		$clean_meta = '';
		switch ( $meta_info['type'] ) {
			case 'url':
				if ( isset( $data[ $meta_key ] ) ) {
					$clean_meta = filter_var( $data[ $meta_key ], FILTER_SANITIZE_URL );
				}
				break;
			default:
				if ( isset( $data[ $meta_key ] ) ) {
					$clean_meta = sanitize_text_field( $data[ $meta_key ] );
				}
		}

		if ( empty( $dirty_meta ) ) {
			delete_post_meta( $pid, $meta_key );
		} else {
			update_post_meta( $pid, $meta_key, $clean_meta );
		}
	}
}


/**
 * Hook the save_post action to sanitize and save the meta fields
 * Hooked to: save_post
 *
 * @param integer $pid  - Product ID.
 * @param WP_Post $post - WP_Post object to act on.
 */
function dbprod_hook_save_post( $pid, $post ) {

	// Is this the correct post type?
	if ( 'diffbot-product' !== $post->post_type ) {
		return;
	}

	// Is there a nonce?
	if ( empty( $_POST['__wpnonce_dbprod_meta_fields'] ) ) {
		return;
	}

	// Is the nonce valid?
	// phpcs:ignore
	if ( ! wp_verify_nonce( $_POST['__wpnonce_dbprod_meta_fields'], 'dbprod-meta-field-nonce' ) ) {
		return;
	}

	// Is the user authorized?
	if ( ! current_user_can( DIFFBOT_PRODUCT_CAPABILITY ) ) {
		return;
	}

	dbprod_save_posted_meta( $pid, $_POST );
}
add_action( 'save_post', 'dbprod_hook_save_post', 20, 2 );
