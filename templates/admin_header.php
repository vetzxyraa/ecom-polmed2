<?php
require_once __DIR__ . '/../app/init.php';

require_admin_login();

$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo get_setting('shop_name', 'Toko Online'); ?></title>
    
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/img/favicon.png" type="image/png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<header class="main-header admin-nav">
    <div class="container">
        <nav class="navbar">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="logo">
                <img src="<?php echo BASE_URL; ?>/assets/img/favicon.png" alt="Logo" style="height: 32px; width: auto;">
                <span>Admin Panel</span>
            </a>
            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">Pesanan</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/daftar_produk.php" class="<?php echo ($current_page == 'daftar_produk.php' || $current_page == 'kelola_produk.php') ? 'active' : ''; ?>">Produk</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/pengaturan.php" class="<?php echo ($current_page == 'pengaturan.php') ? 'active' : ''; ?>">Pengaturan</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/logout.php" class="btn btn-logout btn-sm">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="content-wrapper">
    <div class="container">