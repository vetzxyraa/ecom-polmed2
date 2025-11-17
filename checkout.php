<?php
require 'templates/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$order_success = false;
$new_order_code = '';
$new_payment_method = '';

if ($id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    } catch (PDOException $e) {
        set_flash_message('checkout_msg', 'Error: ' . $e->getMessage(), 'error');
    }
}

if (!$product) {
    set_flash_message('home_msg', 'Produk tidak ditemukan.', 'error');
    header('Location: ' . BASE_URL . '/daftar_produk.php');
    exit;
}

if ($product['stock'] <= 0) {
    set_flash_message('home_msg', 'Stok produk ' . htmlspecialchars($product['name']) . ' telah habis.', 'error');
    header('Location: ' . BASE_URL . '/daftar_produk.php');
    exit;
}

$payment_bank_name = get_setting('payment_bank_name');
$payment_ewallet_name = get_setting('payment_ewallet_name');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama_pembeli']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $jumlah = (int)$_POST['jumlah'];
    $payment_method = trim($_POST['payment_method']);
    
    if (empty($nama) || empty($no_hp) || empty($alamat) || $jumlah <= 0) {
        set_flash_message('checkout_msg', 'Semua field wajib diisi dan jumlah harus valid.', 'error');
    } elseif (empty($payment_method)) {
        set_flash_message('checkout_msg', 'Silakan pilih metode pembayaran.', 'error');
    } elseif ($jumlah > $product['stock']) {
        set_flash_message('checkout_msg', "Jumlah pesanan melebihi stok. Sisa stok: {$product['stock']}", 'error');
    } else {
        try {
            $db->beginTransaction();
            
            $stmt_check = $db->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
            $stmt_check->execute([$id]);
            $current_stock = $stmt_check->fetchColumn();
            
            if ($jumlah > $current_stock) {
                throw new Exception("Stok tidak mencukupi saat proses checkout. Sisa stok: $current_stock");
            }
            
            $kode_pesanan = "TB-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            $total_harga = $product['price'] * $jumlah;
            
            $sql_insert = "INSERT INTO orders (product_id, order_code, customer_name, customer_phone, customer_address, quantity, total_price, status, payment_method) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
            $stmt_insert = $db->prepare($sql_insert);
            $stmt_insert->execute([$id, $kode_pesanan, $nama, $no_hp, $alamat, $jumlah, $total_harga, $payment_method]);
            
            $sql_update = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stmt_update = $db->prepare($sql_update);
            $stmt_update->execute([$jumlah, $id]);
            
            $db->commit();
            
            $order_success = true;
            $new_order_code = $kode_pesanan;
            $new_payment_method = $payment_method;
            
        } catch (Exception $e) {
            $db->rollBack();
            set_flash_message('checkout_msg', 'Gagal memproses pesanan: ' . $e->getMessage(), 'error');
        }
    }
}

?>

<?php if ($order_success): ?>
    
    <div class="card status-card" style="text-align: center;">
        <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
        <h1 class="page-title" style="border: none; margin-bottom: 1rem;">Pesanan Berhasil Dibuat!</h1>
        <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">Terima kasih. Segera selesaikan pembayaran Anda.</p>
        
        <div class="message-box success">
            Kode Pesanan Anda: <strong><?php echo $new_order_code; ?></strong>
            <br>
            <small>Simpan kode ini untuk cek status pesanan.</small>
        </div>

        <div class="message-box info" style="text-align: left; margin-top: 1.5rem;">
            <strong>Silakan lakukan pembayaran ke:</strong>
            <hr style="border: 0; border-top: 1px dashed var(--warning-border); margin: 0.75rem 0;">
            <p style="font-size: 1.1rem; font-weight: 600;">
                Metode: <?php echo htmlspecialchars($new_payment_method); ?>
            </p>
            
            <?php if ($new_payment_method == $payment_bank_name): ?>
                <p style="font-size: 1.2rem; font-weight: 700; margin-top: 0.5rem; color: var(--secondary-color);">
                    <?php echo htmlspecialchars(get_setting('payment_bank_account')); ?>
                </p>
            <?php elseif ($new_payment_method == $payment_ewallet_name): ?>
                <p style="font-size: 1.2rem; font-weight: 700; margin-top: 0.5rem; color: var(--secondary-color);">
                    <?php echo htmlspecialchars(get_setting('payment_ewallet_number')); ?>
                </p>
            <?php endif; ?>
            
             <p style="margin-top: 1rem;">
                Total Pembayaran: <strong><?php echo format_rupiah($product['price'] * (int)$_POST['jumlah']); ?></strong>
            </p>
            <small style="margin-top: 0.5rem; display: block;">Pesanan akan diproses setelah konfirmasi pembayaran.</small>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
            <a href="status.php?kode_pesanan=<?php echo $new_order_code; ?>" class="btn btn-primary">Lihat Status Pesanan</a>
            <a href="daftar_produk.php" class="btn btn-secondary">Kembali ke Produk</a>
        </div>
    </div>

<?php else: ?>

    <h1 class="page-title">Form Checkout</h1>
    
    <div class="form-container card">
        
        <?php display_flash_message('checkout_msg'); ?>

        <div class="checkout-summary">
            <h3>Produk yang Dipesan:</h3>
            <p style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($product['name']); ?></p>
            <p>Harga Satuan: <?php echo format_rupiah($product['price']); ?></p>
        </div>
        
        <form action="checkout.php?id=<?php echo $product['id']; ?>" method="POST">
            <div class="form-group">
                <label for="nama_pembeli">Nama Lengkap</label>
                <input type="text" id="nama_pembeli" name="nama_pembeli" class="form-control" value="<?php echo htmlspecialchars($_POST['nama_pembeli'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="no_hp">No. HP (WhatsApp)</label>
                <input type="tel" id="no_hp" name="no_hp" class="form-control" placeholder="0812..." value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="alamat">Alamat Lengkap</label>
                <textarea id="alamat" name="alamat" class="form-control" placeholder="Mohon isi alamat lengkap pengiriman." required><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="jumlah">Jumlah</label>
                <input type="number" id="jumlah" name="jumlah" class="form-control" value="<?php echo htmlspecialchars($_POST['jumlah'] ?? '1'); ?>" min="1" max="<?php echo $product['stock']; ?>" required>
                <small style="color: var(--text-light); margin-top: 5px; display: block;">Stok saat ini: <?php echo $product['stock']; ?></small>
            </div>

            <div class="form-group">
                <label for="payment_method">Metode Pembayaran</label>
                <select id="payment_method" name="payment_method" class="form-control" required>
                    <option value="">-- Pilih Pembayaran --</option>
                    <?php if (!empty($payment_bank_name)): ?>
                        <option value="<?php echo htmlspecialchars($payment_bank_name); ?>" <?php echo (($_POST['payment_method'] ?? '') == $payment_bank_name) ? 'selected' : ''; ?>>
                            Bank Transfer (<?php echo htmlspecialchars($payment_bank_name); ?>)
                        </option>
                    <?php endif; ?>
                    <?php if (!empty($payment_ewallet_name)): ?>
                         <option value="<?php echo htmlspecialchars($payment_ewallet_name); ?>" <?php echo (($_POST['payment_method'] ?? '') == $payment_ewallet_name) ? 'selected' : ''; ?>>
                            E-Wallet (<?php echo htmlspecialchars($payment_ewallet_name); ?>)
                        </option>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="bi bi-bag-check-fill"></i> Buat Pesanan
            </button>
        </form>
    </div>

<?php endif; ?>

<?php
require 'templates/footer.php';
?>