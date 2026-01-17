<?php
if (!defined('ABSPATH'))
  exit;

add_action('rest_api_init', function () {
  register_rest_route('anuario/v1', '/alumni', [
    'methods' => 'GET',
    'callback' => function () {
      global $wpdb;
      return $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}anuario_alumni ORDER BY created_at DESC",
        ARRAY_A
      );
    },
    'permission_callback' => '__return_true'
  ]);
});
