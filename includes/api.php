<?php
if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function () {
  register_rest_route('anuario/v1', '/alumni', [
    'methods'             => 'GET',
    'callback'            => function () {
      global $wpdb;
      $table = $wpdb->prefix . 'anuario_alumni';

      return $wpdb->get_results(
        "SELECT nombre, cargo_actual, empresa, perfil_linkedin, nivel_cargo, ano_egreso, link_foto
         FROM $table
         ORDER BY created_at DESC",
        ARRAY_A
      );
    },
    'permission_callback' => '__return_true',
  ]);
});
