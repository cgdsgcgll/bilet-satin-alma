<?php require_once __DIR__ . '/init.php'; ?>
<?php
require_once __DIR__ . '/auth.php';
require_login();
$me = current_user();

$prefill = (int) ($_GET['amount'] ?? 0);
$return = $_GET['return'] ?? 'add_credit.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (int) ($_POST['amount'] ?? 0);   // kuruş
    $return = $_POST['return'] ?? 'add_credit.php';

    if ($amount < 1000) {
        $err = "Minimum 10,00 ₺ yüklenebilir.";
    } else {

        $card = $_POST['card'] ?? '';
        $exp = $_POST['exp'] ?? '';
        $cvc = $_POST['cvc'] ?? '';

        if (strlen(preg_replace('/\D/', '', $card)) < 12 || strlen(trim($cvc)) < 3) {
            $err = "Kart bilgileri hatalı.";
        } else {

            db()->prepare("UPDATE user SET balance = balance + ? WHERE id = ?")
                ->execute([$amount, $me['id']]);


            header('Location: ' . $return . '?ok=1');
            exit;
        }
    }
}

// Ekranda doğru görünmesi için güncel bakiyenin çekimi
$me = current_user();
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kartla Bakiye Yükle</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <header class="header">
        <h1 class="brand"><a href="index.php" style="color:#fff;text-decoration:none">Respect Ulaşım</a></h1>
        <nav>
            <a href="index.php">Anasayfa</a>
            <a href="my_tickets.php">Biletlerim</a>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Çıkış</a>
        </nav>
    </header>

    <main class="container" style="max-width:720px">
        <div class="card">
            <h2 style="margin-top:0">Kart ile Bakiye Yükle</h2>
            <p><b>Mevcut bakiye:</b> <?= number_format(($me['balance'] ?? 0) / 100, 2) ?> ₺</p>

            <?php if (!empty($err)): ?>
                <div class="alert"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="return" value="<?= htmlspecialchars($return) ?>">

                <div class="form-row" style="margin-bottom:8px">
                    <label for="amount">Tutar (kuruş)</label>
                    <input class="input" id="amount" name="amount" type="number" min="1000" step="500"
                        value="<?= $prefill ?: 10000 ?>">
                </div>

                <div class="form-row" style="margin-bottom:8px">
                    <label for="card">Kart Numarası</label>
                    <input class="input" id="card" name="card" placeholder="5555 4444 3333 2222" inputmode="numeric">
                </div>

                <div class="form-row" style="margin-bottom:8px">
                    <label>SKT / CVC</label>
                    <div class="grid" style="grid-template-columns:1fr 1fr; gap:8px">
                        <input class="input" name="exp" placeholder="AA/YY">
                        <input class="input" name="cvc" placeholder="123" inputmode="numeric">
                    </div>
                </div>

                <div class="helper" style="color:#6b7280;margin-bottom:8px">Minimum yükleme: 10,00 ₺</div>

                <div class="pay-actions" style="text-align:right">
                    <button class="btn primary">Öde ve Yükle</button>
                </div>
            </form>
            <form method="post" action="">
                <!-- ... mevcut form alanların ... -->
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <button type="submit">Gönder</button>
            </form>

        </div>
    </main>
</body>

</html>