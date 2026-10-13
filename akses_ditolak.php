<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();
$pageTitle = 'Akses Ditolak';
include 'includes/header.php';
?>
<div class="text-center py-5">
    <i class="bi bi-shield-exclamation text-danger" style="font-size:4rem;"></i>
    <h3 class="mt-3">Akses Ditolak</h3>
    <p class="text-muted">Anda tidak memiliki hak akses untuk membuka halaman ini.</p>
    <a href="<?= BASE_URL ?>dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
</div>
<?php include 'includes/footer.php'; ?>