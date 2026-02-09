<?php get_header(); ?>

<section class="px-2 lg:px-0 py-6 min-h-[550px]">
	<div class="container">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<h2 class="mb-5 text-xl font-semibold"><?php the_title(); ?></h2>

			<div class="entry">
				<?php the_content(); ?>
			</div>

		<?php endwhile; endif; ?>
	</div>
</section>

<style>
	.single_add_to_cart_button {
		    color: black!important;
	}
</style>

<?php get_footer(); ?>