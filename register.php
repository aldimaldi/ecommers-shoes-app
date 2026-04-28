<?php
session_start();
require 'koneksi.php';

// Jika sudah login, tendang ke halaman depan
if (isset($_SESSION['customer_id']) || isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$pesan = '';

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'customer'; // Default role
    $waktu = date('Y-m-d H:i:s');

    try {
        // Cek apakah email sudah terdaftar
        $cek = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $cek->execute([$email]);
        
        if ($cek->rowCount() > 0) {
            $pesan = 'Email sudah digunakan, silakan gunakan email lain!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $role, $waktu, $waktu]);
            
            // Langsung arahkan ke form login setelah berhasil
            header("Location: login.php?pesan=berhasil_daftar");
            exit;
        }
    } catch (PDOException $e) {
        $pesan = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register Page</title>
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
                <div class="d-flex justify-content-center w-100">
                    <a href="index.php" class="text-nowrap logo-img d-flex align-items-center gap-2 text-decoration-none">
                        <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded-2 fw-bolder" style="width: 32px; height: 32px; font-size: 18px;">
                            S
                        </div>
                        <span class="fw-bolder fs-5 text-primary" style="letter-spacing: 1.5px;">SNEAKERS.</span>
                    </a>
                </div>
                <p class="text-center">Buat akun</p>
                <?php if ($pesan): ?>
                    <div class="alert alert-danger" role="alert"><?= $pesan ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                  <div class="mb-3">
                    <label for="exampleInputtext1" class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" id="exampleInputtext1" aria-describedby="textHelp" required>
                  </div>
                  <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" required> 
                  </div>
                  <div class="mb-4">
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="exampleInputPassword1" required>
                  </div>
                  <button type="submit" name="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Sign Up</button>
                  <div class="d-flex align-items-center justify-content-center">
                    <p class="fs-4 mb-0 fw-bold">Sudah Memiliki Akun?</p>
                    <a class="text-primary fw-bold ms-2" href="login.php">Log In</a>
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