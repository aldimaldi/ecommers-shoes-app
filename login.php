<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['admin_id'])) { header("Location: admin/index.php"); exit; }
if (isset($_SESSION['customer_id'])) { header("Location: index.php"); exit; }

$error = '';
$pesan_sukses = '';

// Menangkap pesan sukses dari register
if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil_daftar') {
    $pesan_sukses = 'Akun berhasil dibuat! Silakan login.';
}

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cari user tanpa melihat rolenya (karena dua-duanya bisa login)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        
        // KONDISI PENENTU ARAH (ADMIN / CUSTOMER)
        if ($user['role'] == 'admin') {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            header("Location: admin/index.php");
        } else {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['name'];
            header("Location: index.php");
        }
        exit;
        
    } else {
        $error = 'Email atau Password salah!';
    }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login page</title>
  <!-- <link rel="shortcut icon" type="image/png" href="./assets/images/logos/favicon.png" /> -->
  <link rel="stylesheet" href="./assets/css/styles.min.css" />
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden text-bg-light min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <div class="d-flex justify-content-center w-100 mb-2">
                    <a href="index.php" class="text-nowrap logo-img d-flex align-items-center gap-2 text-decoration-none">
                        <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded-2 fw-bolder" style="width: 32px; height: 32px; font-size: 18px;">
                            S
                        </div>
                        <span class="fw-bolder fs-5 text-primary" style="letter-spacing: 1.5px;">SNEAKERS.</span>
                    </a>
                </div>
                <p class="text-center">Login ke Akun Mu</p>
                <?php if ($pesan_sukses): ?>
                    <div class="alert alert-success" role="alert"><?= $pesan_sukses ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                  <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Username</label>
                    <input type="email" name="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" required>
                  </div>
                  <div class="mb-4">
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="exampleInputPassword1" required>
                  </div>
                  <button type="submit" name="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Sign In</button>
                  <div class="d-flex align-items-center justify-content-center">
                    <p class="fs-4 mb-0 fw-bold">Tidak Memiliki akun ?</p>
                    <a class="text-primary fw-bold ms-2" href="register.php">Register</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="./assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="./assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>