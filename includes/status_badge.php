<?php
/**
 * Komponen kecil untuk menampilkan badge warna sesuai status absensi.
 * Membutuhkan variabel $t['status'] (array baris absensi) yang sudah tersedia di scope pemanggil.
 */
$mapWarna = [
    'hadir'     => 'success',
    'terlambat' => 'warning text-dark',
    'izin'      => 'info text-dark',
    'sakit'     => 'primary',
    'alpha'     => 'danger',
    'cuti'      => 'secondary',
];
$statusNow = $t['status'] ?? '-';
$warna = $mapWarna[$statusNow] ?? 'secondary';
?>
<span class="badge bg-<?= $warna ?>"><?= ucfirst($statusNow) ?></span>
