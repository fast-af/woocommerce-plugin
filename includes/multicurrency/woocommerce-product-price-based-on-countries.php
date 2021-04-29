<?php
/**
 * Add support for Price Based on Country for WooCommerce plugin.
 *
 * @see https://wordpress.org/plugins/woocommerce-product-price-based-on-countries/
 *
 * @package fast
 */

/**
 * Update the product price for multicurrency.
 *
 * @param string     $price   Value of the price.
 * @param WC_Product $product The product object.
 * @param WC_Data    $order   The order to check.
 * @param WC_Request $request Request object.
 *
 * @return string
 */
function fastwc_update_price_for_multicurrency_woocommerce_product_price_based_on_countries( $price, $product, $order, $request ) {

	$country = fastwc_woocommerce_product_price_based_on_countries_get_billing_address_country( $request );

	if ( ! empty( $country ) ) {
		$zone = wcpbc_get_zone_by_country( $country );

		if ( ! empty( $zone ) ) {
			$price = $zone->get_post_price( $product, '_price' );
		}
	}

	return $price;
}
add_filter( 'fastwc_update_price_for_multicurrency_woocommerce_product_price_based_on_countries', 'fastwc_update_price_for_multicurrency_woocommerce_product_price_based_on_countries', 10, 2 );

/**
 * Update the shipping rate for multicurrency.
 *
 * @param array           $rate_info The rate response information.
 * @param string          $currency  The customer currency.
 * @param WP_REST_Request $request   The request object.
 *
 * @return array
 */
function fastwc_update_shipping_rate_for_multicurrency_woocommerce_product_price_based_on_countries( $rate_info, $currency, $request ) {

	$country = fastwc_woocommerce_product_price_based_on_countries_get_billing_address_country( $request );

	if ( ! empty( $country ) ) {
		$zone = wcpbc_get_zone_by_country( $country );

		if ( ! empty( $zone ) ) {
			$rate_info['price'] = $zone->get_exchange_rate_price( $rate_info['price'] );

			if ( ! empty( $rate_info['taxes'] ) ) {
				$rate_taxes = $rate_info['taxes'];

				foreach ( $rate_taxes as $rate_tax_id => $rate_tax ) {
					$rate_info['taxes'][ $rate_tax_id ] = $zone->get_exchange_rate_price( $rate_tax );
				}
			}
		}
	}

	return $rate_info;
}
add_filter( 'fastwc_update_shipping_rate_for_multicurrency_woocommerce_product_price_based_on_countries', 'fastwc_update_shipping_rate_for_multicurrency_woocommerce_product_price_based_on_countries', 10, 3 );

/**
 * Get the billing address country from the request.
 *
 * @param mixed $request The request object.
 *
 * @return string
 */
function fastwc_woocommerce_product_price_based_on_countries_get_billing_address_country( $request ) {
	$country = '';

	if ( is_array( $request ) ) {
		if ( ! emtpy( $request['billing']['country'] ) ) {
			$country = $request['billing']['country'];
		}
	} elseif ( is_a( $request, 'WP_REST_Request' ) ) {
		$params = $request->get_params();

		if ( ! emtpy( $params['billing']['country'] ) ) {
			$country = $params['billing']['country'];
		}
	}

	return $country;
}
