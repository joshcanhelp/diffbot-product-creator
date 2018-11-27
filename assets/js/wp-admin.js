/* globals jQuery, console, alert, diffbotProduct */

jQuery( document ).ready(
	function ($) {

		'use strict';

		//
		// AJAX request to search product URL in Diffbot
		//
		$( '#js-dbprod-add-product-form' ).submit(
			function (e) {
				e.preventDefault();

				// Refresh the UI for a new search
				prodSearchUiRefresh( true, '', false );

				// Data being sent for the AJAX request
				var postData = {
					'nonce' : $( '#__wpnonce_dbprod_search' ).val(),
					'action' : 'dbprod_search_product',
					'pageUrl' : $( '#dbprod-prodUrl' ).val()
				};

				if (diffbotProduct.debug) {
					console.log( 'Request:' );
					console.log( postData );
				}

				// Fire in the hole!
				$.post(
					diffbotProduct.ajaxUrl,
					postData
				).done(
					function (data) {

						data = JSON.parse( data );

						if (data.error) {

							// WP AJAX process returned an error
							prodSearchUiRefresh( false, data.msg, true );

						} else {

							// All good, output item
							prodSearchUiRefresh( false, diffbotProduct.i18n.productFound, false );

							$( '#js-dbprod-title' ).text( data.items.title );
							$( '#js-dbprod-desc' ).text( data.items.text );
							$( '#js-dbprod-offerPrice' ).text( data.items.offerPrice );
							$( '#js-dbprod-regularPrice' ).text( data.items.regularPrice );

						}

						if (diffbotProduct.debug) {
							console.log( 'Response:' );
							console.log( data.items );
						}

					}
				).fail(
					function (data) {

						// AJAX failed
						prodSearchUiRefresh( false, diffbotProduct.i18n.error, true );

						if (diffbotProduct.debug) {
							console.log( 'Failed:' );
							console.log( data );
						}

					}
				);
			}
		);

		//
		// AJAX request to create product in the database
		//
		$( '#js-dbprod-create-product' ).on(
			'click',
			function (e) {
				e.preventDefault();

				prodSearchUiRefresh( true, '', false );
				$( '#js-dbprod-add-product-result' ).addClass( 'showing' );

				// Data being sent for the AJAX request
				var postData = {
					'nonce'  : $( this ).attr( 'data-nonce' ),
					'action' : 'dbprod_create_product',
					'post_title' : $( '#js-dbprod-title' ).text(),
					'post_content' : $( '#js-dbprod-desc' ).text(),
					'dbprod_offerPrice' : $( '#js-dbprod-offerPrice' ).text(),
					'dbprod_regularPrice' : $( '#js-dbprod-regularPrice' ).text(),
					'dbprod_pageUrl' : $( '#dbprod-prodUrl' ).val()
				};

				if (diffbotProduct.debug) {
					console.log( 'Request:' );
					console.log( postData );
				}

				// Fire in the hole!
				$.post(
					diffbotProduct.ajaxUrl,
					postData
				).done(
					function (data) {

						data = JSON.parse( data );

						if (data.error) {

							// WP AJAX process returned an error
							prodSearchUiRefresh( false, data.msg, true );

						} else {

							// Product added
							prodSearchUiRefresh( false, diffbotProduct.i18n.productAdded, false );
							$( '#js-dbprod-add-product-result' ).removeClass( 'showing' );

						}

						if (diffbotProduct.debug) {
							console.log( 'Response:' );
							console.log( data );
						}

					}
				).fail(
					function (data) {

						// AJAX failed
						prodSearchUiRefresh( false, diffbotProduct.i18n.error, true );

						if (diffbotProduct.debug) {
							console.log( 'Failed:' );
							console.log( data );
						}

					}
				);

			}
		);

		//
		// Adjust product search UI for start and finish of search
		//
		function prodSearchUiRefresh(start, msg, error) {

			if (start) {

				// Hide the product result
				$( '#js-dbprod-add-product-result' ).removeClass( 'showing' );

				// Show the loading graphic
				$( '#js-dbprod-loading' ).addClass( 'showing' );

				// Disable buttons
				$( '.dbprod-add-product-wrap' ).find( '.button' ).addClass( 'disabled' ).prop( 'disabled', true );

			} else {

				if (error !== true) {
					$( '#js-dbprod-add-product-result' ).addClass( 'showing' );
				}

				$( '#js-dbprod-loading' ).removeClass( 'showing' );

				$( '.dbprod-add-product-wrap' ).find( '.button' ).removeClass( 'disabled' ).prop( 'disabled', false );
			}

			// Update the message box
			$( '#js-dbprod-message' )
			.text( msg )
			.removeClass( 'dbprod-msg-error dbprod-msg-success' )
			.addClass( error === true ? 'dbprod-msg-error' : 'dbprod-msg-success' );
		}
	}
);
