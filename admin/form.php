<?php
if (!defined('ABSPATH')) exit;

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