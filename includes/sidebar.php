<?php
$currentPage = basename($_SERVER['PHP_SELF']);
function navActive($file, $current) {
    return $file === $current ? 'active' : '';
}
?>
<nav class="sidebar" id="sidebarMenu">
    <ul class="nav flex-column">

        <li class="nav-item">
            <a class="nav-link <?= navActive('dashboard.php', $currentPage) ?>" href="<?= BASE_URL ?>dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        <?php if (isPengelola()): ?>
        <li class="nav-item">
            <a class="nav-link <?= navActive('index.php', $currentPage) && strpos($_SERVER['REQUEST_URI'],'pegawai')!==false ? 'active':'' ?>" href="<?= BASE_URL ?>pegawai/index.php">
                <i class="bi bi-people"></i> Data Pegawai
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>absensi/index.php">
                <i class="bi bi-calendar-check"></i> Absensi (Semua)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>laporan/index.php">
                <i class="bi bi-bar-chart"></i> Laporan
            </a>
        </li>
        <?php endif; ?>

        <?php if (isPegawai()): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>absensi/absen.php">
                <i class="bi bi-clock-history"></i> Absen Saya
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>laporan/index.php">
                <i class="bi bi-file-earmark-text"></i> Riwayat Absensi
            </a>
        </li>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>users/index.php">
                <i class="bi bi-shield-lock"></i> Kelola Akun & Hak Akses
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>pengaturan.php">
                <i class="bi bi-gear"></i> Pengaturan
            </a>
        </li>
        <?php endif; ?>

        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>profil.php">
                <i class="bi bi-person-badge"></i> Profil Saya
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="<?= BASE_URL ?>logout.php">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </a>
        </li>
    </ul>
</nav>
