<?php
/**
 * Class SampleTest
 *
 * @package Digital_Checkout_For_Woocommerce
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

	/**
	 * Test that relies on WooCommerce
	 */
	function test_woocommerce() {
		// Assuming the product actually exists :)
		$product_id = 666;
		WC()->cart->add_to_cart( $product_id );

		$this->assertNotEmpty( WC()->cart->cart_contents );
	}
}
