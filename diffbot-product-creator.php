<?php
/**
 * Plugin Name: Diffbot Product Creator
 * Plugin URI: http://www.joshcanhelp.com
 * Description: Create products in the WordPress database using the Diffbot API
 * Version: 0.1.0
 * Author: Josh Cunningham
 * Author URI: http://www.joshcanhelp.com
 * Text Domain: diffbotproducts
 * Domain Path: /languages/
 * License: GPL v3
 *
 * @package WordPress
 */

/*
 * Constants
 */
define( 'DIFFBOT_PRODUCTS_VERSION', '0.1.0' );
define( 'DIFFBOT_PRODUCTS_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets/' );
define( 'DIFFBOT_PRODUCT_API_URL', 'http://api.diffbot.com/v3/product' );
define( 'DIFFBOT_PRODUCT_CAPABILITY', 'publish_posts' );

/*
 * Includes
 */
require_once 'inc/data-lookup.php';
require_once 'inc/diffbot-product-register.php';
require_once 'inc/diffbot-product-ui.php';
require_once 'inc/diffbot-product-meta-fields.php';
require_once 'inc/diffbot-product-create.php';
require_once 'inc/classes/class-diffbotapi.php';

/**
 * Create the new rewrites for the custom post type
 */
function dbprod_plugin_activation() {
	add_option( 'dbprod_plugin_activated', 1, '', 'no' );
}
register_activation_hook( __FILE__, 'dbprod_plugin_activation' );

/**
 * CSS and JS for admin pages
 */
function dbprod_admin_enqueue_scripts() {

	global $pagenow;

	// Only need these on the create product page.
	// phpcs:ignore
	if ( 'edit.php' !== $pagenow || ! isset( $_GET['post_type'] ) || 'diffbot-product' === $_GET['post_type'] ) {
		return;
	}

	wp_enqueue_style(
		'dbprod-admin',
		DIFFBOT_PRODUCTS_ASSETS_URL . 'css/wp-admin.css',
		false,
		DIFFBOT_PRODUCTS_VERSION
	);

	wp_enqueue_script(
		'dbprod-admin',
		DIFFBOT_PRODUCTS_ASSETS_URL . 'js/wp-admin.js',
		array( 'jquery' ),
		DIFFBOT_PRODUCTS_VERSION,
		true
	);

	wp_localize_script(
		'dbprod-admin',
		'diffbotProduct',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'debug'   => WP_DEBUG,
			'i18n'    => array(
				'error'        => __( 'An error occurred; please refresh and try again', 'diffbotproducts' ),
				'productAdded' => __( 'Product added!', 'diffbotproducts' ),
				'productFound' => __( 'Product found!', 'diffbotproducts' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'dbprod_admin_enqueue_scripts' );
