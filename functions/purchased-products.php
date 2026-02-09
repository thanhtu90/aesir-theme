<?php
/**
 * Show Previously Purchased Products
 *
 * OPTIMIZED: SQL-001 fix
 * - Uses caching to avoid repeated queries
 * - Limits to recent 50 orders instead of ALL orders
 * - Cache invalidated when user places new order
 */

add_action('woocommerce_after_single_product_summary', 'show_user_purchased_products', 5);

function show_user_purchased_products() {
    if (!is_user_logged_in()) return;

    $user_id = get_current_user_id();
    $product_ids = aesir_get_user_purchased_products($user_id);

    if (empty($product_ids)) return;

    // Limit to 4 random products for display
    shuffle($product_ids);
    $product_ids = array_slice($product_ids, 0, 4);

    // Query those products with cache priming
    $args = [
        'post_type' => 'product',
        'post__in' => $product_ids,
        'posts_per_page' => 4,
        'orderby' => 'post__in',
        'update_post_meta_cache' => true,  // Prime meta cache
        'update_post_term_cache' => true,  // Prime term cache
    ];
    $products = new WP_Query($args);

    if ($products->have_posts()) {
        echo '<section class="related products user-bought-products">';
        echo '<h2>' . __('Previously Purchased Products', 'woocommerce') . '</h2>';
        woocommerce_product_loop_start();

        while ($products->have_posts()) {
            $products->the_post();
            wc_get_template_part('content', 'product');
        }

        woocommerce_product_loop_end();
        echo '</section>';
        wp_reset_postdata();
    }
}

/**
 * Get user's purchased product IDs with caching
 *
 * @param int $user_id
 * @return array Product IDs
 */
function aesir_get_user_purchased_products($user_id) {
    $cache_key = 'aesir_user_products_' . $user_id;
    $product_ids = get_transient($cache_key);

    if ($product_ids === false) {
        $product_ids = [];

        // Only query last 50 orders - sufficient for "previously bought" feature
        // This prevents memory issues for customers with hundreds of orders
        $orders = wc_get_orders([
            'limit' => 50,  // Reasonable limit instead of -1
            'customer_id' => $user_id,
            'status' => ['completed', 'processing'],
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        if (!empty($orders)) {
            foreach ($orders as $order) {
                foreach ($order->get_items() as $item) {
                    $product_id = $item->get_product_id();
                    if ($product_id) {
                        $product_ids[] = $product_id;
                    }
                }
            }
            $product_ids = array_unique($product_ids);
        }

        // Cache for 30 minutes
        set_transient($cache_key, $product_ids, 30 * MINUTE_IN_SECONDS);
    }

    return $product_ids;
}

/**
 * Invalidate user's purchased products cache when they place a new order
 */
add_action('woocommerce_order_status_completed', 'aesir_invalidate_user_products_cache', 10, 1);
add_action('woocommerce_order_status_processing', 'aesir_invalidate_user_products_cache', 10, 1);

function aesir_invalidate_user_products_cache($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    $user_id = $order->get_user_id();
    if ($user_id) {
        delete_transient('aesir_user_products_' . $user_id);
    }
}
