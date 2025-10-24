<?php
// Oturum çerezini sertleştir
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']), // HTTPS varsa Secure bayrağı
    'samesite' => 'Lax',
]);
session_start();

// CSRF token hazırla
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Giriş sonrası oturumu yenile (session fixation önlemi)
function login_regenerate_session()
{
    session_regenerate_id(true);
}

// CSRF doğrulaması
function require_csrf(): void
{
    $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '');
    if (!$ok) {
        http_response_code(400);
        exit('Invalid CSRF');
    }
}

// XSS'e karşı çıktı kaçırma
function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
