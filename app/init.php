<?php
// File inisialisasi utama
// Mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set zona waktu
date_default_timezone_set('Asia/Jakarta');

// Muat konfigurasi database
require_once __DIR__ . '/config.php';

// Muat fungsi-fungsi
require_once __DIR__ . '/functions.php';

// Muat semua pengaturan ke dalam variabel global untuk digunakan di seluruh aplikasi
global $APP_SETTINGS;
$APP_SETTINGS = load_all_settings($db);

?>