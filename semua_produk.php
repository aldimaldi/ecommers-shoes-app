<?php
session_start();
require 'koneksi.php'; 

$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$sepatu = $stmt->fetchAll(PDO::FETCH_ASSOC);

$keranjang_cookie = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];
$jumlah_keranjang = array_sum($keranjang_cookie);
?>
<!DOCTYPE html>
<html>
<head>
<title>Semua Produk | SNEAKERS</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">
<!-- Nav same as index.php -->
<nav class="bg-white p-4 shadow">
<div class="max-w-7xl mx-auto flex justify-between items-center">
<a href="index.php" class="text-2xl font-extrabold text-indigo-600">SNEAKERS.</a>
<!-- cart badge etc -->
</div>
</nav>

<div class="max-w-7xl mx-auto px-6 py-16">
<h1 class="text-4xl font-bold text-slate-900 mb-12">Semua Sepatu</h1>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
<?php foreach($sepatu as $s): ?>
<div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl group">
<img src="uploads/<?= htmlspecialchars($s['image']) ?>" class="w-full aspect-square object-cover rounded-xl mb-4 group-hover:scale-105 transition">
<h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($s['name']) ?></h3>
<p class="text-2xl font-black text-indigo-600 mb-4">Rp <?= number_format($s['price'], 0, ',', '.') ?></p>
<a href="detail.php?produk=<?= htmlspecialchars($s['slug']) ?>" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold text-center hover:bg-indigo-700">Lihat Detail</a>
</div>
<?php endforeach; ?>
</div>
</div>
</body>
</html>

