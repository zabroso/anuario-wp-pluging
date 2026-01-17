<?php
if (!defined('ABSPATH'))
  exit;

function anuario_render_bulk()
{
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

    $expected = ['nombre', 'cargo_actual', 'linkedin', 'nivel_cargo', 'ano_egreso', 'link_foto'];
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

