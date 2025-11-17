<?php
require 'templates/header.php';

$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];
$params = [];

$sql = "SELECT * FROM products";

if (!empty($search_term)) {
    $sql .= " WHERE name LIKE ?";
    $params[] = '%' . $search_term . '%';
}

$sql .= " ORDER BY stock > 0 DESC, id DESC";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<div class='message-box error'>Gagal memuat produk: " . $e->getMessage() . "</div>";
}
?>

<?php if (!empty($search_term)): ?>
    <h1 class="page-title">Hasil Pencarian: "<?php echo htmlspecialchars($search_term); ?>"</h1>
<?php else: ?>
    <h1 class="page-title">Semua Produk</h1>
<?php endif; ?>


<div class="product-grid">
    <?php if (empty($products)): ?>
        <?php if (!empty($search_term)): ?>
            <p style="grid-column: 1 / -1; text-align: center; font-size: 1.1rem;">
                Pencarian "<?php echo htmlspecialchars($search_term); ?>" tidak ditemukan.
            </p>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center; font-size: 1.1rem;">
                Belum ada produk.
            </p>
        <?php endif; ?>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card card">
                <a href="produk.php?id=<?php echo $product['id']; ?>" class="product-card-image">
                    <?php 
                    $image_url = $product['image'];
                    if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
                        $image_url = BASE_URL . '/assets/img/' . htmlspecialchars($image_url);
                    }
                    $fallback_url = 'https://placehold.co/400x400/e2e8f0/475569?text=Error';
                    ?>
                    <img src="<?php echo htmlspecialchars($image_url); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         onerror="this.onerror=null; this.src='<?php echo $fallback_url; ?>';">
                </a>
                <div class="product-card-content">
                    <h3 class="product-card-title">
                        <a href="produk.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                    </h3>
                    <p class="product-card-price"><?php echo format_rupiah($product['price']); ?></p>
                    <p class="product-card-stock">Stok: <?php echo $product['stock']; ?></p>
                    
                    <div class="product-card-actions">
                        <a href="produk.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">
                            <i class="bi bi-search"></i> Detail
                        </a>
                        <?php if ($product['stock'] > 0): ?>
                            <a href="checkout.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-cart-plus"></i> Beli
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm btn-disabled" disabled>
                                Stok Habis
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
require 'templates/footer.php';
?>