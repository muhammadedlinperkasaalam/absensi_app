<?php
// Pastikan config sudah di-load sebelum file ini di-include
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?><?= APP_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="topbar">
    <button class="btn btn-sm btn-outline-secondary d-lg-none me-2" id="btnToggleSidebar">
        <i class="bi bi-list"></i>
    </button>
    <span class="brand"><i class="bi bi-fingerprint"></i> <?= APP_NAME ?></span>

    <div class="ms-auto dropdown">
        <button class="btn btn-light border dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle me-1"></i>
            <span class="d-none d-sm-inline"><?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['role']) ?>)</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>profil.php"><i class="bi bi-person"></i> Profil Saya</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
        </ul>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>
<?php include __DIR__ . '/sidebar.php'; ?>

<div class="content-wrapper">
