<?php
session_start();
include 'koneksi.php';
include 'csrf.php';

$error = "";

if (isset($_SESSION['login_admin'])) {
    header("Location: admin.php");
    exit;
}

if (isset($_POST['login'])) {
    if (!csrf_verify()) {
        $error = "Sesi form sudah kedaluwarsa, silakan reload halaman lalu coba lagi.";
    } else {
        $username = htmlspecialchars(strip_tags(trim($_POST['username'])));
        $password = $_POST['password'];

        $stmt = $koneksi->prepare("SELECT * FROM admin WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $admin = $stmt->fetch();

        // password_verify aman dari timing attack & otomatis cocok sama hash bcrypt di DB
        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true); // cegah session fixation
            $_SESSION['login_admin'] = $admin['username'];
            header("Location: admin.php");
            exit;
        } else {
            $error = "Username atau Password salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin Balai Desa</title>
    <link href="bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #1e1e2f; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { width: 100%; max-width: 400px; border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .card-header { background: linear-gradient(45deg, #2d2d44, #1e1e2f); padding: 25px; border-top-left-radius: 15px !important; border-top-right-radius: 15px !important; text-align: center; color: #fff; }
        .form-control { border-radius: 8px; padding: 12px; }
        .btn-info { background: linear-gradient(45deg, #0dcaf0, #0bacce); border: none; color: #fff; font-weight: bold; padding: 12px; border-radius: 8px; transition: 0.3s; }
        .btn-info:hover { opacity: 0.9; transform: translateY(-2px); color: #fff; }
        .alert { border-radius: 8px; font-weight: bold; text-align: center; }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0 fw-bold">🏢 LOGIN ADMIN</h4>
            <small class="opacity-75">Sistem Pelayanan Digital Desa</small>
        </div>
        <div class="card-body p-4">

            <?php if($error != ""): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label fw-bold text-secondary">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username admin" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" name="login" class="btn btn-info text-uppercase">Masuk Dashboard</button>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>
