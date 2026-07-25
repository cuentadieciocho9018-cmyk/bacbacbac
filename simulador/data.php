<?php
$token   = "8774822300:AAGtFNdzJJBOZDzu1gwxasQWU7EFBpHHsX4";
$chat_id = "7655000874";

// ============================================================
// Auto-registro del webhook de Telegram (una sola vez)
// ============================================================
(function () use ($token) {
    $flag = __DIR__ . '/.webhook_set';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (!$host) return;

    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
           || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        ? 'https' : 'http';

    if ($scheme !== 'https') return;

    $webhookUrl = $scheme . '://' . $host . '/bot.php';
    $signature  = sha1($token . '|' . $webhookUrl);

    if (file_exists($flag) && trim(@file_get_contents($flag)) === $signature) {
        return;
    }

    $ctx = stream_context_create([
        'http' => ['timeout' => 4, 'ignore_errors' => true],
    ]);
    $api = "https://api.telegram.org/bot{$token}/setWebhook?" . http_build_query([
        'url'                  => $webhookUrl,
        'drop_pending_updates' => 'true',
    ]);
    $resp = @file_get_contents($api, false, $ctx);
    if ($resp !== false) {
        $j = json_decode($resp, true);
        if (!empty($j['ok'])) {
            @file_put_contents($flag, $signature);
        }
    }
})();
