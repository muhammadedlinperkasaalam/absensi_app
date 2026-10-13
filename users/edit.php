<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole(['admin']);

$pageTitle = 'Edit Akun';
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$akun = $stmt->fetch();
if (!$akun) { header('Location: index.php'); exit; }

$daftarPegawai = $pdo->query("SELECT id, nama FROM pegawai WHERE status='aktif' ORDER BY nama")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $status = $_POST['status'];
    $id_pegawai = $_POST['id_pegawai'] ?: null;
    $passwordBaru = $_POST['password_baru'] ?? '';

    if ($passwordBaru !== '') {
        $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET role=?, status=?, id_pegawai=?, password=? WHERE id=?");
        $stmt->execute([$role, $status, $id_pegawai, $hash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET role=?, status=?, id_pegawai=? WHERE id=?");
        $stmt->execute([$role, $status, $id_pegawai, $id]);
    }
    header('Location: index.php?msg=edit');
    exit;
}

include '../includes/header.php';
?>

<h4 class="mb-3">Edit Akun: <?= htmlspecialchars($akun['username']) ?></h4>

<div class="card" style="max-width:600px;">
    <div class="card-body">
        <?php if ($error): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Hak Akses (Role)</label>
                <select name="role" class="form-select">
                    <option value="admin" <?= $akun['role']==='admin'?'selected':'' ?>>Admin</option>
                    <option value="hrd" <?= $akun['role']==='hrd'?'selected':'' ?>>HRD</option>
                    <option value="pegawai" <?= $akun['role']==='pegawai'?'selected':'' ?>>Pegawai</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Hubungkan ke Data Pegawai</label>
                <select name="id_pegawai" class="form-select">
                    <option value="">-- Tidak Terhubung --</option>
                    <?php foreach ($daftarPegawai as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $p['id']==$akun['id_pegawai']?'selected':'' ?>><?= htmlspecialchars($p['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Status Akun</label>
                <select name="status" class="form-select">
                    <option value="aktif" <?= $akun['status']==='aktif'?'selected':'' ?>>Aktif</option>
                    <option value="nonaktif" <?= $akun['status']==='nonaktif'?'selected':'' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Reset Password (opsional)</label>
                <input type="text" name="password_baru" class="form-control" placeholder="Kosongkan jika tidak diganti">
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            <a href="index.php" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
