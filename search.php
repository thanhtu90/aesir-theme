<?php get_header(); ?>

<section>
    <h2 class="text-center my-8">Search Results</h2>

    <?php if (have_posts()) : ?>

        <ul class="product-grid border-t border-black">
            <?php while (have_posts()) : the_post(); ?>
                <li class="product type-product status-publish instock has-post-thumbnail shipping-taxable purchasable product-type-variable" data-product_id="<?php the_ID(); ?>">
                    <a href="<?php the_permalink(); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                        <?php
                        if (has_post_thumbnail()) {
                            the_post_thumbnail('large', [
                                'class' => 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail',
                                'decoding' => 'async',
                                'width' => 300,
                                'height' => 300,
                                'alt' => get_the_title(),
                            ]);
                        } else {
                            echo '<img src="' . wc_placeholder_img_src('large') . '" alt="' . esc_attr__('Placeholder', 'woocommerce') . '" width="300" height="300" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" decoding="async" />';
                        }
                        ?>
                        <h2 class="woocommerce-loop-product__title"><?php the_title(); ?></h2>
                        <span class="price">
                            <?php   woocommerce_template_loop_price(); ?>
                        </span>
                        <div class="product-variations-info" style="visibility: hidden; text-align:center; height: 30px;">Checking variations...</div>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>

        <div class="mt-8 text-center">
            <?php
            the_posts_pagination( array(
                'mid_size'           => 2,
                'prev_text'          => '&laquo; Prev',
                'next_text'          => 'Next &raquo;',
                'screen_reader_text' => 'Search results navigation',
                'type'               => 'list',
            ) );
            ?>
        </div>

    <?php else : ?>

        <p class="text-center">Nothing Found</p>

    <?php endif; ?>

</section>

<?php get_footer(); ?>