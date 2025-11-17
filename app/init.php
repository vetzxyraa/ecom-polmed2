<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/config.php';

require_once __DIR__ . '/functions.php';

global $APP_SETTINGS;
$APP_SETTINGS = load_all_settings($db);

?>