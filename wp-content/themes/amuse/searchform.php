<?php
/**
 * default search form
 */
?>
<form class="navbar-form navbar-right" role="search" method="get" id="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <div class="form-group">
    	<label class="screen-reader-text" for="s"><?php _e( 'Search for:', 'presentation' ); ?></label>
        <input type="search"  class="form-control" placeholder="<?php echo esc_attr( 'Search…', 'presentation' ); ?>" name="s" id="search-input" value="<?php echo esc_attr( get_search_query() ); ?>" />
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>