<?php
require_once __DIR__ . '/auth.php';
require_login();
$me = current_user();
if (($me['role'] ?? '') !== 'admin')
    exit('Yetkisiz');

$msg = '';
$err = '';
$companyFilter = $_GET['company_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_company'])) {
            db()->prepare("INSERT INTO bus_company(id,name) VALUES(?,?)")->execute([uuid4(), trim($_POST['name'])]);
            $msg = 'Firma eklendi';
        }
        if (isset($_POST['add_company_admin'])) {
            $cid = $_POST['company_id'];
            $email = trim($_POST['email']);
            $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
            db()->prepare("INSERT INTO user(id,full_name,email,password,role,company_id,balance) VALUES(?,?,?,?,?,?,0)")
                ->execute([uuid4(), trim($_POST['full_name']), $email, $hash, 'company', $cid]);
            $msg = 'Firma admini eklendi';
        }
        if (isset($_POST['del_trip'])) {
            db()->prepare("DELETE FROM trips WHERE id=?")->execute([$_POST['del_trip']]);
            $msg = 'Sefer iptal edildi (silindi)';
        }
    } catch (Throwable $e) {
        $err = $e->getMessage();
    }
}
$companies = db()->query("SELECT * FROM bus_company ORDER BY name")->fetchAll();

$tr = db()->prepare("
  SELECT t.*,c.name firm_name FROM trips t
  JOIN bus_company c ON c.id=t.company_id
  WHERE (:cid='' OR t.company_id=:cid)
  ORDER BY t.departure_time DESC
");
$tr->execute([':cid' => $companyFilter]);
$trips = $tr->fetchAll();
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <header class="header">
        <h1 class="brand"><a href="index.php" style="color:#fff;text-decoration:none">Respect Ulaşım</a></h1>
        <nav><a href="index.php">Anasayfa</a><a href="logout.php">Çıkış</a></nav>
    </header>

    <main class="container">
        <?php if ($msg): ?>
            <div class="alert success"><?= $msg ?></div><?php endif; ?>
        <?php if ($err): ?>
            <div class="alert"><?= $err ?></div><?php endif; ?>

        <div class="card">
            <h2>Firmalar</h2>
            <form method="post" class="grid" style="grid-template-columns:1fr auto;gap:8px;margin-bottom:8px">
                <input class="input" name="name" placeholder="Firma Adı">
                <button class="btn primary" name="add_company" value="1">Ekle</button>
            </form>

            <h3>Firma Admini Oluştur</h3>
            <form method="post" class="grid" style="grid-template-columns:repeat(5,1fr);gap:8px">
                <select class="input" name="company_id">
                    <?php foreach ($companies as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option><?php endforeach; ?>
                </select>
                <input class="input" name="full_name" placeholder="Ad Soyad">
                <input class="input" name="email" placeholder="E-posta">
                <input class="input" name="password" placeholder="Şifre">
                <button class="btn primary" name="add_company_admin" value="1">Oluştur</button>
            </form>
        </div>

        <div class="card">
            <h2>Seferler</h2>
            <form class="row" method="get" style="margin-bottom:10px">
                <select class="input" style="max-width:280px" name="company_id" onchange="this.form.submit()">
                    <option value="">Tüm firmalar</option>
                    <?php foreach ($companies as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $companyFilter === $c['id'] ? 'selected' : '' ?>><?= $c['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="helper">Seçili firmaya ait seferleri gösterir.</div>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>Firma</th>
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
                            <td><span class="badge gray"><?= htmlspecialchars($t['firm_name']) ?></span></td>
                            <td><?= htmlspecialchars($t['departure_city'] . ' → ' . $t['destination_city']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($t['departure_time'])) ?></td>
                            <td><?= number_format($t['price'] / 100, 2) ?> ₺</td>
                            <td><?= $t['capacity'] ?></td>
                            <td class="row">
                                <a class="btn link sm" href="trip.php?id=<?= $t['id'] ?>">Gör</a>
                                <form method="post" onsubmit="return confirm('Bu sefer iptal edilsin mi?')">
                                    <button class="btn cancel sm" name="del_trip" value="<?= $t['id'] ?>">Seferi İptal
                                        Et</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach;
                    if (!$trips): ?>
                        <tr>
                            <td colspan="6" class="empty">Sefer yok.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>