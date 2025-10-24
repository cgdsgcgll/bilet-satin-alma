<?php
require_once __DIR__ . '/auth.php';
$me = current_user();

$id = $_GET['id'] ?? '';
$st = db()->prepare("SELECT t.*,c.name firm_name FROM trips t JOIN bus_company c ON c.id=t.company_id WHERE t.id=?");
$st->execute([$id]);
$trip = $st->fetch() ?: die('Sefer bulunamadı');

$bs = db()->prepare("
  SELECT bs.seat_number FROM booked_seats bs
  JOIN tickets tk ON tk.id=bs.ticket_id
  WHERE tk.trip_id=? AND tk.status='active'
");
$bs->execute([$id]);
$busy = array_map(fn($r) => (int) $r['seat_number'], $bs->fetchAll());
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sefer Detayı</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/seats.css">
</head>

<body>
    <header class="header">
        <h1 class="brand"><a href="index.php" style="color:#fff;text-decoration:none">Respect Ulaşım</a></h1>
        <nav>
            <a href="index.php">Anasayfa</a>
            <?php if ($me): ?><a href="my_tickets.php">Biletlerim</a><a href="add_credit.php">Bakiye</a><a
                    href="profile.php">Profil</a>
                <?php if ($me['role'] === 'company'): ?><a href="firm.php">Firma Paneli</a><?php endif; ?>
                <?php if ($me['role'] === 'admin'): ?><a href="admin.php">Admin</a><?php endif; ?>
                <a href="logout.php">Çıkış</a>
            <?php else: ?><a href="login.php">Giriş</a><a href="register.php">Kayıt Ol</a><?php endif; ?>
        </nav>
    </header>

    <main class="container" style="max-width:820px">
        <div class="card">
            <h2><?= htmlspecialchars($trip['departure_city'] . ' → ' . $trip['destination_city']) ?></h2>
            <div class="kv">
                <div class="helper">Firma</div>
                <div><span class="badge blue"><?= htmlspecialchars($trip['firm_name']) ?></span></div>
                <div class="helper">Kalkış</div>
                <div><?= date('d.m.Y H:i', strtotime($trip['departure_time'])) ?></div>
                <div class="helper">Varış (tahmini)</div>
                <div><?= date('d.m.Y H:i', strtotime($trip['arrival_time'])) ?></div>
                <div class="helper">Fiyat</div>
                <div><b><?= number_format($trip['price'] / 100, 2) ?> ₺</b></div>
                <div class="helper">Kapasite</div>
                <div><?= (int) $trip['capacity'] ?> koltuk</div>
            </div>
        </div>

        <?php if (!$me): ?>
            <div class="card callout">
                <div>🔐</div>
                <div>
                    <div><b>Satın almak için giriş yapın</b></div>
                    <div class="helper">Koltuk seçimi yapabilir, giriş sonrası hızlıca satın alabilirsiniz.</div>
                </div>
                <div style="margin-left:auto"><a class="btn primary"
                        href="login.php?next=<?= urlencode('trip.php?id=' . $trip['id']) ?>">Giriş Yap</a></div>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>Koltuk Seçimi</h3>
            <form method="post" action="buy.php" id="buyForm" <?= !$me ? 'onsubmit="return false;"' : '' ?>>
                <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">

                <div class="seats-1plus2">
                    <?php
                    $total = (int) $trip['capacity'];
                    $rows = (int) ceil($total / 3);
                    $seat = 1;
                    for ($r = 0; $r < $rows; $r++):
                        echo '<div class="row">';
                        if ($seat <= $total) {
                            $busyf = in_array($seat, $busy, true);
                            $cls = $busyf ? 'seat busy' : 'seat';
                            echo "<label class='$cls'><input type='radio' name='seat_no' value='$seat' " . ($busyf ? 'disabled' : 'required') . "><span>$seat</span></label>";
                            $seat++;
                        }
                        echo '<div class="aisle"></div>';
                        for ($k = 0; $k < 2; $k++) {
                            if ($seat <= $total) {
                                $busyf = in_array($seat, $busy, true);
                                $cls = $busyf ? 'seat busy' : 'seat';
                                echo "<label class='$cls'><input type='radio' name='seat_no' value='$seat' " . ($busyf ? 'disabled' : 'required') . "><span>$seat</span></label>";
                                $seat++;
                            }
                        }
                        echo '</div>';
                    endfor;
                    ?>
                </div>

                <div class="grid" style="grid-template-columns:1fr auto;gap:8px;margin-top:12px">
                    <input class="input" id="coupon" name="coupon_code" placeholder="Kupon kodu (opsiyonel)">
                </div>

                <div id="buy-section" class="float-bar" style="display:none;margin-top:16px">
                    <div class="row" style="justify-content:space-between;width:100%">
                        <div>
                            <div class="helper">Seçilen koltuk</div>
                            <div id="chosenSeat" class="badge green">-</div>
                        </div>
                        <div style="text-align:right">
                            <div class="helper">Ödenecek Tutar</div>
                            <div><b id="priceText"><?= number_format($trip['price'] / 100, 2) ?> ₺</b></div>
                            <div class="helper" id="discNote" style="display:none"></div>
                        </div>
                    </div>
                    <div class="hr"></div>
                    <button class="btn primary block" type="submit" <?= !$me ? 'disabled' : '' ?>>Satın Al</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        const chosen = document.getElementById('chosenSeat');
        document.querySelectorAll('input[name="seat_no"]').forEach(i => {
            i.addEventListener('change', () => {
                document.querySelectorAll('.seat').forEach(s => s.classList.remove('chosen'));
                if (i.checked) {
                    i.closest('.seat').classList.add('chosen');
                    document.getElementById('buy-section').style.display = 'block';
                    chosen.textContent = i.value;
                }
            });
        });

        const basePrice = <?= (int) $trip['price'] ?>; // kuruş
        const priceText = document.getElementById('priceText');
        const discNote = document.getElementById('discNote');
        const couponInp = document.getElementById('coupon');
        function tl(kurus) { return (kurus / 100).toFixed(2) + ' ₺'; }
        let timer;
        couponInp.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(async () => {
                const code = couponInp.value.trim();
                if (code === '') { priceText.textContent = tl(basePrice); discNote.style.display = 'none'; return; }
                try {
                    const res = await fetch(`coupon_check.php?code=${encodeURIComponent(code)}&trip_id=<?= $trip['id'] ?>`);
                    const js = await res.json();
                    if (js.ok) {
                        priceText.textContent = tl(js.new_price);
                        discNote.textContent = `%${js.discount} indirim uygulandı`;
                        discNote.style.display = 'block';
                    } else {
                        priceText.textContent = tl(basePrice);
                        discNote.style.display = 'none';
                    }
                } catch (e) {
                    priceText.textContent = tl(basePrice);
                    discNote.style.display = 'none';
                }
            }, 300);
        });
    </script>
</body>

</html>