<?php
/**
 * Product Slider Template
 *
 * OPTIMIZED: SQL-002 fix - Uses WP_Query with cache priming
 */

$products = [];
$slide_products = get_field('slide_products');

if ($slide_products && is_array($slide_products) && count($slide_products) > 0) {
    // Get product IDs from ACF field
    $product_ids = array_map(function($p) {
        return is_object($p) ? $p->ID : (int) $p;
    }, $slide_products);

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
                'name' => $wc_product->get_name(),
                'price_html' => $wc_product->get_price_html(),
                'link' => get_permalink(),
                'full_image' => $image_id ? wp_get_attachment_url($image_id) : wc_placeholder_img_src(),
                'thumb' => $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : wc_placeholder_img_src('thumbnail'),
            ];
        }
    }
    wp_reset_postdata();
}

if (count($products) > 0) {
    ?>
    <h2 class="!my-5 lg:!my-10 px-2 lg:px-5 font-semibold">LATEST: GENTLE MONSTER'S NEW ARRIVAL</h2>
    <div class="product-slider w-full overflow-hidden">
        <div class="swiper-container main-slider">
            <div class="swiper-wrapper">
                <?php foreach ($products as $product): ?>
                    <div class="swiper-slide p-6">
                        <a href="<?php echo esc_url($product['link']); ?>"
                            class="flex flex-col lg:flex-row items-center lg:items-end justify-center">
                            <div
                                class="flex-none w-full lg:w-[250px] order-2 lg:order-1 text-center lg:text-left pb-0 lg:pb-28">
                                <h3><?php echo esc_html($product['name']); ?></h3>
                                <div class="price"><?php echo $product['price_html']; ?></div>
                            </div>
                            <div class="flex-auto order-1 lg:order-2">
                                <img src="<?php echo esc_url($product['full_image']); ?>"
                                    alt="<?php echo esc_attr($product['name']); ?>"
                                    class="object-cover"
                                    loading="lazy" />
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="swiper-container thumb-slider">
            <div class="swiper-wrapper flex items-center justify-center">
                <?php foreach ($products as $product): ?>
                    <div class="swiper-slide">
                        <img src="<?php echo esc_url($product['thumb']); ?>" alt="<?php echo esc_attr($product['name']); ?>"
                            class="object-cover mx-auto cursor-pointer"
                            loading="lazy" />
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swiper === 'undefined') return;

            var thumbSlider = new Swiper('.thumb-slider', {
                slidesPerView: 7,
                watchSlidesVisibility: true,
                watchSlidesProgress: true,
                slideToClickedSlide: true,
                loop: true,
            });

            var mainSlider = new Swiper('.main-slider', {
                spaceBetween: 10,
                loop: true,
                slidesPerView: 1,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                thumbs: {
                    swiper: thumbSlider,
                },
                autoplay: false,
                breakpoints: {
                    1024: {
                        slidesPerView: 2,
                    }
                }
            });
        });
    </script>
<?php
}
?>
