<?php require_once __DIR__ . '/init.php'; ?>
<?php
require_once __DIR__ . '/auth.php';
$me = current_user();

$o = trim($_GET['o'] ?? '');
$d = trim($_GET['d'] ?? '');

$sql = "
  SELECT t.id, t.departure_city, t.destination_city, t.departure_time, t.price,
         c.name AS firm_name
  FROM trips t
  JOIN bus_company c ON c.id = t.company_id
";
$conds = [];
$params = [];
if ($o !== '') {
    $conds[] = "t.departure_city LIKE :ol";
    $params[':ol'] = "%$o%";
}
if ($d !== '') {
    $conds[] = "t.destination_city LIKE :dl";
    $params[':dl'] = "%$d%";
}
if ($conds) {
    $sql .= " WHERE " . implode(" AND ", $conds);
}
$sql .= " ORDER BY t.departure_time ASC";

$st = db()->prepare($sql);
$st->execute($params);
$trips = $st->fetchAll();
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sefer Ara</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <header class="header">
        <h1 class="brand"><a href="index.php" style="color:#fff;text-decoration:none">Respect Ulaşım</a></h1>
        <nav>
            <a href="index.php">Anasayfa</a>
            <?php if ($me): ?>
                <a href="my_tickets.php">Biletlerim</a>
                <a href="add_credit.php">Bakiye</a>
                <a href="profile.php">Profil</a>
                <?php if ($me['role'] === 'company'): ?><a href="firm.php">Firma Paneli</a><?php endif; ?>
                <?php if ($me['role'] === 'admin'): ?><a href="admin.php">Admin</a><?php endif; ?>
                <a href="logout.php">Çıkış</a>
            <?php else: ?>
                <a href="login.php">Giriş</a><a href="register.php">Kayıt Ol</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container">
        <div class="card">
            <h2>Sefer Ara</h2>
            <form method="get" class="grid" style="grid-template-columns:1.2fr 1.2fr auto;gap:10px;align-items:end">
                <div><label class="helper">Kalkış</label><input class="input" name="o" placeholder="İl/İlçe"
                        value="<?= htmlspecialchars($o) ?>"></div>
                <div><label class="helper">Varış</label><input class="input" name="d" placeholder="İl/İlçe"
                        value="<?= htmlspecialchars($d) ?>"></div>
                <button class="btn primary">Ara</button>
            </form>
        </div>

        <div class="card">
            <h3><?= ($o === '' && $d === '') ? 'Tüm Seferler' : 'Sonuçlar' ?></h3>
            <?php if (!$trips): ?>
                <div class="empty">Aramanızla eşleşen sefer bulunamadı.</div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Firma</th>
                            <th>Güzergâh</th>
                            <th>Kalkış</th>
                            <th>Fiyat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trips as $t): ?>
                            <tr>
                                <td><span class="badge blue"><?= htmlspecialchars($t['firm_name']) ?></span></td>
                                <td><?= htmlspecialchars($t['departure_city'] . ' → ' . $t['destination_city']) ?></td>
                                <td><span class="badge time"><?= date('d.m.Y H:i', strtotime($t['departure_time'])) ?></span>
                                </td>
                                <td><span class="badge price"><b><?= number_format($t['price'] / 100, 2) ?> ₺</b></span></td>
                                <td><a class="btn link sm" href="trip.php?id=<?= $t['id'] ?>">Detay</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>