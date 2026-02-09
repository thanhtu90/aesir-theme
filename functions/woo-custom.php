<?php
/**
 * WooCommerce Customizations
 * Version: 2.0
 */

defined('ABSPATH') || exit;

// ============================================================
// THEME SUPPORT
// ============================================================

add_action('after_setup_theme', function() {
    add_theme_support('woocommerce');
});

// ============================================================
// SINGLE PRODUCT PAGE CUSTOMIZATIONS
// ============================================================

// Remove sidebar on product pages
add_action('wp', function() {
    if (is_product()) {
        remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    }
});

// Remove product data tabs
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);

// Remove upsells
add_action('wp', function() {
    if (is_product()) {
        remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
    }
});

// Remove "added to cart" message on single product pages
add_filter('wc_add_to_cart_message_html', function($message, $products) {
    if (is_product()) {
        return '';
    }
    return $message;
}, 10, 2);

// Clear notices on product pages
add_action('template_redirect', function() {
    if (is_product() && function_exists('wc_clear_notices')) {
        wc_clear_notices();
    }
});

// ============================================================
// SALE BADGE REMOVAL
// ============================================================

add_filter('woocommerce_sale_flash', '__return_empty_string', 10, 3);

add_action('init', function() {
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
});

// ============================================================
// QUANTITY LIMITS
// ============================================================

define('AESIR_MAX_QUANTITY', 5);

// Validate on add to cart
// OPTIMIZED: SQL-006 fix - Uses $cart_item['data'] instead of wc_get_product()
add_filter('woocommerce_add_to_cart_validation', function($passed, $product_id, $quantity) {
    $max_qty = AESIR_MAX_QUANTITY;

    if (!WC()->cart) return $passed;

    $product = wc_get_product($product_id);
    if (!$product) return $passed;

    $cart_qty = 0;

    foreach (WC()->cart->get_cart() as $cart_item) {
        // Use cached product object from cart item instead of querying again
        $cart_product = isset($cart_item['data']) ? $cart_item['data'] : null;
        if (!$cart_product) continue;

        $cart_product_id = $cart_item['product_id'];
        $cart_variation_id = isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;

        // Simple product check
        if ($product->is_type('simple') && $cart_product_id === $product->get_id()) {
            $cart_qty += $cart_item['quantity'];
        }

        // Variable product check
        if ($product->is_type('variation') && $cart_variation_id === $product->get_id()) {
            $cart_qty += $cart_item['quantity'];
        }
    }

    if (($cart_qty + $quantity) > $max_qty) {
        wc_add_notice(
            sprintf(__('You can only have a maximum of %d units of this product in your cart.', 'aesir'), $max_qty),
            'error'
        );
        return false;
    }

    return $passed;
}, 10, 3);

// Validate on cart update
add_filter('woocommerce_update_cart_validation', function($passed, $cart_item_key, $values, $quantity) {
    $max_qty = AESIR_MAX_QUANTITY;

    if ($quantity > $max_qty) {
        wc_add_notice(
            sprintf(__('You can only have a maximum of %d units of this product in your cart.', 'aesir'), $max_qty),
            'error'
        );
        return false;
    }

    return $passed;
}, 10, 4);

// Set max on quantity input
add_filter('woocommerce_quantity_input_args', function($args) {
    $args['max_value'] = AESIR_MAX_QUANTITY;
    return $args;
}, 10);

// ============================================================
// VND CURRENCY FORMATTING
// ============================================================

add_filter('woocommerce_currency_symbol', function($symbol, $currency) {
    return $currency === 'VND' ? 'VND' : $symbol;
}, 10, 2);

add_filter('woocommerce_currency_pos', function($position) {
    return get_woocommerce_currency() === 'VND' ? 'right_space' : $position;
});

add_filter('woocommerce_price_num_decimals', function($decimals) {
    return get_woocommerce_currency() === 'VND' ? 0 : $decimals;
});

add_filter('woocommerce_price_thousand_separator', function($sep) {
    return get_woocommerce_currency() === 'VND' ? '.' : $sep;
});

add_filter('woocommerce_price_decimal_separator', function($sep) {
    return get_woocommerce_currency() === 'VND' ? '' : $sep;
});

add_filter('wc_price', function($formatted_price, $price, $args) {
    if (get_woocommerce_currency() !== 'VND') {
        return $formatted_price;
    }

    $raw = wc_format_decimal($price);
    $number = number_format((float)$raw, 0, '', '.');

    return $number . ' VND';
}, 10, 3);

// ============================================================
// PRODUCT TITLE WITH NOTRANSLATE CLASS
// ============================================================

add_action('wp', function() {
    if (is_product()) {
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
        add_action('woocommerce_single_product_summary', function() {
            echo '<h1 class="product_title entry-title notranslate">' . esc_html(get_the_title()) . '</h1>';
        }, 5);
    }
});

add_action('init', function() {
    if (function_exists('woocommerce_template_loop_product_title')) {
        remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);

        add_action('woocommerce_shop_loop_item_title', function() {
            echo '<h2 class="woocommerce-loop-product__title notranslate">' . esc_html(get_the_title()) . '</h2>';
        }, 10);
    }
});

// ============================================================
// BREADCRUMB WITH NOTRANSLATE CLASS
// ============================================================

add_filter('woocommerce_breadcrumb_defaults', function($defaults) {
    if (!empty($defaults['wrap_before']) && strpos($defaults['wrap_before'], 'woocommerce-breadcrumb') !== false) {
        $defaults['wrap_before'] = str_replace(
            'class="woocommerce-breadcrumb"',
            'class="woocommerce-breadcrumb notranslate"',
            $defaults['wrap_before']
        );
    } elseif (!empty($defaults['wrap_before'])) {
        $defaults['wrap_before'] = preg_replace('/class="([^"]*)"/', 'class="$1 notranslate"', $defaults['wrap_before'], 1);
    }
    return $defaults;
}, 10, 1);

// ============================================================
// HEADER CART ICON & BADGE (TOP-RIGHT)
// ============================================================

/**
 * Output or return the header cart count badge HTML.
 *
 * @param bool $echo Whether to echo (true) or return (false).
 * @return string Empty string when echoing, else badge HTML.
 */
function aesir_header_cart_count_badge($echo = true) {
    if (!function_exists('WC') || !WC()->cart) {
        return '';
    }
    $count = WC()->cart->get_cart_contents_count();
    $hidden = $count > 0 ? '' : ' hidden';
    // Circle badge at top-right of icon (circle styling in style.css)
    $html = '<span class="header-cart-count' . esc_attr($hidden) . '">' . absint($count) . '</span>';
    if ($echo) {
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- badge count is escaped above.
        return '';
    }
    return $html;
}

/**
 * Refresh header cart badge via WooCommerce cart fragments (AJAX).
 */
add_filter('woocommerce_add_to_cart_fragments', function($fragments) {
    if (!function_exists('WC') || !WC()->cart) {
        return $fragments;
    }
    $fragments['.header-cart-count-wrap'] = '<span class="header-cart-count-wrap">' . aesir_header_cart_count_badge(false) . '</span>';
    return $fragments;
});

// ============================================================
// EMAIL CC TO CUSTOMER
// ============================================================

add_filter('woocommerce_email_headers', function($headers, $email_id, $order) {
    if ('new_order' === $email_id && is_object($order)) {
        $customer_email = $order->get_billing_email();
        if ($customer_email) {
            $headers .= 'Cc: ' . $customer_email . "\r\n";
        }
    }
    return $headers;
}, 10, 3);

// ============================================================
// CART PAGE CROSS-SELL SECTION
// ============================================================

/**
 * Render \"Complete the outfit with…\" cross-sell section on the cart page.
 *
 * - Uses WooCommerce cross-sell relationships when present.
 * - Falls back to related products from the same categories as items in the cart.
 * - Products can be added directly from the cart page (standard AJAX add-to-cart).
 */
function aesir_render_cart_cross_sells() {
    if ( ! function_exists( 'WC' ) || ! is_cart() || ! WC()->cart || WC()->cart->is_empty() ) {
        return;
    }

    $cart        = WC()->cart;
    $cross_sells = $cart->get_cross_sells();

    // Fallback: if no manual cross-sells, use related products based on each cart item.
    if ( empty( $cross_sells ) ) {
        $cart_product_ids = array();

        foreach ( $cart->get_cart() as $item ) {
            if ( ! empty( $item['product_id'] ) ) {
                $cart_product_ids[] = (int) $item['product_id'];
            }
        }

        $cart_product_ids = array_unique( array_filter( $cart_product_ids ) );

        if ( ! empty( $cart_product_ids ) ) {
            $related = array();

            // Collect related products per-item (same categories/tags), excluding items already in cart.
            foreach ( $cart_product_ids as $pid ) {
                $per_product_related = wc_get_related_products( $pid, 4, array_merge( $cart_product_ids, $related ) );
                if ( ! empty( $per_product_related ) ) {
                    $related = array_merge( $related, $per_product_related );
                }

                if ( count( $related ) >= 4 ) {
                    break;
                }
            }

            if ( ! empty( $related ) ) {
                $cross_sells = $related;
            }
        }
    }

    $cross_sells = apply_filters( 'aesir_cart_cross_sells_ids', array_unique( array_filter( $cross_sells ) ) );

    if ( empty( $cross_sells ) ) {
        return;
    }

    $args = array(
        'post_type'           => 'product',
        'post_status'         => 'publish',
        'ignore_sticky_posts' => 1,
        'no_found_rows'       => true,
        'posts_per_page'      => 4,
        'post__in'            => $cross_sells,
        'orderby'             => 'post__in',
    );

    $products = new WP_Query( $args );

    if ( ! $products->have_posts() ) {
        wp_reset_postdata();
        return;
    }

    echo '<section class="aesir-cart-cross-sells cart-cross-sells">';
    echo '<h2 class="title aesir-cart-cross-sells__title">' . esc_html__( 'Complete the outfit with…', 'aesir' ) . '</h2>';

    woocommerce_product_loop_start();

    while ( $products->have_posts() ) {
        $products->the_post();
        wc_get_template_part( 'content', 'product' );
    }

    woocommerce_product_loop_end();

    echo '</section>';

    wp_reset_postdata();
}

// Render below cart items but before totals/collaterals.
add_action( 'woocommerce_after_cart_table', 'aesir_render_cart_cross_sells', 15 );
