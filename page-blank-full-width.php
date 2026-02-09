<?php /* Template Name: Blank Full Width Template */ ?>

<?php get_header(); ?>

    <div class="min-h-[550px]">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

            <?php the_content(); ?>

        <?php endwhile; endif; ?>
    </div>
<?php get_footer(); ?>