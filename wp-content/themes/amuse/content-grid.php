<!-- horizontal rule -->
<hr>

<!-- Post Thumbnail with defaukts stored in /uploads/category-images/-->

<?php if ( has_post_thumbnail() ) : ?>

<div><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('large',  array( 'class' => 'img-responsive center-block' )); ?></a></div>

<?php else : 
$category = get_the_category(); 
?>

<div class="entry-thumbnail">
<a href="<?php the_permalink(); ?>"><img class="img-responsive" src="<?php bloginfo('url'); ?>/wp-content/uploads/category-images/<?php echo $category[0]->category_nicename ; ?>-360x180.jpg" alt="<?php the_title(); ?>" /></a>
</div>

<?php endif; ?>


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