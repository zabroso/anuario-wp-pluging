<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {

  add_menu_page(
    'Alumni',
    'Alumni',
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
    'Carga masiva',
    'Carga masiva',
    'manage_options',
    'anuario-alumni-bulk',
    'anuario_render_bulk'
  );
});
