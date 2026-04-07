<?php
if (!defined('ABSPATH')) exit;

function anuario_render_bulk() {
  global $wpdb;
  $table_alumni    = $wpdb->prefix . 'anuario_alumni';
  $table_comments  = $wpdb->prefix . 'anuario_comentarios';
  $table_programs  = $wpdb->prefix . 'anuario_programas';

  $message_alumni   = '';
  $message_comments = '';
  $message_programs = '';

  /* =========================================================
     CARGA MASIVA — ALUMNI
  ========================================================= */
  if (isset($_POST['bulk_import_alumni'])) {
    check_admin_referer('anuario_bulk_alumni');

    if ($_POST['bulk_mode'] === 'replace') {
      if (
        empty($_POST['confirm_replace_1']) ||
        empty($_POST['confirm_replace_2']) ||
        empty($_POST['confirm_replace_3'])
      ) {
        $message_alumni = ['error', 'Debes confirmar todas las casillas para reemplazar.'];
      } else {
        $wpdb->query("TRUNCATE TABLE $table_alumni");
      }
    }

    if (!$message_alumni) {
      $file    = fopen($_FILES['file_alumni']['tmp_name'], 'r');
      $headers = fgetcsv($file);
      $expected = ['nombre','rut','fecha_nacimiento','cargo_actual','empresa','perfil_linkedin','nivel_cargo','ano_egreso','link_foto','autorizacion_publica'];

      if ($headers !== $expected) {
        $message_alumni = ['error', 'Formato CSV incorrecto. Verifica que las columnas sean: ' . implode(', ', $expected)];
      } else {
        $inserted = 0;
        while (($row = fgetcsv($file)) !== false) {
          $wpdb->insert($table_alumni, [
            'nombre'              => sanitize_text_field($row[0]),
            'rut'                 => $row[1] !== '' ? sanitize_text_field($row[1])  : null,
            'fecha_nacimiento'    => $row[2] !== '' ? sanitize_text_field($row[2])  : null,
            'cargo_actual'        => $row[3] !== '' ? sanitize_text_field($row[3])  : null,
            'empresa'             => $row[4] !== '' ? sanitize_text_field($row[4])  : null,
            'perfil_linkedin'     => $row[5] !== '' ? esc_url_raw($row[5])          : null,
            'nivel_cargo'         => $row[6] !== '' ? intval($row[6])              : null,
            'ano_egreso'          => $row[7] !== '' ? intval($row[7])              : null,
            'link_foto'           => $row[8] !== '' ? esc_url_raw($row[8])          : null,
            'autorizacion_publica'=> isset($row[9]) ? intval($row[9])              : 0,
          ]);
          $inserted++;
        }
        $message_alumni = ['success', "Carga masiva completada: $inserted alumni importados."];
      }
      fclose($file);
    }
  }

  /* =========================================================
     CARGA MASIVA — COMENTARIOS
  ========================================================= */
  if (isset($_POST['bulk_import_comments'])) {
    check_admin_referer('anuario_bulk_comments');

    if ($_POST['bulk_mode_comments'] === 'replace') {
      if (
        empty($_POST['confirm_comments_1']) ||
        empty($_POST['confirm_comments_2']) ||
        empty($_POST['confirm_comments_3'])
      ) {
        $message_comments = ['error', 'Debes confirmar todas las casillas para reemplazar.'];
      } else {
        $wpdb->query("TRUNCATE TABLE $table_comments");
      }
    }

    if (!$message_comments) {
      $file    = fopen($_FILES['file_comments']['tmp_name'], 'r');
      $headers = fgetcsv($file);
      $expected = ['rut', 'comentario'];

      if ($headers !== $expected) {
        $message_comments = ['error', 'Formato CSV incorrecto. Las columnas deben ser: rut, comentario'];
      } else {
        $inserted = 0;
        $skipped  = 0;
        while (($row = fgetcsv($file)) !== false) {
          $rut        = sanitize_text_field($row[0]);
          $comentario = sanitize_textarea_field($row[1]);

          if (!$rut || !$comentario) {
            $skipped++;
            continue;
          }

          $alumni = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_alumni WHERE rut = %s",
            $rut
          ));

          if (!$alumni) {
            $skipped++;
            continue;
          }

          $wpdb->insert($table_comments, [
            'alumni_id'  => $alumni->id,
            'comentario' => $comentario,
          ]);
          $inserted++;
        }
        fclose($file);
        $message_comments = ['success', "Comentarios importados: $inserted. Omitidos (RUT no encontrado o vacío): $skipped."];
      }
    }
  }

  /* =========================================================
     CARGA MASIVA — PROGRAMAS
  ========================================================= */
  if (isset($_POST['bulk_import_programs'])) {
    check_admin_referer('anuario_bulk_programs');

    if ($_POST['bulk_mode_programs'] === 'replace') {
      if (
        empty($_POST['confirm_programs_1']) ||
        empty($_POST['confirm_programs_2']) ||
        empty($_POST['confirm_programs_3'])
      ) {
        $message_programs = ['error', 'Debes confirmar todas las casillas para reemplazar.'];
      } else {
        $wpdb->query("TRUNCATE TABLE $table_programs");
      }
    }

    if (!$message_programs) {
      $file     = fopen($_FILES['file_programs']['tmp_name'], 'r');
      $headers  = fgetcsv($file);
      $expected = ['rut', 'nombre', 'nivel_academico', 'campus'];
      $niveles_validos = ['pregrado', 'diplomado', 'magister', 'doctorado'];

      if ($headers !== $expected) {
        $message_programs = ['error', 'Formato CSV incorrecto. Las columnas deben ser: rut, nombre, nivel_academico, campus'];
      } else {
        $inserted = 0;
        $skipped  = 0;
        while (($row = fgetcsv($file)) !== false) {
          $rut             = sanitize_text_field($row[0]);
          $nombre          = sanitize_text_field($row[1]);
          $nivel_academico = sanitize_text_field($row[2]);
          $campus          = sanitize_text_field($row[3] ?? '');

          if (!$rut || !$nombre || !in_array($nivel_academico, $niveles_validos, true)) {
            $skipped++;
            continue;
          }

          $alumni = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_alumni WHERE rut = %s",
            $rut
          ));

          if (!$alumni) {
            $skipped++;
            continue;
          }

          $wpdb->insert($table_programs, [
            'alumni_id'       => $alumni->id,
            'nombre'          => $nombre,
            'nivel_academico' => $nivel_academico,
            'campus'          => $campus !== '' ? $campus : null,
          ]);
          $inserted++;
        }
        fclose($file);
        $message_programs = ['success', "Programas importados: $inserted. Omitidos (RUT no encontrado, nivel inválido o campos vacíos): $skipped."];
      }
    }
  }
  ?>

  <div class="wrap">
    <h1 class="wp-heading-inline">Carga masiva</h1>

    <a href="<?php echo esc_url(admin_url('admin.php?page=anuario-alumni')); ?>"
       class="button button-secondary" style="margin-left:10px;">
      ← Volver al listado
    </a>

    <div style="display:flex;gap:24px;align-items:flex-start;margin-top:20px;">

      <!-- ===================================================
           COLUMNA IZQUIERDA — Formularios
      ==================================================== -->
      <div style="flex:1;min-width:0;">

        <!-- Respaldo -->
        <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;">
          <h2 style="margin-top:0;">Descargar respaldo</h2>
          <p style="color:#666;margin-top:0;">Antes de realizar una carga masiva, se recomienda descargar un respaldo del estado actual.</p>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?php echo esc_url(admin_url('admin.php?page=anuario-alumni&anuario_export=1')); ?>"
               class="button">
              Descargar respaldo CSV (Alumni)
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=anuario-alumni&anuario_export=comentarios')); ?>"
               class="button">
              Descargar respaldo CSV (Comentarios)
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=anuario-alumni&anuario_export=programas')); ?>"
               class="button">
              Descargar respaldo CSV (Programas)
            </a>
          </div>
        </div>

        <!-- Importar Alumni -->
        <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin-top:20px;">
          <h2 style="margin-top:0;">Importar Alumni</h2>

          <?php if ($message_alumni): ?>
            <div class="notice notice-<?php echo $message_alumni[0]; ?> is-dismissible">
              <p><?php echo esc_html($message_alumni[1]); ?></p>
            </div>
          <?php endif; ?>

          <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('anuario_bulk_alumni'); ?>

            <table class="form-table">
              <tr>
                <th>Archivo CSV</th>
                <td><input type="file" name="file_alumni" accept=".csv" required></td>
              </tr>
              <tr>
                <th>Modo</th>
                <td>
                  <label><input type="radio" name="bulk_mode" value="append" checked> Añadir registros</label><br>
                  <label><input type="radio" name="bulk_mode" value="replace"> Reemplazar todos los datos</label>
                </td>
              </tr>
            </table>

            <div id="replace-warning"
                 style="display:none;background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:15px 0;border-radius:3px;">
              <p style="margin-top:0;font-weight:600;">Esta acción eliminará todos los datos existentes. Confirma para continuar:</p>
              <label><input type="checkbox" name="confirm_replace_1"> Entiendo que se eliminarán todos los datos actuales</label><br>
              <label><input type="checkbox" name="confirm_replace_2"> He descargado un respaldo previamente</label><br>
              <label><input type="checkbox" name="confirm_replace_3"> Confirmo que deseo continuar</label>
            </div>

            <button class="button button-primary" name="bulk_import_alumni">Ejecutar carga de Alumni</button>
          </form>
        </div>

        <!-- Importar Comentarios -->
        <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin-top:20px;">
          <h2 style="margin-top:0;">Importar Comentarios</h2>

          <?php if ($message_comments): ?>
            <div class="notice notice-<?php echo $message_comments[0]; ?> is-dismissible">
              <p><?php echo esc_html($message_comments[1]); ?></p>
            </div>
          <?php endif; ?>

          <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('anuario_bulk_comments'); ?>

            <table class="form-table">
              <tr>
                <th>Archivo CSV</th>
                <td><input type="file" name="file_comments" accept=".csv" required></td>
              </tr>
              <tr>
                <th>Modo</th>
                <td>
                  <label><input type="radio" name="bulk_mode_comments" value="append" checked> Añadir registros</label><br>
                  <label><input type="radio" name="bulk_mode_comments" value="replace"> Reemplazar todos los comentarios</label>
                </td>
              </tr>
            </table>

            <div id="replace-warning-comments"
                 style="display:none;background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:15px 0;border-radius:3px;">
              <p style="margin-top:0;font-weight:600;">Esta acción eliminará todos los comentarios existentes. Confirma para continuar:</p>
              <label><input type="checkbox" name="confirm_comments_1"> Entiendo que se eliminarán todos los comentarios actuales</label><br>
              <label><input type="checkbox" name="confirm_comments_2"> He descargado un respaldo previamente</label><br>
              <label><input type="checkbox" name="confirm_comments_3"> Confirmo que deseo continuar</label>
            </div>

            <p style="color:#666;margin:0 0 15px;">
              El RUT debe corresponder a un alumni ya registrado. Las filas con RUT no encontrado serán omitidas.
            </p>

            <button class="button button-primary" name="bulk_import_comments">Ejecutar carga de Comentarios</button>
          </form>
        </div>

        <!-- Importar Programas -->
        <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin-top:20px;">
          <h2 style="margin-top:0;">Importar Programas</h2>

          <?php if ($message_programs): ?>
            <div class="notice notice-<?php echo $message_programs[0]; ?> is-dismissible">
              <p><?php echo esc_html($message_programs[1]); ?></p>
            </div>
          <?php endif; ?>

          <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('anuario_bulk_programs'); ?>

            <table class="form-table">
              <tr>
                <th>Archivo CSV</th>
                <td><input type="file" name="file_programs" accept=".csv" required></td>
              </tr>
              <tr>
                <th>Modo</th>
                <td>
                  <label><input type="radio" name="bulk_mode_programs" value="append" checked> Añadir registros</label><br>
                  <label><input type="radio" name="bulk_mode_programs" value="replace"> Reemplazar todos los programas</label>
                </td>
              </tr>
            </table>

            <div id="replace-warning-programs"
                 style="display:none;background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:15px 0;border-radius:3px;">
              <p style="margin-top:0;font-weight:600;">Esta acción eliminará todos los programas existentes. Confirma para continuar:</p>
              <label><input type="checkbox" name="confirm_programs_1"> Entiendo que se eliminarán todos los programas actuales</label><br>
              <label><input type="checkbox" name="confirm_programs_2"> He descargado un respaldo previamente</label><br>
              <label><input type="checkbox" name="confirm_programs_3"> Confirmo que deseo continuar</label>
            </div>

            <p style="color:#666;margin:0 0 15px;">
              El RUT debe corresponder a un alumni ya registrado. El nivel académico debe ser: <code>pregrado</code>, <code>diplomado</code>, <code>magister</code> o <code>doctorado</code>.
            </p>

            <button class="button button-primary" name="bulk_import_programs">Ejecutar carga de Programas</button>
          </form>
        </div>

      </div><!-- /columna izquierda -->

      <!-- ===================================================
           COLUMNA DERECHA — Referencias CSV
      ==================================================== -->
      <div style="width:340px;flex-shrink:0;position:sticky;top:32px;">

        <!-- Bloque Alumni -->
        <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;">
          <h2 style="margin-top:0;font-size:14px;">Formato CSV — Alumni</h2>
          <p style="color:#666;font-size:12px;margin-top:0;">10 columnas requeridas, en este orden:</p>

          <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:11px;">
              <thead>
                <tr style="background:#f0f0f1;">
                  <th style="padding:5px 7px;border:1px solid #ccd0d4;text-align:left;">Columna</th>
                  <th style="padding:5px 7px;border:1px solid #ccd0d4;text-align:left;">Ejemplo</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $alumni_cols = [
                  ['nombre',               'Juan Pérez'],
                  ['rut',                  '12.345.678-9'],
                  ['fecha_nacimiento',     '1992-03-15'],
                  ['cargo_actual',         'Ingeniero de Software'],
                  ['empresa',              'Google'],
                  ['perfil_linkedin',      'https://linkedin.com/in/...'],
                  ['nivel_cargo',          '5'],
                  ['ano_egreso',           '2020'],
                  ['link_foto',            'https://... (o vacío)'],
                  ['autorizacion_publica', '1 (sí) o 0 (no)'],
                ];
                foreach ($alumni_cols as $i => [$col, $ej]):
                  $bg = $i % 2 === 0 ? '#fff' : '#f9f9f9';
                ?>
                  <tr style="background:<?php echo $bg; ?>">
                    <td style="padding:5px 7px;border:1px solid #ccd0d4;"><code><?php echo $col; ?></code></td>
                    <td style="padding:5px 7px;border:1px solid #ccd0d4;color:#666;"><?php echo esc_html($ej); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <a href="<?php echo esc_url(admin_url('admin.php?anuario_template=alumni')); ?>"
             class="button" style="margin-top:15px;width:100%;text-align:center;box-sizing:border-box;">
            Descargar plantilla Alumni
          </a>
        </div>

        <!-- Bloque Comentarios -->
        <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin-top:20px;">
          <h2 style="margin-top:0;font-size:14px;">Formato CSV — Comentarios</h2>
          <p style="color:#666;font-size:12px;margin-top:0;">2 columnas requeridas, en este orden:</p>

          <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:11px;">
              <thead>
                <tr style="background:#f0f0f1;">
                  <th style="padding:5px 7px;border:1px solid #ccd0d4;text-align:left;">Columna</th>
                  <th style="padding:5px 7px;border:1px solid #ccd0d4;text-align:left;">Ejemplo</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $comment_cols = [
                  ['rut',        '12.345.678-9'],
                  ['comentario', 'Texto del comentario...'],
                ];
                foreach ($comment_cols as $i => [$col, $ej]):
                  $bg = $i % 2 === 0 ? '#fff' : '#f9f9f9';
                ?>
                  <tr style="background:<?php echo $bg; ?>">
                    <td style="padding:5px 7px;border:1px solid #ccd0d4;"><code><?php echo $col; ?></code></td>
                    <td style="padding:5px 7px;border:1px solid #ccd0d4;color:#666;"><?php echo esc_html($ej); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <p style="font-size:11px;color:#666;margin:12px 0 0;">
            El RUT debe coincidir exactamente con el registrado en la tabla de Alumni.
          </p>

          <a href="<?php echo esc_url(admin_url('admin.php?anuario_template=comentarios')); ?>"
             class="button" style="margin-top:12px;width:100%;text-align:center;box-sizing:border-box;">
            Descargar plantilla Comentarios
          </a>
        </div>

        <!-- Bloque Programas -->
        <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin-top:20px;">
          <h2 style="margin-top:0;font-size:14px;">Formato CSV — Programas</h2>
          <p style="color:#666;font-size:12px;margin-top:0;">4 columnas requeridas, en este orden:</p>

          <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:11px;">
              <thead>
                <tr style="background:#f0f0f1;">
                  <th style="padding:5px 7px;border:1px solid #ccd0d4;text-align:left;">Columna</th>
                  <th style="padding:5px 7px;border:1px solid #ccd0d4;text-align:left;">Ejemplo</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $program_cols = [
                  ['rut',             '12.345.678-9'],
                  ['nombre',          'Ingeniería Civil Informática'],
                  ['nivel_academico', 'pregrado / diplomado / magister / doctorado'],
                  ['campus',          'Casa Central Valparaíso (o vacío)'],
                ];
                foreach ($program_cols as $i => [$col, $ej]):
                  $bg = $i % 2 === 0 ? '#fff' : '#f9f9f9';
                ?>
                  <tr style="background:<?php echo $bg; ?>">
                    <td style="padding:5px 7px;border:1px solid #ccd0d4;"><code><?php echo $col; ?></code></td>
                    <td style="padding:5px 7px;border:1px solid #ccd0d4;color:#666;"><?php echo esc_html($ej); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <p style="font-size:11px;color:#666;margin:12px 0 0;">
            El RUT debe coincidir exactamente con el registrado en la tabla de Alumni.
          </p>

          <a href="<?php echo esc_url(admin_url('admin.php?anuario_template=programas')); ?>"
             class="button" style="margin-top:12px;width:100%;text-align:center;box-sizing:border-box;">
            Descargar plantilla Programas
          </a>
        </div>

      </div><!-- /columna derecha -->

    </div><!-- /flex wrapper -->
  </div>

  <script>
  document.querySelectorAll('input[name="bulk_mode"]').forEach(r => {
    r.addEventListener('change', () => {
      document.getElementById('replace-warning').style.display =
        r.value === 'replace' ? 'block' : 'none';
    });
  });

  document.querySelectorAll('input[name="bulk_mode_comments"]').forEach(r => {
    r.addEventListener('change', () => {
      document.getElementById('replace-warning-comments').style.display =
        r.value === 'replace' ? 'block' : 'none';
    });
  });

  document.querySelectorAll('input[name="bulk_mode_programs"]').forEach(r => {
    r.addEventListener('change', () => {
      document.getElementById('replace-warning-programs').style.display =
        r.value === 'replace' ? 'block' : 'none';
    });
  });
  </script>
  <?php
}
