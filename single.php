<?php get_header(); ?>

<section>
	<div class="container">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<div <?php post_class() ?> id="post-<?php the_ID(); ?>">

				<?php the_category(' | ');?>
				
				<h3 class="mb-[0.5rem]"><?php the_title(); ?></h3>
				
				<p>by <span class="[&>a]:font-bold"><?php the_author_posts_link(); ?></span></p>
				<p class="font-bold"><?php echo get_the_date(); ?></p>

				<div class="entry my-2">
					
					<?php the_content(); ?>

				</div>
				
				<?php edit_post_link('Edit this entry','','.'); ?>
				
			</div>

			<?php // comments_template(); ?>

		<?php endwhile; endif; ?>
	</div>
</section>

<?php get_footer(); ?>