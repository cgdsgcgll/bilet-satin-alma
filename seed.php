<?php
// Yalnızca komut satırından (CLI) çalışsın; web'den 403
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}
require_once __DIR__ . '/db.php';

$pdo = db();

echo "<pre>Seed işlemi başladı...\n";

// Örnek otobüs firması
$companyId = '1';
$pdo->prepare("INSERT OR IGNORE INTO bus_company (id, name, logo_path)
               VALUES (?, ?, ?)")
    ->execute([$companyId, 'Respect Turizm', 'assets/respect_logo.png']);

// Admin kullanıcı
$adminId = '1';
$adminPass = password_hash('admin123', PASSWORD_BCRYPT);
$pdo->prepare("INSERT OR IGNORE INTO user (id, full_name, email, password, role, balance)
               VALUES (?, ?, ?, ?, ?, 0)")
    ->execute([$adminId, 'Sistem Yöneticisi', 'admin@respect.com', $adminPass, 'admin']);

// Firma Admin (Respect Turizm firması için)
$firmaId = '2';
$firmaPass = password_hash('firma123', PASSWORD_BCRYPT);
$pdo->prepare("INSERT OR IGNORE INTO user (id, full_name, email, password, role, company_id, balance)
               VALUES (?, ?, ?, ?, ?, ?, 0)")
    ->execute([$firmaId, 'Respect Turizm Yetkilisi', 'firma@respect.com', $firmaPass, 'company', $companyId]);

// Örnek sefer
$tripId = '1001';
$pdo->prepare("INSERT OR IGNORE INTO trips (id, company_id, departure_city, destination_city, departure_time, price, capacity)
               VALUES (?, ?, ?, ?, ?, ?, ?)")
    ->execute([$tripId, $companyId, 'İstanbul', 'Ankara', '2025-10-25 10:00', 15000, 30]);

echo "Seed tamamlandı!\n";
echo "Admin hesabı: admin@respect.com / admin123\n";
echo "Firma hesabı: firma@respect.com / firma123\n";
echo "</pre>";
