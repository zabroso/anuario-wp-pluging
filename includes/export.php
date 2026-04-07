<?php
if (!defined('ABSPATH')) exit;

add_action('admin_init', function () {
  if (!isset($_GET['anuario_template'])) return;
  if (!current_user_can('manage_options')) return;

  header('Content-Type: text/csv; charset=utf-8');

  if ($_GET['anuario_template'] === 'alumni') {
    header('Content-Disposition: attachment; filename=plantilla_alumni.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['nombre','rut','fecha_nacimiento','cargo_actual','empresa','perfil_linkedin','nivel_cargo','ano_egreso','link_foto','autorizacion_publica']);
    fputcsv($out, ['Juan Pérez','12.345.678-9','1992-03-15','Ingeniero de Software','Google','https://linkedin.com/in/juanperez','5','2020','','1']);
  } elseif ($_GET['anuario_template'] === 'comentarios') {
    header('Content-Disposition: attachment; filename=plantilla_comentarios.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['rut','comentario']);
    fputcsv($out, ['12.345.678-9','Texto del comentario sobre el alumni.']);
  } elseif ($_GET['anuario_template'] === 'programas') {
    header('Content-Disposition: attachment; filename=plantilla_programas.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['rut','nombre','nivel_academico','campus']);
    fputcsv($out, ['12.345.678-9','Ingeniería Civil Informática','pregrado','Casa Central Valparaíso']);
  } else {
    return;
  }

  fclose($out);
  exit;
});

add_action('admin_init', function () {
  if (!isset($_GET['anuario_export'])) return;
  if (!current_user_can('manage_options')) return;

  global $wpdb;

  if ($_GET['anuario_export'] === 'programas') {
    $table_alumni   = $wpdb->prefix . 'anuario_alumni';
    $table_programs = $wpdb->prefix . 'anuario_programas';

    $rows = $wpdb->get_results(
      "SELECT a.rut, p.nombre, p.nivel_academico, p.campus
       FROM $table_programs p
       JOIN $table_alumni a ON p.alumni_id = a.id
       ORDER BY a.rut ASC, p.nivel_academico ASC",
      ARRAY_A
    );

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=anuario_programas.csv');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['rut', 'nombre', 'nivel_academico', 'campus']);
    foreach ($rows as $r) {
      fputcsv($out, [$r['rut'], $r['nombre'], $r['nivel_academico'], $r['campus']]);
    }
    fclose($out);
    exit;
  }

  if ($_GET['anuario_export'] === 'comentarios') {
    $table_alumni   = $wpdb->prefix . 'anuario_alumni';
    $table_comments = $wpdb->prefix . 'anuario_comentarios';

    $rows = $wpdb->get_results(
      "SELECT a.rut, c.comentario
       FROM $table_comments c
       JOIN $table_alumni a ON c.alumni_id = a.id
       ORDER BY a.rut ASC, c.created_at ASC",
      ARRAY_A
    );

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=anuario_comentarios.csv');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['rut', 'comentario']);
    foreach ($rows as $r) {
      fputcsv($out, [$r['rut'], $r['comentario']]);
    }
    fclose($out);
    exit;
  }

  $table = $wpdb->prefix . 'anuario_alumni';
  $rows = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=anuario_alumni.csv');

  $out = fopen('php://output', 'w');
  fputcsv($out, ['nombre','rut','fecha_nacimiento','cargo_actual','empresa','perfil_linkedin','nivel_cargo','ano_egreso','link_foto','autorizacion_publica']);

  foreach ($rows as $r) {
    fputcsv($out, [
      $r['nombre'],
      $r['rut'],
      $r['fecha_nacimiento'],
      $r['cargo_actual'],
      $r['empresa'],
      $r['perfil_linkedin'],
      $r['nivel_cargo'],
      $r['ano_egreso'],
      $r['link_foto'],
      $r['autorizacion_publica'],
    ]);
  }
  fclose($out);
  exit;
});
