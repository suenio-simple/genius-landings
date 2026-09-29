<?php
/**
 * GL-F08 — Gestión de landings por cliente.
 * Muestra las landings del cliente seleccionado y permite registrar nuevas.
 */
require_once 'api.php';

$cliente  = $_GET['cliente'] ?? '';
$landings = $cliente ? get_landings($cliente) : [];
$leads_by_landing = [];

/**
 * Configuración de templates.
 * - 'auto_fields' => keys de 'fields' que se rellenan con el nombre de la landing.
 * - 'defaults'    => valores hardcodeados que se inyectan en el payload.
 */
$template_configs = [
    1 => [
        'templateId'  => 1,
        'name'        => 'promo-event',
        'auto_fields' => ['title', 'subtitle'],
        'defaults'    => [
            'ctaText'      => 'Ver ofertas',
            'eventDate'    => '2026-05-20',
            'heroImageUrl' => 'https://via.placeholder.com/1200x400',
        ],
    ],
    2 => [
        'templateId'  => 2,
        'name'        => 'product-launch',
        'auto_fields' => ['productName', 'productDescription'],
        'defaults'    => [
            'price'         => '85000',
            'originalPrice' => '120000',
            'ctaText'       => 'Comprarlo ahora',
        ],
    ],
    3 => [
        'templateId'  => 3,
        'name'        => 'lead-capture',
        'auto_fields' => ['title', 'description'],
        'defaults'    => [
            'ctaText'    => 'Quiero ofertas',
            'privacyUrl' => 'https://suenosimple.com/privacidad',
        ],
    ],
];

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre     = trim($_POST['nombre']   ?? '');
    $archivo    = trim($_POST['archivo']  ?? '');
    $templateId = (int) ($_POST['template'] ?? 0);
    $errores    = [];

    if (!$nombre || !$archivo || !$cliente) {
        $errores[] = 'Nombre, archivo y cliente son obligatorios.';
    }

    if (!isset($template_configs[$templateId])) {
        $errores[] = 'Template inválido.';
    }

    if (!preg_match('/^[a-z0-9][a-z0-9-]{2,59}$/', $archivo)) {
        $errores[] = 'El nombre del archivo solo admite minúsculas, números y guiones (ej: hot-sale-2026).';
    }

    if ($errores) {
        $mensaje = 'Error: ' . implode(' ', $errores);
    } else {
        $cfg    = $template_configs[$templateId];
        $fields = [];

        // Campos autocompletados con el nombre de la landing
        foreach ($cfg['auto_fields'] as $key) {
            $fields[$key] = $nombre;
        }

        // Defaults hardcodeados
        foreach ($cfg['defaults'] as $key => $val) {
            $fields[$key] = $val;
        }

        // ctaUrl derivado del nombre de archivo
        $fields['ctaUrl'] = 'https://suenosimple.com/' . $archivo;

        $payload = [
            'templateId' => $templateId,
            'name'       => $nombre,
            'client'     => $cliente,
            'status'     => 'active',
            'fields'     => $fields,
        ];

        $resultado = post_landing($payload);

        if ($resultado['ok']) {
            header('Location: landings.php?cliente=' . urlencode($cliente) . '&creada=1');
            exit;
        }
        $mensaje = 'Error al registrar en el CRM: ' . $resultado['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Landings — <?= htmlspecialchars($cliente) ?> — Genius Admin</title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    .admin-header { background:#0f172a; color:#fff; padding:0 32px; height:56px; display:flex; align-items:center; gap:16px; }
    .admin-header a { color:#94a3b8; text-decoration:none; font-size:.85rem; }
    .admin-main  { max-width:900px; margin:0 auto; padding:32px 24px; }
    .form-card   { background:#fff; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.1); padding:24px; margin-bottom:28px; }
    .form-card h2 { font-size:1rem; font-weight:700; margin-bottom:16px; }
    .field { margin-bottom:14px; }
    .field label { display:block; font-size:.82rem; font-weight:600; margin-bottom:4px; }
    .field input, .field select { width:100%; padding:8px 12px; border:1px solid #dee2e6; border-radius:4px; font-size:.88rem; box-sizing:border-box; }
    .btn { padding:8px 20px; border-radius:4px; font-size:.82rem; font-weight:600; border:none; cursor:pointer; }
    .btn-primary { background:#198754; color:#fff; }
    .msg { padding:10px 14px; border-radius:4px; margin-bottom:16px; background:#d1e7dd; color:#0a3622; font-size:.85rem; }
    .msg-error { background:#f8d7da; color:#842029; }
    table { width:100%; border-collapse:collapse; background:#fff; border-radius:6px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); }
    thead { background:#212529; color:#fff; }
    thead th { padding:10px 14px; text-align:left; font-size:.8rem; }
    tbody td { padding:10px 14px; border-bottom:1px solid #e9ecef; font-size:.88rem; }
    .badge { display:inline-block; padding:2px 10px; border-radius:12px; font-size:.72rem; font-weight:700; }
    .badge-active   { background:#d1e7dd; color:#0a3622; }
    .badge-draft    { background:#e9ecef; color:#495057; }
    .badge-inactive { background:#f8d7da; color:#842029; }
    .no-api-warning { color:#dc3545; background:#f8d7da; padding:12px 16px; border-radius:4px; margin-bottom:20px; font-size:.88rem; }
  </style>
</head>
<body>
  <div class="admin-header">
    <strong>Genius Admin</strong>
    <a href="index.php">← Clientes</a>
  </div>

  <div class="admin-main">
    <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:4px;">
      Landings — <?= htmlspecialchars($cliente ?: 'Sin cliente') ?>
    </h1>
    <p style="color:#64748b;font-size:.85rem;margin-bottom:20px;">Registrá y administrá las landing pages del cliente.</p>

    <?php if (!$cliente): ?>
      <p style="color:#dc3545;">No se indicó ningún cliente. <a href="index.php">Volver al inicio.</a></p>
    <?php else: ?>

      <?php if (isset($_GET['creada'])): ?>
        <div class="msg">Landing registrada correctamente.</div>
      <?php endif; ?>

      <?php if ($mensaje): ?>
        <div class="msg msg-error"><?= htmlspecialchars($mensaje) ?></div>
      <?php endif; ?>

      <?php if (empty($landings)): ?>
        <div class="no-api-warning">
          ⚠ No se obtuvieron landings desde la API. Verificá que el Landing CRM esté corriendo (puerto 3000) o que el cliente tenga landings registradas.
        </div>
      <?php endif; ?>

      <table style="margin-bottom:28px;">
        <thead><tr><th>ID</th><th>Nombre</th><th>Template</th><th>Estado</th><th>Leads</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php foreach ($landings as $l): ?>
            <tr>
              <td><?= htmlspecialchars($l['id'] ?? '—') ?></td>
              <td><?= htmlspecialchars($l['name'] ?? $l['title'] ?? '—') ?></td>
              <td><?= htmlspecialchars($l['template'] ?? $l['templateId'] ?? '—') ?></td>
              <td><span class="badge badge-<?= htmlspecialchars($l['status'] ?? 'draft') ?>"><?= htmlspecialchars($l['status'] ?? 'draft') ?></span></td>
              <td><?= $leads_by_landing[$l['id'] ?? 0] ?? '—' ?></td>
              <td><a href="<?= LANDING_CRM_URL ?>/landings/<?= $l['id'] ?>/preview" target="_blank">Preview</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($landings)): ?>
            <tr><td colspan="6" style="color:#64748b;text-align:center;padding:16px;">Sin landings registradas.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <div class="form-card">
        <h2>Registrar nueva landing</h2>
        <form method="POST">
          <div class="field">
            <label>Nombre de la landing</label>
            <input type="text" name="nombre" placeholder="Ej: Hot Sale 2026"
                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Nombre del archivo (sin espacios, sin .html)</label>
            <input type="text" name="archivo" placeholder="Ej: hot-sale-2026"
                   value="<?= htmlspecialchars($_POST['archivo'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Template</label>
            <select name="template">
              <?php foreach ($template_configs as $id => $c): ?>
                <option value="<?= $id ?>" <?= ((int)($_POST['template'] ?? 1) === $id) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Registrar landing</button>
        </form>
      </div>

    <?php endif; ?>
  </div>
</body>
</html>