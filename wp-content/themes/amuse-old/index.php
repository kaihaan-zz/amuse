<?php get_header(); ?>

 <div class="container-fluid">
 <div class="container">
 <div class="row">
<?php
	if( have_posts()) :  while( have_posts()) : the_post(); ?>
			<div class="col-xl-4 col-lg-4 col-med-6 col-sm-6 col-xs-12 gridunit"> <!-- grid post unit -->
			<?php get_template_part( 'content', 'grid' ); ?>
			</div>  <!-- gridunit -->

		<?php
								
		endwhile; ?>
		</div>  <!-- grid row -->
	
	<?php else : ?>
		<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
	<?php endif; ?>

</div>  <!-- container -->
</div>  <!-- container-fluid -->

<?php get_footer(); ?>