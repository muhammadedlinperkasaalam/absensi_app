<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole(['admin','hrd']);

$pageTitle = 'Absensi Pegawai';

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

$stmt = $pdo->prepare("SELECT p.id, p.nip, p.nama, a.jam_masuk, a.jam_keluar, a.status, a.keterangan, a.id AS absensi_id
                        FROM pegawai p
                        LEFT JOIN absensi a ON a.id_pegawai = p.id AND a.tanggal = ?
                        WHERE p.status = 'aktif'
                        ORDER BY p.nama");
$stmt->execute([$tanggal]);
$data = $stmt->fetchAll();

// Update manual status oleh admin/hrd (mis. menandai alpha atau mengoreksi)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pegawai = (int)$_POST['id_pegawai'];
    $status = $_POST['status'];
    $keterangan = trim($_POST['keterangan'] ?? '');

    $cek = $pdo->prepare("SELECT id FROM absensi WHERE id_pegawai=? AND tanggal=?");
    $cek->execute([$id_pegawai, $tanggal]);
    $ada = $cek->fetch();

    if ($ada) {
        $u = $pdo->prepare("UPDATE absensi SET status=?, keterangan=? WHERE id=?");
        $u->execute([$status, $keterangan, $ada['id']]);
    } else {
        $u = $pdo->prepare("INSERT INTO absensi (id_pegawai, tanggal, status, keterangan) VALUES (?,?,?,?)");
        $u->execute([$id_pegawai, $tanggal, $status, $keterangan]);
    }
    header("Location: index.php?tanggal=$tanggal&msg=1");
    exit;
}

include '../includes/header.php';
?>

<h4 class="mb-3">Absensi Pegawai</h4>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2">Status absensi berhasil diperbarui.</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-8 col-md-3">
                <label class="form-label mb-1">Pilih Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>">
            </div>
            <div class="col-4 col-md-2">
                <button class="btn btn-primary w-100">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>NIP</th><th>Nama</th><th>Masuk</th><th>Keluar</th><th>Status</th><th>Keterangan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php foreach ($data as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['nip']) ?></td>
                    <td><?= htmlspecialchars($t['nama']) ?></td>
                    <td><?= $t['jam_masuk'] ?? '-' ?></td>
                    <td><?= $t['jam_keluar'] ?? '-' ?></td>
                    <td>
                        <?php if ($t['status']): ?>
                            <?php include __DIR__ . '/../includes/status_badge.php'; ?>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border">Belum Absen</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($t['keterangan'] ?? '-') ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#modalEdit<?= $t['id'] ?>">
                            <i class="bi bi-pencil"></i> Koreksi
                        </button>
                    </td>
                </tr>

                <!-- Modal koreksi status -->
                <div class="modal fade" id="modalEdit<?= $t['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <input type="hidden" name="id_pegawai" value="<?= $t['id'] ?>">
                        <div class="modal-header">
                          <h6 class="modal-title">Koreksi Status - <?= htmlspecialchars($t['nama']) ?></h6>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <?php foreach (['hadir','terlambat','izin','sakit','alpha','cuti'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $t['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2"><?= htmlspecialchars($t['keterangan'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                          <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
