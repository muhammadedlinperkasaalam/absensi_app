<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard';
$user = currentUser();
$hariIni = date('Y-m-d');

if (isPengelola()) {
    // Statistik untuk admin/hrd: seluruh pegawai
    $totalPegawai = $pdo->query("SELECT COUNT(*) c FROM pegawai WHERE status='aktif'")->fetch()['c'];

    $stmt = $pdo->prepare("SELECT status, COUNT(*) c FROM absensi WHERE tanggal = ? GROUP BY status");
    $stmt->execute([$hariIni]);
    $rekap = ['hadir'=>0,'terlambat'=>0,'izin'=>0,'sakit'=>0,'alpha'=>0,'cuti'=>0];
    foreach ($stmt->fetchAll() as $row) {
        $rekap[$row['status']] = $row['c'];
    }
    $sudahAbsen = array_sum($rekap);
    $belumAbsen = max($totalPegawai - $sudahAbsen, 0);

    // 10 absensi terbaru
    $terbaru = $pdo->prepare("SELECT a.*, p.nama, p.nip FROM absensi a
                               JOIN pegawai p ON p.id = a.id_pegawai
                               ORDER BY a.tanggal DESC, a.created_at DESC LIMIT 10");
    $terbaru->execute();
    $terbaru = $terbaru->fetchAll();
} else {
    // Statistik untuk pegawai: rekap absensi pribadi bulan ini
    $stmt = $pdo->prepare("SELECT status, COUNT(*) c FROM absensi
                            WHERE id_pegawai = ? AND MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())
                            GROUP BY status");
    $stmt->execute([$user['id_pegawai']]);
    $rekapSaya = ['hadir'=>0,'terlambat'=>0,'izin'=>0,'sakit'=>0,'alpha'=>0,'cuti'=>0];
    foreach ($stmt->fetchAll() as $row) {
        $rekapSaya[$row['status']] = $row['c'];
    }

    $absenHariIni = $pdo->prepare("SELECT * FROM absensi WHERE id_pegawai=? AND tanggal=?");
    $absenHariIni->execute([$user['id_pegawai'], $hariIni]);
    $absenHariIni = $absenHariIni->fetch();
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Dashboard</h4>
    <span class="text-muted"><?= date('l, d F Y') ?></span>
</div>

<?php if (isPengelola()): ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3" style="background:#2563eb;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Total Pegawai Aktif</div>
                        <h3 class="mb-0"><?= $totalPegawai ?></h3>
                    </div>
                    <i class="bi bi-people icon"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3" style="background:#16a34a;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Hadir Hari Ini</div>
                        <h3 class="mb-0"><?= $rekap['hadir'] + $rekap['terlambat'] ?></h3>
                    </div>
                    <i class="bi bi-check-circle icon"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3" style="background:#f59e0b;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Izin / Sakit / Cuti</div>
                        <h3 class="mb-0"><?= $rekap['izin'] + $rekap['sakit'] + $rekap['cuti'] ?></h3>
                    </div>
                    <i class="bi bi-file-medical icon"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3" style="background:#dc2626;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Belum Absen</div>
                        <h3 class="mb-0"><?= $belumAbsen ?></h3>
                    </div>
                    <i class="bi bi-exclamation-circle icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white fw-semibold">Absensi Terbaru</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th><th>NIP</th><th>Nama</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($terbaru)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">Belum ada data absensi</td></tr>
                <?php else: foreach ($terbaru as $t): ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($t['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($t['nip']) ?></td>
                        <td><?= htmlspecialchars($t['nama']) ?></td>
                        <td><?= $t['jam_masuk'] ?? '-' ?></td>
                        <td><?= $t['jam_keluar'] ?? '-' ?></td>
                        <td><?php include __DIR__ . '/includes/status_badge.php'; ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php else: ?>
    <!-- Dashboard Pegawai -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3" style="background:#16a34a;">
                <div class="small">Hadir Bulan Ini</div>
                <h3 class="mb-0"><?= $rekapSaya['hadir'] + $rekapSaya['terlambat'] ?></h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3" style="background:#f59e0b;">
                <div class="small">Izin</div>
                <h3 class="mb-0"><?= $rekapSaya['izin'] ?></h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3" style="background:#0ea5e9;">
                <div class="small">Sakit</div>
                <h3 class="mb-0"><?= $rekapSaya['sakit'] ?></h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3" style="background:#dc2626;">
                <div class="small">Alpha</div>
                <h3 class="mb-0"><?= $rekapSaya['alpha'] ?></h3>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body text-center py-4">
            <h5>Status Absensi Hari Ini</h5>
            <?php if ($absenHariIni): ?>
                <p class="mb-1">Jam Masuk: <b><?= $absenHariIni['jam_masuk'] ?? '-' ?></b></p>
                <p class="mb-3">Jam Keluar: <b><?= $absenHariIni['jam_keluar'] ?? 'Belum absen pulang' ?></b></p>
            <?php else: ?>
                <p class="text-muted mb-3">Anda belum melakukan absensi hari ini.</p>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>absensi/absen.php" class="btn btn-primary">
                <i class="bi bi-clock"></i> Absen Sekarang
            </a>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
