<?php
function db(): PDO
{
    static $pdo;
    if ($pdo)
        return $pdo;

    $path = __DIR__ . '/database/database.sqlite';
    if (!is_dir(__DIR__ . '/database')) {
        mkdir(__DIR__ . '/database', 0777, true);
    }
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("PRAGMA foreign_keys=ON");
    return $pdo;
}

function uuid4(): string
{
    $d = random_bytes(16);
    $d[6] = chr((ord($d[6]) & 0x0f) | 0x40);
    $d[8] = chr((ord($d[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}
