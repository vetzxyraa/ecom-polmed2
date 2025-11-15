<?php
// Selalu mulai session untuk mengakses dan menghapusnya
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Redirect ke halaman login
// Ambil BASE_URL dari config jika init.php tidak di-include
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../app/config.php';
}

header('Location: ' . BASE_URL . '/admin/index.php');
exit;
?>