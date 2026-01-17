<?php
if (!defined('ABSPATH')) exit;

add_action('admin_init', 'anuario_download_csv');

function anuario_download_csv() {
  if (!isset($_GET['anuario_export'])) return;
  if (!current_user_can('manage_options')) return;

  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';
  $rows = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=anuario_backup.csv');

  $output = fopen('php://output', 'w');

  fputcsv($output, [
    'nombre',
    'cargo_actual',
    'linkedin',
    'nivel_cargo',
    'ano_egreso',
    'link_foto'
  ]);

  foreach ($rows as $r) {
    $socials = json_decode($r['perfil_linkedin'] ?? '{}', true);

    fputcsv($output, [
      $r['nombre'],
      $r['cargo_actual'],
      $socials['linkedin'] ?? '',
      $r['nivel_cargo'],
      $r['ano_egreso'],
      $r['link_foto']
    ]);
  }

  fclose($output);
  exit;
}
add_action('admin_init', 'anuario_download_csv');
