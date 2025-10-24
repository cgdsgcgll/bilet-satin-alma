<?php require_once __DIR__ . '/init.php'; ?>
<?php
require_once __DIR__ . '/auth.php';

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF koruması
    require_csrf();

    // Giriş denemesi (auth.php içindeki login() fonksiyonunu kullanıyoruz)
    // login() fonksiyonunun password_verify() + session_regenerate_id(true)
    // yapması en doğrusu. (Yapmıyorsa haber ver, birlikte ekleyelim.)
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login($email, $password)) {
        // Başarılı giriş → güvenlik için tekrar yenilemek istersen (opsiyonel):
        // login_regenerate_session();

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
                <div class="alert"><?= e($err) ?></div>
            <?php endif; ?>

            <!-- Tek form: CSRF input bu formun içinde -->
            <form method="post" class="grid" style="grid-template-columns:1fr;gap:10px">
                <div>
                    <label class="helper">E-posta</label>
                    <input class="input" type="email" name="email" placeholder="ornek@mail.com"
                        value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>" required>
                </div>

                <div>
                    <label class="helper">Şifre</label>
                    <input class="input" type="password" name="password" placeholder="••••••••" required>
                </div>

                <!-- CSRF token -->
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

                <button class="btn primary">Giriş</button>
                <div class="helper">Hesabın yok mu? <a href="register.php">Kayıt ol</a></div>
            </form>
        </div>
    </main>
</body>

</html>