<?php
require_once __DIR__ . '/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (register($_POST['full_name'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '')) {
        header('Location: login.php');
        exit;
    } else {
        $err = 'Kayıt oluşturulamadı (e-posta kullanımda olabilir).';
    }
}
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kayıt Ol</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <main class="container" style="max-width:480px">
        <div class="card">
            <h2>Kayıt Ol</h2>
            <?php if ($err): ?>
                <div class="alert"><?= $err ?></div><?php endif; ?>
            <form method="post" class="grid" style="grid-template-columns:1fr;gap:10px">
                <div><label class="helper">Ad Soyad</label><input class="input" name="full_name" placeholder="Ad Soyad">
                </div>
                <div><label class="helper">E-posta</label><input class="input" name="email"
                        placeholder="ornek@mail.com"></div>
                <div><label class="helper">Şifre</label><input class="input" type="password" name="password"
                        placeholder="••••••••"></div>
                <button class="btn primary">Kayıt Ol</button>
                <div class="helper">Zaten üye misin? <a href="login.php">Giriş yap</a></div>
            </form>
        </div>
    </main>
</body>

</html>