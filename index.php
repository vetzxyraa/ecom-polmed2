<?php
require 'templates/header.php';

$shop_name = get_setting('shop_name', 'Toko Baru');
$about_text = get_setting('shop_about_text', 'Selamat datang di toko kami. Silakan atur deskripsi ini di panel admin.');
$hero_image = get_setting('shop_about_image');

if (empty($hero_image)) {
    $image_url = 'https://placehold.co/400x400/3B82F6/ffffff?text=Toko'; // <-- Warna disesuaikan ke biru
} elseif (!filter_var($hero_image, FILTER_VALIDATE_URL)) {
    $image_url = BASE_URL . '/assets/img/' . htmlspecialchars($hero_image);
} else {
    $image_url = htmlspecialchars($hero_image);
}
$fallback_url = 'https://placehold.co/400x400/e2e8f0/475569?text=Error';

?>

<section class="home-split-layout">
    <div class="home-text-content">
        <h1><?php echo htmlspecialchars($shop_name); ?></h1>
        <p><?php echo htmlspecialchars($about_text); ?></p>
    </div>
    
    <div class="home-image-content">
        <img src="<?php echo $image_url; ?>" 
             alt="Foto <?php echo $shop_name; ?>" 
             class="home-hero-image"
             onerror="this.onerror=null; this.src='<?php echo $fallback_url; ?>';">
             
        <a href="daftar_produk.php" class="btn btn-primary home-cta-button">
            <i class="bi bi-box-seam-fill"></i> Lihat Produk
        </a>
    </div>
</section>


<?php
require 'templates/footer.php';
?>