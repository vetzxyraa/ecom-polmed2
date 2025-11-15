<?php
// File untuk fungsi-fungsi pembantu

/**
 * Mengambil semua pengaturan dari database dan menyimpannya dalam variabel global.
 * @param PDO $db
 * @return array
 */
function load_all_settings(PDO $db): array {
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        try {
            $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            // Tangani error jika tabel belum ada, dll.
            error_log("Gagal memuat pengaturan: " . $e->getMessage());
        }
    }
    return $settings;
}

/**
 * Mengambil satu nilai pengaturan spesifik.
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_setting(string $key, $default = '') {
    global $APP_SETTINGS; // Menggunakan variabel global yang di-set di init.php
    return isset($APP_SETTINGS[$key]) ? htmlspecialchars($APP_SETTINGS[$key]) : $default;
}

/**
 * Memformat angka menjadi format Rupiah.
 * @param int $number
 * @return string
 */
function format_rupiah(int $number): string {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

/**
 * Mengelola pesan flash untuk ditampilkan ke pengguna (misal: sukses, error).
 * @param string $name
 * @param string $message
 * @param string $type (success, error, info)
 */
function set_flash_message(string $name, string $message, string $type = 'info'): void {
    $_SESSION[$name] = [
        'message' => $message,
        'type'    => $type
    ];
}

/**
 * Menampilkan pesan flash jika ada.
 * @param string $name
 */
function display_flash_message(string $name): void {
    if (isset($_SESSION[$name])) {
        $data = $_SESSION[$name];
        echo '<div class="message-box ' . htmlspecialchars($data['type']) . '">' . htmlspecialchars($data['message']) . '</div>';
        unset($_SESSION[$name]);
    }
}

/**
 * Memeriksa apakah admin sudah login.
 * Jika tidak, redirect ke halaman login.
 */
function require_admin_login(): void {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        set_flash_message('login_error', 'Anda harus login untuk mengakses halaman ini.', 'error');
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }
}

?>