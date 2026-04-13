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

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login | SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-extrabold text-center text-slate-800 mb-6">Selamat Datang</h2>
        
        <?php if ($pesan_sukses): ?>
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4 text-sm"><?= $pesan_sukses ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4 text-sm"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-slate-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-indigo-500">
            </div>
            <div class="mb-6">
                <label class="block text-slate-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-indigo-500">
            </div>
            <button type="submit" name="submit" class="w-full bg-slate-900 text-white font-bold py-2 px-4 rounded hover:bg-slate-800 transition">
                Masuk
            </button>
        </form>
        <div class="text-center mt-4">
            <a href="register.php" class="text-sm text-slate-500 hover:text-indigo-600">Belum punya akun? Daftar</a>
        </div>
    </div>
</body>
</html>