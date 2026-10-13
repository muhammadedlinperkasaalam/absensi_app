<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole(['admin','hrd']);

$pageTitle = 'Data Pegawai';

// Hapus pegawai
if (isset($_GET['hapus']) && isAdmin()) {
    $stmt = $pdo->prepare("DELETE FROM pegawai WHERE id = ?");
    $stmt->execute([(int)$_GET['hapus']]);
    header('Location: index.php?msg=hapus');
    exit;
}

$q = trim($_GET['q'] ?? '');
$sql = "SELECT p.*, j.nama_jabatan, d.nama_departemen
        FROM pegawai p
        LEFT JOIN jabatan j ON j.id = p.jabatan_id
        LEFT JOIN departemen d ON d.id = p.departemen_id";
$params = [];
if ($q !== '') {
    $sql .= " WHERE p.nama LIKE ? OR p.nip LIKE ?";
    $params = ["%$q%", "%$q%"];
}
$sql .= " ORDER BY p.nama ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftarPegawai = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">Data Pegawai</h4>
    <a href="tambah.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah Pegawai</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2">Data berhasil diperbarui.</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-9 col-md-4">
                <input type="text" name="q" class="form-control" placeholder="Cari nama / NIP..." value="<?= htmlspecialchars($q) ?>">
            </div>
            <div class="col-3 col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>NIP</th><th>Nama</th><th>Jabatan</th><th>Departemen</th><th>No. HP</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($daftarPegawai)): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data pegawai</td></tr>
            <?php else: foreach ($daftarPegawai as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nip']) ?></td>
                    <td><?= htmlspecialchars($p['nama']) ?></td>
                    <td><?= htmlspecialchars($p['nama_jabatan'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['nama_departemen'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['no_hp'] ?? '-') ?></td>
                    <td>
                        <span class="badge bg-<?= $p['status']==='aktif' ? 'success':'secondary' ?>">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <?php if (isAdmin()): ?>
                        <a href="index.php?hapus=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Hapus pegawai ini? Data absensi & akun terkait juga akan terhapus.')">
                           <i class="bi bi-trash"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
