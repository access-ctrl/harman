<?php 
function harman_wp_theme_setup() {
  add_theme_support('menus');
  add_theme_support('title-tag');  
  add_theme_support('post-thumbnails');
  register_nav_menus([
    'header' => __('Header Menu', 'siemxpert-wp'),
    'footer_menu' => __('Footer Menu', 'siemxpert-wp'),    
    'footer_menu5' => __('Legal Menu', 'siemxpert-wp'),
  ]);
}
add_action('after_setup_theme', 'harman_wp_theme_setup');

// Enqueue Theme Assets
function harman_wp_enqueue_assets() {
  wp_enqueue_style('fonts', 'https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap');
  wp_enqueue_style('fonts', 'https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
  
  wp_enqueue_style('theme-style', get_template_directory_uri() . '/style.css');
  wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.min.css');
  wp_enqueue_style('owl-style', get_template_directory_uri() . '/assets/css/owl.carousel.min.css');  
  wp_enqueue_style('menu-style', get_template_directory_uri() . '/assets/css/slimmenu.min.css');  
  wp_enqueue_style('custom-css-style', get_template_directory_uri() . '/assets/css/style.css', [], '1.1.0');

  wp_enqueue_script('jquery', get_template_directory_uri() . '/assets/js/jquery.min.js', [], null, true);
  wp_enqueue_script('menu', get_template_directory_uri() . '/assets/js/jquery.slimmenu.min.js', ['jquery'], null, true); 
  wp_enqueue_script('owl-script', get_template_directory_uri() . '/assets/js/owl.carousel.min.js', ['jquery'], null, true); 
  wp_enqueue_script('bootstrap-js', get_template_directory_uri() . '/assets/js/bootstrap.bundle.js', ['jquery'], null, true); 
  wp_enqueue_script('custom-script', get_template_directory_uri() . '/assets/js/custom.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'harman_wp_enqueue_assets');


// Enqueue admin styles
function harman_admin_enqueue_assets() {
  wp_enqueue_style('admin-custom-css', get_template_directory_uri().'/assets/css/admin.css', [], null);
}
add_action('admin_enqueue_scripts', 'harman_admin_enqueue_assets');


add_filter('use_block_editor_for_post', '__return_false');
 