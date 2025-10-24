<?php

$base = __DIR__;
$dir = $base . '/database';
$path = $dir . '/database.sqlite';


if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}


if (file_exists($path)) {
    $tmp = $path . '.' . uniqid('old_', true);
    @rename($path, $tmp);
    @unlink($tmp);
    // Eğer rename başarısız olduysa doğrudan unlink'i de deneriz
    @unlink($path);
}


require_once __DIR__ . '/db.php';
$pdo = db(); // Bu çağrı yeni dosyayı oluşturur
$sql = file_get_contents($base . '/database/schema_uuid.sql');
$pdo->exec($sql);

echo "DB reset OK";
