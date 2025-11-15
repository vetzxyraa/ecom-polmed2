<?php
require '../templates/admin_header.php'; // Sudah termasuk init.php & cek login

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$page_title = "Tambah Produk Baru";

// Data produk default (hanya 1 gambar dan 'stock')
$product = [
    'id' => $id,
    'name' => '',
    'price' => '',
    'stock' => '', // Menggunakan 'stock'
    'description' => '',
    'image' => '', // Hanya 'image'
];

// --- LOGIKA DELETE ---
if ($action == 'delete' && $id > 0) {
    try {
        // Ambil nama file gambar untuk dihapus
        $stmt_get = $db->prepare("SELECT image FROM products WHERE id = ?");
        $stmt_get->execute([$id]);
        $image_name = $stmt_get->fetchColumn();

        // Hapus produk dari DB
        $stmt_del = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt_del->execute([$id]);

        // Hapus file gambar jika ada
        $upload_dir = __DIR__ . "/../assets/img/";
        if (!empty($image_name) && !filter_var($image_name, FILTER_VALIDATE_URL) && file_exists($upload_dir . $image_name)) {
            unlink($upload_dir . $image_name);
        }
        
        set_flash_message('product_msg', 'Produk berhasil dihapus.', 'success');
    } catch (PDOException $e) {
        set_flash_message('product_msg', 'Gagal menghapus produk: ' . $e->getMessage(), 'error');
    }
    header('Location: ' . BASE_URL . '/admin/daftar_produk.php');
    exit;
}

// --- LOGIKA EDIT (Ambil data) ---
if ($id > 0) {
    $page_title = "Edit Produk";
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product_data = $stmt->fetch();
        if ($product_data) {
            $product = $product_data;
        } else {
            set_flash_message('product_msg', 'Produk tidak ditemukan.', 'error');
            header('Location: ' . BASE_URL . '/admin/daftar_produk.php');
            exit;
        }
    } catch (PDOException $e) {
        set_flash_message('product_msg', 'Error: ' . $e->getMessage(), 'error');
    }
}

// --- LOGIKA SIMPAN (Create/Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $price = (int)$_POST['price'];
    $stock = (int)$_POST['stock']; // Menggunakan 'stock'
    $description = trim($_POST['description']);
    $image_url = trim($_POST['image_url']);
    
    // Ambil nama gambar lama
    $old_image = $_POST['old_image'];
    $new_image_name = $old_image;

    $upload_dir = __DIR__ . "/../assets/img/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Fungsi helper untuk upload (disederhanakan)
    function handle_upload($file_key, $old_name, $url_input, $prefix, $upload_dir) {
        if (!empty($url_input)) {
            // Jika URL diisi, hapus file lama (jika ada) dan gunakan URL
            if (!empty($old_name) && !filter_var($old_name, FILTER_VALIDATE_URL) && file_exists($upload_dir . $old_name)) {
                unlink($upload_dir . $old_name);
            }
            return $url_input;
        }
        
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == UPLOAD_ERR_OK) {
            $file_info = pathinfo($_FILES[$file_key]["name"]);
            $ext = strtolower($file_info['extension']);
            $new_name = $prefix . time() . "." . $ext;
            
            if (move_uploaded_file($_FILES[$file_key]["tmp_name"], $upload_dir . $new_name)) {
                // Hapus file lama jika sukses upload
                if (!empty($old_name) && !filter_var($old_name, FILTER_VALIDATE_URL) && file_exists($upload_dir . $old_name)) {
                    unlink($upload_dir . $old_name);
                }
                return $new_name; // Kembalikan nama file baru
            }
        }
        return $old_name; // Kembalikan nama lama jika tidak ada upload/URL baru
    }

    $new_image_name = handle_upload('image_file', $old_image, $image_url, 'prod_', $upload_dir);

    // Jika produk baru dan tidak ada gambar, gunakan placeholder
    if ($id == 0 && empty($new_image_name)) {
        $new_image_name = 'https://placehold.co/600x600/e2e8f0/475569?text=' . urlencode($name);
    }

    // Update atau Insert
    try {
        if ($id > 0) {
            // UPDATE
            $sql = "UPDATE products SET name = ?, price = ?, stock = ?, description = ?, image = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $price, $stock, $description, $new_image_name, $id]);
        } else {
            // INSERT
            $sql = "INSERT INTO products (name, price, stock, description, image) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $price, $stock, $description, $new_image_name]);
        }
        set_flash_message('product_msg', 'Produk berhasil disimpan.', 'success');
        header('Location: ' . BASE_URL . '/admin/daftar_produk.php');
        exit;

    } catch (PDOException $e) {
        set_flash_message('form_msg', 'Gagal menyimpan produk: ' . $e->getMessage(), 'error');
        // Set ulang data form agar tidak hilang
        $product = $_POST;
        $product['image'] = $old_image; // Tetap tampilkan gambar lama jika gagal
    }
}

// Fungsi untuk menampilkan preview
function get_preview_src($img_name) {
    if (empty($img_name)) {
        return 'https://placehold.co/200x200/e2e8f0/475569?text=Kosong';
    }
    if (filter_var($img_name, FILTER_VALIDATE_URL)) {
        return htmlspecialchars($img_name);
    }
    return BASE_URL . '/assets/img/' . htmlspecialchars($img_name);
}
?>

<a href="daftar_produk.php" class="btn btn-secondary btn-sm" style="margin-bottom: 1rem;">
    <i class="bi bi-arrow-left-short"></i> Kembali ke Daftar Produk
</a>
<h1 class="page-title"><?php echo $page_title; ?></h1>

<div class="card form-container">

    <?php display_flash_message('form_msg'); ?>

    <form action="kelola_produk.php<?php echo ($id > 0) ? '?id='.$id : ''; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($product['image']); ?>">
        
        <div class="form-group">
            <label for="name">Nama Produk</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="price">Harga (Rp)</label>
            <input type="number" id="price" name="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" min="1" required>
        </div>
        <div class="form-group">
            <label for="stock">Stok</label>
            <input type="number" id="stock" name="stock" class="form-control" value="<?php echo htmlspecialchars($product['stock']); ?>" min="0" required>
        </div>
        <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 2rem 0;">

        <div class="form-group">
            <label>Gambar Produk</label>
            <img src="<?php echo get_preview_src($product['image']); ?>" alt="Preview" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 1rem;">
            
            <label for="image_file" style="font-weight: 500;">Upload File Baru:</label>
            <input type="file" id="image_file" name="image_file" class="form-control" accept="image/jpeg,image/png,image/gif">
            <small>Abaikan jika tidak ingin ganti, atau tempel URL di bawah.</small>

            <label for="image_url" style="font-weight: 500; margin-top: 1rem;">Tempel URL Gambar:</label>
            <input type="text" id="image_url" name="image_url" class="form-control" placeholder="https://..." value="<?php echo filter_var($product['image'], FILTER_VALIDATE_URL) ? htmlspecialchars($product['image']) : ''; ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save-fill"></i> Simpan Produk
        </button>
    </form>
</div>

<?php
require '../templates/admin_footer.php';
?>