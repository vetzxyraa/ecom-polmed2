<?php
// Admin Login Page
require_once __DIR__ . '/../app/init.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_msg = "Username dan password wajib diisi.";
    } else {
        try {
            // Ambil data user dari database baru (tabel 'users')
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // Verifikasi password
            if ($user && password_verify($password, $user['password_hash'])) {
                // Sukses login
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                
                header('Location: ' . BASE_URL . '/admin/dashboard.php');
                exit;
            } else {
                $error_msg = "Username atau password salah.";
            }

        } catch (PDOException $e) {
            $error_msg = "Error database: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo get_setting('shop_name', 'Toko Online'); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="login-wrapper">

    <div class="card login-box">
        <a href="<?php echo BASE_URL; ?>/index.php" class="logo">
            <i class="bi bi-shop-window"></i>
            <span>Admin Panel</span>
        </a>

        <?php if ($error_msg): ?>
            <div class="message-box error"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>
        
        <?php display_flash_message('login_error'); ?>

        <form action="index.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>
        
        <a href="<?php echo BASE_URL; ?>/index.php" class="back-link">
            <i class="bi bi-arrow-left-short"></i> Kembali ke Toko
        </a>
    </div>

</body>
</html>