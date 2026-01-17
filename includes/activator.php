<?php
if (!defined('ABSPATH'))
  exit;

register_activation_hook(
  dirname(__DIR__) . '/anuario.php',
  'anuario_create_table'
);

function anuario_create_table()
{
  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';
  $charset = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    cargo_actual VARCHAR(255) NULL,
    perfil_linkedin TEXT NULL,
    nivel_cargo INT NULL,
    ano_egreso YEAR NULL,
    link_foto VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
  ) $charset;";

  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  dbDelta($sql);
}
