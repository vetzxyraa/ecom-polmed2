<?php
require '../templates/admin_header.php'; // Sudah termasuk init.php & cek login

// Logika untuk update status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $admin_message = trim($_POST['admin_message']);

    // Validasi status
    $allowed_statuses = ['pending', 'berhasil', 'gagal'];
    if ($order_id > 0 && in_array($status, $allowed_statuses)) {
        try {
            $sql = "UPDATE orders SET status = ?, admin_message = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$status, $admin_message, $order_id]);
            
            set_flash_message('dashboard_msg', 'Status pesanan berhasil diperbarui.', 'success');

        } catch (PDOException $e) {
            set_flash_message('dashboard_msg', 'Gagal memperbarui: ' . $e->getMessage(), 'error');
        }
    }
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}


// Ambil semua pesanan
$orders = [];
try {
    $sql = "SELECT o.*, p.name as product_name 
            FROM orders o
            JOIN products p ON o.product_id = p.id 
            ORDER BY o.order_date DESC";
    $orders = $db->query($sql)->fetchAll();
} catch (PDOException $e) {
    echo "<div class='message-box error'>Gagal memuat pesanan: " . $e->getMessage() . "</div>";
}
?>

<div class="admin-header">
    <h1 class="page-title">Manajemen Pesanan</h1>
</div>

<?php display_flash_message('dashboard_msg'); ?>

<div class="card table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Detail Pesanan</th>
                <th>Pembeli & Alamat</th>
                <th>Status Saat Ini</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="4" style="text-align: center;">Belum ada pesanan.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($order['order_code']); ?></strong><br>
                        <small><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></small><br>
                        Produk: <?php echo htmlspecialchars($order['product_name']); ?><br>
                        Jumlah: <?php echo $order['quantity']; ?> pcs<br>
                        Total: <?php echo format_rupiah($order['total_price']); ?><br>
                        <!-- Menampilkan metode bayar -->
                        Metode Bayar: <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong>
                    </td>
                    <td class="customer-details">
                        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                        <i class="bi bi-telephone-fill"></i> <?php echo htmlspecialchars($order['customer_phone']); ?><br>
                        <i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($order['customer_address']); ?>
                    </td>
                    <td>
                        <?php
                            $status = htmlspecialchars($order['status']);
                            $status_class = 'status-badge-pending';
                            if ($status == 'berhasil') $status_class = 'status-badge-success';
                            if ($status == 'gagal') $status_class = 'status-badge-failed';
                        ?>
                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php echo ucfirst($status); ?>
                        </span>
                        
                        <?php if (!empty($order['admin_message'])): ?>
                            <div class="message-box admin-note">
                                <?php echo htmlspecialchars($order['admin_message']); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                         <form action="dashboard.php" method="POST" class="status-form">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            
                            <div class="form-group">
                                <label for="status_<?php echo $order['id']; ?>" class="sr-only">Status</label>
                                <select name="status" id="status_<?php echo $order['id']; ?>" class="form-control">
                                    <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="berhasil" <?php echo ($order['status'] == 'berhasil') ? 'selected' : ''; ?>>Berhasil</option>
                                    <option value="gagal" <?php echo ($order['status'] == 'gagal') ? 'selected' : ''; ?>>Gagal</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="pesan_<?php echo $order['id']; ?>" class="sr-only">Pesan Admin</label>
                                <textarea name="admin_message" id="pesan_<?php echo $order['id']; ?>" class="form-control" placeholder="Pesan untuk pelanggan (opsional)"><?php echo htmlspecialchars($order['admin_message'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>
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