<?php
if (!defined('ABSPATH')) exit;

function anuario_render_view() {
  global $wpdb;

  $id = intval($_GET['id'] ?? 0);
  if (!$id) {
    echo '<div class="wrap"><p>Alumni no especificado.</p></div>';
    return;
  }

  $table_alumni   = $wpdb->prefix . 'anuario_alumni';
  $table_comments = $wpdb->prefix . 'anuario_comentarios';
  $table_programs = $wpdb->prefix . 'anuario_programas';

  $alumni = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_alumni WHERE id = %d",
    $id
  ));

  if (!$alumni) {
    echo '<div class="wrap"><p>Alumni no encontrado.</p></div>';
    return;
  }

  $comments = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_comments WHERE alumni_id = %d ORDER BY created_at DESC",
    $id
  ));

  $programs = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_programs WHERE alumni_id = %d ORDER BY nivel_academico ASC",
    $id
  ));

  $niveles_label = [
    'pregrado'   => 'Pregrado',
    'diplomado'  => 'Diplomado',
    'magister'   => 'Magíster',
    'doctorado'  => 'Doctorado',
  ];
  ?>

  <div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html($alumni->nombre); ?></h1>

    <a href="<?php echo esc_url(admin_url('admin.php?page=anuario-alumni-edit&id=' . $id)); ?>"
       class="button button-secondary" style="margin-left:10px;">
      Editar
    </a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=anuario-alumni')); ?>"
       class="button button-secondary" style="margin-left:8px;">
      ← Volver al listado
    </a>

    <div style="display:flex;gap:24px;align-items:flex-start;margin-top:20px;">

      <!-- ===================================================
           COLUMNA IZQUIERDA — Datos del alumni
      ==================================================== -->
      <div style="width:340px;flex-shrink:0;">
        <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;">
          <h2 style="margin-top:0;">Datos del alumni</h2>

          <?php if ($alumni->link_foto): ?>
            <div style="text-align:center;margin-bottom:16px;">
              <img src="<?php echo esc_url($alumni->link_foto); ?>"
                   alt="Foto de <?php echo esc_attr($alumni->nombre); ?>"
                   style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:2px solid #ccd0d4;">
            </div>
          <?php endif; ?>

          <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <?php
            $fields = [
              'RUT'                  => $alumni->rut,
              'Fecha de nacimiento'  => $alumni->fecha_nacimiento,
              'Cargo actual'         => $alumni->cargo_actual,
              'Empresa'              => $alumni->empresa,
              'LinkedIn'             => $alumni->perfil_linkedin
                ? '<a href="' . esc_url($alumni->perfil_linkedin) . '" target="_blank">Ver perfil</a>'
                : null,
              'Nivel de cargo'       => $alumni->nivel_cargo ? 'Nivel ' . $alumni->nivel_cargo : null,
              'Año de egreso'        => $alumni->ano_egreso,
              'Autorización pública' => $alumni->autorizacion_publica ? 'Sí' : 'No',
            ];
            foreach ($fields as $label => $value):
              if ($value === null || $value === '') continue;
            ?>
              <tr>
                <td style="padding:7px 0;color:#666;width:50%;vertical-align:top;border-bottom:1px solid #f0f0f1;">
                  <?php echo esc_html($label); ?>
                </td>
                <td style="padding:7px 0;font-weight:500;vertical-align:top;border-bottom:1px solid #f0f0f1;">
                  <?php echo $label === 'LinkedIn' ? $value : esc_html($value); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </table>
        </div>
      </div>

      <!-- ===================================================
           COLUMNA DERECHA — Comentarios + Programas
      ==================================================== -->
      <div style="flex:1;min-width:0;">

        <!-- Comentarios -->
        <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h2 style="margin:0;">Comentarios <span style="font-size:13px;color:#666;font-weight:400;">(<?php echo count($comments); ?>)</span></h2>
            <a href="<?php echo esc_url(admin_url('admin.php?page=anuario-alumni-comments&id=' . $id)); ?>"
               class="button button-small">Gestionar</a>
          </div>

          <?php if ($comments): ?>
            <div style="display:flex;flex-direction:column;gap:10px;">
              <?php foreach ($comments as $c): ?>
                <div style="background:#f9f9f9;border-left:3px solid #2271b1;padding:10px 12px;border-radius:0 3px 3px 0;">
                  <p style="margin:0 0 6px;font-size:13px;"><?php echo esc_html($c->comentario); ?></p>
                  <span style="font-size:11px;color:#999;"><?php echo esc_html($c->created_at); ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p style="color:#999;margin:0;">No hay comentarios registrados para este alumni.</p>
          <?php endif; ?>
        </div>

        <!-- Programas -->
        <div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin-top:20px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h2 style="margin:0;">Programas académicos <span style="font-size:13px;color:#666;font-weight:400;">(<?php echo count($programs); ?>)</span></h2>
            <a href="<?php echo esc_url(admin_url('admin.php?page=anuario-alumni-programs&id=' . $id)); ?>"
               class="button button-small">Gestionar</a>
          </div>

          <?php if ($programs): ?>
            <div style="display:flex;flex-direction:column;gap:10px;">
              <?php foreach ($programs as $p): ?>
                <div style="background:#f9f9f9;border:1px solid #e0e0e0;padding:12px;border-radius:3px;">
                  <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <strong style="font-size:13px;"><?php echo esc_html($p->nombre); ?></strong>
                    <span style="font-size:11px;background:#2271b1;color:#fff;padding:2px 8px;border-radius:10px;white-space:nowrap;margin-left:10px;">
                      <?php echo esc_html($niveles_label[$p->nivel_academico] ?? ucfirst($p->nivel_academico)); ?>
                    </span>
                  </div>
                  <?php if ($p->campus): ?>
                    <p style="margin:5px 0 0;font-size:12px;color:#666;"><?php echo esc_html($p->campus); ?></p>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p style="color:#999;margin:0;">No hay programas registrados para este alumni.</p>
          <?php endif; ?>
        </div>

      </div><!-- /columna derecha -->

    </div><!-- /flex wrapper -->
  </div>
  <?php
}
