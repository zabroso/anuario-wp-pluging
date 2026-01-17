<?php
if (!defined('ABSPATH')) exit;

function anuario_render_list() {
  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';
  $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

  if ($search) {
    $alumni = $wpdb->get_results(
      $wpdb->prepare("SELECT * FROM $table WHERE nombre LIKE %s ORDER BY created_at DESC", "%$search%")
    );
  } else {
    $alumni = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
  }
  ?>

  <div class="wrap">
    <h1 class="wp-heading-inline">Alumni</h1>
    <a href="<?php echo admin_url('admin.php?page=anuario-alumni-edit'); ?>" class="page-title-action">
      Agregar nuevo
    </a>

    <form method="get" style="margin-top:15px;">
      <input type="hidden" name="page" value="anuario-alumni">
      <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Buscar por nombre">
      <button class="button">Buscar</button>
    </form>

    <table class="wp-list-table widefat striped" style="margin-top:20px;">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Cargo</th>
          <th>Año egreso</th>
          <th>Éxito</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($alumni): foreach ($alumni as $a): ?>
        <tr>
          <td><?php echo esc_html($a->nombre); ?></td>
          <td><?php echo esc_html($a->cargo_actual); ?></td>
          <td><?php echo esc_html($a->ano_egreso); ?></td>
          <td><?php echo esc_html($a->nivel_cargo); ?></td>
          <td>
            <a href="<?php echo admin_url('admin.php?page=anuario-alumni-edit&id=' . $a->id); ?>">Editar</a> |
            <a href="<?php echo wp_nonce_url(
              admin_url('admin.php?page=anuario-alumni&delete=' . $a->id),
              'delete_alumni'
            ); ?>" onclick="return confirm('¿Eliminar este alumni?')">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="5">No hay registros</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php
}
