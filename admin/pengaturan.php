<?php
require '../templates/admin_header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $settings_to_update = $_POST['settings'];
    $upload_dir = __DIR__ . "/../assets/img/";
    
    if (isset($_FILES['shop_about_image_file']) && $_FILES['shop_about_image_file']['error'] == UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['shop_about_image_file']["name"]);
        $ext = strtolower($file_info['extension']);
        $new_name = 'about_image_' . time() . '.' . $ext;
        
        if (move_uploaded_file($_FILES['shop_about_image_file']["tmp_name"], $upload_dir . $new_name)) {
            $old_img = get_setting('shop_about_image');
            if (!empty($old_img) && !filter_var($old_img, FILTER_VALIDATE_URL) && file_exists($upload_dir . $old_img)) {
                unlink($upload_dir . $old_img);
            }
            $settings_to_update['shop_about_image'] = $new_name;
        } else {
            set_flash_message('settings_msg', 'Gagal mengupload gambar baru.', 'error');
        }
    } elseif (!empty($settings_to_update['shop_about_image_url'])) {
         $old_img = get_setting('shop_about_image');
         if (!empty($old_img) && !filter_var($old_img, FILTER_VALIDATE_URL) && file_exists($upload_dir . $old_img)) {
            unlink($upload_dir . $old_img);
         }
        $settings_to_update['shop_about_image'] = $settings_to_update['shop_about_image_url'];
    } else {
        $settings_to_update['shop_about_image'] = get_setting('shop_about_image');
    }
    unset($settings_to_update['shop_about_image_url']);

    if (!empty($_POST['admin_password'])) {
        if (strlen($_POST['admin_password']) < 6) {
            set_flash_message('settings_msg', 'Password baru harus minimal 6 karakter.', 'error');
        } else {
            $new_pass_hash = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
            $new_user = trim($settings_to_update['admin_user']);
            
            try {
                $stmt_user = $db->prepare("UPDATE users SET username = ?, password_hash = ? WHERE id = ?");
                $stmt_user->execute([$new_user, $new_pass_hash, $_SESSION['admin_id']]);
                $_SESSION['admin_username'] = $new_user;
            } catch (PDOException $e) {
                 set_flash_message('settings_msg', 'Gagal update user admin: ' . $e->getMessage(), 'error');
            }
        }
    }
    unset($settings_to_update['admin_user']);

    try {
        $db->beginTransaction();
        $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = $db->prepare($sql);
        
        foreach ($settings_to_update as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        
        $db->commit();
        if (!isset($_SESSION['settings_msg'])) {
             set_flash_message('settings_msg', 'Pengaturan berhasil disimpan.', 'success');
        }
    } catch (Exception $e) {
        $db->rollBack();
        set_flash_message('settings_msg', 'Gagal menyimpan pengaturan: ' . $e->getMessage(), 'error');
    }
    
    $APP_SETTINGS = load_all_settings($db);
    header('Location: ' . BASE_URL . '/admin/pengaturan.php');
    exit;
}

function get_setting_preview_src($key) {
    $img_name = get_setting($key);
    if (empty($img_name)) {
        return 'https://placehold.co/200x200/e2e8f0/475569?text=No+Image';
    }
    if (filter_var($img_name, FILTER_VALIDATE_URL)) {
        return htmlspecialchars($img_name);
    }
    return BASE_URL . '/assets/img/' . htmlspecialchars($img_name);
}
?>

<h1 class="page-title">Pengaturan Website</h1>

<div class="card form-container">
    
    <?php display_flash_message('settings_msg'); ?>

    <form action="pengaturan.php" method="POST" enctype="multipart/form-data">
        
        <h3 class="form-section-title">Info Toko</h3>
        <div class="form-group">
            <label for="shop_name">Nama Toko</label>
            <input type="text" id="shop_name" name="settings[shop_name]" class="form-control" value="<?php echo get_setting('shop_name'); ?>">
        </div>
        <div class="form-group">
            <label for="owner_name">Nama Pemilik</label>
            <input type="text" id="owner_name" name="settings[owner_name]" class="form-control" value="<?php echo get_setting('owner_name'); ?>">
        </div>

        <h3 class="form-section-title">Info Kontak</h3>
        <div class="form-group">
            <label for="shop_whatsapp">Nomor WhatsApp</label>
            <input type="text" id="shop_whatsapp" name="settings[shop_whatsapp]" class="form-control" value="<?php echo get_setting('shop_whatsapp'); ?>" placeholder="628...">
        </div>
        <div class="form-group">
            <label for="shop_email">Email Kontak</label>
            <input type="email" id="shop_email" name="settings[shop_email]" class="form-control" value="<?php echo get_setting('shop_email'); ?>">
        </div>
        <div class="form-group">
            <label for="shop_address">Alamat Toko</label>
            <textarea id="shop_address" name="settings[shop_address]" class="form-control"><?php echo get_setting('shop_address'); ?></textarea>
        </div>
        
        <h3 class="form-section-title">Pengaturan Pembayaran</h3>
        <p style="margin-top: -1rem; margin-bottom: 1.5rem; color: var(--text-light); font-size: 0.9rem;">
            Info ini akan tampil di halaman checkout & status.
        </p>

        <div class="form-group">
            <label for="payment_bank_name">Nama Bank (e.g., BCA)</label>
            <input type="text" id="payment_bank_name" name="settings[payment_bank_name]" class="form-control" value="<?php echo get_setting('payment_bank_name'); ?>">
        </div>
         <div class="form-group">
            <label for="payment_bank_account">No. Rekening (e.g., 123456 a/n Toko)</label>
            <input type="text" id="payment_bank_account" name="settings[payment_bank_account]" class="form-control" value="<?php echo get_setting('payment_bank_account'); ?>">
        </div>
         <div class="form-group">
            <label for="payment_ewallet_name">Nama E-Wallet (e.g., GoPay)</label>
            <input type="text" id="payment_ewallet_name" name="settings[payment_ewallet_name]" class="form-control" value="<?php echo get_setting('payment_ewallet_name'); ?>">
        </div>
         <div class="form-group">
            <label for="payment_ewallet_number">Nomor E-Wallet (e.g., 0812... a/n Toko)</label>
            <input type="text" id="payment_ewallet_number" name="settings[payment_ewallet_number]" class="form-control" value="<?php echo get_setting('payment_ewallet_number'); ?>">
        </div>
        
        <h3 class="form-section-title">Halaman 'Tentang Kami' / Home</h3>
        <div class="form-group">
            <label>Gambar Halaman Home (1:1)</label>
            <img src="<?php echo get_setting_preview_src('shop_about_image'); ?>" alt="Preview" style="width: 200px; height: 200px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 1rem;">
            
            <label for="shop_about_image_file" style="font-weight: 500;">Upload File Baru:</label>
            <input type="file" id="shop_about_image_file" name="shop_about_image_file" class="form-control" accept="image/jpeg,image/png,image/gif">
            <small>Abaikan jika tidak ganti.</small>

            <label for="shop_about_image_url" style="font-weight: 500; margin-top: 1rem;">Atau URL Gambar:</label>
            <input type="text" id="shop_about_image_url" name="settings[shop_about_image_url]" class="form-control" placeholder="https://example.com/image.png" value="<?php echo filter_var(get_setting('shop_about_image'), FILTER_VALIDATE_URL) ? get_setting('shop_about_image') : ''; ?>">
        </div>
         <div class="form-group">
            <label for="shop_about_text">Teks Halaman Home</label>
            <textarea id="shop_about_text" name="settings[shop_about_text]" class="form-control" style="min-height: 150px;"><?php echo get_setting('shop_about_text'); ?></textarea>
        </div>

        <h3 class="form-section-title">Akun Admin</h3>
        <div class="form-group">
            <label for="admin_user">Username Admin</label>
            <input type="text" id="admin_user" name="settings[admin_user]" class="form-control" value="<?php echo htmlspecialchars($_SESSION['admin_username']); ?>" required>
        </div>
         <div class="form-group">
            <label for="admin_password">Password Baru</label>
            <input type="password" id="admin_password" name="admin_password" class="form-control" placeholder="Isi untuk ganti password">
            <small>Kosongkan jika tidak ganti.</small>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save-fill"></i> Simpan Pengaturan
        </button>
    </form>
</div>

<style>
    .form-section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--secondary-color);
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 5px;
        margin: 2.5rem 0 1.5rem 0;
    }
    .form-section-title:first-of-type {
        margin-top: 0;
    }
</style>

<?php
require '../templates/admin_footer.php';
?>