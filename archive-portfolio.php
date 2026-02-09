<?php get_header(); ?>

<section class="pt-0 lg:pt-20">
    <div class="container">
        <div class="bg-cover bg-center bg-no-repeat min-h-fit lg:min-h-screen flex items-center justify-center py-14 lg:py-0"
        style="background-image: url(<?php echo bloginfo('template_directory'); ?>/assets/images/hehe.jpg);">
            <h1 class="text-[18px] lg:text-[80px] text-white uppercase ">the portfolio</h1>
        </div>
    </div>
</section>
<section class="px-3 lg:px-0">
    <div class="container">
        <div class=" bg-[#ECEBE8] p-1 pt-5 lg:p-16 lg:pt-[30px]">
            <div class="text-center">
                <h6 class="mb-4 lg:mb-2">GALLERY FEATURES</h6>
                <h3 class="text-[19px] leading-[0.6] lg:text-[50px] lg:leading-[2.2]">WEDDING & ENGAGEMENT</h3>
                <p class="text-[11px] lg:text-[18px] mt-5 lg:mt-0">Erik approaches every story with the keen eye of a seasoned strategist and the creativity of an artist. He crafts compelling narratives and visuals that resonate with authenticity and timelessness. His dedication to understanding your vision ensures that each project reflects your unique story.</p>
            </div>
            <?php if (have_posts()) : ?>
                <div class="
                    grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-8 mt-2 lg:mt-16
                    [&_.item>img]:object-cover [&_.item>div]:bg-transparent [&_.item>div]:lg:bg-white [&_.item>div]:p-5 [&_.item>div]:pt-3 [&_.item>div]:lg:p-[30px] [&_.item>div]:text-center 
                    [&_.item>div>h5]:truncate [&_.item>div>h5>a]:capitalize [&_.item>div>h5>a]:text-[15px] [&_.item>div>h5>a]:lg:text-[36px] [&_.item>div>h5]:leading-[16px] [&_.item>div>h5]:lg:leading-[1.2]
                ">
                    <?php while (have_posts()) : the_post(); ?>
                    <div class="item">
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('full', array('class' => 'object-cover')); ?>
                            </a>
                        <?php endif; ?>
                        <div>
                            <h5><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h5>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <div class="
                    pagination py-4 [&_.nav-links]:flex [&_.nav-links]:items-center [&_.nav-links]:justify-center [&_.nav-links]:gap-1
                        [&_.page-numbers]:inline-flex [&_.page-numbers]:items-center [&_.page-numbers]:justify-center [&_.page-numbers]:w-[35px] [&_.page-numbers]:h-[35px] [&_.page-numbers]:text-[15px] [&_.page-numbers]:bg-transparent [&_.page-numbers]:text-black [&_.page-numbers]:rounded-full [&_.page-numbers]:transition-all [&_.page-numbers]:duration-300 
                        [&_.page-numbers:hover]:bg-black [&_.page-numbers:hover]:text-white [&_.page-numbers.current]:bg-black [&_.page-numbers.current]:text-white [&_.page-numbers.next]:w-auto [&_.page-numbers.next:hover]:bg-transparent [&_.page-numbers.next:hover]:text-black [&_.page-numbers.prev]:w-auto [&_.page-numbers.prev:hover]:bg-transparent [&_.page-numbers.prev:hover]:text-black
                ">
                    <?php
                        the_posts_pagination( array(
                            'mid_size'  => 2,
                            'prev_text' => __( '« Previous', 'textdomain' ),
                            'next_text' => __( 'Next »', 'textdomain' ),
                        ) );
                    ?>
                </div>
            </div>
        <?php wp_reset_postdata(); else : ?>

            <p class="text-center">Nothing Found</p>

        <?php endif; ?>
    </div>
</section>

<?php
// Get all terms in custom taxonomy 'portfolio-category'
$terms = get_terms( array(
    'taxonomy' => 'portfolio-category',
    'hide_empty' => false, // Show empty categories as well
) );

if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :

    foreach ( $terms as $term ) :
        // Get the ACF field value for the taxonomy term (loop_archive)
        $loop_archive = get_field( 'loop_archive', 'portfolio-category_' . $term->term_id );
        $layout = get_field( 'layout', 'portfolio-category_' . $term->term_id );

        // Only continue if the ACF field 'loop_archive' is true
        if ( $loop_archive ) :
?>

<section class="px-3 lg:px-0">
    <div class="container">
        <div class="py-8">
            <div class="text-center">
                <h6 class="mb-4 lg:mb-2">GALLERY FEATURES</h6>
                <h3 class="text-[19px] leading-[0.6] lg:text-[50px] lg:leading-[2.2] uppercase">
                    <?php echo esc_html( $term->name ); ?>
                </h3>
            </div>
            <?php
                // Query posts under this specific 'portfolio-category' term
                $args = array(
                    'post_type' => 'portfolio',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'portfolio-category',
                            'field' => 'slug',
                            'terms' => $term->slug,
                        ),
                    ),
                );

                $portfolio_query = new WP_Query( $args );

                if ( $portfolio_query->have_posts() ) :
            ?>
                <?php if ($layout=='Vertical') : ?>
                    <div class="
                        grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-24 mt-6
                        [&_.item]:grid [&_.item]:grid-cols-2 [&_.item]:lg:grid-cols-1 [&_.item]:items-center [&_.item]:justify-center 
                        [&_.item>div]:text-center [&_.item>div]:p-6
                        [&_.item>div>h3]:truncate
                        [&_.item>div>h3>a]:text-[18px] [&_.item>div>h3]:leading-[18px] [&_.item>div>h3>a]:text-black [&_.item>div>h3>a]:font-normal
                        [&_.item>div>p]:truncate [&_.item>div>p]:font-lato [&_.item>div>p]:text-black [&_.item>div>p]:font-normal
                        [&_.item:nth-child(odd)>a.col]:order-1 [&_.item:nth-child(odd)>div.col]:order-2 
                        [&_.item:nth-child(even)>a.col]:order-2 [&_.item:nth-child(even)>div.col]:order-1
                        [&_.item:nth-child(even)>a.col]:lg:order-1 [&_.item:nth-child(even)>div.col]:lg:order-2
                    ">
                    <?php while ( $portfolio_query->have_posts() ) : $portfolio_query->the_post(); ?>
                        <div class="item">
                            <?php if (has_post_thumbnail()) : ?>
                                <a class="col" href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('full', array('class' => 'object-cover')); ?>
                                </a>
                            <?php endif; ?>
                            <div class="col">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p><?php echo get_field('description');?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <div class="
                        grid gap-8 lg:gap-24 mt-6
                        [&_.item]:flex [&_.item]:items-center [&_.item]:justify-center 
                        [&_.item>div]:text-center [&_.item>div]:p-6
                        [&_.item>div>h3]:truncate
                        [&_.item>div>h3>a]:text-[15px] [&_.item>div>h3>a]:lg:text-[28px] [&_.item>div>h3]:leading-[1.2em] [&_.item>div>h3>a]:text-[#19191A] [&_.item>div>h3>a]:font-normal
                        [&_.item>div>p]:text-[10px] [&_.item>div>p]:lg:text-[18px] [&_.item>div>p]:truncate [&_.item>div>p]:font-lato [&_.item>div>p]:text-[#19191A] [&_.item>div>p]:font-light
                        [&_.item>div>.more]:inline-block [&_.item>div>.more]:mt-2 [&_.item>div>.more]:lg:mt-6
                        [&_.item>a.col]:w-[55%] [&_.item>div.col]:w-[45%] [&_.item>a.col]:lg:w-[45%] [&_.item>div.col]:lg:w-[55%]
                        [&_.item:nth-child(odd)>a.col]:order-2 [&_.item:nth-child(odd)>div.col]:order-1 
                        [&_.item:nth-child(even)>a.col]:order-1 [&_.item:nth-child(even)>div.col]:order-2
                    ">
                    <?php while ( $portfolio_query->have_posts() ) : $portfolio_query->the_post(); ?>
                        <div class="item">
                            <?php if (has_post_thumbnail()) : ?>
                                <a class="col" href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('full', array('class' => 'object-cover')); ?>
                                </a>
                            <?php endif; ?>
                            <div class="col">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p><?php echo get_field('description');?></p>
                                <a href="<?php the_permalink(); ?>" class="more">View gallery</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            <?php
                endif;
            // Reset post data
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
<?php
        endif; // End loop_archive ACF check
    endforeach;
endif;
?>

<!-- <section class="px-3 lg:px-0">
    <div class="container">
        <div class="py-8">
            <div class="text-center">
                <h6 class="mb-4 lg:mb-2">GALLERY FEATURES</h6>
                <h3 class="text-[19px] leading-[0.6] lg:text-[50px] lg:leading-[2.2]">WEDDING & ENGAGEMENT</h3>
            </div>
            <div class="
                grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-24 mt-6
                [&_.item]:grid [&_.item]:grid-cols-2 [&_.item]:lg:grid-cols-1 [&_.item]:items-center [&_.item]:justify-center 
                [&_.item>div]:text-center [&_.item>div]:p-6
                [&_.item>div>h3]:truncate
                [&_.item>div>h3>a]:text-[18px] [&_.item>div>h3]:leading-[18px] [&_.item>div>h3>a]:text-black [&_.item>div>h3>a]:font-normal
                [&_.item>div>p]:truncate [&_.item>div>p]:font-lato [&_.item>div>p]:text-black [&_.item>div>p]:font-normal
                [&_.item:nth-child(odd)>a.col]:order-1 [&_.item:nth-child(odd)>div.col]:order-2 
                [&_.item:nth-child(even)>a.col]:order-2 [&_.item:nth-child(even)>div.col]:order-1
                [&_.item:nth-child(even)>a.col]:lg:order-1 [&_.item:nth-child(even)>div.col]:lg:order-2
            ">
                <div class="item">
                    <a class="col" href="#"><img src="<?php echo bloginfo('template_directory'); ?>/assets/images/Robin-Florence-13-scaled.jpg" /></a>
                    <div class="col">
                        <h3><a href="#">title</a></h3>
                        <p>description</p>
                    </div>
                </div>
                <div class="item">
                    <a class="col" href="#"><img src="<?php echo bloginfo('template_directory'); ?>/assets/images/Robin-Florence-13-scaled.jpg" /></a>
                    <div class="col">
                        <h3><a href="#">title</a></h3>
                        <p>description</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="px-3 lg:px-0">
    <div class="container">
        <div class="py-8">
            <div class="text-center">
                <h6 class="mb-4 lg:mb-2">GALLERY FEATURES</h6>
                <h3 class="text-[19px] leading-[0.6] lg:text-[50px] lg:leading-[2.2]">LIFESTYLE PORTRAIT</h3>
            </div>
            <div class="
                grid gap-8 lg:gap-24 mt-6
                [&_.item]:flex [&_.item]:items-center [&_.item]:justify-center 
                [&_.item>div]:text-center [&_.item>div]:p-6
                [&_.item>div>h3]:truncate
                [&_.item>div>h3>a]:text-[15px] [&_.item>div>h3>a]:lg:text-[28px] [&_.item>div>h3]:leading-[1.2em] [&_.item>div>h3>a]:text-[#19191A] [&_.item>div>h3>a]:font-normal
                [&_.item>div>p]:text-[10px] [&_.item>div>p]:lg:text-[18px] [&_.item>div>p]:truncate [&_.item>div>p]:font-lato [&_.item>div>p]:text-[#19191A] [&_.item>div>p]:font-light
                [&_.item>div>.more]:inline-block [&_.item>div>.more]:mt-2 [&_.item>div>.more]:lg:mt-6
                [&_.item>a.col]:w-[55%] [&_.item>div.col]:w-[45%] [&_.item>a.col]:lg:w-[45%] [&_.item>div.col]:lg:w-[55%]
                [&_.item:nth-child(odd)>a.col]:order-2 [&_.item:nth-child(odd)>div.col]:order-1 
                [&_.item:nth-child(even)>a.col]:order-1 [&_.item:nth-child(even)>div.col]:order-2
            ">
                <div class="item">
                    <a class="col" href="#"><img src="<?php echo bloginfo('template_directory'); ?>/assets/images/Robin-Florence-13-scaled.jpg" /></a>
                    <div class="col">
                        <h3><a href="#">title</a></h3>
                        <p>description</p>
                        <a href="#" class="more">View gallery</a>
                    </div>
                </div>
                <div class="item">
                    <a class="col" href="#"><img src="<?php echo bloginfo('template_directory'); ?>/assets/images/Robin-Florence-13-scaled.jpg" /></a>
                    <div class="col">
                        <h3><a href="#">title</a></h3>
                        <p>description</p>
                        <a href="#" class="more">View gallery</a>
                    </div>
                </div>
                <div class="item">
                    <a class="col" href="#"><img src="<?php echo bloginfo('template_directory'); ?>/assets/images/Robin-Florence-13-scaled.jpg" /></a>
                    <div class="col">
                        <h3><a href="#">title</a></h3>
                        <p>description</p>
                        <a href="#" class="more">View gallery</a>
                    </div>
                </div>
                <div class="item">
                    <a class="col" href="#"><img src="<?php echo bloginfo('template_directory'); ?>/assets/images/Robin-Florence-13-scaled.jpg" /></a>
                    <div class="col">
                        <h3><a href="#">title</a></h3>
                        <p>description</p>
                        <a href="#" class="more">View gallery</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->

<?php get_footer(); ?>