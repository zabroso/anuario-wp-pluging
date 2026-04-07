<?php
/*
Plugin Name: Anuario Alumni
Description: Gestión de alumni para el anuario institucional
Version: 1.0
Author: Alumni
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/database.php';
require_once plugin_dir_path(__FILE__) . 'includes/actions.php';
require_once plugin_dir_path(__FILE__) . 'includes/export.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-list.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-bulk.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-comments.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-programs.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-view.php';
require_once plugin_dir_path(__FILE__) . 'includes/api.php';

register_activation_hook(__FILE__, 'anuario_create_table');

add_action('admin_menu', function () {
  add_menu_page(
    'Anuario Alumni',
    'Anuario Alumni',
    'manage_options',
    'anuario-alumni',
    'anuario_render_list',
    'dashicons-groups'
  );

  add_submenu_page(
    null,
    'Editar Alumni',
    'Editar',
    'manage_options',
    'anuario-alumni-edit',
    'anuario_render_form'
  );

  add_submenu_page(
    'anuario-alumni',
    'Comentarios',
    'Comentarios',
    'manage_options',
    'anuario-alumni-comments',
    'anuario_render_comments'
  );

  add_submenu_page(
    'anuario-alumni',
    'Programas Académicos',
    'Programas',
    'manage_options',
    'anuario-alumni-programs',
    'anuario_render_programs'
  );

  add_submenu_page(
    'anuario-alumni',
    'Carga masiva',
    'Carga masiva',
    'manage_options',
    'anuario-alumni-bulk',
    'anuario_render_bulk'
  );

  add_submenu_page(
    null,
    'Ver Alumni',
    'Ver',
    'manage_options',
    'anuario-alumni-view',
    'anuario_render_view'
  );
});
