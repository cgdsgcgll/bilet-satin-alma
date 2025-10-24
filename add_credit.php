<?php
require_once __DIR__ . '/auth.php';
require_login();
$me = current_user();

$prefill = (int) ($_GET['amount'] ?? 50000);
$return = $_GET['return'] ?? 'my_tickets.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (int) ($_POST['amount'] ?? 0);
    $return = $_POST['return'] ?? 'my_tickets.php';
    if ($amount > 0) {
        db()->prepare("UPDATE user SET balance=balance+? WHERE id=?")->execute([$amount, $me['id']]);
        header('Location: ' . $return);
        exit;
    }
}
$me = current_user();
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Bakiye Yükle</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <header class="header">
        <h1 class="brand"><a href="index.php" style="color:#fff;text-decoration:none">Respect Ulaşım</a></h1>
        <nav><a href="index.php">Anasayfa</a><a href="my_tickets.php">Biletlerim</a><a href="logout.php">Çıkış</a></nav>
    </header>

    <main class="container" style="max-width:700px">
        <div class="card">
            <h2>Kart ile Bakiye Yükle</h2>
            <p><b>Mevcut bakiye:</b> <?= number_format($me['balance'] / 100, 2) ?> ₺</p>
            <form method="post" class="grid" style="grid-template-columns:1fr;gap:12px">
                <input type="hidden" name="return" value="<?= htmlspecialchars($return) ?>">
                <div>
                    <label class="helper">Tutar </label>
                    <input class="input" name="amount" type="number" min="1000" step="500" value="<?= $prefill ?>">
                </div>
                <input class="input" value="5555 4444 3333 2222" readonly>
                <div class="grid" style="grid-template-columns:1fr 1fr;gap:10px">
                    <input class="input" value="11/29" readonly>
                    <input class="input" value="123" readonly>
                </div>
                <button class="btn primary">Öde ve Yükle</button>
                <div class="helper">Minimum yükleme: 10,00 ₺</div>
            </form>
        </div>
    </main>
</body>

</html>