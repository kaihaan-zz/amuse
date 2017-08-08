<?php get_header(); ?>

 <div class="container-fluid">
  <div class="container">

	<div class="row">

		<div class="col-lg-12">

				<?php
					/* Start the Loop */
					while ( have_posts() ) : 
						the_post();
						get_template_part( 'content', 'single' );
					endwhile; // End of the loop.
				?>

		</div> <!-- /.blog-main -->

		<?php // get_sidebar(); ?>

	</div> <!-- /.row -->
	
	</div>
	</div>

<?php get_footer(); ?>
