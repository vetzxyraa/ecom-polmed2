<?php
require 'templates/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

if ($id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    } catch (PDOException $e) {
        echo "<div class='message-box error'>Error: " . $e->getMessage() . "</div>";
    }
}

if (!$product) {
    echo "<h1 class='page-title'>Produk Tidak Ditemukan</h1>";
    echo "<p>Produk yang Anda cari tidak ada atau telah dihapus.</p>";
    echo "<a href='index.php' class='btn btn-primary'>Kembali ke Home</a>";
    require 'templates/footer.php';
    exit;
}

$image_url = $product['image'];
if (empty($image_url)) {
    $image_url = 'https://placehold.co/600x600/e2e8f0/475569?text=Produk';
} elseif (!filter_var($image_url, FILTER_VALIDATE_URL)) {
    $image_url = BASE_URL . '/assets/img/' . htmlspecialchars($image_url);
}

$fallback_url = 'https://placehold.co/600x600/e2e8f0/475569?text=Error';
?>

<div class="product-detail-layout card">
    <div class="product-detail-gallery">
        <img src="<?php echo htmlspecialchars($image_url); ?>" 
             alt="<?php echo htmlspecialchars($product['name']); ?>"
             onerror="this.onerror=null; this.src='<?php echo $fallback_url; ?>';">
    </div>
    
    <div class="product-detail-info">
        <h1 class="name"><?php echo htmlspecialchars($product['name']); ?></h1>
        <p class="price"><?php echo format_rupiah($product['price']); ?></p>
        
        <?php if ($product['stock'] > 0): ?>
            <p class="stock">Stok Tersedia: <?php echo $product['stock']; ?></p>
        <?php else: ?>
            <p class="stock habis">Stok Habis</p>
        <?php endif; ?>

        <div class="description">
            <h3>Deskripsi Produk</h3>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
        
        <div class="product-detail-actions">
            <?php if ($product['stock'] > 0): ?>
                <a href="checkout.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                    <i class="bi bi-cart-plus-fill"></i> Beli Sekarang
                </a>
            <?php else: ?>
                <button class="btn btn-disabled" disabled>Stok Habis</button>
            <?php endif; ?>
            
            <?php
                $wa_number = get_setting('shop_whatsapp', '6281234567890');
                $wa_text = urlencode("Halo, saya tertarik dengan produk \"" . $product['name'] . "\". Apakah masih tersedia?");
                $wa_link = "https://api.whatsapp.com/send?phone={$wa_number}&text={$wa_text}";
            ?>
            <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-secondary btn-whatsapp">
                <i class="bi bi-whatsapp"></i> Tanya via WhatsApp
            </a>
        </div>
    </div>
</div>

<?php
require 'templates/footer.php';
?>