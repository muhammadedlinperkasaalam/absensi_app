<?php
/**
 * Konfigurasi umum aplikasi
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ubah sesuai lokasi folder project Anda di server / htdocs
define('BASE_URL', '/absensi_app/');
define('APP_NAME', 'Sistem Absensi Pegawai');
?>