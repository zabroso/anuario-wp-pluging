<?php
if (!defined('ABSPATH')) exit;

function anuario_render_list() {
  global $wpdb;
  $table = $wpdb->prefix . 'anuario_alumni';

  $per_page = 20;
  $paged = max(1, intval($_GET['paged'] ?? 1));
  $offset = ($paged - 1) * $per_page;

  $search     = sanitize_text_field($_GET['s'] ?? '');
  $rut        = sanitize_text_field($_GET['rut'] ?? '');
  $year_from  = intval($_GET['year_from'] ?? 0);
  $year_to    = intval($_GET['year_to'] ?? 0);
  $level      = intval($_GET['level'] ?? 0);

  // Validación server-side: si from > to, ignorar ambos filtros
  if ($year_from && $year_to && $year_from > $year_to) {
    $year_from = 0;
    $year_to   = 0;
  }

  $where = "WHERE 1=1";
  if ($search) {
    $where .= $wpdb->prepare(
      " AND (nombre LIKE %s OR cargo_actual LIKE %s)",
      "%$search%", "%$search%"
    );
  }
  if ($rut) {
    $where .= $wpdb->prepare(" AND rut LIKE %s", "%$rut%");
  }
  if ($year_from) {
    $where .= $wpdb->prepare(" AND ano_egreso >= %d", $year_from);
  }
  if ($year_to) {
    $where .= $wpdb->prepare(" AND ano_egreso <= %d", $year_to);
  }
  if ($level) {
    $where .= $wpdb->prepare(" AND nivel_cargo = %d", $level);
  }

  $total  = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
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

    <form method="get" style="margin-top:20px;">
      <input type="hidden" name="page" value="anuario-alumni">

      <input type="search" name="s" placeholder="Buscar nombre o cargo" value="<?php echo esc_attr($search); ?>">

      <input type="search" name="rut" placeholder="Buscar por RUT" value="<?php echo esc_attr($rut); ?>" style="margin-left:8px;">

      <label style="margin-left:8px;">Egreso desde</label>
      <select name="year_from" id="year_from">
        <option value="">—</option>
        <?php for ($y = date('Y'); $y >= 1980; $y--): ?>
          <option value="<?php echo $y; ?>" <?php selected($year_from, $y); ?>><?php echo $y; ?></option>
        <?php endfor; ?>
      </select>

      <label style="margin-left:8px;">hasta</label>
      <select name="year_to" id="year_to">
        <option value="">—</option>
        <?php for ($y = date('Y'); $y >= 1980; $y--): ?>
          <option value="<?php echo $y; ?>" <?php selected($year_to, $y); ?>><?php echo $y; ?></option>
        <?php endfor; ?>
      </select>

      <span id="year-range-error" style="color:#b91c1c;margin-left:8px;display:none;">
        "Desde" no puede ser mayor que "Hasta".
      </span>

      <select name="level" style="margin-left:8px;">
        <option value="">Nivel</option>
        <?php for ($i = 1; $i <= 10; $i++): ?>
          <option value="<?php echo $i; ?>" <?php selected($level, $i); ?>><?php echo $i; ?></option>
        <?php endfor; ?>
      </select>

      <button class="button" id="filter-btn" style="margin-left:8px;">Filtrar</button>

      <?php if ($search || $rut || $year_from || $year_to || $level): ?>
        <a href="<?php echo admin_url('admin.php?page=anuario-alumni'); ?>" class="button">
          Limpiar filtros
        </a>
      <?php endif; ?>
    </form>

    <form method="post" id="bulk-delete-form">
      <?php wp_nonce_field('bulk_delete_alumni'); ?>

      <table class="wp-list-table widefat striped" style="margin-top:20px;">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all"></th>
            <th>Nombre</th>
            <th>RUT</th>
            <th>Cargo</th>
            <th>Empresa</th>
            <th>Año egreso</th>
            <th>Nivel</th>
            <th>Fecha nacimiento</th>
            <th>Autorización pública</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($alumni): foreach ($alumni as $a): ?>
            <tr>
              <td><input type="checkbox" name="ids[]" value="<?php echo $a->id; ?>"></td>
              <td><?php echo esc_html($a->nombre); ?></td>
              <td><?php echo esc_html($a->rut ?? '—'); ?></td>
              <td><?php echo esc_html($a->cargo_actual ?? '—'); ?></td>
              <td><?php echo esc_html($a->empresa ?? '—'); ?></td>
              <td><?php echo esc_html($a->ano_egreso ?? '—'); ?></td>
              <td><?php echo esc_html($a->nivel_cargo ?? '—'); ?></td>
              <td><?php echo esc_html($a->fecha_nacimiento ?? '—'); ?></td>
              <td><?php echo $a->autorizacion_publica ? 'Sí' : 'No'; ?></td>
              <td>
                <a href="<?php echo admin_url('admin.php?page=anuario-alumni-edit&id=' . $a->id); ?>">Editar</a>
                |
                <a href="<?php echo admin_url('admin.php?page=anuario-alumni-comments&id=' . $a->id); ?>">Comentarios</a>
                |
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=anuario-alumni&delete=' . $a->id), 'delete_alumni'); ?>"
                   onclick="return confirm('¿Eliminar este alumni?')">Eliminar</a>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="10">No hay resultados</td></tr>
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

  <script>
  document.getElementById('select-all').addEventListener('change', e => {
    document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = e.target.checked);
  });

  (function () {
    const fromSel  = document.getElementById('year_from');
    const toSel    = document.getElementById('year_to');
    const errorMsg = document.getElementById('year-range-error');
    const filterBtn = document.getElementById('filter-btn');

    function validateYearRange() {
      const from = parseInt(fromSel.value) || 0;
      const to   = parseInt(toSel.value)   || 0;
      const invalid = from && to && from > to;
      errorMsg.style.display  = invalid ? 'inline' : 'none';
      filterBtn.disabled      = invalid;
    }

    fromSel.addEventListener('change', validateYearRange);
    toSel.addEventListener('change', validateYearRange);
  })();

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
