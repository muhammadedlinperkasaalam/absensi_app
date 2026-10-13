<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT u.*, p.nama AS nama_pegawai
                                FROM users u
                                LEFT JOIN pegawai p ON p.id = u.id_pegawai
                                WHERE u.username = ? LIMIT 1");
        $stmt->execute([$username]);
        $akun = $stmt->fetch();

        if(!$akun) {
            $error = 'Username tidak ditemukan.';
        } elseif ($akun['status'] !== 'aktif') {
            $error = 'Akun Anda tidak aktif. Hubungi admin.';
        } elseif (!password_verify($password, $akun['password'])) {
            $error = 'Password salah.';
        } else {
            $_SESSION['user_id'] = $akun['id'];
            $_SESSION['username'] = $akun['username'];
            $_SESSION['role'] = $akun['role'];
            $_SESSION['id_pegawai'] = $akun['id_pegawai'];
            $_SESSION['nama'] = $akun['nama_pegawai'] ?? $akun['usernama'];
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - <?= APP_NAME ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    body { background: linear-gradient(135deg,#2563eb,#1e293b); min-height:100vh; display:flex; align-items:center; }
    .login-card { border:none; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.25); }
</style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-8 col-md-5 col-lg-4">
                <div class="card login-card p-4">
                    <div class="text-center mb-3">
                        <i class="bi bi-fingerprint" style="font-size:2.5rem;color:#2563eb;"></i>
                        <h4 class="mt-2 mb-0"><?= APP_NAME ?></h4>
                        <small class="text-muted">Silahkan login untuk melanjutkan</small>
                    </div>
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                    </button>
                </form>
                <p class="text-center text-muted mt-3 mb-0" style="font-size:.8rem;">
                    &copy; <?= date('Y') ?> <?= APP_NAME ?>
                </p>
                </div>            
            </div>        
        </div>
    </div>
</body>
</html>