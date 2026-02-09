<?php

defined('ABSPATH') || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action('woocommerce_before_single_product');

if (post_password_required()) {
    echo get_the_password_form(); // WPCS: XSS ok.
    return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>

    <div id="product-single-wrapper" class="flex flex-col lg:flex-row max-w-7xl mx-auto min-h-[200vh] relative">
        <!-- Left column -->
        <div id="product-single-left" class="w-full lg:w-1/2 space-y-10">
            <div class="hidden lg:block">
                <div class="flex items-center justify-center border-b border-black">
                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($product->get_id(), 'full')); ?>"
                        class="w-full object-cover" />
                </div>
                <?php
                if (have_rows('gallery')):
                    $rows = count(get_field('gallery'));
                    $i = 0;
                    while (have_rows('gallery')):
                        the_row();
                        $is_video = get_sub_field('is_video');
                        // Determine border class
                        $border_class = ($i < $rows - 1) ? 'border-b border-black' : '';

                        if ($is_video):
                            $video = get_sub_field('video');
                            ?>
                            <div class="flex items-center justify-center <?php echo esc_attr($border_class); ?>">
                                <video controls autoplay loop muted playsinline class="w-full object-cover">
                                    <source src="<?php echo esc_url($video); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                            <?php
                        endif;
                        $image = get_sub_field('image');
                        if ($image):
                            ?>
                            <div class="flex items-center justify-center <?php echo esc_attr($border_class); ?>">
                                <?php
                                echo $image_html = wp_get_attachment_image(get_sub_field('image'), 'full', false, array(
                                    'class' => 'w-full object-cover',
                                    'width' => '500',
                                    'height' => '500',
                                ));
                                ?>
                            </div>
                            <?php
                        endif;
                        $i++;
                    endwhile;
                endif;
                ?>
            </div>
            <div class="block lg:hidden">
                <div class="block lg:hidden">
                    <div class="swiper product-swiper">
                        <div class="swiper-wrapper">
                            <?php
                            // First slide: featured image
                            $thumb_id = get_post_thumbnail_id(isset($product) && is_object($product) ? $product->get_id() : get_the_ID());
                            if ($thumb_id):
                                ?>
                                <div class="swiper-slide">
                                    <div class="flex items-center justify-center">
                                        <?php echo wp_get_attachment_image($thumb_id, 'medium', false, array('class' => 'w-full object-cover')); ?>
                                    </div>
                                </div>
                                <?php
                            endif;

                            // Other slides: gallery (use get_field to avoid exhausting ACF pointer)
                            $gallery = function_exists('get_field') ? get_field('gallery') : false;
                            if ($gallery && is_array($gallery)):
                                foreach ($gallery as $row):
                                    $is_video = !empty($row['is_video']);
                                    if ($is_video && !empty($row['video'])):
                                        $video_src = $row['video'];
                                        ?>
                                        <div class="swiper-slide">
                                            <div class="flex items-center justify-center">
                                                <video controls autoplay loop muted playsinline class="w-full object-cover">
                                                    <source src="<?php echo esc_url($video_src); ?>" type="video/mp4">
                                                    <?php esc_html_e('Your browser does not support the video tag.', 'aesir'); ?>
                                                </video>
                                            </div>
                                        </div>
                                        <?php
                                    endif;

                                    $image = !empty($row['image']) ? $row['image'] : false;
                                    if ($image):
                                        $img_id = is_array($image) && isset($image['ID']) ? $image['ID'] : $image;
                                        ?>
                                        <div class="swiper-slide">
                                            <div class="flex items-center justify-center">
                                                <?php echo wp_get_attachment_image($img_id, 'medium', false, array('class' => 'w-full object-cover')); ?>
                                            </div>
                                        </div>
                                        <?php
                                    endif;
                                endforeach;
                            endif;
                            ?>
                        </div>

                        <!-- Pagination / Navigation -->
                        <div class="swiper-pagination"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>

                    <script>
                        (function ($) {
                            $(function () {
                                if (typeof Swiper === 'undefined') return;

                                new Swiper('.product-swiper', {
                                    loop: true,
                                    spaceBetween: 8,
                                    pagination: {
                                        el: '.product-swiper .swiper-pagination',
                                        clickable: true
                                    },
                                    navigation: {
                                        nextEl: '.product-swiper .swiper-button-next',
                                        prevEl: '.product-swiper .swiper-button-prev'
                                    },
                                    // adjust responsive behaviour if needed
                                    slidesPerView: 1
                                });
                            });
                        })(jQuery);
                    </script>
                </div>
            </div>
        </div>

        <!-- Right column -->
        <div id="product-single-info" class="w-full lg:w-1/2 px-10 py-12 bg-white">
            <div class="summary entry-summary single-product-right ">
                <?php
                /**
                 * Hook: woocommerce_single_product_summary.
                 *
                 * @hooked woocommerce_template_single_title - 5
                 * @hooked woocommerce_template_single_rating - 10
                 * @hooked woocommerce_template_single_price - 10
                 * @hooked woocommerce_template_single_excerpt - 20
                 * @hooked woocommerce_template_single_add_to_cart - 30
                 * @hooked woocommerce_template_single_meta - 40
                 * @hooked woocommerce_template_single_sharing - 50
                 * @hooked WC_Structured_Data::generate_product_data() - 60
                 */
                do_action('woocommerce_single_product_summary');
                ?>

                <!-- Collapse Sections -->
                <div class="pt-6 divide-y divide-black border-t border-black">

                    <!-- Product Details -->
                    <details class="group">
                        <summary class="flex justify-between items-center cursor-pointer py-3 font-medium px-2 lg:px-0">
                            Product Details
                            <i class="cursor-pointer arrow">
                                <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M24 24H0V0h24v24z" fill="none" opacity=".87"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6-1.41-1.41z"/></svg>
                            </i>
                        </summary>
                        <div class="pb-3 text-sm text-gray-700 px-2 lg:px-0">
                            <?php the_content(); ?>
                            <?php wc_get_template('single-product/tabs/additional-information.php'); ?>
                        </div>
                    </details>

                    <!-- Size & Fit -->
                    <details class="group">
                        <summary class="flex justify-between items-center cursor-pointer py-3 font-medium px-2 lg:px-0">
                            Size & Fit
                            <i class="cursor-pointer arrow">
                                <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M24 24H0V0h24v24z" fill="none" opacity=".87"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6-1.41-1.41z"/></svg>
                            </i>
                        </summary>
                        <div class="pb-3 text-sm text-gray-700 px-2 lg:px-0">
                            <?php
                            if (function_exists('get_field')) {
                                $post_id = (isset($product) && is_object($product)) ? $product->get_id() : get_the_ID();
                                $size_fit = get_field('size_fit', $post_id);
                                if ($size_fit) {
                                    // Allow basic HTML from ACF, convert line breaks to paragraphs
                                    echo wp_kses_post(wpautop($size_fit));
                                } else {
                                    echo '<p>No size &amp; fit information available.</p>';
                                }
                            } else {
                                echo '<p>ACF not available.</p>';
                            }
                            ?>
                        </div>
                    </details>

                    <!-- Product Care -->
                    <details class="group">
                        <summary class="flex justify-between items-center cursor-pointer py-3 font-medium px-2 lg:px-0">
                            Product Care
                            <i class="cursor-pointer arrow">
                                <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M24 24H0V0h24v24z" fill="none" opacity=".87"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6-1.41-1.41z"/></svg>
                            </i>
                        </summary>
                        <div class="pb-3 text-sm text-gray-700 px-2 lg:px-0">
                            <?php
                            if (function_exists('get_field')) {
                                $post_id = (isset($product) && is_object($product)) ? $product->get_id() : get_the_ID();
                                $product_care = get_field('product_care', $post_id);
                                if ($product_care) {
                                    // Allow basic HTML from ACF, convert line breaks to paragraphs
                                    echo wp_kses_post(wpautop($product_care));
                                } else {
                                    echo '<p>No size &amp; fit information available.</p>';
                                }
                            } else {
                                echo '<p>ACF not available.</p>';
                            }
                            ?>
                        </div>
                    </details>
                </div>
                <div id="custom-fbt-section" class="px-2 lg:px-0">
                    <?php echo do_shortcode('[cuw_fbt]'); ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    /**
     * Hook: woocommerce_after_single_product_summary.
     *
     * @hooked woocommerce_output_product_data_tabs - 10
     * @hooked woocommerce_upsell_display - 15
     * @hooked woocommerce_output_related_products - 20
     */
    do_action('woocommerce_after_single_product_summary');
    ?>
</div>

<?php do_action('woocommerce_after_single_product'); ?>

<script>
    jQuery(function ($) {
        var $window = $(window);
        var $wrapper = $('#product-single-wrapper');
        var $left = $('#product-single-left');
        var $info = $('#product-single-info');

        // ensure wrapper is positioned relative so absolute children are relative to it
        $wrapper.css('position', 'relative');

        function updateSticky() {
            var scrollTop = $window.scrollTop();
            var winH = $window.height();
            var winW = $window.width();
            var topOffset = 45; // fixed top offset when we "stick" to top

            // mobile: reset
            if (winW < 1024) {
                $left.css('width', '100%');
                $info.css({
                    width: '100%',
                    position: 'relative',
                    top: '',
                    left: '',
                    transform: '',
                    zIndex: '',
                    overflow: ''
                });
                return;
            }

            // measurements
            var wrapperTop = $wrapper.offset().top;
            var wrapperH = $wrapper.outerHeight();
            var wrapperBottom = wrapperTop + wrapperH;
            var infoH = $info.outerHeight();
            var wrapperW = $wrapper.width();
            var halfW = wrapperW / 2;

            // set column widths
            $left.css('width', halfW);
            $info.css('width', halfW);

            // viewport area available to show info when fixed
            var avail = Math.max(0, winH - topOffset);

            // how much we must shift info up (negative translateY) so its bottom becomes visible
            var maxShift = Math.max(0, infoH - avail);

            // Points used:
            // - when page top reaches wrapperTop we start interacting
            // - as user scrolls, we increase "shift" from 0 -> maxShift
            // - once shift == maxShift the info's bottom has been revealed; keep fixed at topOffset.
            // - when wrapper bottom is reached we pin absolute to wrapper bottom
            var start = wrapperTop;
            var endShiftPoint = wrapperTop + maxShift; // scrollTop where shift reaches maxShift
            var pinBottomPoint = wrapperBottom - infoH; // scrollTop where we must pin to wrapper bottom

            // If info is shorter than available area, just use classic sticky behavior
            if (infoH <= avail) {
                var stopPoint = wrapperBottom - infoH;
                if (scrollTop < wrapperTop) {
                    // above wrapper
                    $info.css({ position: 'relative', top: '', left: '', transform: '', zIndex: '', overflow: '' });
                } else if (scrollTop >= wrapperTop && scrollTop < stopPoint) {
                    // sticky fixed at topOffset
                    $info.css({
                        position: 'fixed',
                        top: topOffset + 'px',
                        left: $wrapper.offset().left + halfW,
                        transform: 'translateX(0)',
                        zIndex: 99,
                        overflow: ''
                    });
                } else {
                    // pinned to bottom of wrapper
                    $info.css({
                        position: 'absolute',
                        top: wrapperH - infoH,
                        left: '50%',
                        transform: 'translateX(0)',
                        zIndex: '',
                        overflow: ''
                    });
                }
                return;
            }

            // Make sure pinBottomPoint is not less than wrapperTop (clamp)
            pinBottomPoint = Math.max(pinBottomPoint, wrapperTop);

            // --- Tall info: run the translate-based reveal, then fixed-top phase, then pin bottom ---
            if (scrollTop < start) {
                // before wrapper: normal flow
                $info.css({ position: 'relative', top: '', left: '', transform: '', zIndex: '', overflow: '' });
            } else if (scrollTop >= start && scrollTop < endShiftPoint && scrollTop < pinBottomPoint) {
                // while user scrolls and we haven't fully revealed bottom: fixed + translateY(-progress)
                var progress = scrollTop - start;
                var shift = Math.min(maxShift, Math.max(0, progress)); // px to shift up (0..maxShift)
                $info.css({
                    position: 'fixed',
                    top: topOffset + 'px',
                    left: $wrapper.offset().left + halfW,
                    zIndex: 99,
                    transform: 'translateX(0) translateY(' + (-shift) + 'px)',
                    overflow: ''
                });
            } else if (scrollTop >= endShiftPoint && scrollTop < pinBottomPoint) {
                // we've revealed entire content; keep fixed at topOffset and keep fully shifted
                $info.css({
                    position: 'fixed',
                    top: topOffset + 'px',
                    left: $wrapper.offset().left + halfW,
                    zIndex: 99,
                    transform: 'translateX(0) translateY(' + (-maxShift) + 'px)',
                    overflow: ''
                });
            } else {
                // reached wrapper bottom: pin absolute to wrapper bottom
                $info.css({
                    position: 'absolute',
                    top: wrapperH - infoH,
                    left: '50%',
                    transform: 'translateX(0)',
                    zIndex: '',
                    overflow: ''
                });
            }
        }

        $window.on('scroll resize', updateSticky);
        // also run once after images/fonts load because heights may change
        $(window).on('load', updateSticky);
        updateSticky();
    });
</script>