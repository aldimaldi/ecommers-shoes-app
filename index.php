<?php
session_start();
require 'koneksi.php';

$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 8");
$sepatu = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Shoeshops</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white p-4 shadow">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-extrabold text-indigo-600">SNEAKERS.</a>
            
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['customer_name'])): ?>
                    <span class="text-slate-700 font-medium">Halo, <?= htmlspecialchars($_SESSION['customer_name']) ?></span>
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded text-sm font-bold hover:bg-red-600">Logout</a>
                
                <?php elseif (isset($_SESSION['admin_name'])): ?>
                    <a href="admin/index.php" class="text-indigo-600 font-bold hover:text-indigo-800">Dashboard Admin</a>
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded text-sm font-bold hover:bg-red-600">Logout</a>
                
                <?php else: ?>
                    <a href="login.php" class="text-slate-600 hover:text-indigo-600 font-bold">Masuk</a>
                    <a href="register.php" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm font-bold hover:bg-indigo-700">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-12">
        <h2 class="text-3xl font-extrabold mb-8">Koleksi Terpopuler</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            
            <?php if(count($sepatu) > 0): ?>
                <?php foreach($sepatu as $item): ?>
                    <div class="bg-white rounded-xl shadow p-4">
                        <img src="uploads/<?= $item['image'] ?? 'sepatu_default.jpg' ?>" alt="Sepatu" class="w-full h-48 object-cover rounded-lg mb-4">
                        <h3 class="text-lg font-bold"><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="text-indigo-600 font-extrabold mt-2">Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                        <a href="detail.php?id=<?= $item['id'] ?>" class="block w-full text-center bg-slate-900 text-white mt-4 py-2 rounded">Detail</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-slate-500">Belum ada produk di database.</p>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>