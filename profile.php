<?php
require_once __DIR__ . '/auth.php';
require_login();
$me = current_user();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['full_name'] ?? '');
  $pass = trim($_POST['password'] ?? '');
  if ($name !== '')
    db()->prepare("UPDATE user SET full_name=? WHERE id=?")->execute([$name, $me['id']]);
  if ($pass !== '')
    db()->prepare("UPDATE user SET password=? WHERE id=?")->execute([password_hash($pass, PASSWORD_BCRYPT), $me['id']]);
  $msg = 'Kaydedildi.';
  $me = current_user();
}
?>
<!doctype html>
<html lang="tr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Profil</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <header class="header">
    <h1 class="brand"><a href="index.php" style="color:#fff;text-decoration:none">Respect Ulaşım</a></h1>
    <nav><a href="index.php">Anasayfa</a><a href="my_tickets.php">Biletlerim</a><a href="logout.php">Çıkış</a></nav>
  </header>
  <main class="container" style="max-width:720px">
    <div class="card">
      <h2>Profil</h2>
      <?php if ($msg): ?>
        <div class="alert success"><?= $msg ?></div><?php endif; ?>
      <form method="post" class="grid" style="grid-template-columns:1fr;gap:10px">
        <div><label class="helper">Ad Soyad</label><input class="input" name="full_name"
            value="<?= htmlspecialchars($me['full_name']) ?>"></div>
        <div><label class="helper">E-posta</label><input class="input" value="<?= htmlspecialchars($me['email']) ?>"
            disabled></div>
        <div><label class="helper">Yeni Şifre (isteğe bağlı)</label><input class="input" name="password"
            placeholder="••••••••"></div>
        <div style="text-align:right"><button class="btn primary">Kaydet</button></div>
      </form>
    </div>
  </main>
</body>

</html>