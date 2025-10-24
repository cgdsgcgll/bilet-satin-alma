<?php
require_once __DIR__ . '/db.php';
session_start();

function current_user(): ?array
{
    if (!isset($_SESSION['uid']))
        return null;
    $st = db()->prepare("SELECT * FROM user WHERE id=?");
    $st->execute([$_SESSION['uid']]);
    return $st->fetch() ?: null;
}
function require_login(): void
{
    if (!current_user()) {
        header('Location: login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}
function login(string $email, string $password): bool
{
    $s = db()->prepare("SELECT * FROM user WHERE email=?");
    $s->execute([$email]);
    $u = $s->fetch();
    if ($u && password_verify($password, $u['password'])) {
        $_SESSION['uid'] = $u['id'];
        return true;
    }
    return false;
}
function register(string $name, string $email, string $password): bool
{
    $id = uuid4();
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $s = db()->prepare(
        "INSERT INTO user(id,full_name,email,password,role,balance) VALUES(?,?,?,?,?,0)"
    );
    try {
        $s->execute([$id, $name, $email, $hash, 'user']);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}
