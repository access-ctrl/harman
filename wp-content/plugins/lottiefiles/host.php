<?php

/**
 * Plugin Name: LottieFiles
 * Plugin URI: https://lottiefiles.com/plugins/wordpress
 * Description: LottieFiles for WordPress is the easiest way to add Lottie animations to your WordPress website using the Gutenberg editor.
 * Author: LottieFiles
 * Author URI: https://lottiefiles.com/
 * Version: 3.0.0
 * License: GPLv3
 *
 * @package LottieFiles
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Block Initializer.
 */
define('LOTTIE_PLUGIN_DIR', plugin_dir_path(__FILE__));
require_once LOTTIE_PLUGIN_DIR . 'src/common.php';
require_once LOTTIE_PLUGIN_DIR . 'src/gutenberg-block/init.php';
require_once LOTTIE_PLUGIN_DIR . 'src/admin/settings/init.php';

register_activation_hook(__FILE__, 'lottiefiles_activate');
add_action('admin_init', 'lottiefiles_activation_redirect');

/**
 * Plugin activation callback. Registers option to redirect on next admin load.
 */
function lottiefiles_activate()
{
  add_option('lottiefiles_activation_redirect', true);
}


/**
 * Redirects the user after plugin activation
 */
function lottiefiles_activation_redirect()
{
  // Make sure it's the correct user
  if (get_option('lottiefiles_activation_redirect', false)) {
    // Make sure we don't redirect again after this one
    delete_option('lottiefiles_activation_redirect');
    wp_safe_redirect(admin_url('admin.php?page=lottiefiles-admin-settings'));
    exit;
  }
}


add_filter('upload_mimes', 'lottiefiles_mime_types');

function lottiefiles_mime_types($mimes)
{
  $mimes['txt'] = 'text/plain';
  $mimes['json'] = 'text/plain';
  return $mimes;
}

add_filter('wp_check_filetype_and_ext', 'lottiefiles_filetypes', 10, 5);

function lottiefiles_filetypes($data, $file, $filename, $mimes, $real_mime)
{
  if (!empty($data['ext']) && !empty($data['type'])) {
    return $data;
  }
  $wp_file_type = wp_check_filetype($filename, $mimes);
  if ('json' === $wp_file_type['ext']) {
    $data['ext']  = 'json';
    $data['type'] = 'text/plain';
  } elseif ('txt' === $wp_file_type['ext']) {
    $data['ext']  = 'txt';
    $data['type'] = 'text/plain';
  }
  return $data;
}
