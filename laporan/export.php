<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

$user = currentUser();
$dariTanggal = $_GET['dari'] ?? date('Y-m-01');
$sampaiTanggal = $_GET['sampai'] ?? date('Y-m-d');
$id_pegawai = $_GET['id_pegawai'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$where = ["a.tanggal BETWEEN ? AND ?"];
$params = [$dariTanggal, $sampaiTanggal];

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

$sql = "SELECT a.tanggal, p.nip, p.nama, d.nama_departemen, a.jam_masuk, a.jam_keluar, a.status, a.keterangan
        FROM absensi a
        JOIN pegawai p ON p.id = a.id_pegawai
        LEFT JOIN departemen d ON d.id = p.departemen_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY a.tanggal DESC, p.nama ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_absensi_' . $dariTanggal . '_sd_' . $sampaiTanggal . '.csv');

$output = fopen('php://output', 'w');
// BOM agar Excel membaca UTF-8 dengan benar
fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, ['Tanggal','NIP','Nama','Departemen','Jam Masuk','Jam Keluar','Status','Keterangan']);
foreach ($data as $row) {
    fputcsv($output, [
        $row['tanggal'],
        $row['nip'],
        $row['nama'],
        $row['nama_departemen'] ?? '-',
        $row['jam_masuk'] ?? '-',
        $row['jam_keluar'] ?? '-',
        ucfirst($row['status']),
        $row['keterangan'] ?? '-',
    ]);
}
fclose($output);
exit;