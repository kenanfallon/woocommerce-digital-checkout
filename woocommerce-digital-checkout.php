<?php
/**
 * WooCommerce Digital Checkout
 *
 * @package woocommerce-digital-checkout
 *
 * Plugin Name: WooCommerce Digital Checkout
 * Plugin URI: https://github.com/kenanfallon/woocommerce-digital-checkout
 * Description: Hide Billing and Shipping Checkout Fields For Virtual/Download Products
 * Version: 0.31
 * Author: Kenan Fallon
 * Author URI: http://kenanfallon.com
 * License: GPLv2 or later
 */

/**
 * Prevent direct access
 **/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	add_filter( 'woocommerce_checkout_fields', 'wdc_remove_billing_checkout_fields' );

	/**
	 * Remove the unneeded checkout fields
	 *
	 * @param array $fields Checkout fields array.
	 *
	 * @return mixed
	 */
	function wdc_remove_billing_checkout_fields( $fields ) {

		if ( WDC_virtual_product_check() == true ) {
			unset( $fields['billing']['billing_company'] );
			unset( $fields['billing']['billing_address_1'] );
			unset( $fields['billing']['billing_address_2'] );
			unset( $fields['billing']['billing_city'] );
			unset( $fields['billing']['billing_postcode'] );
			unset( $fields['billing']['billing_country'] );
			unset( $fields['billing']['billing_state'] );
			unset( $fields['billing']['billing_phone'] );
			unset( $fields['order']['order_comments'] );
			unset( $fields['billing']['billing_address_2'] );
			unset( $fields['billing']['billing_postcode'] );
			unset( $fields['billing']['billing_company'] );
			unset( $fields['billing']['billing_city'] );

			unset( $fields['shipping']['shipping_first_name'] );
			unset( $fields['shipping']['shipping_last_name'] );
			unset( $fields['shipping']['shipping_company'] );
			unset( $fields['shipping']['shipping_country'] );
			unset( $fields['shipping']['shipping_address_1'] );
			unset( $fields['shipping']['shipping_address_2'] );
			unset( $fields['shipping']['shipping_city'] );
			unset( $fields['shipping']['shipping_state'] );
			unset( $fields['shipping']['shipping_postcode'] );
		}

		return $fields;
	}

	/**
	 * Check for virtual products
	 *
	 * @return bool
	 */
	function wdc_virtual_product_check() {

		$products = WC()->cart->cart_contents;
		$virtual_products = 0;

		foreach ( $products as $product ) {

			if ( $product['variation_id'] ) {
				$product_id = $product['variation_id'];
			} else {
				$product_id = $product['product_id'];
			}

			$product_obj = wc_get_product( $product_id );

			if ( $product_obj->is_downloadable() || $product_obj->is_virtual() ) {
				$virtual_products += 1;
			} else {
				// do nothing.
			};

		}

		if ( count( $products ) == $virtual_products ) {
			return true;
		} else {
			// do nothing.
		}
	}
}// End if().
