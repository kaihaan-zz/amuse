<?php get_header(); ?>

 <div class="container-fluid">
  <div class="container">

<?php
	$colcount = 1; //start counter
	$cols = 2; //working cols per row - the layout has an empty middle col!
	$rows = 3; // rows
	global $query_string; //Need this to make pagination work

	/*Setting up our custom query (In here we are setting it to show a 2 cols x 3rows  per page ) */
	$query1 = new WP_Query( array('posts_per_page'=>6) );
	

	if( $query1->have_posts()) :  while( $query1->have_posts()) : $query1->the_post();
	
		if( $colcount == 1) : ?>
			<div class="row">
		<?php endif; ?>

		<div class="col-lg-5 col-xs-12 gridunit"> <!-- grid post unit -->
			<?php get_template_part( 'content', get_post_format() ); ?>
		</div>

		<?php 
		if( $colcount == 1) : ?>
			<div class="hidden-xs col-lg-2 gridunit">&nbsp;</div>
		<?php endif;
				
		if( $colcount == $cols ) : 
			$colcount = 0; // Reset counter ?>
			</div> <!-- end row -->  
		<?php endif;
				
		$colcount++;

		endwhile;
	//Pagination can go here if you want it.
	endif;
	wp_reset_postdata(); // Reset post_data after each loop
?>





  </div>
</div>

<?php get_footer(); ?>