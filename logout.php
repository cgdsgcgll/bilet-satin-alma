<?php require_once __DIR__ . '/init.php'; ?>
<?php
require_once __DIR__ . '/auth.php';
session_destroy();
header('Location: index.php');
