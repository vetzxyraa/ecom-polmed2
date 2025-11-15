<?php
require 'templates/header.php'; // Sudah termasuk init.php

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
            <input type="text" id="kode_pesanan" name="kode_pesanan" class="form-control" value="<?php echo htmlspecialchars($order_code); ?>" placeholder="Contoh: TB-ABC12345" required>
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
            $status_class = 'status-badge-pending'; // Default
            if ($status == 'berhasil') $status_class = 'status-badge-success';
            if ($status == 'gagal') $status_class = 'status-badge-failed';
        ?>
        <span class="status-badge <?php echo $status_class; ?>">
            <?php echo ucfirst($status); ?>
        </span>
    </p>
    
    <p class="status-info"><strong>Produk:</strong> <?php echo htmlspecialchars($order['product_name']); ?></p>
    <p class="status-info"><strong>Jumlah:</strong> <?php echo $order['quantity']; ?> pcs</p>
    <p class="status-info"><strong>Total:</strong> <?php echo format_rupiah($order['total_price']); ?></p>
    <p class="status-info"><strong>Metode Bayar:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
    <p class="status-info"><strong>Atas Nama:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
    <p class="status-info"><strong>Alamat:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
    
    
    <!-- ====================================================== -->
    <!-- BLOK BARU: Tampilkan instruksi pembayaran jika 'pending' -->
    <!-- ====================================================== -->
    <?php if ($order['status'] == 'pending'): ?>
        <div class="message-box info" style="text-align: left; margin-top: 1rem;">
            <strong>Segera lakukan pembayaran ke:</strong>
            <hr style="border: 0; border-top: 1px dashed var(--warning-border); margin: 0.75rem 0;">
            
            <?php
            // Ambil info payment dari settings
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
            
             <p style="margin-top: 1rem;">
                Total Pembayaran: <strong><?php echo format_rupiah($order['total_price']); ?></strong>
            </p>
            <small style="margin-top: 0.5rem; display: block;">Pesanan akan diproses setelah pembayaran dikonfirmasi oleh admin.</small>
        </div>
    
    <?php elseif ($order['status'] == 'berhasil'): ?>
         <div class="message-box success" style="margin-top: 1rem;">
            <strong>Pesanan Selesai:</strong>
            <p style="margin-top: 5px;">Pembayaran Anda telah dikonfirmasi. Terima kasih!</p>
        </div>
    <?php endif; ?>
    <!-- ====================================================== -->
    <!-- AKHIR BLOK BARU -->
    <!-- ====================================================== -->

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