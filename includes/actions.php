<?php
if (!defined('ABSPATH')) exit;

add_action('admin_init', function () {
  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';

  // Eliminación individual vía GET
  if (isset($_GET['delete']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_alumni')) {
    $wpdb->delete($table, ['id' => intval($_GET['delete'])]);
    wp_redirect(admin_url('admin.php?page=anuario-alumni'));
    exit;
  }

  // Eliminación masiva vía POST
  if (isset($_POST['ids']) && is_array($_POST['ids']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'bulk_delete_alumni')) {
    $ids = array_map('intval', $_POST['ids']);
    if (!empty($ids)) {
      $ids_placeholder = implode(',', array_fill(0, count($ids), '%d'));
      $wpdb->query(
        $wpdb->prepare("DELETE FROM $table WHERE id IN ($ids_placeholder)", ...$ids)
      );
    }
    wp_redirect(admin_url('admin.php?page=anuario-alumni'));
    exit;
  }

  // Eliminación de comentario vía GET
  if (isset($_GET['delete_comment']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_comment')) {
    $table_comments = $wpdb->prefix . 'anuario_comentarios';
    $wpdb->delete($table_comments, ['id' => intval($_GET['delete_comment'])]);
    wp_redirect(admin_url('admin.php?page=anuario-alumni-comments'));
    exit;
  }

  // Eliminación individual de programa vía GET
  if (isset($_GET['delete_program']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_program')) {
    $table_programs = $wpdb->prefix . 'anuario_programas';
    $wpdb->delete($table_programs, ['id' => intval($_GET['delete_program'])]);
    wp_redirect(admin_url('admin.php?page=anuario-alumni-programs'));
    exit;
  }

  // Eliminación masiva de programas vía POST
  if (isset($_POST['program_ids']) && is_array($_POST['program_ids']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'bulk_delete_programs')) {
    $table_programs = $wpdb->prefix . 'anuario_programas';
    $ids = array_map('intval', $_POST['program_ids']);
    if (!empty($ids)) {
      $placeholders = implode(',', array_fill(0, count($ids), '%d'));
      $wpdb->query(
        $wpdb->prepare("DELETE FROM $table_programs WHERE id IN ($placeholders)", ...$ids)
      );
    }
    wp_redirect(admin_url('admin.php?page=anuario-alumni-programs'));
    exit;
  }

  // Eliminación masiva de comentarios vía POST
  if (isset($_POST['comment_ids']) && is_array($_POST['comment_ids']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'bulk_delete_comments')) {
    $table_comments = $wpdb->prefix . 'anuario_comentarios';
    $ids = array_map('intval', $_POST['comment_ids']);
    if (!empty($ids)) {
      $placeholders = implode(',', array_fill(0, count($ids), '%d'));
      $wpdb->query(
        $wpdb->prepare("DELETE FROM $table_comments WHERE id IN ($placeholders)", ...$ids)
      );
    }
    wp_redirect(admin_url('admin.php?page=anuario-alumni-comments'));
    exit;
  }
});
