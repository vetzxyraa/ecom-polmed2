<?php
// Hapus 3 baris ini jika web sudah normal
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'templates/header.php';

$order_code = isset($_GET['kode_pesanan']) ? trim($_GET['kode_pesanan']) : '';
$order = null;

if (!empty($order_code)) {
    try {
        $sql = "SELECT o.*, p.name as product_name 
                FROM orders o 
                JOIN products p ON o.product_id = p.id 
                WHERE o.order_code = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$order_code]);
        $order = $stmt->fetch();

        if (!$order) {
            set_flash_message('status_msg', 'Kode pesanan tidak ditemukan.', 'error');
        }
    } catch (PDOException $e) {
        set_flash_message('status_msg', 'Error database: ' . $e->getMessage(), 'error');
    }
}
?>

<h1 class="page-title">Cek Status Pesanan</h1>

<div class="form-container card" style="margin-bottom: 2rem;">
    <?php display_flash_message('status_msg'); ?>
    
    <form action="status.php" method="GET">
        <div class="form-group">
            <label for="kode_pesanan">Masukkan Kode Pesanan Anda</label>
            <input type="text" id="kode_pesanan" name="kode_pesanan" class="form-control" value="<?php echo htmlspecialchars($order_code); ?>" placeholder="Masukkan kode pesanan..." required>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-search"></i> Cari Pesanan
        </button>
    </form>
</div>

<?php if ($order): ?>
<div class="card status-card">
    <h2>Detail Pesanan: <?php echo htmlspecialchars($order['order_code']); ?></h2>
    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 1rem 0;">

    <p class="status-info">
        <strong>Status:</strong> 
        <?php
            $status = htmlspecialchars($order['status']);
            $status_class = 'status-badge-pending';
            if ($status == 'berhasil') $status_class = 'status-badge-success';
            if ($status == 'gagal') $status_class = 'status-badge-failed';
        ?>
        <span class="status-badge <?php echo $status_class; ?>">
            <?php echo ucfirst($status); ?>
        </span>
    </p>
    
    <p class="status-info"><strong>Produk:</strong> <?php echo htmlspecialchars($order['product_name']); ?></p>
    
    <?php if (!empty($order['selected_variant'])): ?>
    <p class="status-info"><strong>Varian:</strong> <?php echo htmlspecialchars($order['selected_variant']); ?></p>
    <?php endif; ?>

    <p class="status-info"><strong>Jumlah:</strong> <?php echo $order['quantity']; ?> pcs</p>
    
    <p class="status-info"><strong>Subtotal:</strong> <?php echo format_rupiah($order['subtotal_price']); ?></p>
    <p class="status-info"><strong>Ongkir:</strong> <?php echo format_rupiah($order['shipping_cost']); ?></p>
    <p class="status-info" style="font-size: 1.2rem; font-weight: 700;">
        <strong>Total:</strong> <?php echo format_rupiah($order['total_price']); ?>
    </p>
    
    <p class="status-info"><strong>Metode Bayar:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
    <p class="status-info"><strong>Atas Nama:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
    <p class="status-info"><strong>Alamat:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
    
    
    <?php if ($order['status'] == 'pending'): ?>
        <div class="message-box info" style="text-align: left; margin-top: 1rem;">
            <strong>Segera lakukan pembayaran ke:</strong>
            <hr style="border: 0; border-top: 1px dashed var(--warning-border); margin: 0.75rem 0;">
            
            <?php
            $payment_bank_name = get_setting('payment_bank_name');
            $payment_ewallet_name = get_setting('payment_ewallet_name');
            
            if ($order['payment_method'] == $payment_bank_name): ?>
                <p>Metode: <strong>Bank Transfer (<?php echo htmlspecialchars($payment_bank_name); ?>)</strong></p>
                <p style="font-size: 1.2rem; font-weight: 700; margin-top: 0.5rem; color: var(--secondary-color);">
                    <?php echo htmlspecialchars(get_setting('payment_bank_account')); ?>
                </p>
            <?php elseif ($order['payment_method'] == $payment_ewallet_name): ?>
                <p>Metode: <strong>E-Wallet (<?php echo htmlspecialchars($payment_ewallet_name); ?>)</strong></p>
                <p style="font-size: 1.2rem; font-weight: 700; margin-top: 0.5rem; color: var(--secondary-color);">
                    <?php echo htmlspecialchars(get_setting('payment_ewallet_number')); ?>
                </p>
            <?php else: ?>
                 <p>Metode pembayaran tidak valid. Silakan hubungi admin.</p>
            <?php endif; ?>
            
             <p style="margin-top: 1rem; font-size: 1.1rem;">
                Total Pembayaran: <strong><?php echo format_rupiah($order['total_price']); ?></strong>
            </p>
            <small style="margin-top: 0.5rem; display: block;">Pesanan akan diproses setelah konfirmasi pembayaran.</small>
            
            <?php
                $wa_number = get_setting('shop_whatsapp');
                $wa_text = "Halo, saya ingin konfirmasi pembayaran untuk pesanan: *" . $order['order_code'] . "*. Berikut saya kirimkan bukti transfernya.";
                $wa_link = "https://api.whatsapp.com/send?phone=" . htmlspecialchars($wa_number) . "&text=" . urlencode($wa_text);
            ?>
            <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-primary" style="background-color: #25D366; margin-top: 1.5rem; width: 100%;">
                <i class="bi bi-whatsapp"></i> Kirim Bukti Transfer via WhatsApp
            </a>
            </div>
    
    <?php elseif ($order['status'] == 'berhasil'): ?>
         <div class="message-box success" style="margin-top: 1rem;">
            <strong>Pesanan Selesai:</strong>
            <p style="margin-top: 5px;">Pembayaran Anda telah dikonfirmasi. Terima kasih!</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($order['admin_message'])): ?>
        <div class="message-box admin-note" style="margin-top: 1rem;">
            <strong>Catatan dari Admin:</strong>
            <p style="margin-top: 5px;"><?php echo nl2br(htmlspecialchars($order['admin_message'])); ?></p>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
require 'templates/footer.php';
?>