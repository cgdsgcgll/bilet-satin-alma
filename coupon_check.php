<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$code = strtoupper(trim($_GET['code'] ?? ''));   // normalize
$tripId = $_GET['trip_id'] ?? '';

if ($code === '' || $tripId === '') {
    echo json_encode(['ok' => false]);
    exit;
}

$st = db()->prepare("SELECT price, company_id FROM trips WHERE id=?");
$st->execute([$tripId]);
$trip = $st->fetch();
if (!$trip) {
    echo json_encode(['ok' => false]);
    exit;
}

$cs = db()->prepare("
  SELECT * FROM coupons
  WHERE UPPER(code)=? AND (company_id IS NULL OR company_id=?)
");
$cs->execute([$code, $trip['company_id']]);
$cp = $cs->fetch();
if (!$cp) {
    echo json_encode(['ok' => false]);
    exit;
}

$used = db()->prepare("SELECT COUNT(*) FROM user_coupons WHERE coupon_id=?");
$used->execute([$cp['id']]);

$valid = ((int) $used->fetchColumn() < (int) $cp['usage_limit']) && (strtotime($cp['expire_date']) > time());
if (!$valid) {
    echo json_encode(['ok' => false]);
    exit;
}

$price = (int) $trip['price'];
$newPrice = (int) round($price * (100 - (float) $cp['discount']) / 100);

echo json_encode([
    'ok' => true,
    'discount' => (float) $cp['discount'],
    'price' => $price,
    'new_price' => $newPrice
]);
