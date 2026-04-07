<?php
if (!defined('ABSPATH')) exit;

function anuario_render_programs() {
  global $wpdb;
  $table_alumni    = $wpdb->prefix . 'anuario_alumni';
  $table_programs  = $wpdb->prefix . 'anuario_programas';

  /* =========================================================
     CREAR PROGRAMA
  ========================================================= */
  $notice = '';
  if (isset($_POST['create_program'])) {
    check_admin_referer('anuario_create_program');

    if (empty($_POST['confirm_submit'])) {
      $notice = ['error', 'Debes confirmar antes de guardar el programa.'];
    } else {
      $alumni_id      = intval($_POST['alumni_id']);
      $nombre         = sanitize_text_field($_POST['nombre']);
      $nivel_academico = sanitize_text_field($_POST['nivel_academico']);
      $campus         = sanitize_text_field($_POST['campus']);

      if (!$alumni_id || !$nombre || !$nivel_academico) {
        $notice = ['error', 'El alumni, el nombre del programa y el nivel académico son obligatorios.'];
      } else {
        $wpdb->insert($table_programs, [
          'alumni_id'       => $alumni_id,
          'nombre'          => $nombre,
          'nivel_academico' => $nivel_academico,
          'campus'          => $campus !== '' ? $campus : null,
        ]);
        $notice = ['success', 'Programa guardado correctamente.'];
      }
    }
  }

  /* =========================================================
     FILTROS Y LISTADO
  ========================================================= */
  $per_page      = 20;
  $paged         = max(1, intval($_GET['paged'] ?? 1));
  $offset        = ($paged - 1) * $per_page;
  $filter_alumni = intval($_GET['alumni_id'] ?? 0);

  $where = "WHERE 1=1";
  if ($filter_alumni) {
    $where .= $wpdb->prepare(" AND p.alumni_id = %d", $filter_alumni);
  }

  $total = $wpdb->get_var(
    "SELECT COUNT(*) FROM $table_programs p $where"
  );
  $programs = $wpdb->get_results(
    "SELECT p.*, a.nombre AS alumni_nombre, a.rut
     FROM $table_programs p
     JOIN $table_alumni a ON p.alumni_id = a.id
     $where
     ORDER BY a.nombre ASC, p.nivel_academico ASC
     LIMIT $per_page OFFSET $offset"
  );
  $pages = ceil($total / $per_page);

  $all_alumni = $wpdb->get_results(
    "SELECT id, nombre, rut FROM $table_alumni ORDER BY nombre ASC",
    ARRAY_A
  );

  $preselected_id = intval($_GET['id'] ?? $filter_alumni);

  $niveles = ['pregrado', 'diplomado', 'magister', 'doctorado'];
  ?>

  <div class="wrap">
    <h1 class="wp-heading-inline">Programas Académicos</h1>

    <a href="<?php echo esc_url(admin_url('admin.php?page=anuario-alumni')); ?>"
       class="button button-secondary" style="margin-left:10px;">
      ← Volver al listado
    </a>

    <?php if ($notice): ?>
      <div class="notice notice-<?php echo $notice[0]; ?> is-dismissible" style="margin-top:15px;">
        <p><?php echo esc_html($notice[1]); ?></p>
      </div>
    <?php endif; ?>

    <!-- =====================================================
         FORMULARIO CREAR PROGRAMA
    ====================================================== -->
    <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin-top:20px;max-width:700px;">
      <h2 style="margin-top:0;">Agregar programa</h2>

      <form method="post">
        <?php wp_nonce_field('anuario_create_program'); ?>

        <table class="form-table">
          <tr>
            <th>Buscar alumni</th>
            <td>
              <input type="search" id="alumni-search"
                     placeholder="Buscar por nombre o RUT..."
                     class="regular-text"
                     autocomplete="off">
            </td>
          </tr>
          <tr>
            <th>Alumni *</th>
            <td>
              <select name="alumni_id" id="alumni_id_select" required class="regular-text">
                <option value="">— Seleccionar alumni —</option>
                <?php foreach ($all_alumni as $a): ?>
                  <option value="<?php echo $a['id']; ?>"
                          data-nombre="<?php echo esc_attr(strtolower($a['nombre'])); ?>"
                          data-rut="<?php echo esc_attr($a['rut'] ?? ''); ?>"
                          <?php selected($preselected_id, $a['id']); ?>>
                    <?php echo esc_html($a['nombre']); ?>
                    <?php if ($a['rut']): ?> — <?php echo esc_html($a['rut']); ?><?php endif; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
          <tr>
            <th>Nombre del programa *</th>
            <td>
              <input type="text" name="nombre" required
                     class="regular-text"
                     placeholder="Ej: Ingeniería Civil Informática">
            </td>
          </tr>
          <tr>
            <th>Nivel académico *</th>
            <td>
              <select name="nivel_academico" required class="regular-text">
                <option value="">— Seleccionar nivel —</option>
                <?php foreach ($niveles as $n): ?>
                  <option value="<?php echo $n; ?>"><?php echo ucfirst($n); ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
          <tr>
            <th>Campus</th>
            <td>
              <input type="text" name="campus"
                     class="regular-text"
                     placeholder="Ej: Casa Central Valparaíso">
            </td>
          </tr>
          <tr>
            <th>Confirmación</th>
            <td>
              <label>
                <input type="checkbox" name="confirm_submit" required>
                Confirmo que deseo guardar este programa
              </label>
            </td>
          </tr>
        </table>

        <?php submit_button('Guardar programa', 'primary', 'create_program'); ?>
      </form>
    </div>

    <!-- =====================================================
         FILTRO + ACCIONES MASIVAS
    ====================================================== -->
    <div style="display:flex;align-items:center;gap:10px;margin-top:25px;flex-wrap:wrap;">
      <form method="get" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <input type="hidden" name="page" value="anuario-alumni-programs">

        <select name="alumni_id" class="regular-text">
          <option value="">Todos los alumni</option>
          <?php foreach ($all_alumni as $a): ?>
            <option value="<?php echo $a['id']; ?>" <?php selected($filter_alumni, $a['id']); ?>>
              <?php echo esc_html($a['nombre']); ?>
              <?php if ($a['rut']): ?> — <?php echo esc_html($a['rut']); ?><?php endif; ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button class="button">Filtrar</button>

        <?php if ($filter_alumni): ?>
          <a href="<?php echo admin_url('admin.php?page=anuario-alumni-programs'); ?>" class="button">
            Limpiar filtro
          </a>
        <?php endif; ?>
      </form>

      <button id="bulk-delete-programs-btn" class="button" style="color:#b91c1c;">
        Eliminar seleccionados
      </button>
    </div>

    <!-- =====================================================
         TABLA DE PROGRAMAS
    ====================================================== -->
    <form method="post" id="bulk-delete-programs-form">
      <?php wp_nonce_field('bulk_delete_programs'); ?>

      <table class="wp-list-table widefat striped" style="margin-top:15px;">
        <thead>
          <tr>
            <th style="width:30px;"><input type="checkbox" id="select-all-programs"></th>
            <th>Alumni</th>
            <th>RUT</th>
            <th>Programa</th>
            <th>Nivel académico</th>
            <th>Campus</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($programs): foreach ($programs as $p): ?>
            <tr>
              <td><input type="checkbox" name="program_ids[]" value="<?php echo $p->id; ?>"></td>
              <td><?php echo esc_html($p->alumni_nombre); ?></td>
              <td><?php echo esc_html($p->rut ?? '—'); ?></td>
              <td><?php echo esc_html($p->nombre); ?></td>
              <td><?php echo esc_html(ucfirst($p->nivel_academico)); ?></td>
              <td><?php echo esc_html($p->campus ?? '—'); ?></td>
              <td>
                <a href="<?php echo wp_nonce_url(
                  admin_url('admin.php?page=anuario-alumni-programs&delete_program=' . $p->id),
                  'delete_program'
                ); ?>"
                   onclick="return confirm('¿Eliminar este programa? Esta acción no se puede deshacer.')">
                  Eliminar
                </a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="7">No hay programas registrados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </form>

    <?php if ($pages > 1): ?>
      <div class="tablenav-pages" style="margin-top:10px;">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <a class="button <?php echo $p === $paged ? 'button-primary' : ''; ?>"
             href="<?php echo add_query_arg('paged', $p); ?>">
            <?php echo $p; ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- =====================================================
       MODAL CONFIRMACIÓN ELIMINACIÓN MASIVA
  ====================================================== -->
  <div id="bulk-delete-modal-programs" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:4px;padding:30px;max-width:440px;width:90%;box-shadow:0 4px 20px rgba(0,0,0,.2);">
      <h2 style="margin-top:0;">Confirmar eliminación</h2>
      <p>Estás a punto de eliminar <strong id="modal-programs-count"></strong>. Esta acción no se puede deshacer.</p>
      <p style="color:#b91c1c;font-weight:600;">¿Deseas continuar?</p>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
        <button id="modal-programs-cancel" class="button">Cancelar</button>
        <button id="modal-programs-confirm" class="button button-primary" style="background:#b91c1c;border-color:#b91c1c;">
          Sí, eliminar
        </button>
      </div>
    </div>
  </div>

  <script>
  document.getElementById('alumni-search').addEventListener('input', function () {
    const term = this.value.toLowerCase();
    const select = document.getElementById('alumni_id_select');
    Array.from(select.options).forEach(opt => {
      if (!opt.value) return;
      const nombre = opt.dataset.nombre || '';
      const rut    = opt.dataset.rut    || '';
      opt.hidden = term.length > 0 && !nombre.includes(term) && !rut.includes(term);
    });
  });

  document.getElementById('select-all-programs').addEventListener('change', e => {
    document.querySelectorAll('input[name="program_ids[]"]').forEach(cb => cb.checked = e.target.checked);
  });

  const modal      = document.getElementById('bulk-delete-modal-programs');
  const modalCount = document.getElementById('modal-programs-count');

  document.getElementById('bulk-delete-programs-btn').addEventListener('click', () => {
    const selected = Array.from(document.querySelectorAll('input[name="program_ids[]"]:checked'));
    if (!selected.length) {
      alert('Debes seleccionar al menos un programa.');
      return;
    }
    const n = selected.length;
    modalCount.textContent = n === 1 ? '1 programa' : `${n} programas`;
    modal.style.display = 'flex';
  });

  document.getElementById('modal-programs-cancel').addEventListener('click', () => {
    modal.style.display = 'none';
  });

  document.getElementById('modal-programs-confirm').addEventListener('click', () => {
    const form = document.getElementById('bulk-delete-programs-form');
    form.action = "<?php echo admin_url('admin.php?page=anuario-alumni-programs'); ?>";
    form.submit();
  });

  modal.addEventListener('click', e => {
    if (e.target === modal) modal.style.display = 'none';
  });
  </script>
  <?php
}
