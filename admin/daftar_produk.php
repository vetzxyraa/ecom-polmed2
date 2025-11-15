<?php
require '../templates/admin_header.php'; // Sudah termasuk init.php & cek login

// Cek pesan flash dari aksi tambah/edit/hapus
display_flash_message('product_msg');

// Ambil semua produk
$products = [];
try {
    $products = $db->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    echo "<div class='message-box error'>Gagal memuat produk: " . $e->getMessage() . "</div>";
}
?>

<div class="admin-header">
    <h1 class="page-title">Manajemen Produk</h1>
    <a href="kelola_produk.php" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill"></i> Tambah Produk Baru
    </a>
</div>

<div class="card table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 10%;">Gambar</th>
                <th style="width: 35%;">Nama Produk</th>
                <th style="width: 20%;">Harga</th>
                <th style="width: 10%;">Stok</th>
                <th style="width: 25%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Belum ada produk.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <?php
                        // PERUBAHAN: Hanya 'image'
                        $img_url = $product['image'];
                        if (!filter_var($img_url, FILTER_VALIDATE_URL)) {
                            $img_url = BASE_URL . '/assets/img/' . htmlspecialchars($img_url);
                        }
                        $fallback_url = 'https://placehold.co/100x100/e2e8f0/475569?text=N/A';
                        ?>
                        <img src="<?php echo $img_url; ?>" alt="" class="product-thumb" onerror="this.onerror=null; this.src='<?php echo $fallback_url; ?>';">
                    </td>
                    <td><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                    <td><?php echo format_rupiah($product['price']); ?></td>
                    <!-- PERBAIKAN: Mengganti 'stok' menjadi 'stock' -->
                    <td><?php echo $product['stock']; ?></td>
                    <td>
                        <div class="actions">
                            <a href="kelola_produk.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm" title="Edit">
                                <i class="bi bi-pencil-fill"></i> Edit
                            </a>
                            <a href="kelola_produk.php?action=delete&id=<?php echo $product['id']; ?>" 
                               class="btn btn-danger btn-sm" 
                               title="Hapus"
                               onclick="return confirm('Anda yakin ingin menghapus produk ini? Pesanan terkait akan tetap ada, namun produk tidak bisa dipesan lagi.');">
                                <i class="bi bi-trash-fill"></i> Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require '../templates/admin_footer.php';
?>