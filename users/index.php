<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole(['admin']);

$pageTitle = 'Kelola Akun & Hak Akses';
$error = '';

// Hapus akun
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    if ($idHapus !== (int)$_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$idHapus]);
    }
    header('Location: index.php?msg=hapus');
    exit;
}

// Tambah akun baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $id_pegawai = $_POST['id_pegawai'] ?: null;

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $cek->execute([$username]);
        if ($cek->fetch()) {
            $error = 'Username sudah digunakan.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, id_pegawai, status) VALUES (?,?,?,?, 'aktif')");
            $stmt->execute([$username, $hash, $role, $id_pegawai]);
            header('Location: index.php?msg=tambah');
            exit;
        }
    }
}

$daftarUser = $pdo->query("SELECT u.*, p.nama AS nama_pegawai FROM users u
                            LEFT JOIN pegawai p ON p.id = u.id_pegawai
                            ORDER BY u.id ASC")->fetchAll();
$daftarPegawai = $pdo->query("SELECT id, nama FROM pegawai WHERE status='aktif' ORDER BY nama")->fetchAll();

include '../includes/header.php';
?>

<h4 class="mb-3">Kelola Akun & Hak Akses</h4>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success py-2">Data akun berhasil diperbarui.</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white fw-semibold">Tambah Akun Baru</div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="text" name="password" class="form-control" required minlength="6">
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hak Akses (Role)</label>
                        <select name="role" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="hrd">HRD</option>
                            <option value="pegawai">Pegawai</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hubungkan ke Data Pegawai (opsional, untuk role Pegawai)</label>
                        <select name="id_pegawai" class="form-select">
                            <option value="">-- Tidak Terhubung --</option>
                            <?php foreach ($daftarPegawai as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah Akun</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white fw-semibold">Daftar Akun</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Username</th><th>Role</th><th>Pegawai</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($daftarUser as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><span class="badge bg-dark"><?= ucfirst($u['role']) ?></span></td>
                            <td><?= htmlspecialchars($u['nama_pegawai'] ?? '-') ?></td>
                            <td>
                                <span class="badge bg-<?= $u['status']==='aktif'?'success':'secondary' ?>"><?= ucfirst($u['status']) ?></span>
                            </td>
                            <td>
                                <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="index.php?hapus=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus akun ini?')"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>