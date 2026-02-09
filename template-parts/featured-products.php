<?php
/**
 * Featured Products Template
 *
 * OPTIMIZED: SQL-002 fix - Uses WP_Query with cache priming
 */

$products = [];
$featured_products = get_field('featured_products');

if ($featured_products && is_array($featured_products) && count($featured_products) > 0) {
    // Get product IDs from ACF field
    $product_ids = array_map(function($p) {
        return is_object($p) ? $p->ID : (int) $p;
    }, $featured_products);

    // Single query with cache priming
    $query = new WP_Query([
        'post_type' => 'product',
        'post__in' => $product_ids,
        'posts_per_page' => count($product_ids),
        'orderby' => 'post__in',
        'update_post_meta_cache' => true,
        'update_post_term_cache' => true,
        'no_found_rows' => true,
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
                    <h2 class="woocommerce-loop-product__title"><?php echo esc_html($product['name']); ?></h2>
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
