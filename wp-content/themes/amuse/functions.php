<?php

add_theme_support( 'post-thumbnails' ); 
set_post_thumbnail_size( 400, 300);

/**
 * Filter the except length to 20 words.
 *
 * @param int $length Excerpt length.
 * @return int (Maybe) modified excerpt length.
 */
function wpdocs_custom_excerpt_length( $length ) {
    return 20;
}
add_filter( 'excerpt_length', 'wpdocs_custom_excerpt_length', 999 );

/**
 * Filter the "read more" excerpt string link to the post.
 *
 * @param string $more "Read more" excerpt string.
 * @return string (Maybe) modified "read more" excerpt string.
 */
function wpdocs_excerpt_more( $more ) {
    return sprintf( '<a class="read-more" href="%1$s">%2$s</a>',
        get_permalink( get_the_ID() ),
        __( ' ...more', 'textdomain' )
    );
}
add_filter( 'excerpt_more', 'wpdocs_excerpt_more' );

function register_my_menu() {
  register_nav_menu('cat-menu',__( 'Category Menu' ));
}
add_action( 'init', 'register_my_menu' );

function qv_isset($var_name) {
    $array = $GLOBALS['wp_query']->query_vars;
    return array_key_exists($var_name, $array);
}
?>