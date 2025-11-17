<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = array();

session_destroy();

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../app/config.php';
}

header('Location: ' . BASE_URL . '/admin/index.php');
exit;
?>