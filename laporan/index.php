<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'Laporan Absensi';
$user = currentUser();

$dariTanggal = $_GET['dari'] ?? date('Y-m-01');
$sampaiTanggal = $_GET['sampai'] ?? date('Y-m-d');
$id_pegawai = $_GET['id_pegawai'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$where = ["a.tanggal BETWEEN ? AND ?"];
$params = [$dariTanggal, $sampaiTanggal];

// Pegawai hanya boleh lihat data dirinya sendiri
if (isPegawai()) {
    $where[] = "a.id_pegawai = ?";
    $params[] = $user['id_pegawai'];
} elseif ($id_pegawai !== '') {
    $where[] = "a.id_pegawai = ?";
    $params[] = $id_pegawai;
}

if ($statusFilter !== '') {
    $where[] = "a.status = ?";
    $params[] = $statusFilter;
}

$sql = "SELECT a.*, p.nip, p.nama, d.nama_departemen
        FROM absensi a
        JOIN pegawai p ON p.id = a.id_pegawai
        LEFT JOIN departemen d ON d.id = p.departemen_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY a.tanggal DESC, p.nama ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan = $stmt->fetchAll();

// Rekap ringkas
$rekap = ['hadir'=>0,'terlambat'=>0,'izin'=>0,'sakit'=>0,'alpha'=>0,'cuti'=>0];
foreach ($laporan as $l) {
    if (isset($rekap[$l['status']])) $rekap[$l['status']]++;
}

$daftarPegawai = isPengelola() ? $pdo->query("SELECT id, nama FROM pegawai ORDER BY nama")->fetchAll() : [];

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">Laporan Absensi</h4>
    <a href="export.php?<?= http_build_query($_GET) ?>" class="btn btn-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label mb-1">Dari</label>
                <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($dariTanggal) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1">Sampai</label>
                <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($sampaiTanggal) ?>">
            </div>
            <?php if (isPengelola()): ?>
            <div class="col-6 col-md-3">
                <label class="form-label mb-1">Pegawai</label>
                <select name="id_pegawai" class="form-select">
                    <option value="">Semua Pegawai</option>
                    <?php foreach ($daftarPegawai as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $id_pegawai==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach (['hadir','terlambat','izin','sakit','alpha','cuti'] as $s): ?>
                        <option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-2 mb-3">
    <?php
    $warnaRekap = ['hadir'=>'success','terlambat'=>'warning','izin'=>'info','sakit'=>'primary','alpha'=>'danger','cuti'=>'secondary'];
    foreach ($rekap as $key => $val): ?>
        <div class="col-4 col-md-2">
            <div class="card text-center py-2">
                <div class="fw-bold text-<?= $warnaRekap[$key] ?>"><?= $val ?></div>
                <div class="small text-muted"><?= ucfirst($key) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th><th>NIP</th><th>Nama</th><th>Departemen</th>
                    <th>Masuk</th><th>Keluar</th><th>Status</th><th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($laporan)): ?>
                <tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data pada periode ini</td></tr>
            <?php else: foreach ($laporan as $t): ?>
                <tr>
                    <td><?= date('d-m-Y', strtotime($t['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($t['nip']) ?></td>
                    <td><?= htmlspecialchars($t['nama']) ?></td>
                    <td><?= htmlspecialchars($t['nama_departemen'] ?? '-') ?></td>
                    <td><?= $t['jam_masuk'] ?? '-' ?></td>
                    <td><?= $t['jam_keluar'] ?? '-' ?></td>
                    <td><?php include __DIR__ . '/../includes/status_badge.php'; ?></td>
                    <td><?= htmlspecialchars($t['keterangan'] ?? '-') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
