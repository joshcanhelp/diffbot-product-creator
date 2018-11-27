<?php
/**
 * Functions for creating the products in the WP database.
 *
 * @since 0.1.0
 *
 * @package WordPress
 */

/**
 * Queue the create product page
 * Hooked to: admin_menu
 */
function dbprod_add_product_page() {
	add_submenu_page(
		'edit.php?post_type=diffbot-product',
		__( 'Add New Diffbot Product', 'diffbotproducts' ),
		__( 'Add New', 'diffbotproducts' ),
		DIFFBOT_PRODUCT_CAPABILITY,
		'diffbot-add-product',
		'dbprod_add_product_page_display'
	);
}
add_action( 'admin_menu', 'dbprod_add_product_page' );


/**
 * Output the Add Product page
 */
function dbprod_add_product_page_display() {
	?>
	<div class="wrap dbprod-add-product-wrap">
		<h1><?php esc_html_e( 'Add Product with Diffbot', 'diffbotproducts' ); ?></h1>
		<div class="postbox">
			<div class="inside">
				<form action="" method="post" class="dbprod-add-product-form" id="js-dbprod-add-product-form">
					<label for="dbprod-prodUrl">
						<p class="label-text"><?php esc_html_e( 'Product URL', 'diffbotproducts' ); ?>:</p>
						<input required type="url" id="dbprod-prodUrl" name="dbprod-prodUrl" value=""
							placeholder="<?php esc_html_e( 'Enter valid product URL', 'diffbotproducts' ); ?>...">
					</label>
					<?php wp_nonce_field( 'dbprod-search-product', '__wpnonce_dbprod_search' ); ?>
					<input type="submit" class="button" value="<?php esc_html_e( 'Search', 'diffbotproducts' ); ?>">
					<span class="dbprod-message" id="js-dbprod-message"></span>
					<img class="dbprod-loading" id="js-dbprod-loading"
						src="<?php echo esc_url_raw( admin_url( 'images/loading.gif' ) ); ?>">
				</form>
				<div class="dbprod-add-product-result" id="js-dbprod-add-product-result">
					<a class="button button-primary" id="js-dbprod-create-product" class="alignright"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'dbprod-create-product' ) ); ?>">
						<?php esc_html_e( 'Create Product', 'diffbotproducts' ); ?></a>
					<h3 id="js-dbprod-title"></h3>
					<p class="dbprod-desc">
						<span id="js-dbprod-desc"></span>
					</p>
					<p class="dbprod-price">
						<strong><?php esc_html_e( 'Offer Price', 'diffbotproducts' ); ?></strong>:
						<span id="js-dbprod-offerPrice"></span><br>
						<strong><?php esc_html_e( 'Regular Price', 'diffbotproducts' ); ?></strong>:
						<span id="js-dbprod-regularPrice"></span>
					</p>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Do the initial search for a product based on a URL
 * Hooked to: wp_ajax_dbprod_search_product
 */
function dbprod_ajax_search_product() {

	check_ajax_referer( 'dbprod-search-product', 'nonce' );

	// Is this user authorized?
	if ( ! current_user_can( DIFFBOT_PRODUCT_CAPABILITY ) ) {
		dbprod_die_with_error( __( 'User is not authorized', 'diffbotproducts' ) );
		die();
	}

	// Do we have a page URL to use?
	if ( empty( $_POST['pageUrl'] ) ) {
		dbprod_die_with_error( __( 'No pageUrl', 'diffbotproducts' ) );
		die();
	}

	$page_url = sanitize_text_field( wp_unslash( $_POST['pageUrl'] ) );

	// Kickstart the Diffbot API and search.
	$diffbot       = new DiffbotApi();
	$diffbot_prods = $diffbot->search_products( $page_url );

	// Something went wrong with the API call, format the error and return.
	if ( is_wp_error( $diffbot_prods ) ) {
		dbprod_die_with_error( $diffbot_prods->get_error_message() );
		die();
	}

	echo wp_json_encode( array( 'items' => $diffbot_prods ) );
	die();
}
add_action( 'wp_ajax_dbprod_search_product', 'dbprod_ajax_search_product' );


/**
 * Do the initial search for a product based on a URL
 * Hooked to: wp_ajax_dbprod_create_product
 */
function dbprod_ajax_create_product() {

	check_ajax_referer( 'dbprod-create-product', 'nonce' );

	// Is this user authorized?
	if ( ! current_user_can( DIFFBOT_PRODUCT_CAPABILITY ) ) {
		dbprod_die_with_error( __( 'User is not authorized', 'diffbotproducts' ) );
	}

	// Do we have a title to use?
	if ( empty( $_POST['post_title'] ) ) {
		dbprod_die_with_error( __( 'No post_title', 'diffbotproducts' ) );
		die();
	}

	// Do we have content to use?
	if ( empty( $_POST['post_content'] ) ) {
		dbprod_die_with_error( __( 'No post_content', 'diffbotproducts' ) );
		die();
	}

	$create_results = wp_insert_post(
		array(
			'post_title'   => sanitize_text_field( wp_unslash( $_POST['post_title'] ) ),
			'post_content' => sanitize_text_field( wp_unslash( $_POST['post_content'] ) ),
			'post_status'  => 'publish',
			'post_type'    => 'diffbot-product',
		)
	);

	// Post was not created, format the error and return.
	if ( is_wp_error( $create_results ) ) {
		dbprod_die_with_error( $create_results->get_error_message() );
	}

	dbprod_save_posted_meta( $create_results, $_POST );
	die( '{}' );

}
add_action( 'wp_ajax_dbprod_create_product', 'dbprod_ajax_create_product' );

/**
 * Echo an error as JSON and end the process.
 *
 * @param string $msg - Message to output.
 */
function dbprod_die_with_error( $msg ) {
	echo wp_json_encode(
		array(
			'error' => 1,
			'msg'   => $msg,
		)
	);
	die();
}
