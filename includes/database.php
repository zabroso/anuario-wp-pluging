<?php
if (!defined('ABSPATH')) exit;

function anuario_create_table() {
  global $wpdb;
  $charset = $wpdb->get_charset_collate();

  $table_alumni    = $wpdb->prefix . 'anuario_alumni';
  $table_programas = $wpdb->prefix . 'anuario_programas';
  $table_coments   = $wpdb->prefix . 'anuario_comentarios';

  $sql = "CREATE TABLE $table_alumni (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    rut VARCHAR(12) DEFAULT NULL,
    nombre VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE DEFAULT NULL,
    cargo_actual VARCHAR(255) DEFAULT NULL,
    empresa VARCHAR(255) DEFAULT NULL,
    perfil_linkedin TEXT DEFAULT NULL,
    nivel_cargo INT DEFAULT NULL,
    ano_egreso YEAR DEFAULT NULL,
    link_foto VARCHAR(255) DEFAULT NULL,
    autorizacion_publica TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (id)
  ) $charset;

  CREATE TABLE $table_programas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    alumni_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    nivel_academico ENUM('pregrado','diplomado','magister','doctorado') NOT NULL,
    campus VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY  (id),
    KEY alumni_id (alumni_id)
  ) $charset;

  CREATE TABLE $table_coments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    alumni_id BIGINT UNSIGNED NOT NULL,
    comentario TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    KEY alumni_id (alumni_id)
  ) $charset;";

  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  dbDelta($sql);
}
