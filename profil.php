<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Profil Saya';
$user = currentUser();
$msg = '';
$error = '';

$stmt = $pdo->prepare("SELECT u.*, p.nama, p.nip, p.no_hp, p.email FROM users u
                        LEFT JOIN pegawai p ON p.id = u.id_pegawai WHERE u.id = ?");
$stmt->execute([$user['id']]);
$akun = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordLama = $_POST['password_lama'];
    $passwordBaru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    if (!password_verify($passwordLama, $akun['password'])) {
        $error = 'Password lama salah.';
    } elseif (strlen($passwordBaru) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } elseif ($passwordBaru !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user['id']]);
        $msg = 'Password berhasil diubah.';
    }
}

include 'includes/header.php';
?>

<h4 class="mb-3">Profil Saya</h4>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white fw-semibold">Informasi Akun</div>
            <div class="card-body">
                <p class="mb-2"><b>Username:</b> <?= htmlspecialchars($akun['username']) ?></p>
                <p class="mb-2"><b>Role:</b> <span class="badge bg-dark"><?= ucfirst($akun['role']) ?></span></p>
                <?php if ($akun['nama']): ?>
                <p class="mb-2"><b>Nama:</b> <?= htmlspecialchars($akun['nama']) ?></p>
                <p class="mb-2"><b>NIP:</b> <?= htmlspecialchars($akun['nip']) ?></p>
                <p class="mb-2"><b>No. HP:</b> <?= htmlspecialchars($akun['no_hp'] ?? '-') ?></p>
                <p class="mb-0"><b>Email:</b> <?= htmlspecialchars($akun['email'] ?? '-') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white fw-semibold">Ganti Password</div>
            <div class="card-body">
                <?php if ($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-key"></i> Ubah Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
