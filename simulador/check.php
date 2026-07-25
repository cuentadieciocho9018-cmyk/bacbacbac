<?php
header('Content-Type: application/json; charset=UTF-8');

$uid = trim($_GET['u'] ?? '');
if (!$uid) { echo json_encode(['action' => null]); exit; }

$file = __DIR__ . '/acciones/' . basename($uid) . '.txt';

if (file_exists($file)) {
    $action = trim(file_get_contents($file));
    unlink($file);
    echo json_encode(['action' => $action]);
} else {
    echo json_encode(['action' => null]);
}
