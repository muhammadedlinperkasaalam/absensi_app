<?php
/**
 * Fungsi otentikasi & otorisasi (Hak akses berbasis role)
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if(!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

/**
 * Membatasi halaman hanya untuk role tertentur.
 * Contoh: requreRole(['admin','hrd']);
 */
function requireRole(array $roles) {
    requireLogin();
    if(!in_array($_SESSION['role'], $roles)) {
        header('Location: ' . BASE_URL . 'akses_ditolak.php');
        exit;
    }
}

function currentUser() {
    return [
        'id'            => $_SESSION['user_id'] ?? null,
        'username'      => $_SESSION['username'] ?? null,
        'role'          => $_SESSION['role'] ?? null,
        'id_pegawai'    => $_SESSION['id_pegawai'] ?? null,
        'nama'          => $_SESSION['nama'] ?? null,
    ];
}

function isAdmin() { return ($_SESSION['role'] ?? '') === 'admin'; }
function isHRD() { return ($_SESSION['role'] ?? '') === 'hrd'; }
function isPegawai() { return ($_SESSION['role'] ?? '') === 'pegawai'; }

/** admin & hrd sama sama boleh kelola data pegawai/absensi/laporan */
function isPengelola() { return isAdmin() || isHRD(); }