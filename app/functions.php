<?php

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
            error_log("Gagal memuat pengaturan: " . $e->getMessage());
        }
    }
    return $settings;
}

function get_setting(string $key, $default = '') {
    global $APP_SETTINGS;
    return isset($APP_SETTINGS[$key]) ? htmlspecialchars($APP_SETTINGS[$key]) : $default;
}

function format_rupiah(int $number): string {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

function set_flash_message(string $name, string $message, string $type = 'info'): void {
    $_SESSION[$name] = [
        'message' => $message,
        'type'    => $type
    ];
}

function display_flash_message(string $name): void {
    if (isset($_SESSION[$name])) {
        $data = $_SESSION[$name];
        echo '<div class="message-box ' . htmlspecialchars($data['type']) . '">' . htmlspecialchars($data['message']) . '</div>';
        unset($_SESSION[$name]);
    }
}

function require_admin_login(): void {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        set_flash_message('login_error', 'Anda harus login untuk mengakses halaman ini.', 'error');
        // KEMBALI KE /admin/
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }
}
?>