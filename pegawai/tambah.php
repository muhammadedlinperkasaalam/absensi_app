<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole(['admin','hrd']);

$pageTitle = 'Tambah Pegawai';
$error = '';

$jabatanList = $pdo->query("SELECT * FROM jabatan ORDER BY nama_jabatan")->fetchAll();
$deptList = $pdo->query("SELECT * FROM departemen ORDER BY nama_departemen")->fetchAll();

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
        // cek NIP unik
        $cek = $pdo->prepare("SELECT id FROM pegawai WHERE nip = ?");
        $cek->execute([$nip]);
        if ($cek->fetch()) {
            $error = 'NIP sudah digunakan pegawai lain.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO pegawai (nip, nama, jabatan_id, departemen_id, no_hp, email, alamat, tanggal_masuk, status)
                                    VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$nip, $nama, $jabatan_id, $departemen_id, $no_hp, $email, $alamat, $tanggal_masuk, $status]);
            header('Location: index.php?msg=tambah');
            exit;
        }
    }
}

include '../includes/header.php';
?>

<h4 class="mb-3">Tambah Pegawai</h4>

<div class="card">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control" required value="<?= htmlspecialchars($_POST['nip'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jabatan</label>
                    <select name="jabatan_id" class="form-select">
                        <option value="">-- Pilih Jabatan --</option>
                        <?php foreach ($jabatanList as $j): ?>
                            <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nama_jabatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Departemen</label>
                    <select name="departemen_id" class="form-select">
                        <option value="">-- Pilih Departemen --</option>
                        <?php foreach ($deptList as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nama_departemen']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" class="form-control" value="<?= htmlspecialchars($_POST['tanggal_masuk'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                <a href="index.php" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>