<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../app/init.php';

function is_active($page_name) {
    $current_page = basename($_SERVER['PHP_SELF']);
    if (in_array($current_page, ['daftar_produk.php', 'produk.php', 'checkout.php']) && $page_name == 'daftar_produk.php') {
        return 'active';
    }
    return ($current_page == $page_name) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_setting('shop_name', 'Toko Baru'); ?></title>
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<header class="main-header">
    <div class="container">
        <nav class="navbar">
            <a href="<?php echo BASE_URL; ?>/index.php" class="logo">
                <i class="bi bi-shop"></i> <?php echo get_setting('shop_name', 'Toko Baru'); ?>
            </a>
            
            <form action="<?php echo BASE_URL; ?>/daftar_produk.php" method="GET" class="search-form">
                <input type="search" name="q" class="form-control" placeholder="Cari produk..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <button class="hamburger-btn" id="hamburger-btn" aria-label="Buka Menu" aria-expanded="false">
                <i class="bi bi-list"></i>
            </button>
            
            <ul class="nav-links" id="nav-menu">
                <li><a href="<?php echo BASE_URL; ?>/index.php" class="<?php echo is_active('index.php'); ?>">Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>/daftar_produk.php" class="<?php echo is_active('daftar_produk.php'); ?>">Produk</a></li>
                <li><a href="<?php echo BASE_URL; ?>/status.php" class="<?php echo is_active('status.php'); ?>">Cek Status</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/" class="btn btn-secondary btn-sm">Admin Panel</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="content-wrapper container">
    <?php display_flash_message('home_msg'); ?>