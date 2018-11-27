<?php
/**
 * Functions to output the admin UI.
 *
 * @since   0.1.0
 *
 * @package WordPress
 */

/**
 * Add custom post columns for Products
 * Hooked to: manage_diffbot-product_posts_columns
 *
 * @param array $columns - Product grid columns.
 *
 * @return array
 */
function dbprod_product_post_columns( array $columns ) {

	// Iterate through meta field array and look for post_col flag.
	foreach ( dbprod_custom_meta_fields() as $meta_key => $meta_info ) {
		if ( ! empty( $meta_info['post_col'] ) ) {
			$columns[ $meta_key ] = $meta_info['label'];
		}
	}
	return $columns;
}
add_filter( 'manage_diffbot-product_posts_columns', 'dbprod_product_post_columns' );

/**
 * Populate the post edit column with the custom post meta data
 * Hooked to: manage_posts_custom_column
 *
 * @param string $col     - Column being shown.
 * @param int    $prod_id - Current product ID.
 */
function dbprod_product_custom_columns( $col, $prod_id ) {
	echo wp_kses( dbprod_meta( $col, $prod_id ), wp_kses_allowed_html( 'strip' ) );
}
add_action( 'manage_posts_custom_column', 'dbprod_product_custom_columns', 10, 2 );

/**
 * Add the custom meta fields to the diffbot-product edit screen
 * Hooked to: admin_menu
 */
function dbprod_add_meta_box() {
	add_meta_box(
		'dbprod-meta-field-box',
		__( 'Diffbot Product Custom Fields', 'diffbotproducts' ),
		'dbprods_display_meta_fields',
		'diffbot-product',
		'normal',
		'high'
	);
}
add_action( 'admin_menu', 'dbprod_add_meta_box' );

/**
 * Display the diffbot-product meta fields
 *
 * @see dbprod_custom_meta_fields
 */
function dbprods_display_meta_fields() {
	wp_nonce_field( 'dbprod-meta-field-nonce', '__wpnonce_dbprod_meta_fields' );

	echo '<table class="form-table dbprod-meta-field-table">';
	foreach ( dbprod_custom_meta_fields() as $meta_key => $meta ) {
		$curr_value = get_post_meta( get_the_ID(), $meta_key, true );
		printf(
			'<tr>
				<th scope="row"><label for="%s_field">%s</label></th>
				<td>
					<input type="%s" name="%s" id="%s_field" value="%s" class="large-text">
					<p class="description">%s</p>
				</td>
			</tr>',
			esc_attr( $meta_key ),
			wp_kses( $meta['label'], wp_kses_allowed_html( 'strip' ) ),
			esc_attr( $meta['type'] ),
			esc_attr( $meta_key ),
			esc_attr( $meta_key ),
			esc_attr( $curr_value ),
			wp_kses( $meta['description'], wp_kses_allowed_html( 'strip' ) )
		);
	}
	echo '</table>';
}
