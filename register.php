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

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Akun | SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-extrabold text-center text-slate-800 mb-6">Buat Akun Baru</h2>
        
        <?php if ($pesan): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4 text-sm"><?= $pesan ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-slate-700 text-sm font-bold mb-2">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-indigo-500">
            </div>
            <div class="mb-4">
                <label class="block text-slate-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-indigo-500">
            </div>
            <div class="mb-6">
                <label class="block text-slate-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border border-slate-300 rounded focus:outline-none focus:border-indigo-500">
            </div>
            <button type="submit" name="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded hover:bg-indigo-700 transition">
                Daftar Sekarang
            </button>
        </form>
        <div class="text-center mt-4">
            <a href="login.php" class="text-sm text-slate-500 hover:text-indigo-600">Sudah punya akun? Masuk</a>
        </div>
    </div>
</body>
</html>