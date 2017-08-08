<?php

add_theme_support( 'post-thumbnails' ); 
set_post_thumbnail_size( 400, 300);

function kai_excerpt($limit) {
    return wp_trim_words(get_the_excerpt(), $limit, '<a href="' . esc_url( get_permalink() ) . '">' . '&nbsp;[...]' . '</a>');
}



function register_my_menu() {
  register_nav_menu('cat-menu',__( 'Category Menu' ));
}
add_action( 'init', 'register_my_menu' );

function qv_isset($var_name) {
    $array = $GLOBALS['wp_query']->query_vars;
    return array_key_exists($var_name, $array);
}
?>