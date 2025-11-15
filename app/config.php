<?php
// Konfigurasi Database Baru (Menggunakan PDO)

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'toko_baru'); // Sesuaikan dengan nama database baru Anda

// Opsi PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// DSN (Data Source Name)
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

try {
    // Buat instance PDO
    $db = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // Tangani error koneksi
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Set base URL
// Ganti 'http://localhost/nama_folder_proyek_baru' sesuai dengan URL Anda
define('BASE_URL', 'http://localhost/ecom');
?>