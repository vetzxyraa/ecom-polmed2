<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'templates/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$order_success = false;
$new_order_code = '';
$new_payment_method = '';
$new_order_details = []; 

if ($id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    } catch (PDOException $e) {
        set_flash_message('checkout_msg', 'Error DB', 'error');
    }
}

if (!$product) {
    header('Location: ' . BASE_URL . '/daftar_produk.php');
    exit;
}

if ($product['stock'] <= 0) {
    set_flash_message('home_msg', 'Stok Habis', 'error');
    header('Location: ' . BASE_URL . '/daftar_produk.php');
    exit;
}

$variants = !empty($product['available_variants']) ? array_map('trim', explode(',', $product['available_variants'])) : [];

$payment_bank_name = get_setting('payment_bank_name');
$payment_ewallet_name = get_setting('payment_ewallet_name');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama_pembeli']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $jumlah = (int)$_POST['jumlah'];
    $payment_method = trim($_POST['payment_method']);
    $selected_variant = isset($_POST['variant']) ? trim($_POST['variant']) : '';
    
    $variants_check = !empty($product['available_variants']) ? array_map('trim', explode(',', $product['available_variants'])) : [];

    if (empty($nama) || empty($no_hp) || empty($alamat) || $jumlah <= 0) {
        set_flash_message('checkout_msg', 'Lengkapi data.', 'error');
    } elseif (empty($payment_method)) {
        set_flash_message('checkout_msg', 'Pilih pembayaran.', 'error');
    } elseif (!empty($variants_check) && empty($selected_variant)) {
        set_flash_message('checkout_msg', 'Pilih varian.', 'error');
    } elseif (!empty($variants_check) && !in_array($selected_variant, $variants_check)) {
        set_flash_message('checkout_msg', 'Varian salah.', 'error');
    } elseif ($jumlah > $product['stock']) {
        set_flash_message('checkout_msg', "Stok kurang.", 'error');
    } else {
        try {
            $db->beginTransaction();
            
            $stmt_check = $db->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
            $stmt_check->execute([$id]);
            $current_stock = $stmt_check->fetchColumn();
            
            if ($jumlah > $current_stock) {
                throw new Exception("Stok habis.");
            }
            
            $kode_pesanan = "TB-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            
            $subtotal_harga = $product['price'] * $jumlah;
            $shipping_cost = (int)$product['shipping_cost']; 
            $total_harga = $subtotal_harga + $shipping_cost;
            
            $sql_insert = "INSERT INTO orders (product_id, order_code, customer_name, customer_phone, customer_address, 
                                           quantity, subtotal_price, shipping_cost, total_price, 
                                           status, payment_method, selected_variant) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)";
            $stmt_insert = $db->prepare($sql_insert);
            $stmt_insert->execute([
                $id, $kode_pesanan, $nama, $no_hp, $alamat, 
                $jumlah, $subtotal_harga, $shipping_cost, $total_harga,
                $payment_method, $selected_variant
            ]);
            
            $sql_update = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stmt_update = $db->prepare($sql_update);
            $stmt_update->execute([$jumlah, $id]);
            
            $db->commit();
            
            $order_success = true;
            $new_order_code = $kode_pesanan;
            $new_payment_method = $payment_method;
            $new_order_details = [
                'subtotal' => $subtotal_harga,
                'shipping' => $shipping_cost,
                'total' => $total_harga
            ];
            
        } catch (Exception $e) {
            $db->rollBack();
            set_flash_message('checkout_msg', 'Gagal.', 'error');
        }
    }
}

?>

<?php if ($order_success): ?>
    
    <div class="card status-card" style="text-align: center;">
        <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
        <h1 class="page-title" style="border: none; margin-bottom: 1rem;">Sukses!</h1>
        <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">Segera bayar.</p>
        
        <div class="message-box success">
            Kode: <strong><?php echo $new_order_code; ?></strong>
        </div>

        <div class="message-box info" style="text-align: left; margin-top: 1.5rem;">
            <strong>Transfer ke:</strong>
            <hr style="border: 0; border-top: 1px dashed var(--warning-border); margin: 0.75rem 0;">
            <p style="font-size: 1.1rem; font-weight: 600;">
                <?php echo htmlspecialchars($new_payment_method); ?>
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
            
             <hr style="border: 0; border-top: 1px dashed var(--warning-border); margin: 0.75rem 0;">
             <p style="margin-top: 1rem; font-size: 1rem;">
                Subtotal: <strong><?php echo format_rupiah($new_order_details['subtotal']); ?></strong>
             </p>
             <p style="font-size: 1rem;">
                Ongkir: <strong><?php echo format_rupiah($new_order_details['shipping']); ?></strong>
             </p>
             <p style="margin-top: 0.5rem; font-size: 1.2rem; font-weight: 700;">
                Total: <strong><?php echo format_rupiah($new_order_details['total']); ?></strong>
            </p>
        </div>
        
        <?php
            $wa_number = get_setting('shop_whatsapp');
            $wa_text = "Halo, saya mau konfirmasi bayar pesanan *" . $new_order_code . "*.";
            $wa_link = "https://api.whatsapp.com/send?phone=" . htmlspecialchars($wa_number) . "&text=" . urlencode($wa_text);
        ?>
        <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-primary" style="background-color: #25D366; margin-top: 1.5rem; width: 100%;">
            <i class="bi bi-whatsapp"></i> Konfirmasi WA
        </a>

        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
            <a href="status.php?kode_pesanan=<?php echo $new_order_code; ?>" class="btn btn-secondary">Status</a>
            <a href="daftar_produk.php" class="btn btn-secondary">Produk</a>
        </div>
    </div>

<?php else: ?>

    <h1 class="page-title">Checkout</h1>
    
    <div class="form-container card">
        
        <?php display_flash_message('checkout_msg'); ?>

        <div class="checkout-summary">
            <h3>Item:</h3>
            <p style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($product['name']); ?></p>
            <p><?php echo format_rupiah($product['price']); ?></p>
            <p>Ongkir: <?php echo format_rupiah($product['shipping_cost']); ?></p>
        </div>
        
        <form action="checkout.php?id=<?php echo $product['id']; ?>" method="POST">
            <div class="form-group">
                <label for="nama_pembeli">Nama</label>
                <input type="text" id="nama_pembeli" name="nama_pembeli" class="form-control" value="<?php echo htmlspecialchars($_POST['nama_pembeli'] ?? ''); ?>" placeholder="Nama Anda" required>
            </div>
            <div class="form-group">
                <label for="no_hp">No. HP</label>
                <input type="tel" id="no_hp" name="no_hp" class="form-control" placeholder="08..." value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" class="form-control" placeholder="Alamat lengkap" required><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
            </div>
            
            <?php if (!empty($variants)): ?>
            <div class="form-group">
                <label for="variant">Varian</label>
                <select id="variant" name="variant" class="form-control" required>
                    <option value="">- Pilih -</option>
                    <?php foreach ($variants as $variant): ?>
                        <option value="<?php echo htmlspecialchars($variant); ?>" <?php echo (($_POST['variant'] ?? '') == $variant) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($variant); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="jumlah">Jumlah</label>
                <input type="number" id="jumlah" name="jumlah" class="form-control" value="<?php echo htmlspecialchars($_POST['jumlah'] ?? '1'); ?>" min="1" max="<?php echo $product['stock']; ?>" required>
            </div>

            <div class="form-group">
                <label for="payment_method">Bayar Pakai</label>
                <select id="payment_method" name="payment_method" class="form-control" required>
                    <option value="">- Pilih -</option>
                    <?php if (!empty($payment_bank_name)): ?>
                        <option value="<?php echo htmlspecialchars($payment_bank_name); ?>" <?php echo (($_POST['payment_method'] ?? '') == $payment_bank_name) ? 'selected' : ''; ?>>
                            Transfer Bank (<?php echo htmlspecialchars($payment_bank_name); ?>)
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
                <i class="bi bi-bag-check-fill"></i> Proses
            </button>
        </form>
    </div>

<?php endif; ?>

<?php
require 'templates/footer.php';
?>