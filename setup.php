<?php
/**
 * SETUP AWAL - JALANKAN SATU KALI SAJA setelah import database/absensi_db.sql
 * File ini men-generate hash password yang valid untuk akun contoh (admin, hrd, budi, siti)
 * Password default setelah setup: "password"
 *
 * SETELAH BERHASIL DIJALANKAN, HAPUS FILE INI DARI SERVER (alasan keamanan).
 */
require_once 'config/database.php';

$passwordDefault = 'password';
$hash = password_hash($passwordDefault, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE password = 'BELUM_DI_SETUP'");
$stmt->execute([$hash]);
$jumlah = $stmt->rowCount();

echo "<h2>Setup Selesai</h2>";
if ($jumlah > 0) {
    echo "<p>$jumlah akun berhasil diberi password default: <b>password</b></p>";
    echo "<p>Akun yang tersedia: <b>admin</b>, <b>hrd</b>, <b>budi</b>, <b>siti</b> (semua pakai password: <b>password</b>)</p>";
} else {
    echo "<p>Tidak ada akun yang diupdate (mungkin setup sudah pernah dijalankan sebelumnya).</p>";
}
echo "<p style='color:red;font-weight:bold;'>PENTING: Segera hapus file setup.php ini dari server setelah selesai!</p>";
echo "<p><a href='login.php'>Lanjut ke halaman Login &raquo;</a></p>";
