<?php get_header(); ?>

<section class="px-3">

    <?php if (have_posts()) : ?>

		<?php /* If this is a category archive */ if (is_category()) { ?>
			<h2 class="text-center mb-[30px]">Archive for the &#8216;<?php single_cat_title(); ?>&#8217; Category</h2>

		<?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
			<h2 class="text-center mb-[30px]">Posts Tagged &#8216;<?php single_tag_title(); ?>&#8217;</h2>

		<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
			<h2 class="text-center mb-[30px]">Archive for <?php the_time('F jS, Y'); ?></h2>

		<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
			<h2 class="text-center mb-[30px]">Archive for <?php the_time('F, Y'); ?></h2>

		<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
			<h2 class="text-center mb-[30px]">Archive for <?php the_time('Y'); ?></h2>

		<?php /* If this is an author archive */ } elseif (is_author()) { ?>
			<h2 class="text-center mb-[30px]">Author Archive</h2>

		<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
			<h2 class="text-center mb-[30px]">Blog Archives</h2>
		
		<?php } ?>

        <?php 
            $column1 = array();
            $column2 = array();
            $column3 = array();
            $column4 = array();

            $m_column = array();

            $index = 0;
            while (have_posts()) : the_post();
                $post_temp = get_post();

                if ($index % 4 === 0) {
                    $column1[] = $post_temp;
                } elseif ($index % 4 === 1) {
                    $column2[] = $post_temp;
                } elseif ($index % 4 === 2) {
                    $column3[] = $post_temp;
                } else {
                    $column4[] = $post_temp;
                }

                $m_column[] = $post_temp;

                $index++;
            endwhile;
        ?>

        <div class="
            grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-start
            [&_.article]:text-center 
            [&_.article>h3>a]:text-[#54595F] [&_.article>h3>a]:font-roboto [&_.article>h3>a]:font-bold
            [&_.article_.date]:text-[13px] [&_.article_.date]:mb-[13px] [&_.article_.date]:text-[#adadad]
            [&_.article_.desc]:mb-[10px] [&_.article_.desc]:text-[14px] [&_.article_.desc]:leading-[1.5em] [&_.article_.desc]:text-[#777] [&_.article_.desc]:font-roboto [&_.article_.desc]:font-normal
            [&_.article_.more]:text-black [&_.article_.more]:font-roboto [&_.article_.more]:text-[13px] [&_.article_.more]:font-bold
        ">
            <?php if (wp_is_mobile()) : ?>
                <div class="grid gap-4 items-start">
                    <?php 
                        foreach($m_column as $post):
                            setup_postdata($post);
                            if( $post ): ?>
                                <div class="article">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('full', array('class' => 'object-cover w-full h-auto')); ?>
                                        <?php endif; ?>
                                    </a>
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p class="date"><?php echo get_the_date(); ?></p>
                                    <p class="desc"><?php echo wp_trim_words(get_the_content(), 20); ?></p>
                                    <a href="<?php the_permalink(); ?>" class="more">Read more »</a>
                                </div>
                            <?php endif;
                        endforeach;
                        wp_reset_postdata();
                    ?>
                </div>
            <?php else : ?>
                <div class="grid gap-4 items-start">
                    <?php 
                        foreach($column1 as $post):
                            setup_postdata($post);
                            if( $post ): ?>
                                <div class="article">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('full', array('class' => 'object-cover w-full h-auto')); ?>
                                        <?php endif; ?>
                                    </a>
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p class="date"><?php echo get_the_date(); ?></p>
                                    <p class="desc"><?php echo wp_trim_words(get_the_content(), 20); ?></p>
                                    <a href="<?php the_permalink(); ?>" class="more">Read more »</a>
                                </div>
                            <?php endif;
                        endforeach;
                        wp_reset_postdata();
                    ?>
                </div>
                <div class="grid gap-4 items-start">
                    <?php 
                        foreach($column2 as $post):
                            setup_postdata($post);
                            if( $post ): ?>
                                <div class="article">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('full', array('class' => 'object-cover w-full h-auto')); ?>
                                        <?php endif; ?>
                                    </a>
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p class="date"><?php echo get_the_date(); ?></p>
                                    <p class="desc"><?php echo wp_trim_words(get_the_content(), 20); ?></p>
                                    <a href="<?php the_permalink(); ?>" class="more">Read more »</a>
                                </div>
                            <?php endif;
                        endforeach;
                        wp_reset_postdata();
                    ?>
                </div>
                <div class="grid gap-4 items-start">
                    <?php 
                        foreach($column3 as $post):
                            setup_postdata($post);
                            if( $post ): ?>
                                <div class="article">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('full', array('class' => 'object-cover w-full h-auto')); ?>
                                        <?php endif; ?>
                                    </a>
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p class="date"><?php echo get_the_date(); ?></p>
                                    <p class="desc"><?php echo wp_trim_words(get_the_content(), 20); ?></p>
                                    <a href="<?php the_permalink(); ?>" class="more">Read more »</a>
                                </div>
                            <?php endif;
                        endforeach;
                        wp_reset_postdata();
                    ?>
                </div>
                <div class="grid gap-4 items-start">
                    <?php 
                        foreach($column4 as $post):
                            setup_postdata($post);
                            if( $post ): ?>
                                <div class="article">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('full', array('class' => 'object-cover w-full h-auto')); ?>
                                        <?php endif; ?>
                                    </a>
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p class="date"><?php echo get_the_date(); ?></p>
                                    <p class="desc"><?php echo wp_trim_words(get_the_content(), 20); ?></p>
                                    <a href="<?php the_permalink(); ?>" class="more">Read more »</a>
                                </div>
                            <?php endif;
                        endforeach;
                        wp_reset_postdata();
                    ?>
                </div>
            <?php endif; ?>
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

    <?php else : ?>

        <p class="text-center">Nothing Found</p>

    <?php endif; ?>

</section>

<?php get_footer(); ?>