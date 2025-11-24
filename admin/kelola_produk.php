<?php
require '../templates/admin_header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$page_title = "Tambah Produk Baru";

$product = [
    'id' => $id,
    'name' => '',
    'price' => '',
    'stock' => '',
    'description' => '',
    'available_variants' => '',
    'shipping_cost' => 0, // <-- FIELD BARU
    'image' => '',
];

if ($action == 'delete' && $id > 0) {
    try {
        $stmt_get = $db->prepare("SELECT image FROM products WHERE id = ?");
        $stmt_get->execute([$id]);
        $image_name = $stmt_get->fetchColumn();

        $stmt_del = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt_del->execute([$id]);

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $price = (int)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $description = trim($_POST['description']);
    $available_variants = trim($_POST['available_variants']);
    $shipping_cost = (int)$_POST['shipping_cost']; // <-- AMBIL ONGKIR BARU
    $image_url = trim($_POST['image_url']);
    
    $old_image = $_POST['old_image'];
    $new_image_name = $old_image;

    $upload_dir = __DIR__ . "/../assets/img/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    function handle_upload($file_key, $old_name, $url_input, $prefix, $upload_dir) {
        // ... (fungsi handle_upload tidak berubah) ...
        if (!empty($url_input)) {
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
                if (!empty($old_name) && !filter_var($old_name, FILTER_VALIDATE_URL) && file_exists($upload_dir . $old_name)) {
                    unlink($upload_dir . $old_name);
                }
                return $new_name;
            }
        }
        return $old_name;
    }

    $new_image_name = handle_upload('image_file', $old_image, $image_url, 'prod_', $upload_dir);

    if ($id == 0 && empty($new_image_name)) {
        $new_image_name = 'https://placehold.co/600x600/e2e8f0/475569?text=' . urlencode('Produk');
    }

    try {
        if ($id > 0) {
            // VVV UPDATE SQL DENGAN ONGKIR BARU VVV
            $sql = "UPDATE products SET name = ?, price = ?, stock = ?, description = ?, 
                        available_variants = ?, shipping_cost = ?, image = ? 
                    WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $price, $stock, $description, $available_variants, $shipping_cost, $new_image_name, $id]);
        } else {
            // VVV INSERT SQL DENGAN ONGKIR BARU VVV
            $sql = "INSERT INTO products (name, price, stock, description, available_variants, shipping_cost, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $price, $stock, $description, $available_variants, $shipping_cost, $new_image_name]);
        }
        set_flash_message('product_msg', 'Produk berhasil disimpan.', 'success');
        header('Location: ' . BASE_URL . '/admin/daftar_produk.php');
        exit;

    } catch (PDOException $e) {
        set_flash_message('form_msg', 'Gagal menyimpan produk: ' . $e->getMessage(), 'error');
        $product = $_POST;
        $product['image'] = $old_image;
    }
}

function get_preview_src($img_name) {
    if (empty($img_name)) {
        return 'https://placehold.co/200x200/e2e8f0/475569?text=No+Image';
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
            <label for="shipping_cost">Harga Ongkir (Rp)</label>
            <input type="number" id="shipping_cost" name="shipping_cost" class="form-control" value="<?php echo htmlspecialchars($product['shipping_cost']); ?>" min="0" required>
            <small>Ongkir untuk produk ini. Isi 0 jika gratis ongkir.</small>
        </div>
        <div class="form-group">
            <label for="stock">Stok</label>
            <input type="number" id="stock" name="stock" class="form-control" value="<?php echo htmlspecialchars($product['stock']); ?>" min="0" required>
        </div>
        <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="available_variants">Varian Tersedia (Pisahkan dengan koma)</label>
            <input type="text" id="available_variants" name="available_variants" class="form-control" value="<?php echo htmlspecialchars($product['available_variants'] ?? ''); ?>">
            <small>Contoh: Pedas, Pedas Manis. Kosongkan jika tidak ada varian.</small>
        </div>
        
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 2rem 0;">

        <div class="form-group">
            <label>Gambar Produk</label>
            <img src="<?php echo get_preview_src($product['image']); ?>" alt="Preview" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 1rem;">
            
            <label for="image_file" style="font-weight: 500;">Upload File Baru:</label>
            <input type="file" id="image_file" name="image_file" class="form-control" accept="image/jpeg,image/png,image/gif">
            <small>Abaikan jika tidak ganti, atau isi URL di bawah.</small>

            <label for="image_url" style="font-weight: 500; margin-top: 1rem;">Tempel URL Gambar:</label>
            <input type="text" id="image_url" name="image_url" class="form-control" placeholder="https://example.com/image.png" value="<?php echo filter_var($product['image'], FILTER_VALIDATE_URL) ? htmlspecialchars($product['image']) : ''; ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save-fill"></i> Simpan Produk
        </button>
    </form>
</div>

<?php
require '../templates/admin_footer.php';
?>