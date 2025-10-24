<?php
require_once __DIR__ . '/auth.php';
require_login();
$me = current_user();
if (($me['role'] ?? '') !== 'company')
    exit('Yetkisiz');

$companyId = $me['company_id'] ?: exit('Firma ataması yok');
$flash = '';
$err = '';
function P($k, $d = '')
{
    return trim($_POST[$k] ?? $d);
}

try {
    if (isset($_POST['add_trip'])) {
        $dep = P('departure_city');
        $dst = P('destination_city');
        $depT = str_replace('T', ' ', P('departure_time'));
        $arrT = str_replace('T', ' ', P('arrival_time'));
        $price = (int) P('price');
        $cap = (int) P('capacity', 44);
        if ($dep && $dst && $depT && $arrT && $price > 0 && $cap > 0) {
            db()->prepare("INSERT INTO trips(id,company_id,destination_city,arrival_time,departure_time,departure_city,price,capacity)
                           VALUES(?,?,?,?,?,?,?,?)")
                ->execute([uuid4(), $companyId, $dst, $arrT, $depT, $dep, $price, $cap]);
            $flash = 'Sefer eklendi';
        } else
            $err = 'Alanları kontrol edin';
    }
    if (isset($_POST['del_trip'])) {
        db()->prepare("DELETE FROM trips WHERE id=? AND company_id=?")->execute([$_POST['del_trip'], $companyId]);
        $flash = 'Sefer silindi';
    }
    if (isset($_POST['add_coupon'])) {
        $code = strtoupper(P('code'));
        $disc = (float) P('discount');
        $limit = (int) P('usage_limit', 100);
        $exp = str_replace('T', ' ', P('expire_date'));
        if ($code && $disc > 0) {
            db()->prepare("INSERT INTO coupons(id,code,discount,company_id,usage_limit,expire_date) VALUES(?,?,?,?,?,?)")
                ->execute([uuid4(), $code, $disc, $companyId, $limit, $exp]);
            $flash = 'Kupon eklendi';
        } else
            $err = 'Kupon bilgileri hatalı';
    }
    if (isset($_POST['del_coupon'])) {
        db()->prepare("DELETE FROM coupons WHERE id=? AND company_id=?")->execute([$_POST['del_coupon'], $companyId]);
        $flash = 'Kupon silindi';
    }
} catch (Throwable $e) {
    $err = $e->getMessage();
}

$tr = db()->prepare("SELECT * FROM trips WHERE company_id=? ORDER BY departure_time DESC");
$tr->execute([$companyId]);
$trips = $tr->fetchAll();
$cp = db()->prepare("SELECT * FROM coupons WHERE company_id=? ORDER BY id DESC");
$cp->execute([$companyId]);
$coupons = $cp->fetchAll();
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Firma Paneli</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <header class="header">
        <h1 class="brand"><a href="index.php" style="color:#fff;text-decoration:none">Respect Ulaşım</a></h1>
        <nav><a href="index.php">Anasayfa</a><a href="logout.php">Çıkış</a></nav>
    </header>
    <main class="container">
        <?php if ($flash): ?>
            <div class="alert success"><?= $flash ?></div><?php endif; ?>
        <?php if ($err): ?>
            <div class="alert"><?= $err ?></div><?php endif; ?>

        <div class="card">
            <h3>Seferler</h3>
            <form method="post" class="grid" style="grid-template-columns:repeat(6,1fr);gap:8px">
                <input class="input" name="departure_city" placeholder="Kalkış">
                <input class="input" name="destination_city" placeholder="Varış">
                <input class="input" type="datetime-local" name="departure_time">
                <input class="input" type="datetime-local" name="arrival_time">
                <input class="input" type="number" name="price" placeholder="Fiyat ">
                <input class="input" type="number" name="capacity" placeholder="Koltuk">
                <div style="grid-column:1/-1;text-align:right"><button class="btn primary" name="add_trip"
                        value="1">Ekle</button></div>
            </form>
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Güzergâh</th>
                        <th>Kalkış</th>
                        <th>Fiyat</th>
                        <th>Koltuk</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $t): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td><?= htmlspecialchars($t['departure_city'] . ' → ' . $t['destination_city']) ?></td>
                            <td><?= $t['departure_time'] ?></td>
                            <td><?= number_format($t['price'] / 100, 2) ?> ₺</td>
                            <td><?= $t['capacity'] ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Sefer silinsin mi?')">
                                    <button class="btn sm danger" name="del_trip" value="<?= $t['id'] ?>">Sil</button>
                                    <a class="btn sm" href="trip.php?id=<?= $t['id'] ?>">Gör</a>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach;
                    if (!$trips): ?>
                        <tr>
                            <td colspan="6">Sefer yok.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Kuponlar</h3>
            <form method="post" class="grid" style="grid-template-columns:repeat(4,1fr);gap:8px">
                <input class="input" name="code" placeholder="Kod">
                <input class="input" type="number" step="0.1" name="discount" placeholder="% Oran">
                <input class="input" type="number" name="usage_limit" placeholder="Kullanım Limiti">
                <input class="input" type="datetime-local" name="expire_date">
                <div style="grid-column:1/-1;text-align:right"><button class="btn primary" name="add_coupon"
                        value="1">Ekle</button></div>
            </form>
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kod</th>
                        <th>%</th>
                        <th>Limit</th>
                        <th>Bitiş</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><span class="badge"><?= $c['code'] ?></span></td>
                            <td><?= $c['discount'] ?></td>
                            <td><?= $c['usage_limit'] ?></td>
                            <td><?= $c['expire_date'] ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Kupon silinsin mi?')">
                                    <button class="btn sm danger" name="del_coupon" value="<?= $c['id'] ?>">Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach;
                    if (!$coupons): ?>
                        <tr>
                            <td colspan="6">Kupon yok.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>