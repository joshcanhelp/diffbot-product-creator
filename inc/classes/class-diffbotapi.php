<?php
/**
 * Contains class DiffbotApi
 *
 * @package WordPress
 */

/**
 * Class DiffbotApi
 */
class DiffbotApi {

	/**
	 * Search products on Diffbot by URL.
	 *
	 * @param string $url - URL to pass to Diffbot.
	 *
	 * @return array|WP_Error
	 */
	public function search_products( $url ) {

		// Need a valid URL.
		if ( ! defined( 'DIFFBOT_API_KEY' ) ) {
			return new WP_Error( 'url', __( 'No DIFFBOT_API_KEY', 'diffbotproducts' ) );
		}

		// Need a valid URL.
		if ( empty( $url ) ) {
			return new WP_Error( 'url', __( 'URL is missing or invalid', 'diffbotproducts' ) );
		}

		$get_url = $this->get_url( $url, DIFFBOT_API_KEY );

		// Fire in the hole!
		$result = wp_remote_get(
			$get_url,
			array(
				'timeout'    => 30,
				'user-agent' => '',
			)
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! isset( $result['response']['code'] ) || 200 !== $result['response']['code'] ) {
			return new WP_Error(
				'api',
				__( 'Diffbot API error: code', 'diffbotproducts' ) . ' = ' .
				$result['response']['code'] . ', ' .
				__( 'message', 'diffbotproducts' ) . ' = ' .
				$result['response']['message']
			);
		}

		// Make sure we have a product to display/create.
		$request_body = wp_remote_retrieve_body( $result );
		$request_body = json_decode( $request_body );

		if ( empty( $request_body->objects[0] ) ) {
			return new WP_Error( 'api', __( 'No product found', 'diffbotproducts' ) );
		}

		return $request_body->objects[0];
	}

	/**
	 * Get a prepared Diffbot URL.
	 *
	 * @param string $api_key - Diffbot API key.
	 * @param string $url     - URL to search.
	 *
	 * @return string
	 */
	protected function get_url( $api_key, $url ) {
		return add_query_arg(
			array(
				'token' => $api_key,
				'url'   => rawurlencode( $url ),
			),
			DIFFBOT_PRODUCT_API_URL
		);
	}
}
