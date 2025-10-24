<?php
require_once __DIR__ . '/auth.php';
require_login();
$me = current_user();
$id = $_GET['id'] ?? '';

$st = db()->prepare("SELECT tk.id,tk.user_id,tk.status,tk.total_price,t.departure_time
                     FROM tickets tk JOIN trips t ON t.id=tk.trip_id WHERE tk.id=?");
$st->execute([$id]);
$tk = $st->fetch() ?: exit('Bilet yok');
if ($tk['user_id'] !== $me['id'])
    exit('Yetkisiz');
if ($tk['status'] !== 'active')
    exit('Bilet zaten iptal');
if (strtotime($tk['departure_time']) - time() < 3600) {
    header('Location: my_tickets.php?canceledError=1');
    exit;
}

db()->beginTransaction();
try {
    db()->prepare("UPDATE tickets SET status='canceled' WHERE id=?")->execute([$id]);
    db()->prepare("UPDATE user SET balance=balance+? WHERE id=?")->execute([(int) $tk['total_price'], $me['id']]);
    db()->commit();
    header('Location: my_tickets.php?canceled=1');
} catch (Throwable $e) {
    db()->rollBack();
    header('Location: my_tickets.php?canceledError=1');
}
