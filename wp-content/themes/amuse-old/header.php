<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>A Muse</title>

        <!-- Bootstrap -->
	<link href="<?php echo get_bloginfo('template_directory'); ?>/css/bootstrap.css" rel="stylesheet">
	<link href="<?php echo get_bloginfo('template_directory'); ?>/style.css" rel="stylesheet">
	
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		
	<!-- <base href="https://localhost/wordpress/" target="_blank">  -->
	  
	<?php wp_head(); ?>
  </head>
  
<body> 
  <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
  <script src="<?php echo get_bloginfo('template_directory'); ?>/js/jquery-1.11.3.min.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="<?php echo get_bloginfo('template_directory'); ?>/js/bootstrap.js"></script>

  <div class="hidden-xs nav-padding"> PADDING  </div> <!-- fill space the top nav takes in larger than mob views -->
 

    <!-- Just the TITLE in mob view -->
  
 <div class="visible-xs container-fluid">
  <div class="container">
    <div class="row">    <!-- Blog Title -->
      <div class="blog-name"><a href="<?php echo get_bloginfo( 'wpurl' );?>"><?php echo get_bloginfo( 'name' ); ?></a></div>
    </div>
   </div>
 </div>
 
   <!-- TOPNAV > MOB view -->
   
<nav class="navbar navbar-default navbar-fixed-top hidden-xs ">
   <div class="container-fluid">

          <!-- Brand and toggle get grouped for better mobile display -->
     <div class="container">       
      <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <span class="blog-name"><a class="navbar-brand" href="<?php echo get_bloginfo( 'wpurl' );?>"> <?php echo get_bloginfo( 'name' ); ?></a></span>
    </div>
      
       <!-- Collect the nav links, forms, and other content for toggling -->
       <div class="collapse navbar-collapse" id="topFixedNavbar1">

       		 <?php get_search_form(); ?>
        
         <ul class="nav navbar-nav navbar-right">
		   <li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Categories <span class="caret"></span></a>
				<?php wp_nav_menu(array(
					'menu'       => 'cat-menu', // specify the menu name
					'menu_class' => 'dropdown-menu', // add classes for the dropdown
					'container'  => '', // don't wrap the menu in <div>
					'items_wrap' => '<ul id="%1$s" class="%2$s" role="menu" >%3$s</ul>',
				));?>  
			 </li>
         </ul>
       </div> <!-- /.navbar-collapse -->

     </div>  <!-- /.container -->
	 </div> <!-- /.container-fluid -->
</nav>

 <!-- BTM NAV in MOB view -->
 
<nav class="visible-xs navbar navbar-default navbar-fixed-bottom">
   <div class="container-fluid">
     <!-- Brand and toggle get grouped for better mobile display -->
     <div class="navbar-header">
       <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bottom_categories" aria-expanded="false"><span class="sr-only">Toggle navigation</span>Categories</button>

	   <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bottom_search" aria-expanded="false"><span class="sr-only">Toggle search</span>
       Search</button>
     </div>
     <!-- Collect the nav links, forms, and other content for toggling -->
     

            
     <div class="collapse navbar-collapse" id="bottom_search">
                     <?php get_search_form(); ?>                
	 </div>

	 <div class="collapse navbar-collapse" id="bottom_categories">                
          <ul class="nav navbar-nav navbar-right">
				<?php wp_nav_menu(array(
					'menu'       => 'cat-menu', // specify the menu name
					'menu_class' => 'footer-menu', // add classes for the dropdown
					'container'  => '', // don't wrap the menu in <div>
					'items_wrap' => '<ul id="%1$s" class="%2$s" role="menu" >%3$s</ul>',
				));?>  
         </ul>
     </div>
     <!-- /.navbar-collapse -->
   </div>
   <!-- /.container-fluid -->
</nav>
