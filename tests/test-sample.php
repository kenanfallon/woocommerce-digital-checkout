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
	 * Setup function
	 */
	function setUp() {
		parent::setUp();
	}

	/**
	 * Teardown function
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * A single example test.
	 */
	function test_sample() {
		$this->assertTrue( true );
	}

	/**
	 * Assert that the checkout fields contain regular billing fields when the cart is empty
	 */
	public function test_checkout_has_billing_fields() {

		$product = \WC_Helper_Product::create_simple_product();
		WC()->cart->add_to_cart( $product->get_id(), 1 );

		$checkout_fields = apply_filters( 'woocommerce_checkout_fields', WC()->checkout->get_checkout_fields() );

		$this->assertArrayHasKey( 'billing_company', $checkout_fields['billing'] );

		WC()->cart->empty_cart();
		\WC_Helper_Product::delete_product( $product->get_id() );

	}

	/**
	 * Check if fields are removed when a single virtual product is in cart.
	 */
	public function test_virtual_product_in_cart() {

		$product    = \WC_Helper_Product::create_simple_product();
		$product_id = $product->get_id();
		update_post_meta( $product_id, '_virtual', 'yes' );
		WC()->cart->add_to_cart( $product->get_id(), 1 );

		$checkout_fields = apply_filters( 'woocommerce_checkout_fields', WC()->checkout->get_checkout_fields() );

		$this->assertArrayNotHasKey( 'billing_company', $checkout_fields['billing'] );

		// Empty cart.
		WC()->cart->empty_cart();
		\WC_Helper_Product::delete_product( $product->get_id() );

	}

	/**
	 * Test WooCommerce Coupons
	 */
	public function test_wc_coupons_enabled() {
		$this->assertEquals( apply_filters( 'woocommerce_coupons_enabled', get_option( 'woocommerce_enable_coupons' ) == 'yes' ), wc_coupons_enabled() );
	}

	/**
	 * Test Pages
	 */
	function test_page() {

		$post_id = $this->factory->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Hello!',
			)
		);

		$this->assertEquals( 'Hello!', get_the_title( $post_id ) );

	}

	/**
	 * Test that relies on WooCommerce
	 */
	function test_cart() {
		$this->assertEmpty( WC()->cart->cart_contents );
	}

	/**
	 * Test Product Price
	 */
	public function test_product_price() {
		// Create dummy product.
		$product = WC_Helper_Product::create_simple_product();

		// Update Price.
		update_post_meta( $product->get_id(), '_regular_price', '29.95' );
		update_post_meta( $product->get_id(), '_price', '29.95' );

		// Clean up product.
		WC_Helper_Product::delete_product( $product->get_id() );

		// Simple.
		$this->assertTrue( true );
	}
}
