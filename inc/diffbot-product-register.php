<?php
/**
 * Functions to register post types and taxonomies.
 *
 * @since   0.1.0
 *
 * @package WordPress
 */

/**
 * Declare the custom post type diffbot-product
 * Hooked to: init
 */
function dbprod_cpt_register() {
	register_post_type(
		'diffbot-product',
		array(
			'description'         => __( 'Products added via the Diffbot API', 'diffbotproducts' ),
			'labels'              => array(
				'name'               => _x( 'Products', 'post type general name', 'diffbotproducts' ),
				'singular_name'      => _x( 'Product', 'post type singular name', 'diffbotproducts' ),
				'add_new'            => _x( 'Add Product', 'diffbot-product', 'diffbotproducts' ),
				'add_new_item'       => __( 'Add New Product', 'diffbotproducts' ),
				'edit_item'          => __( 'Edit Product', 'diffbotproducts' ),
				'new_item'           => __( 'New Product', 'diffbotproducts' ),
				'all_items'          => __( 'All Products', 'diffbotproducts' ),
				'view_item'          => __( 'View Product', 'diffbotproducts' ),
				'not_found'          => __( 'No Products found', 'diffbotproducts' ),
				'not_found_in_trash' => __( 'No Products found in Trash', 'diffbotproducts' ),
				'menu_name'          => __( 'Products', 'diffbotproducts' ),
			),
			'public'              => true,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'has_archive'         => true,
			'hierarchical'        => false,
			'rewrite'             => array(
				'slug' => 'product',
			),
			'menu_position'       => 22,
			'menu_icon'           => 'dashicons-cart',
			'capability_type'     => 'post',

			// Does not allow adding from the WP UI.
			'capabilities'        => array(
				'create_posts' => false,
			),
			'map_meta_cap'        => true,
			'supports'            => array(
				'title',
				'editor',
			),
		)
	);

	// Catch the activation flag if it's there.
	if ( get_option( 'dbprod_plugin_activated', false ) ) {
		flush_rewrite_rules();
		delete_option( 'dbprod_plugin_activated' );
	}
}
add_action( 'init', 'dbprod_cpt_register' );
