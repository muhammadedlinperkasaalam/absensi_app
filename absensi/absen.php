<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole(['pegawai']);

$pageTitle = 'Absen Saya';
$user = currentUser();
$hariIni = date('Y-m-d');
$jamSekarang = date('H:i:s');
$msg = '';
$errorMsg = '';

// Ambil pengaturan jam kerja
$pengaturan = $pdo->query("SELECT * FROM pengaturan LIMIT 1")->fetch();

// Ambil data absensi hari ini (jika ada)
$stmt = $pdo->prepare("SELECT * FROM absensi WHERE id_pegawai = ? AND tanggal = ?");
$stmt->execute([$user['id_pegawai'], $hariIni]);
$absenHariIni = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $lokasi = trim($_POST['lokasi'] ?? '');

    if ($aksi === 'masuk') {
        if ($absenHariIni) {
            $errorMsg = 'Anda sudah melakukan absen masuk hari ini.';
        } else {
            // tentukan status telat / hadir berdasarkan jam masuk + toleransi
            $batasTelat = date('H:i:s', strtotime($pengaturan['jam_masuk'] . " +{$pengaturan['toleransi_menit']} minutes"));
            $status = ($jamSekarang > $batasTelat) ? 'terlambat' : 'hadir';

            $stmt = $pdo->prepare("INSERT INTO absensi (id_pegawai, tanggal, jam_masuk, status, lokasi_masuk)
                                    VALUES (?,?,?,?,?)");
            $stmt->execute([$user['id_pegawai'], $hariIni, $jamSekarang, $status, $lokasi]);
            header('Location: absen.php?msg=masuk');
            exit;
        }
    } elseif ($aksi === 'keluar') {
        if (!$absenHariIni) {
            $errorMsg = 'Anda belum melakukan absen masuk hari ini.';
        } elseif ($absenHariIni['jam_keluar']) {
            $errorMsg = 'Anda sudah melakukan absen pulang hari ini.';
        } else {
            $stmt = $pdo->prepare("UPDATE absensi SET jam_keluar=?, lokasi_keluar=? WHERE id=?");
            $stmt->execute([$jamSekarang, $lokasi, $absenHariIni['id']]);
            header('Location: absen.php?msg=keluar');
            exit;
        }
    } elseif ($aksi === 'izin') {
        if ($absenHariIni) {
            $errorMsg = 'Absensi hari ini sudah tercatat.';
        } else {
            $jenis = $_POST['jenis_izin'];
            $keterangan = trim($_POST['keterangan'] ?? '');
            $stmt = $pdo->prepare("INSERT INTO absensi (id_pegawai, tanggal, status, keterangan) VALUES (?,?,?,?)");
            $stmt->execute([$user['id_pegawai'], $hariIni, $jenis, $keterangan]);
            header('Location: absen.php?msg=izin');
            exit;
        }
    }
}

// refresh data setelah aksi
$stmt = $pdo->prepare("SELECT * FROM absensi WHERE id_pegawai = ? AND tanggal = ?");
$stmt->execute([$user['id_pegawai'], $hariIni]);
$absenHariIni = $stmt->fetch();

// riwayat 7 hari terakhir
$riwayat = $pdo->prepare("SELECT * FROM absensi WHERE id_pegawai = ? ORDER BY tanggal DESC LIMIT 7");
$riwayat->execute([$user['id_pegawai']]);
$riwayat = $riwayat->fetchAll();

include '../includes/header.php';
?>

<h4 class="mb-3">Absen Saya</h4>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2">Absensi berhasil dicatat.</div>
<?php endif; ?>
<?php if ($errorMsg): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-body text-center">
                <h6 class="text-muted">Waktu Sekarang</h6>
                <h2 id="jamSekarang" class="fw-bold"><?= date('H:i:s') ?></h2>
                <p class="text-muted mb-4"><?= date('l, d F Y') ?></p>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <form method="POST" onsubmit="return isiLokasi(this)">
                        <input type="hidden" name="aksi" value="masuk">
                        <input type="hidden" name="lokasi" class="input-lokasi">
                        <button type="submit" class="btn btn-success px-4" <?= $absenHariIni ? 'disabled':'' ?>>
                            <i class="bi bi-box-arrow-in-right"></i> Absen Masuk
                        </button>
                    </form>
                    <form method="POST" onsubmit="return isiLokasi(this)">
                        <input type="hidden" name="aksi" value="keluar">
                        <input type="hidden" name="lokasi" class="input-lokasi">
                        <button type="submit" class="btn btn-primary px-4" <?= (!$absenHariIni || $absenHariIni['jam_keluar']) ? 'disabled':'' ?>>
                            <i class="bi bi-box-arrow-left"></i> Absen Pulang
                        </button>
                    </form>
                </div>

                <button class="btn btn-outline-secondary mt-3" data-bs-toggle="modal" data-bs-target="#modalIzin" <?= $absenHariIni ? 'disabled':'' ?>>
                    <i class="bi bi-file-earmark-text"></i> Ajukan Izin / Sakit / Cuti
                </button>

                <hr>
                <p class="mb-1">Jam Masuk: <b><?= $absenHariIni['jam_masuk'] ?? '-' ?></b></p>
                <p class="mb-0">Jam Keluar: <b><?= $absenHariIni['jam_keluar'] ?? '-' ?></b></p>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">Riwayat 7 Hari Terakhir</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Tanggal</th><th>Masuk</th><th>Keluar</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (empty($riwayat)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat</td></tr>
                    <?php else: foreach ($riwayat as $t): ?>
                        <tr>
                            <td><?= date('d-m-Y', strtotime($t['tanggal'])) ?></td>
                            <td><?= $t['jam_masuk'] ?? '-' ?></td>
                            <td><?= $t['jam_keluar'] ?? '-' ?></td>
                            <td><?php include __DIR__ . '/../includes/status_badge.php'; ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Izin -->
<div class="modal fade" id="modalIzin" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="aksi" value="izin">
        <div class="modal-header">
          <h5 class="modal-title">Ajukan Izin / Sakit / Cuti</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Jenis</label>
                <select name="jenis_izin" class="form-select" required>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="cuti">Cuti</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Jelaskan alasan..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Kirim</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
setInterval(function(){
    document.getElementById('jamSekarang').textContent = new Date().toLocaleTimeString('id-ID');
}, 1000);

// Ambil lokasi GPS dari HP (opsional, jika user mengizinkan)
function isiLokasi(form) {
    const inputLokasi = form.querySelector('.input-lokasi');
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos){
            inputLokasi.value = pos.coords.latitude + ',' + pos.coords.longitude;
            form.submit();
        }, function(){
            inputLokasi.value = 'Lokasi tidak tersedia';
            form.submit();
        }, { timeout: 3000 });
        return false; // tunggu geolocation, submit manual di callback
    }
    return true;
}
</script>

<?php include '../includes/footer.php'; ?>
