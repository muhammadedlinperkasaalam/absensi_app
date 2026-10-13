<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
requireRole(['admin']);

$pageTitle = 'Pengaturan';
$msg = '';

$pengaturan = $pdo->query("SELECT * FROM pengaturan LIMIT 1")->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_instansi = trim($_POST['nama_instansi']);
    $jam_masuk = $_POST['jam_masuk'];
    $jam_keluar = $_POST['jam_keluar'];
    $toleransi = (int)$_POST['toleransi_menit'];

    $stmt = $pdo->prepare("UPDATE pengaturan SET nama_instansi=?, jam_masuk=?, jam_keluar=?, toleransi_menit=? WHERE id=?");
    $stmt->execute([$nama_instansi, $jam_masuk, $jam_keluar, $toleransi, $pengaturan['id']]);
    $msg = 'Pengaturan berhasil disimpan.';

    $pengaturan = $pdo->query("SELECT * FROM pengaturan LIMIT 1")->fetch();
}

include 'includes/header.php';
?>

<h4 class="mb-3">Pengaturan Sistem</h4>

<div class="card" style="max-width:600px;">
    <div class="card-body">
        <?php if ($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Instansi / Perusahaan</label>
                <input type="text" name="nama_instansi" class="form-control" value="<?= htmlspecialchars($pengaturan['nama_instansi']) ?>">
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Jam Masuk Kerja</label>
                    <input type="time" name="jam_masuk" class="form-control" value="<?= $pengaturan['jam_masuk'] ?>">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Jam Pulang Kerja</label>
                    <input type="time" name="jam_keluar" class="form-control" value="<?= $pengaturan['jam_keluar'] ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Toleransi Keterlambatan (menit)</label>
                <input type="number" name="toleransi_menit" class="form-control" value="<?= $pengaturan['toleransi_menit'] ?>" min="0">
                <small class="text-muted">Absen masuk setelah jam masuk + toleransi akan otomatis berstatus "Terlambat"</small>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Pengaturan</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
