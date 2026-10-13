<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole(['admin','hrd']);

$pageTitle = 'Edit Pegawai';
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
$stmt->execute([$id]);
$pegawai = $stmt->fetch();

if (!$pegawai) {
    header('Location: index.php');
    exit;
}

$jabatanList = $pdo->query("SELECT * FROM jabatan ORDER BY nama_jabatan")->fetchAll();
$deptList = $pdo->query("SELECT * FROM departemen ORDER BY nama_departemen")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = trim($_POST['nip']);
    $nama = trim($_POST['nama']);
    $jabatan_id = $_POST['jabatan_id'] ?: null;
    $departemen_id = $_POST['departemen_id'] ?: null;
    $no_hp = trim($_POST['no_hp']);
    $email = trim($_POST['email']);
    $alamat = trim($_POST['alamat']);
    $tanggal_masuk = $_POST['tanggal_masuk'] ?: null;
    $status = $_POST['status'];

    if ($nip === '' || $nama === '') {
        $error = 'NIP dan Nama wajib diisi.';
    } else {
        $cek = $pdo->prepare("SELECT id FROM pegawai WHERE nip = ? AND id != ?");
        $cek->execute([$nip, $id]);
        if ($cek->fetch()) {
            $error = 'NIP sudah digunakan pegawai lain.';
        } else {
            $stmt = $pdo->prepare("UPDATE pegawai SET nip=?, nama=?, jabatan_id=?, departemen_id=?, no_hp=?, email=?, alamat=?, tanggal_masuk=?, status=? WHERE id=?");
            $stmt->execute([$nip, $nama, $jabatan_id, $departemen_id, $no_hp, $email, $alamat, $tanggal_masuk, $status, $id]);
            header('Location: index.php?msg=edit');
            exit;
        }
    }
    // supaya form tetap menampilkan input terbaru jika error
    $pegawai = array_merge($pegawai, $_POST);
}

include '../includes/header.php';
?>

<h4 class="mb-3">Edit Pegawai</h4>

<div class="card">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control" required value="<?= htmlspecialchars($pegawai['nip']) ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($pegawai['nama']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jabatan</label>
                    <select name="jabatan_id" class="form-select">
                        <option value="">-- Pilih Jabatan --</option>
                        <?php foreach ($jabatanList as $j): ?>
                            <option value="<?= $j['id'] ?>" <?= $j['id']==$pegawai['jabatan_id']?'selected':'' ?>><?= htmlspecialchars($j['nama_jabatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Departemen</label>
                    <select name="departemen_id" class="form-select">
                        <option value="">-- Pilih Departemen --</option>
                        <?php foreach ($deptList as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $d['id']==$pegawai['departemen_id']?'selected':'' ?>><?= htmlspecialchars($d['nama_departemen']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($pegawai['no_hp'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($pegawai['email'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($pegawai['alamat'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" class="form-control" value="<?= htmlspecialchars($pegawai['tanggal_masuk'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="aktif" <?= $pegawai['status']==='aktif'?'selected':'' ?>>Aktif</option>
                        <option value="nonaktif" <?= $pegawai['status']==='nonaktif'?'selected':'' ?>>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
                <a href="index.php" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
