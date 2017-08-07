<!-- horizontal rule -->
<hr>

<!-- Post Thumbnail -->
<div><?php the_post_thumbnail('large',  array( 'class' => 'img-responsive' )); ?></div>

<!-- Post Title -->
<div class='grid-title'><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2></div>

<!-- Post Date -->
<div class="grid-attrib"><?php echo get_the_date(); ?></div>

<!-- Post Excerpt -->
<div class="grid-excerpt"><?php the_excerpt(); ?></div>

<!-- Post Category Link -->
<div class="grid-attrib"><?php
	$categories = get_the_category();
 
	if ( ! empty( $categories ) ) {
		echo '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';   
	}
?></div>
<?php // the_content(); ?>