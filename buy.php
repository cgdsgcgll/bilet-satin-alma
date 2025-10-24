<?php require_once __DIR__ . '/init.php'; ?>
<?php
// buy.php – Koltuk satın alma + kupon uygulama + bakiye kontrolü
require_once __DIR__ . '/auth.php';
require_login();
$me = current_user();

$tripId = $_POST['trip_id'] ?? '';
$seat = (int) ($_POST['seat_no'] ?? 0);
$coupon = strtoupper(trim($_POST['coupon_code'] ?? '')); // kuponu normalize et (case-insensitive)

db()->beginTransaction();

try {
    // 1) Sefer bilgisi
    $t = db()->prepare("SELECT id, company_id, price, capacity FROM trips WHERE id=?");
    $t->execute([$tripId]);
    $trip = $t->fetch();
    if (!$trip) {
        throw new Exception('Sefer yok');
    }

    // 2) Koltuk uygun mu
    $c = db()->prepare("
        SELECT 1
        FROM booked_seats bs
        JOIN tickets tk ON tk.id = bs.ticket_id
        WHERE tk.trip_id = ? AND tk.status = 'active' AND bs.seat_number = ?
        LIMIT 1
    ");
    $c->execute([$tripId, $seat]);
    if ($c->fetch()) {
        throw new Exception('Koltuk dolu');
    }

    // 3) Fiyat 
    $price = (int) $trip['price'];

    // 4) Kupon kontrolü (firma uyumu + süre + toplam limit + kullanıcı tekrar kullanımı)
    if ($coupon !== '') {
        $cs = db()->prepare("
            SELECT * FROM coupons
            WHERE UPPER(code)=? AND (company_id IS NULL OR company_id=?)
        ");
        $cs->execute([$coupon, $trip['company_id']]);
        if ($cp = $cs->fetch()) {
            $nowOk = (strtotime($cp['expire_date']) > time());

            // toplam kullanım limiti
            $usedG = db()->prepare("SELECT COUNT(*) FROM user_coupons WHERE coupon_id=?");
            $usedG->execute([$cp['id']]);
            $globalOk = ((int) $usedG->fetchColumn() < (int) $cp['usage_limit']);

            // aynı kullanıcı tekrar kullanamasın 
            $usedU = db()->prepare("SELECT COUNT(*) FROM user_coupons WHERE coupon_id=? AND user_id=?");
            $usedU->execute([$cp['id'], $me['id']]);
            $userOk = ((int) $usedU->fetchColumn() === 0);

            if ($nowOk && $globalOk && $userOk) {
                $price = (int) round($price * (100 - (float) $cp['discount']) / 100);
                // kullanım kaydı
                db()->prepare("INSERT INTO user_coupons(id,coupon_id,user_id) VALUES(?,?,?)")
                    ->execute([uuid4(), $cp['id'], $me['id']]);
            }
            // değilse kupon yok sayılır
        }
        // kupon bulunamazsa da yok sayılır
    }

    // 5) Bakiye kontrolü – yetersizse yönlendir
    if ((int) $me['balance'] < $price) {
        db()->rollBack();
        $need = max(0, $price - (int) $me['balance']);
        $ret = 'trip.php?id=' . urlencode($tripId);
        header('Location: add_credit.php?amount=' . $need . '&return=' . $ret);
        exit;
    }

    // 6) Bilet oluştur
    $ticketId = uuid4();
    db()->prepare("INSERT INTO tickets(id,trip_id,user_id,status,total_price) VALUES(?,?,?,?,?)")
        ->execute([$ticketId, $tripId, $me['id'], 'active', $price]);

    // 7) Koltuk kaydı
    db()->prepare("INSERT INTO booked_seats(id,ticket_id,seat_number) VALUES(?,?,?)")
        ->execute([uuid4(), $ticketId, $seat]);

    // 8) Bakiyeden düş
    db()->prepare("UPDATE user SET balance=balance-? WHERE id=?")
        ->execute([$price, $me['id']]);

    db()->commit();
    header("Location: my_tickets.php?ok=1");
    exit;

} catch (Throwable $e) {
    db()->rollBack();
    exit("Satın alma hatası: " . htmlspecialchars($e->getMessage()));
}
