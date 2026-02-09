<?php
/**
 * Bestseller Products Template
 *
 * OPTIMIZED: SQL-002, SQL-003 fixes
 * - Uses direct database query instead of loading 1000 order objects
 * - Uses WP_Query with cache priming to avoid N+1
 * - Extended cache time (1 hour)
 */

$products = [];

// Get top 4 bestsellers with optimized query
$cache_key = 'aesir_weekly_bestsellers_v2';
$top_ids = get_transient($cache_key);

if ($top_ids === false) {
    global $wpdb;

    // Try to use WooCommerce order product lookup table for efficient query
    $table_name = $wpdb->prefix . 'wc_order_product_lookup';
    $table_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
        DB_NAME,
        $table_name
    ));

    if ($table_exists) {
        // OPTIMIZED: Direct query using indexed table
        $one_week_ago = date('Y-m-d 00:00:00', strtotime('-7 days'));

        $top_ids = $wpdb->get_col($wpdb->prepare("
            SELECT product_id
            FROM {$wpdb->prefix}wc_order_product_lookup
            WHERE date_created >= %s
            GROUP BY product_id
            ORDER BY SUM(product_qty) DESC
            LIMIT 4
        ", $one_week_ago));
    }

    // Fallback if no results or table doesn't exist
    if (empty($top_ids)) {
        // Use total_sales meta (all-time bestsellers)
        $fallback = wc_get_products([
            'limit' => 4,
            'orderby' => 'meta_value_num',
            'meta_key' => 'total_sales',
            'order' => 'DESC',
            'return' => 'ids',  // Only get IDs, not full objects
        ]);
        $top_ids = $fallback;
    }

    // Cache for 1 hour
    set_transient($cache_key, $top_ids, HOUR_IN_SECONDS);
}

// Build product array using WP_Query with cache priming
if (!empty($top_ids)) {
    // Single query that primes all caches
    $query = new WP_Query([
        'post_type' => 'product',
        'post__in' => $top_ids,
        'posts_per_page' => count($top_ids),
        'orderby' => 'post__in',
        'update_post_meta_cache' => true,
        'update_post_term_cache' => true,
        'no_found_rows' => true,  // Skip counting for pagination
    ]);

    while ($query->have_posts()) {
        $query->the_post();
        $wc_product = wc_get_product(get_the_ID());
        if ($wc_product) {
            $image_id = $wc_product->get_image_id();
            $products[] = [
                'id' => $wc_product->get_id(),
                'name' => $wc_product->get_name(),
                'price_html' => $wc_product->get_price_html(),
                'link' => get_permalink(),
                'full_image' => $image_id ? wp_get_attachment_url($image_id) : wc_placeholder_img_src(),
                'thumb' => $image_id ? wp_get_attachment_image_url($image_id, 'large') : wc_placeholder_img_src('large'),
            ];
        }
    }
    wp_reset_postdata();
}

if (count($products) > 0) {
    ?>
    <h2 class="!my-5 lg:!my-10 px-2 lg:px-5 font-semibold">BESTSELLER</h2>
    <ul class="product-grid border-t border-black">
        <?php foreach ($products as $product): ?>
            <li class="product type-product status-publish instock has-post-thumbnail shipping-taxable purchasable product-type-variable" data-product_id="<?php echo esc_attr($product['id']); ?>">
                <a href="<?php echo esc_url($product['link']); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                    <img width="300" height="300"
                         src="<?php echo esc_url($product['thumb']); ?>"
                         class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail"
                         alt="<?php echo esc_attr($product['name']); ?>"
                         decoding="async"
                         loading="lazy" />
                    <h2 class="notranslate woocommerce-loop-product__title"><?php echo esc_html($product['name']); ?></h2>
                    <span class="price">
                        <span class="woocommerce-Price-amount amount"><?php echo $product['price_html']; ?></span>
                    </span>
                    <div class="product-variations-info" style="visibility: hidden; text-align:center; height: 30px;">Checking variations...</div>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php
}
?>
