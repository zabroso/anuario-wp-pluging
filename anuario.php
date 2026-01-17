<?php
/*
Plugin Name: Anuario Alumni
Description: Gestión de alumni para el anuario institucional
Version: 1.0
Author: Alumni
*/

if (!defined('ABSPATH')) exit;

/* =========================================================
   ACTIVACIÓN – CREAR TABLA
========================================================= */

register_activation_hook(__FILE__, 'anuario_create_table');
function anuario_create_table() {
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

/* =========================================================
   MENÚ ADMIN
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
      $wpdb->prepare(
        "SELECT * FROM $table WHERE nombre LIKE %s ORDER BY created_at DESC",
        '%' . $wpdb->esc_like($search) . '%'
      )
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
          <th>Cargo actual</th>
          <th>Año egreso</th>
          <th>Nivel éxito</th>
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

  $success = false;

  if (isset($_POST['confirm_save'])) {
    check_admin_referer('save_alumni');

    $payload = [
      'nombre' => sanitize_text_field($_POST['nombre']),
      'cargo_actual' => sanitize_text_field($_POST['cargo_actual']),
      'perfil_linkedin' => esc_url_raw($_POST['perfil_linkedin']),
      'nivel_cargo' => intval($_POST['nivel_cargo']),
      'ano_egreso' => intval($_POST['ano_egreso']) ?: null,
      'link_foto' => esc_url_raw($_POST['link_foto']),
    ];

    if ($id) {
      $wpdb->update($table, $payload, ['id' => $id], null, ['%d']);
    } else {
      $wpdb->insert($table, $payload);
      $id = $wpdb->insert_id;
    }

    $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id));
    $success = true;
  }
  ?>
  <div class="wrap">
    <h1><?php echo $id ? 'Editar Alumni' : 'Nuevo Alumni'; ?></h1>

    <?php if ($success): ?>
      <div class="notice notice-success"><p>Guardado con éxito.</p></div>
    <?php endif; ?>

    <form method="post">
      <?php wp_nonce_field('save_alumni'); ?>

      <table class="form-table">
        <tr>
          <th>Nombre</th>
          <td><input type="text" name="nombre" required value="<?php echo esc_attr($data->nombre ?? ''); ?>"></td>
        </tr>

        <tr>
          <th>Cargo actual</th>
          <td><input type="text" name="cargo_actual" value="<?php echo esc_attr($data->cargo_actual ?? ''); ?>"></td>
        </tr>

        <tr>
          <th>LinkedIn</th>
          <td>
            <input type="url"
                   name="perfil_linkedin"
                   value="<?php echo esc_attr($data->perfil_linkedin ?? ''); ?>"
                   placeholder="https://www.linkedin.com/in/usuario"
                   style="width:400px">
          </td>
        </tr>

        <tr>
          <th>Nivel de éxito</th>
          <td><input type="number" name="nivel_cargo" min="1" value="<?php echo esc_attr($data->nivel_cargo ?? ''); ?>"></td>
        </tr>

        <tr>
          <th>Año egreso</th>
          <td><input type="number" name="ano_egreso" value="<?php echo esc_attr($data->ano_egreso ?? ''); ?>"></td>
        </tr>

        <tr>
          <th>Link foto</th>
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
  <?php
}

/* =========================================================
   ELIMINAR
========================================================= */

add_action('admin_init', function () {
  if (
    isset($_GET['delete'], $_GET['_wpnonce']) &&
    wp_verify_nonce($_GET['_wpnonce'], 'delete_alumni')
  ) {
    global $wpdb;
    $wpdb->delete(
      $wpdb->prefix . 'anuario_alumni',
      ['id' => intval($_GET['delete'])],
      ['%d']
    );
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

/* =========================================================
   EXPORT CSV
========================================================= */

add_action('admin_init', function () {
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
    'perfil_linkedin',
    'nivel_cargo',
    'ano_egreso',
    'link_foto'
  ]);

  foreach ($rows as $r) {
    fputcsv($output, [
      $r['nombre'],
      $r['cargo_actual'],
      $r['perfil_linkedin'],
      $r['nivel_cargo'],
      $r['ano_egreso'],
      $r['link_foto']
    ]);
  }

  fclose($output);
  exit;
});

/* =========================================================
   CARGA MASIVA CSV
========================================================= */

function anuario_render_bulk() {
  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';
  $message = '';

  if (isset($_POST['bulk_import'])) {
    check_admin_referer('anuario_bulk');

    if (empty($_FILES['file']['tmp_name'])) {
      $message = 'No se seleccionó ningún archivo.';
    } else {

      $mode = $_POST['bulk_mode'] ?? 'append';

      if ($mode === 'replace') {
        if (
          empty($_POST['confirm_replace_1']) ||
          empty($_POST['confirm_replace_2']) ||
          empty($_POST['confirm_replace_3'])
        ) {
          $message = 'Debes confirmar todas las casillas.';
          goto render;
        }
        $wpdb->query("TRUNCATE TABLE $table");
      }

      $file = fopen($_FILES['file']['tmp_name'], 'r');
      $headers = fgetcsv($file);

      $expected = [
        'nombre',
        'cargo_actual',
        'perfil_linkedin',
        'nivel_cargo',
        'ano_egreso',
        'link_foto'
      ];

      if ($headers !== $expected) {
        $message = 'Formato CSV incorrecto.';
        fclose($file);
        goto render;
      }

      while (($row = fgetcsv($file)) !== false) {
        $wpdb->insert($table, [
          'nombre' => sanitize_text_field($row[0]),
          'cargo_actual' => sanitize_text_field($row[1]),
          'perfil_linkedin' => esc_url_raw($row[2]),
          'nivel_cargo' => intval($row[3]),
          'ano_egreso' => intval($row[4]) ?: null,
          'link_foto' => esc_url_raw($row[5]),
        ]);
      }

      fclose($file);
      $message = 'Carga masiva realizada con éxito.';
    }
  }

  render:
  ?>
  <div class="wrap">
    <h1>Carga masiva de Alumni</h1>

    <?php if ($message): ?>
      <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <?php wp_nonce_field('anuario_bulk'); ?>

      <table class="form-table">
        <tr>
          <th>Archivo CSV</th>
          <td><input type="file" name="file" accept=".csv" required></td>
        </tr>

        <tr>
          <th>Modo</th>
          <td>
            <label><input type="radio" name="bulk_mode" value="append" checked> Añadir</label><br>
            <label><input type="radio" name="bulk_mode" value="replace"> Reemplazar</label>
          </td>
        </tr>
      </table>

      <div id="replace-warning" style="display:none">
        <label><input type="checkbox" name="confirm_replace_1"> Entiendo</label><br>
        <label><input type="checkbox" name="confirm_replace_2"> Tengo respaldo</label><br>
        <label><input type="checkbox" name="confirm_replace_3"> Confirmo</label>
      </div>

      <p>
        <button class="button button-primary" name="bulk_import">Ejecutar</button>
        <a href="<?php echo admin_url('admin.php?page=anuario-alumni'); ?>" class="button">Volver</a>
      </p>
    </form>
  </div>

  <script>
  document.querySelectorAll('input[name="bulk_mode"]').forEach(el => {
    el.addEventListener('change', () => {
      document.getElementById('replace-warning').style.display =
        el.value === 'replace' ? 'block' : 'none';
    });
  });
  </script>
  <?php
}
