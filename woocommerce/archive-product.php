<?php
defined('ABSPATH') || exit;

get_header('shop');

$term = get_queried_object(); // Current category object
?>

<div class="border-b border-black py-16">
    <h4 class="notranslate title text-center">
        <?php echo esc_html($term->name); ?>
    </h4>
</div>

<?php
// ✅ Category featured image
$thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
if ($thumbnail_id):
    ?>
    <div id="archive-product-featured-image-wrap" class="border-b border-black flex items-center justify-center">
        <?php echo wp_get_attachment_image($thumbnail_id, 'large', false, ['class' => '']); ?>
    </div>
<?php endif; ?>

<?php if (woocommerce_product_loop()): ?>

    <div class="border-b border-black flex items-center justify-between p-4">
        <div>
            <?php if (function_exists('woocommerce_result_count'))
                woocommerce_result_count(); ?>
        </div>
        <div class="flex items-center gap-4">
            <div>
                <?php if (function_exists('woocommerce_catalog_ordering'))
                    woocommerce_catalog_ordering(); ?>
            </div>
        </div>
    </div>

    <ul class="product-grid">
        <?php while (have_posts()):
            the_post(); ?>
            <?php wc_get_template_part('content', 'product'); ?>
        <?php endwhile; ?>
    </ul>
    <div class="border-b border-black py-6">
        <?php
        /**
         * Output shop pagination. This triggers woocommerce_pagination via the woocommerce_after_shop_loop hook.
         */
        do_action('woocommerce_after_shop_loop');
        ?>
    </div>
<?php else: ?>
    <div class="flex flex-col items-center justify-center gap-3 w-full py-12 text-center px-2 lg:px-0" style="min-height: 28rem;">
        <h2 class="!text-2xl font-semibold"><?php esc_html_e('No products found', 'woocommerce'); ?></h2>
        <p class="text-sm text-gray-600">
            <?php esc_html_e('There are no products in this category right now. Please check back later or try a different category.', 'woocommerce'); ?>
        </p>
    </div>
<?php endif; ?>

<?php get_footer('shop'); ?>