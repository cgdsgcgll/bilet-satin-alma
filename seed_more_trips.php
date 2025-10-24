<?php

// Birden fazla firma + her birine firma admini + bol sefer ekler.

require_once __DIR__ . '/db.php';

function addCompany($name, $logoPath = null)
{
    // Aynı isim varsa onu döndür
    $s = db()->prepare("SELECT id FROM bus_company WHERE name=?");
    $s->execute([$name]);
    if ($row = $s->fetch())
        return $row['id'];

    $id = uuid4();
    db()->prepare("INSERT INTO bus_company(id,name,logo_path) VALUES(?,?,?)")
        ->execute([$id, $name, $logoPath]);
    return $id;
}
function addCompanyAdmin($cid, $fullName, $email, $password)
{
    // E-posta varsa atla
    $s = db()->prepare("SELECT 1 FROM user WHERE email=?");
    $s->execute([$email]);
    if ($s->fetch())
        return;

    $hash = password_hash($password, PASSWORD_BCRYPT);
    db()->prepare("INSERT INTO user(id,full_name,email,password,role,company_id,balance)
                  VALUES(?,?,?,?,?,?,0)")
        ->execute([uuid4(), $fullName, $email, $hash, 'company', $cid]);
}
function addTrip($cid, $o, $d, DateTime $dep, DateTime $arr, $priceKurus, $cap = 45)
{
    db()->prepare("INSERT INTO trips(id,company_id,destination_city,arrival_time,departure_time,departure_city,price,capacity)
                  VALUES(?,?,?,?,?,?,?,?)")
        ->execute([uuid4(), $cid, $d, $arr->format('Y-m-d H:i:s'), $dep->format('Y-m-d H:i:s'), $o, $priceKurus, $cap]);
}
function guessPriceKurus(string $from, string $to): int
{
    if ($from === $to)
        return 20000;
    $base = 120; // TL
    $bonus = (crc32($from . $to) % 100); // 0–99 TL
    return ($base + $bonus) * 100;
}

// 1) Firma listesi (+ oluşturulacak firma admin hesapları)
$companies = [
    // name                      , admin name            , email                      , pass
    ['Respect Ulaşım', 'Respect Yetkilisi', 'respect@firma.com', 'respect123'],
    ['KentTur Seyahat', 'KentTur Yetkilisi', 'kenttur@firma.com', 'kenttur123'],
    ['Anadolu Ekspres', 'Anadolu Yetkilisi', 'anadolu@firma.com', 'anadolu123'],
    ['MaviYol Turizm', 'MaviYol Yetkilisi', 'maviyol@firma.com', 'maviyol123'],
    ['İpekYolu Otobüs', 'İpekYolu Yetkilisi', 'ipekyolu@firma.com', 'ipekyolu123'],
    ['Ege Ulaşım', 'Ege Yetkilisi', 'ege@firma.com', 'ege12345'],
    ['Karadeniz Ekspres', 'Karadeniz Yetkilisi', 'karadeniz@firma.com', 'karadeniz123'],
    ['DoğuAnadolu Turizm', 'DoğuAnadolu Yetkilisi', 'doguanadolu@firma.com', 'dogu12345'],
];

// 2) Şehirler ve kalkış saatleri
$cities = [
    'İstanbul',
    'Ankara',
    'İzmir',
    'Bursa',
    'Antalya',
    'Eskişehir',
    'Konya',
    'Kayseri',
    'Adana',
    'Gaziantep',
    'Samsun',
    'Trabzon',
    'Sakarya',
    'Kocaeli',
    'Çanakkale',
    'Balıkesir',
    'Niğde',
    'Konya'
];
$depTimes = ['07:30:00', '12:30:00', '17:45:00']; // günde 3 sefer
$days = 7; // 7 gün için

$addedCompanies = 0;
$addedAdmins = 0;
$addedTrips = 0;

// 3) Firmaları ve adminlerini ekle
foreach ($companies as [$name, $adminName, $email, $pass]) {
    $cid = addCompany($name);
    $addedCompanies++;

    addCompanyAdmin($cid, $adminName, $email, $pass);
    $addedAdmins++;

    // 4) Her firmaya çok sayıda sefer
    for ($d = 0; $d < $days; $d++) {
        foreach ($depTimes as $t) {
            $from = $cities[array_rand($cities)];
            do {
                $to = $cities[array_rand($cities)];
            } while ($to === $from);

            $dep = new DateTime("+$d day $t");
            $durationH = 3 + (crc32($from . $to . $t) % 6); // 3–8 saat
            $arr = (clone $dep)->modify("+$durationH hours");

            $price = guessPriceKurus($from, $to);
            $cap = 40 + ($durationH % 6); // 40–45

            addTrip($cid, $from, $to, $dep, $arr, $price, $cap);
            $addedTrips++;
        }
    }
}

$totalCompanies = db()->query("SELECT COUNT(*) FROM bus_company")->fetchColumn();
$totalTrips = db()->query("SELECT COUNT(*) FROM trips")->fetchColumn();

echo "<pre>";
echo "✓ Firmalar eklendi/var: $addedCompanies\n";
echo "✓ Firma adminleri eklendi/var: $addedAdmins\n";
echo "✓ Eklenen sefer sayısı: $addedTrips\n";
echo "Toplam firma: $totalCompanies\n";
echo "Toplam sefer: $totalTrips\n\n";
echo "Giriş bilgileri (firma admin örnekleri):\n";
foreach ($companies as [$name, $adminName, $email, $pass]) {
    echo " - $name → $email / $pass\n";
}
echo "\nArtık anasayfada (index.php) tüm seferleri ve arama ile filtreleri görebilirsin.\n";
echo "</pre>";
