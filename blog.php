<?php
session_start();
require 'koneksi.php';

// Hanya ambil yang statusnya PUBLISHED
$stmt = $pdo->query("SELECT posts.*, users.name AS author_name FROM posts JOIN users ON posts.user_id = users.id WHERE posts.status = 'PUBLISHED' ORDER BY posts.published_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Penghitung keranjang untuk navbar
$keranjang_cookie = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];
$jumlah_keranjang = array_sum($keranjang_cookie);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Our Blog | SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">
    <nav class="bg-white p-4 shadow mb-8">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-extrabold text-indigo-600">SNEAKERS.</a>
            <div class="flex items-center space-x-6">

                <a href="blog.php" class="relative text-slate-600 hover:text-indigo-600 font-bold text-sm flex items-center transition pr-2">
                    Our Blog
                </a>
                
                <?php if (isset($_SESSION['customer_id'])): ?>
                    
                    <a href="pesanan_saya.php" class="relative text-slate-600 hover:text-indigo-600 font-bold text-sm flex items-center transition pr-2">
                        📦 Pesanan Saya
                        <?php 
                        $jumlah_pesanan_aktif = 0;
                        if (isset($_SESSION['customer_id'])) {
                            $stmt_pesanan = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status != 'COMPLETED'");
                            $stmt_pesanan->execute([$_SESSION['customer_id']]);
                            $jumlah_pesanan_aktif = $stmt_pesanan->fetchColumn();
                        }
                        ?>
                        <?php if($jumlah_pesanan_aktif > 0): ?>
                            <span class="absolute -top-3 -right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-md">
                                <?= $jumlah_pesanan_aktif ?>
                            </span>
                        <?php endif; ?>

                    </a>

                    <a href="keranjang.php" class="relative text-slate-600 hover:text-indigo-600 transition ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <?php if($jumlah_keranjang > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-md">
                                <?= $jumlah_keranjang ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    
                    <div class="w-px h-6 bg-slate-300 ml-4"></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['customer_name'])): ?>
                    <span class="text-slate-700 font-medium text-sm">Halo, <?= htmlspecialchars($_SESSION['customer_name']) ?></span>
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded text-sm font-bold hover:bg-red-600">Logout</a>
                
                <?php elseif (isset($_SESSION['admin_name'])): ?>
                    <a href="admin/index.php" class="text-indigo-600 font-bold text-sm hover:text-indigo-800">Panel Admin</a>
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded text-sm font-bold hover:bg-red-600">Logout</a>
                
                <?php else: ?>
                    <a href="login.php" class="text-slate-600 text-sm hover:text-indigo-600 font-bold">Masuk</a>
                    <a href="register.php" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm font-bold hover:bg-indigo-700">Daftar</a>
                <?php endif; ?>
                
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 mb-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-slate-900 mb-4">SNEAKERS Journal.</h1>
            <p class="text-slate-500 text-lg">Berita terbaru, tips gaya, dan kultur sneakers.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($posts as $b): ?>
                <a href="detail_blog.php?slug=<?= $b['slug'] ?>" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition group">
                    <div class="h-56 overflow-hidden">
                        <img src="uploads/<?= htmlspecialchars($b['thumbnail']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    </div>
                    <div class="p-6">
                        <p class="text-xs font-bold text-indigo-600 mb-2 uppercase tracking-wider"><?= date('d M Y', strtotime($b['published_at'])) ?></p>
                        <h2 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-indigo-600 transition"><?= htmlspecialchars($b['title']) ?></h2>
                        <p class="text-slate-500 text-sm line-clamp-3">
                            <?= htmlspecialchars(substr($b['content'], 0, 150)) ?>...
                        </p>
                        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center gap-2">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold">
                                <?= substr($b['author_name'], 0, 1) ?>
                            </div>
                            <span class="text-sm font-medium text-slate-600"><?= htmlspecialchars($b['author_name']) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>

</div>

<!-- SHOES GRID FOR CUSTOMERS -->
<div class="mt-20 pt-12 border-t border-slate-200">
<h3 class="text-3xl font-bold text-slate-900 mb-12 text-center">🔥 Featured Sneakers</h3>
<?php 
$stmt_shoes = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 12");
$shoes = $stmt_shoes->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
<?php foreach($shoes as $shoe): ?>
<div class="bg-white rounded-2xl shadow-sm hover:shadow-xl border p-6 group transition">
<img src="uploads/<?= htmlspecialchars($shoe['image']) ?>" alt="" class="w-full aspect-square object-cover rounded-xl mb-4 group-hover:scale-105 transition">
<h4 class="font-bold text-lg mb-2 truncate"><?= htmlspecialchars($shoe['name']) ?></h4>
<p class="text-xl font-black text-indigo-600 mb-4">Rp <?= number_format($shoe['price'], 0, ',', '.') ?></p>
<a href="detail.php?produk=<?= htmlspecialchars($shoe['slug']) ?>" class="w-full bg-indigo-600 text-white py-2 rounded-xl font-bold text-center hover:bg-indigo-700">Beli Sekarang</a>
</div>
<?php endforeach; ?>
</div>
<a href="semua_produk.php" class="block text-center mt-12 text-indigo-600 font-bold hover:underline">Lihat Semua Sepatu →</a>
</div>

</div>
</body>
</html>

