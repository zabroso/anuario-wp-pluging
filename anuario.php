<?php
/*
Plugin Name: Anuario
Description: CRUD de Alumni con API REST
Version: 1.2
*/

if (!defined('ABSPATH')) exit;

/* =========================================================
   ACTIVACIÓN — TABLA
========================================================= */

register_activation_hook(__FILE__, 'anuario_create_table');
function anuario_create_table() {
  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';
  $charset = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(255) NOTNULL,
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

/* =========================================================
   MENÚ
========================================================= */

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

/* =========================================================
   LISTADO + BÚSQUEDA
========================================================= */

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

/* =========================================================
   FORMULARIO CREAR / EDITAR
========================================================= */

function anuario_render_form() {
  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

  $data = $id
    ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id))
    : null;

  $socials = $data ? json_decode($data->perfil_linkedin ?? '{}', true) : [];
  $success = false;

  if (isset($_POST['confirm_save'])) {
    check_admin_referer('save_alumni');

    $redes = [];
    if (isset($_POST['has_linkedin']) && $_POST['has_linkedin'] === 'on') {
      if (!empty($_POST['linkedin_url'])) {
        $redes['linkedin'] = esc_url_raw($_POST['linkedin_url']);
      }
    }

    $payload = [
      'nombre' => sanitize_text_field($_POST['nombre'] ?? ''),
      'cargo_actual' => sanitize_text_field($_POST['cargo_actual'] ?? ''),
      'nivel_cargo' => intval($_POST['nivel_cargo'] ?? 0),
      'ano_egreso' => intval($_POST['ano_egreso'] ?? 0),
      'link_foto' => esc_url_raw($_POST['link_foto'] ?? ''),
      'perfil_linkedin' => json_encode($redes),
    ];

    if ($id) {
      $wpdb->update($table, $payload, ['id' => $id]);
    } else {
      $wpdb->insert($table, $payload);
      $id = $wpdb->insert_id;
    }

    $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id));
    $socials = json_decode($data->perfil_linkedin ?? '{}', true);
    $success = true;
  }
  ?>

  <div class="wrap">
    <h1><?php echo $id ? 'Editar Alumni' : 'Nuevo Alumni'; ?></h1>

    <?php if ($success): ?>
      <div class="notice notice-success">
        <p>Guardado con éxito.</p>
      </div>
    <?php endif; ?>

    <form method="post">
      <?php wp_nonce_field('save_alumni'); ?>

      <table class="form-table">
        <tr>
          <th>Nombre</th>
          <td><input type="text" name="nombre" value="<?php echo esc_attr($data->nombre ?? ''); ?>"></td>
        </tr>

        <tr>
          <th>Cargo</th>
          <td><input type="text" name="cargo_actual" value="<?php echo esc_attr($data->cargo_actual ?? ''); ?>"></td>
        </tr>
        <tr>
            <th>LinkedIn</th>
            <td>
                          <label>
                            <input type="checkbox" id="linkedin_enabled" <?php checked($linkedin_enabled); ?> />
                            Posee LinkedIn
                          </label>
            
                          <br><br>
            
                          <input
                            type="url"
                            name="linkedin_url"
                            id="linkedin_url"
                            placeholder="https://www.linkedin.com/in/usuario"
                            value="<?php echo esc_attr($linkedin_url); ?>"
                            style="width:400px"
                            <?php echo $linkedin_enabled ? '' : 'disabled'; ?>
                          />
                        </td>
        </tr>

        <tr>
          <th>Nivel éxito</th>
          <td><input type="number" name="nivel_cargo" min="1" value="<?php echo esc_attr($data->nivel_cargo ?? ''); ?>"></td>
        </tr>

        <tr>
          <th>Año egreso</th>
          <td><input type="number" name="ano_egreso" value="<?php echo esc_attr($data->ano_egreso ?? ''); ?>"></td>
        </tr>

        <tr>
          <th>link_Foto (URL)</th>
          <td><input type="url" name="link_foto" value="<?php echo esc_attr($data->link_foto ?? ''); ?>"></td>
        </tr>
      </table>

      <p>
        <label>
          <input type="checkbox" name="confirm_save" required>
          Confirmo que deseo guardar los cambios
        </label>
      </p>

      <p>
        <button class="button button-primary">Guardar</button>
        <a href="<?php echo admin_url('admin.php?page=anuario-alumni'); ?>" class="button">Volver</a>
      </p>
    </form>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.getElementById('linkedin_enabled');
    const input = document.getElementById('linkedin_url');

    checkbox.addEventListener('change', function () {
      input.disabled = !this.checked;
      if (!this.checked) input.value = '';
    });
  });
  </script>
  <?php
}

  

/* =========================================================
   ELIMINAR
========================================================= */

add_action('admin_init', function () {
  if (isset($_GET['delete']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_alumni')) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'anuario_alumni', ['id' => intval($_GET['delete'])]);
    wp_redirect(admin_url('admin.php?page=anuario-alumni'));
    exit;
  }
});

/* =========================================================
   API REST
========================================================= */

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

function anuario_render_bulk() {
  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';

  $message = '';

  if (isset($_POST['bulk_import'])) {
  check_admin_referer('anuario_bulk');

  if (empty($_FILES['file']['tmp_name'])) {
    $message = 'No se seleccionó ningún archivo.';
    return;
  }

  $mode = $_POST['bulk_mode'] ?? 'append';

  if ($mode === 'replace') {
    if (
      empty($_POST['confirm_replace_1']) ||
      empty($_POST['confirm_replace_2']) ||
      empty($_POST['confirm_replace_3'])
    ) {
      $message = 'Debes confirmar todas las casillas para reescribir la base.';
      return;
    }

    $wpdb->query("TRUNCATE TABLE $table");
  }

  $file = fopen($_FILES['file']['tmp_name'], 'r');
  $headers = fgetcsv($file);

  $expected = ['nombre','cargo_actual','linkedin','nivel_cargo','ano_egreso','link_foto'];
  if ($headers !== $expected) {
    fclose($file);
    $message = 'Formato de archivo incorrecto.';
    return;
  }

  while (($row = fgetcsv($file)) !== false) {
    $redes = [];

    if (!empty($row[2])) {
      $redes['linkedin'] = esc_url_raw($row[2]);
    }

    $wpdb->insert($table, [
      'nombre' => sanitize_text_field($row[0]),
      'cargo_actual' => sanitize_text_field($row[1]),
      'perfil_linkedin' => json_encode($redes),
      'nivel_cargo' => intval($row[3]),
      'ano_egreso' => intval($row[4]),
      'link_foto' => esc_url_raw($row[5]),
    ]);
  }

  fclose($file);
  $message = 'Carga masiva realizada con éxito.';
}

  ?>

  <div class="wrap">
    <h1>Carga masiva de Alumni</h1>

    <p>
      Recomendamos <strong>descargar un respaldo</strong> antes de realizar una carga masiva.
    </p>

    <p>
      <a href="<?php echo admin_url('admin.php?page=anuario-alumni&anuario_export=1'); ?>"
         class="button button-secondary">
        Descargar respaldo (CSV)
      </a>
    </p>

    <hr>

    <?php if ($message): ?>
      <div class="notice notice-success">
        <p><?php echo esc_html($message); ?></p>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
  <?php wp_nonce_field('anuario_bulk'); ?>

  <table class="form-table">
    <tr>
      <th>Archivo CSV</th>
      <td>
        <input type="file" name="file" accept=".csv" required>
      </td>
    </tr>

    <tr>
      <th>Modo de carga</th>
      <td>
        <label>
          <input type="radio" name="bulk_mode" value="append" checked>
          Añadir datos (no elimina información existente)
        </label><br>

        <label>
          <input type="radio" name="bulk_mode" value="replace">
          Reescribir toda la base de datos
        </label>
      </td>
    </tr>
  </table>

  <div id="replace-warning" style="display:none; margin-top:15px;">
    <p style="color:#b91c1c;">
      Esta acción eliminará todos los alumni actuales.
      Recomendamos descargar un respaldo antes de continuar.
    </p>

    <label><input type="checkbox" name="confirm_replace_1"> Entiendo que se eliminarán los datos</label><br>
    <label><input type="checkbox" name="confirm_replace_2"> He descargado un respaldo</label><br>
    <label><input type="checkbox" name="confirm_replace_3"> Confirmo que deseo continuar</label>
  </div>

  <p style="margin-top:20px;">
    <button class="button button-primary" name="bulk_import">
      Ejecutar carga
    </button>
    <a href="<?php echo admin_url('admin.php?page=anuario-alumni'); ?>" class="button">
      Volver
    </a>
  </p>
</form>

<script>
document.querySelectorAll('input[name="bulk_mode"]').forEach(el => {
  el.addEventListener('change', () => {
    document.getElementById('replace-warning').style.display =
      el.value === 'replace' ? 'block' : 'none';
  });
});
</script>

  </div>
  <?php
}
