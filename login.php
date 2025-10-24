<?php
require_once __DIR__ . '/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        header('Location: ' . ($_GET['next'] ?? 'index.php'));
        exit;
    } else {
        $err = 'Hatalı e-posta veya şifre.';
    }
}
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Giriş</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <main class="container" style="max-width:420px">
        <div class="card">
            <h2>Giriş Yap</h2>
            <?php if ($err): ?>
                <div class="alert"><?= $err ?></div><?php endif; ?>
            <form method="post" class="grid" style="grid-template-columns:1fr;gap:10px">
                <div><label class="helper">E-posta</label><input class="input" name="email"
                        placeholder="ornek@mail.com"></div>
                <div><label class="helper">Şifre</label><input class="input" type="password" name="password"
                        placeholder="••••••••"></div>
                <button class="btn primary">Giriş</button>
                <div class="helper">Hesabın yok mu? <a href="register.php">Kayıt ol</a></div>
            </form>
        </div>
    </main>
</body>

</html>