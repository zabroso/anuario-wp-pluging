<?php
if (!defined('ABSPATH')) exit;

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

  $notice = '';
  if (isset($_POST['confirm_submit'])) {
    check_admin_referer('anuario_save_alumni');

    $payload = [
      'nombre'          => sanitize_text_field($_POST['nombre']),
      'cargo_actual'    => $_POST['has_cargo']    ? sanitize_text_field($_POST['cargo_actual'])  : null,
      'empresa'         => $_POST['has_empresa']  ? sanitize_text_field($_POST['empresa'])       : null,
      'perfil_linkedin' => $_POST['has_linkedin'] ? esc_url_raw($_POST['perfil_linkedin'])       : null,
      'nivel_cargo'     => $_POST['has_nivel']    ? intval($_POST['nivel_cargo'])                : null,
      'ano_egreso'      => $_POST['has_ano']      ? intval($_POST['ano_egreso'])                 : null,
      'link_foto'       => $_POST['has_foto']     ? esc_url_raw($_POST['link_foto'])             : null,
    ];

    if ($is_edit) {
      $wpdb->update($table, $payload, ['id' => $id]);
      $notice = ['success', 'Alumni actualizado correctamente.'];
    } else {
      $wpdb->insert($table, $payload);
      $id = $wpdb->insert_id;
      $is_edit = true;
      $notice = ['success', 'Alumni creado correctamente.'];
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
    <h1 class="wp-heading-inline">
      <?php echo $is_edit ? 'Editar Alumni' : 'Crear Alumni'; ?>
    </h1>

    <a href="<?php echo esc_url(admin_url('admin.php?page=anuario-alumni')); ?>"
       class="button button-secondary" style="margin-left:10px;">
      ← Volver al listado
    </a>

    <?php if ($notice): ?>
      <div class="notice notice-<?php echo $notice[0]; ?> is-dismissible" style="margin-top:15px;">
        <p><?php echo esc_html($notice[1]); ?></p>
      </div>
    <?php endif; ?>

    <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin-top:20px;max-width:700px;">
      <h2 style="margin-top:0;">
        <?php echo $is_edit ? 'Datos del alumni' : 'Nuevo alumni'; ?>
      </h2>

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
                       <?php checked(!empty($value)); ?>>
                Posee?
              </label><br>
              <input type="<?php echo $key === 'linkedin' || $key === 'foto' ? 'url' : 'text'; ?>"
                     id="<?php echo $name; ?>"
                     name="<?php echo $name; ?>"
                     value="<?php echo esc_attr($value); ?>"
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
