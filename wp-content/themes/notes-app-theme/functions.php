<?php

/*
 *  functions.php holds all important functions.
 */


//handy contstant
define('TEMPL_PATH', get_bloginfo('template_directory'));

function hide_admin_bar_from_front_end(){
  if (is_blog_admin()) {
    return true;
  }
  return false;
}
add_filter( 'show_admin_bar', 'hide_admin_bar_from_front_end' );

/*
 *  mobile_meta() adds all meta information to the <head> element for us
 */

function mobile_meta(){ ?>
        <meta name="description" content="<?php bloginfo('description'); ?>">
        <meta name="HandheldFriendly" content="True">
        <meta name="MobileOptimized" content="320">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="cleartype" content="on">

        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="img/touch/apple-touch-icon-144x144-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="img/touch/apple-touch-icon-114x114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/touch/apple-touch-icon-72x72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="img/touch/apple-touch-icon-57x57-precomposed.png">
        <link rel="shortcut icon" href="img/touch/apple-touch-icon.png">

        <!-- Tile icon for Win8 (144x144 + tile color) -->
        <meta name="msapplication-TileImage" content="img/touch/apple-touch-icon-144x144-precomposed.png">
        <meta name="msapplication-TileColor" content="#f8f0b1">


        <!-- For iOS web apps. Delete if not needed. https://github.com/h5bp/mobile-boilerplate/issues/94 -->
    
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="">
<?php }

add_action('wp_head', 'mobile_meta');

/*
 *  register_scripts_and_styles() adds all stylesheets and scripts for us
 */

function register_scripts_and_styles(){
  wp_enqueue_style( 'normalize', TEMPL_PATH.'/css/normalize.css' );
  wp_enqueue_style( 'notes', TEMPL_PATH.'/css/main.css' );

  wp_enqueue_script( 'modernizr', TEMPL_PATH.'/js/vendor/modernizr-2.6.2.min.js', array(), false, $in_footer = false );
  wp_enqueue_script( 'helper', TEMPL_PATH.'/js/helper.js', array(), false, true );
  wp_enqueue_script( 'mustache', TEMPL_PATH.'/js/mustache.js', array(), false, true );
  wp_enqueue_script( 'main', TEMPL_PATH.'/js/main.js', array('jquery'), false, true );

  //WP LOCALIZE SCRIPT
  
}

add_action('wp_enqueue_scripts', 'register_scripts_and_styles');

/*
 *  THINGS TO ADD
 */

//FORCE LOGIN TO VIEW SITE

//CUSTOM LOGIN STYLE

//LOGIN REDIRECT TO HOME

//INCLUDE AJAX ACTIONS