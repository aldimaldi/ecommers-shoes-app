<?php
session_start();

// Pengecekan krusial: Jika tidak ada session admin_id, tendang kembali ke login!
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-8">

    <div class="max-w-7xl mx-auto bg-white p-6 rounded-xl shadow">
        <div class="flex justify-between items-center mb-8 border-b pb-4">
            <h1 class="text-2xl font-bold text-indigo-600">Dashboard Panel</h1>
            <div class="flex items-center gap-4">
                <p>Halo, <b><?= htmlspecialchars($_SESSION['admin_name']) ?></b></p>
                <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded text-sm font-bold hover:bg-red-600">Keluar</a>
            </div>
        </div>

        <p class="text-slate-600">Selamat datang di pusat kendali toko sepatu Anda.</p>
        
        <div class="mt-8">
            <a href="tambah_product.php" class="bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-800">+ Tambah Sepatu Baru</a>
        </div>
    </div>

</body>
</html>