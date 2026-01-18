<?php
/*
Plugin Name: Anuario Alumni
Description: Gestión de alumni para el anuario institucional
Version: 1.0
Author: Alumni
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
    nombre VARCHAR(255) NOT NULL,
    cargo_actual VARCHAR(255) NULL,
    empresa VARCHAR(255) NULL,
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
    'Carga masiva',
    'Carga masiva',
    'manage_options',
    'anuario-alumni-bulk',
    'anuario_render_bulk'
  );
});

/* =========================================================
   LISTADO
========================================================= */

function anuario_render_list() {
  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';

  $per_page = 20;
  $paged = max(1, intval($_GET['paged'] ?? 1));
  $offset = ($paged - 1) * $per_page;

  $search = sanitize_text_field($_GET['s'] ?? '');
  $year   = intval($_GET['year'] ?? 0);
  $level  = intval($_GET['level'] ?? 0);

  $where = "WHERE 1=1";
  if ($search) {
    $where .= $wpdb->prepare(
      " AND (nombre LIKE %s OR cargo_actual LIKE %s)",
      "%$search%", "%$search%"
    );
  }
  if ($year) {
    $where .= $wpdb->prepare(" AND ano_egreso = %d", $year);
  }
  if ($level) {
    $where .= $wpdb->prepare(" AND nivel_cargo = %d", $level);
  }

  $total = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
  $alumni = $wpdb->get_results(
    "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset"
  );
  $pages = ceil($total / $per_page);
  ?>

  <div class="wrap">
    <h1 class="wp-heading-inline">Alumni</h1>

    <a href="<?php echo admin_url('admin.php?page=anuario-alumni-edit'); ?>" class="page-title-action">
      Crear Alumni
    </a>

    <button id="bulk-delete-btn" class="button button-danger" style="margin-left:10px;color:#b91c1c">
      Eliminar seleccionados
    </button>

    <form method="get" style="margin-top:15px;">
      <input type="hidden" name="page" value="anuario-alumni">

      <input type="search" name="s" placeholder="Buscar nombre o cargo" value="<?php echo esc_attr($search); ?>">

      <select name="year">
        <option value="">Año</option>
        <?php for ($y = date('Y'); $y >= 1980; $y--): ?>
          <option value="<?php echo $y; ?>" <?php selected($year, $y); ?>><?php echo $y; ?></option>
        <?php endfor; ?>
      </select>

      <select name="level">
        <option value="">Nivel</option>
        <?php for ($i=1; $i<=10; $i++): ?>
          <option value="<?php echo $i; ?>" <?php selected($level, $i); ?>><?php echo $i; ?></option>
        <?php endfor; ?>
      </select>

      <button class="button">Filtrar</button>
    </form>

    <form method="post" id="bulk-delete-form">
      <?php wp_nonce_field('bulk_delete_alumni'); ?>

      <table class="wp-list-table widefat striped" style="margin-top:20px;">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all"></th>
            <th>Nombre</th>
            <th>Cargo</th>
            <th>Empresa</th>
            <th>Año</th>
            <th>Nivel</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($alumni): foreach ($alumni as $a): ?>
            <tr>
              <td><input type="checkbox" name="ids[]" value="<?php echo $a->id; ?>"></td>
              <td><?php echo esc_html($a->nombre); ?></td>
              <td><?php echo esc_html($a->cargo_actual ?? 'None'); ?></td>
              <td><?php echo esc_html($a->empresa ?? 'None'); ?></td>
              <td><?php echo esc_html($a->ano_egreso ?? 'None'); ?></td>
              <td><?php echo esc_html($a->nivel_cargo ?? 'None'); ?></td>
              <td>
                <a href="<?php echo admin_url('admin.php?page=anuario-alumni-edit&id='.$a->id); ?>">Editar</a>
                |
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=anuario-alumni&delete=' . $a->id), 'delete_alumni'); ?>"
                   onclick="return confirm('¿Eliminar este alumni?')">Eliminar</a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="7">No hay resultados</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </form>

    <?php if ($pages > 1): ?>
      <div class="tablenav-pages">
        <?php for ($p=1; $p<=$pages; $p++): ?>
          <a class="button <?php echo $p==$paged?'button-primary':''; ?>"
             href="<?php echo add_query_arg('paged', $p); ?>">
            <?php echo $p; ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>

  <script>
  document.getElementById('select-all').addEventListener('change', e => {
    document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = e.target.checked);
  });

  document.getElementById('bulk-delete-btn').addEventListener('click', () => {
    const selected = Array.from(document.querySelectorAll('input[name="ids[]"]:checked'));
    if (!selected.length) {
      alert('Debes seleccionar alumni para eliminar');
      return;
    }

    if (!confirm('¿Eliminar los alumni seleccionados?')) return;

    const form = document.getElementById('bulk-delete-form');
    form.action = "<?php echo admin_url('admin.php?page=anuario-alumni'); ?>";
    form.submit();
  });
  </script>
  <?php
}


/* =========================================================
   FORMULARIO CREAR / EDITAR
========================================================= */

function anuario_render_form($alumni = null) {
  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';

  $is_edit = isset($_GET['id']) && intval($_GET['id']) > 0;
  $id = $is_edit ? intval($_GET['id']) : 0;

  if ($is_edit && !$alumni) {
    $alumni = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table WHERE id = %d",
      $id
    ));
  }

  /* =========================
     GUARDAR (CREAR / EDITAR)
  ========================= */
  $notice = '';
  if (isset($_POST['confirm_submit'])) {
    check_admin_referer('anuario_save_alumni');

    $payload = [
      'nombre' => sanitize_text_field($_POST['nombre']),
      'cargo_actual'    => $_POST['has_cargo']    ? sanitize_text_field($_POST['cargo_actual'])    : 'None',
      'empresa'         => $_POST['has_empresa']  ? sanitize_text_field($_POST['empresa'])         : 'None',
      'perfil_linkedin' => $_POST['has_linkedin'] ? esc_url_raw($_POST['perfil_linkedin'])          : 'None',
      'nivel_cargo'     => $_POST['has_nivel']    ? intval($_POST['nivel_cargo'])                  : null,
      'ano_egreso'      => $_POST['has_ano']      ? intval($_POST['ano_egreso'])                    : null,
      'link_foto'       => $_POST['has_foto']     ? esc_url_raw($_POST['link_foto'])                : 'None',
    ];

    if ($is_edit) {
      $wpdb->update($table, $payload, ['id' => $id]);
      $notice = 'Alumni actualizado correctamente.';
    } else {
      $wpdb->insert($table, $payload);
      $id = $wpdb->insert_id;
      $is_edit = true;
      $notice = 'Alumni creado correctamente.';
    }

    $alumni = $wpdb->get_row($wpdb->prepare(
      "SELECT * FROM $table WHERE id = %d",
      $id
    ));
  }

  $nombre   = $alumni->nombre ?? '';
  $cargo    = $alumni->cargo_actual ?? '';
  $empresa  = $alumni->empresa ?? '';
  $linkedin = $alumni->perfil_linkedin ?? '';
  $nivel    = $alumni->nivel_cargo ?? '';
  $ano      = $alumni->ano_egreso ?? '';
  $foto     = $alumni->link_foto ?? '';
  ?>

  <div class="wrap">
    <h1><?php echo $is_edit ? 'Editar Alumni' : 'Crear Alumni'; ?></h1>

    <a href="<?php echo esc_url(admin_url('admin.php?page=anuario-alumni')); ?>"
       class="button button-secondary" style="margin-bottom:15px;">
      ← Volver
    </a>

    <?php if ($notice): ?>
      <div class="notice notice-success">
        <p><?php echo esc_html($notice); ?></p>
      </div>
    <?php endif; ?>

    <form method="post">
      <?php wp_nonce_field('anuario_save_alumni'); ?>

      <table class="form-table">
        <tr>
          <th>Nombre *</th>
          <td>
            <input type="text" name="nombre" required
                   value="<?php echo esc_attr($nombre); ?>"
                   placeholder="Ej: Juan Pérez"
                   class="regular-text">
          </td>
        </tr>

        <?php
        $fields = [
          'cargo'    => ['Cargo actual', 'cargo_actual', $cargo, 'Ej: Ingeniero'],
          'empresa'  => ['Empresa', 'empresa', $empresa, 'Ej: Google'],
          'linkedin' => ['LinkedIn', 'perfil_linkedin', $linkedin, 'https://linkedin.com/in/usuario'],
          'foto'     => ['Link foto', 'link_foto', $foto, 'https://sitio.cl/foto.jpg'],
        ];

        foreach ($fields as $key => [$label, $name, $value, $ph]): ?>
        <tr>
          <th><?php echo $label; ?></th>
          <td>
            <label>
              <input type="checkbox" name="has_<?php echo $key; ?>"
                     data-target="<?php echo $name; ?>"
                     <?php checked($value && $value !== 'None'); ?>>
              Posee?
            </label><br>
            <input type="<?php echo $key === 'linkedin' || $key === 'foto' ? 'url' : 'text'; ?>"
                   id="<?php echo $name; ?>"
                   name="<?php echo $name; ?>"
                   value="<?php echo esc_attr($value !== 'None' ? $value : ''); ?>"
                   placeholder="<?php echo esc_attr($ph); ?>"
                   class="regular-text">
          </td>
        </tr>
        <?php endforeach; ?>

        <tr>
          <th>Nivel de cargo</th>
          <td>
            <label>
              <input type="checkbox" name="has_nivel" data-target="nivel_cargo"
                     <?php checked(!empty($nivel)); ?>>
              Posee?
            </label><br>
            <select id="nivel_cargo" name="nivel_cargo">
              <option value="">Seleccionar nivel</option>
              <?php for ($i = 1; $i <= 10; $i++): ?>
                <option value="<?php echo $i; ?>" <?php selected($nivel, $i); ?>>
                  Nivel <?php echo $i; ?>
                </option>
              <?php endfor; ?>
            </select>
          </td>
        </tr>

        <tr>
          <th>Año de egreso</th>
          <td>
            <label>
              <input type="checkbox" name="has_ano" data-target="ano_egreso"
                     <?php checked(!empty($ano)); ?>>
              Posee?
            </label><br>
            <select id="ano_egreso" name="ano_egreso">
              <option value="">Seleccionar año</option>
              <?php for ($y = date('Y'); $y >= 1980; $y--): ?>
                <option value="<?php echo $y; ?>" <?php selected($ano, $y); ?>>
                  <?php echo $y; ?>
                </option>
              <?php endfor; ?>
            </select>
          </td>
        </tr>

        <tr>
          <th>Confirmación</th>
          <td>
            <label>
              <input type="checkbox" name="confirm_submit" required>
              Confirmo <?php echo $is_edit ? 'la edición' : 'la creación'; ?> del alumni
            </label>
          </td>
        </tr>
      </table>

      <?php submit_button($is_edit ? 'Actualizar Alumni' : 'Crear Alumni'); ?>
    </form>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="checkbox"][data-target]').forEach(function (cb) {
      const field = document.getElementById(cb.dataset.target);
      if (!field) return;

      field.disabled = !cb.checked;

      cb.addEventListener('change', function () {
        field.disabled = !cb.checked;
        if (!cb.checked) field.value = '';
      });
    });
  });
  </script>
  <?php
}





/* =========================================================
   ELIMINAR
========================================================= */

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
});


/* =========================================================
   EXPORTAR CSV
========================================================= */

add_action('admin_init', function () {
  if (!isset($_GET['anuario_export'])) return;
  if (!current_user_can('manage_options')) return;

  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';
  $rows = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=anuario_alumni.csv');

  $out = fopen('php://output', 'w');
  fputcsv($out, ['nombre','cargo_actual','empresa','perfil_linkedin','nivel_cargo','ano_egreso','link_foto']);

  foreach ($rows as $r) {
    fputcsv($out, [
      $r['nombre'],
      $r['cargo_actual'],
      $r['empresa'],
      $r['perfil_linkedin'],
      $r['nivel_cargo'],
      $r['ano_egreso'],
      $r['link_foto'],
    ]);
  }
  fclose($out);
  exit;
});

/* =========================================================
   CARGA MASIVA
========================================================= */

function anuario_render_bulk() {
  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';
  $message = '';

  if (isset($_POST['bulk_import'])) {
    check_admin_referer('anuario_bulk');

    if ($_POST['bulk_mode'] === 'replace') {
      if (
        empty($_POST['confirm_replace_1']) ||
        empty($_POST['confirm_replace_2']) ||
        empty($_POST['confirm_replace_3'])
      ) {
        $message = 'Debes confirmar todas las casillas.';
      } else {
        $wpdb->query("TRUNCATE TABLE $table");
      }
    }

    $file = fopen($_FILES['file']['tmp_name'], 'r');
    $headers = fgetcsv($file);
    $expected = ['nombre','cargo_actual','empresa','perfil_linkedin','nivel_cargo','ano_egreso','link_foto'];

    if ($headers !== $expected) {
      $message = 'Formato CSV incorrecto.';
    } else {
      while (($row = fgetcsv($file)) !== false) {
        $wpdb->insert($table, [
          'nombre' => sanitize_text_field($row[0]),
          'cargo_actual' => sanitize_text_field($row[1]),
          'empresa' => sanitize_text_field($row[2]),
          'perfil_linkedin' => esc_url_raw($row[3]),
          'nivel_cargo' => intval($row[4]),
          'ano_egreso' => intval($row[5]),
          'link_foto' => esc_url_raw($row[6]),
        ]);
      }
      $message = 'Carga masiva realizada con éxito.';
    }
    fclose($file);
  }
  ?>
  <div class="wrap">
    <h1>Carga masiva de Alumni</h1>

    <p>
      <a href="<?php echo admin_url('admin.php?page=anuario-alumni&anuario_export=1'); ?>" class="button">
        Descargar respaldo CSV
      </a>
    </p>

    <?php if ($message): ?>
      <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <?php wp_nonce_field('anuario_bulk'); ?>
      <table class="form-table">
        <tr><th>Archivo CSV</th><td><input type="file" name="file" accept=".csv" required></td></tr>
        <tr>
          <th>Modo</th>
          <td>
            <label><input type="radio" name="bulk_mode" value="append" checked> Añadir</label><br>
            <label><input type="radio" name="bulk_mode" value="replace"> Reemplazar</label>
          </td>
        </tr>
      </table>

      <div id="replace-warning" style="display:none">
        <label><input type="checkbox" name="confirm_replace_1"> Entiendo que se eliminarán los datos</label><br>
        <label><input type="checkbox" name="confirm_replace_2"> He descargado un respaldo</label><br>
        <label><input type="checkbox" name="confirm_replace_3"> Confirmo continuar</label>
      </div>

      <p>
        <button class="button button-primary" name="bulk_import">Ejecutar carga</button>
        <a href="<?php echo admin_url('admin.php?page=anuario-alumni'); ?>" class="button">Volver</a>
      </p>
    </form>
  </div>

  <script>
  document.querySelectorAll('input[name="bulk_mode"]').forEach(r => {
    r.addEventListener('change', () => {
      document.getElementById('replace-warning').style.display =
        r.value === 'replace' ? 'block' : 'none';
    });
  });
  </script>
  <?php
}

/* =========================================================
   API REST - Alumni
========================================================= */
add_action('rest_api_init', function () {
    register_rest_route('anuario/v1', '/alumni', [
        'methods' => 'GET',
        'callback' => function ($request) {
            global $wpdb;
            $table = $wpdb->prefix . 'anuario_alumni';
            
            $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);

            // Convertimos campos vacíos a "None", excepto el nombre
            foreach ($results as &$alumni) {
                $alumni['nombre'] = $alumni['nombre'] ?? ''; // nombre obligatorio
                $alumni['cargo_actual'] = $alumni['cargo_actual'] ?? 'None';
                $alumni['empresa'] = $alumni['empresa'] ?? 'None';
                $alumni['perfil_linkedin'] = $alumni['perfil_linkedin'] ?? 'None';
                $alumni['nivel_cargo'] = $alumni['nivel_cargo'] ?? 'None';
                $alumni['ano_egreso'] = $alumni['ano_egreso'] ?? 'None';
                $alumni['link_foto'] = $alumni['link_foto'] ?? 'None';
            }

            return $results;
        },
        'permission_callback' => '__return_true', // público
    ]);
});
