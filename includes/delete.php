<?php
if (!defined('ABSPATH')) exit;

add_action('admin_init', function () {
  if (isset($_GET['delete']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_alumni')) {
    global $wpdb;
    $wpdb->delete(
      $wpdb->prefix . 'anuario_alumni',
      ['id' => intval($_GET['delete'])]
    );
    wp_redirect(admin_url('admin.php?page=anuario-alumni'));
    exit;
  }
});
