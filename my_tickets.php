<?php
require_once __DIR__ . '/auth.php';
require_login();
$me = current_user();

$toast = '';
if (isset($_GET['canceled']))
    $toast = 'Bilet iptal edildi.';
if (isset($_GET['canceledError']))
    $toast = 'İptal edilemedi.';
if (isset($_GET['ok']))
    $toast = 'Satın alma başarılı.';

$st = db()->prepare("
  SELECT tk.id,tk.status,tk.total_price,tk.created_at,
         t.departure_city,t.destination_city,t.departure_time,
         c.name firm_name, GROUP_CONCAT(bs.seat_number, ',') seats
  FROM tickets tk
  JOIN trips t ON t.id=tk.trip_id
  JOIN bus_company c ON c.id=t.company_id
  LEFT JOIN booked_seats bs ON bs.ticket_id=tk.id
  WHERE tk.user_id=?
  GROUP BY tk.id
  ORDER BY t.departure_time DESC
");
$st->execute([$me['id']]);
$rows = $st->fetchAll();
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Biletlerim</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <?php if ($toast): ?>
        <div class="toast" id="toast"><?= htmlspecialchars($toast) ?><span class="close"
                onclick="this.parentElement.remove()">✕</span></div>
        <script>setTimeout(() => { var t = document.getElementById('toast'); if (t) t.remove(); }, 3500);</script>
    <?php endif; ?>

    <header class="header">
        <h1 class="brand"><a href="index.php" style="color:#fff;text-decoration:none">Respect Ulaşım</a></h1>
        <nav><a href="index.php">Anasayfa</a><a class="active" href="my_tickets.php">Biletlerim</a><a
                href="add_credit.php">Bakiye</a><a href="profile.php">Profil</a><a href="logout.php">Çıkış</a></nav>
    </header>

    <main class="container">
        <div class="card">
            <h2>Biletlerim</h2>
            <?php if (!$rows): ?>
                <div class="empty">Henüz biletin yok.</div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Firma</th>
                            <th>Güzergâh</th>
                            <th>Kalkış</th>
                            <th>Koltuk</th>
                            <th>Durum</th>
                            <th>Ödenen</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><span class="badge gray"><?= htmlspecialchars($r['firm_name']) ?></span></td>
                                <td><?= htmlspecialchars($r['departure_city'] . ' → ' . $r['destination_city']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($r['departure_time'])) ?></td>
                                <td><?= $r['seats'] ?: '-' ?></td>
                                <td>
                                    <?php if ($r['status'] === 'active'): ?><span class="badge green">aktif</span>
                                    <?php elseif ($r['status'] === 'canceled'): ?><span class="badge red">iptal</span>
                                    <?php else: ?><span class="badge">diğer</span><?php endif; ?>
                                </td>
                                <td><b><?= number_format($r['total_price'] / 100, 2) ?> ₺</b></td>
                                <td>
                                    <?php if ($r['status'] === 'active'): ?>
                                        <a class="btn cancel sm" href="ticket_cancel.php?id=<?= $r['id'] ?>"
                                            onclick="return confirm('İptal edilsin mi?')">İptal</a>
                                    <?php endif; ?>
                                    <!-- PDF butonu kaldırıldı -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>