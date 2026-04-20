<?php
session_start();
require 'koneksi.php';

$stmt = $pdo->query("SELECT posts.*, users.name AS author_name FROM posts JOIN users ON posts.user_id = users.id WHERE posts.status = 'PUBLISHED' ORDER BY posts.published_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | SNEAKERS.</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">

<?php include 'navbar.php'; ?>

<div class="max-w-7xl mx-auto px-4 py-10 mb-12">

    <!-- Header -->
    <div class="text-center mb-12">
        <span class="inline-block bg-indigo-50 text-indigo-600 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest mb-4">Sneakers Journal</span>
        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4">Blog & Berita Terkini</h1>
        <p class="text-slate-500 text-lg max-w-xl mx-auto">Tips gaya, rilis terbaru, dan kultur sneakers dari seluruh dunia.</p>
    </div>

    <?php if (empty($posts)): ?>
        <!-- Empty state -->
        <div class="bg-white rounded-2xl p-16 text-center border border-slate-100 shadow-sm">
            <div class="text-6xl mb-4">📰</div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Belum ada artikel</h3>
            <p class="text-slate-500 mb-6">Artikel blog akan tampil di sini setelah dipublikasikan admin.</p>
            <?php if(isset($_SESSION['admin_name'])): ?>
                <a href="admin/index.php" class="inline-block bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-indigo-700 transition">Tambah Artikel</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Featured Post (first post) -->
        <?php $featured = $posts[0]; ?>
        <a href="detail_blog.php?slug=<?= $featured['slug'] ?>" class="group block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition mb-10">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="h-64 md:h-auto overflow-hidden">
                    <img src="uploads/<?= htmlspecialchars($featured['thumbnail']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 min-h-[280px]" onerror="this.src='https://via.placeholder.com/800x400?text=SNEAKERS+Blog'">
                </div>
                <div class="p-8 md:p-10 flex flex-col justify-center">
                    <span class="inline-block bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-4 w-max">Featured</span>
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900 mb-4 group-hover:text-indigo-600 transition leading-tight"><?= htmlspecialchars($featured['title']) ?></h2>
                    <p class="text-slate-500 mb-6 line-clamp-3"><?= htmlspecialchars(substr(strip_tags($featured['content']), 0, 200)) ?>...</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-sm">
                                <?= strtoupper(substr($featured['author_name'], 0, 1)) ?>
                            </div>
                            <span class="text-sm font-medium text-slate-600"><?= htmlspecialchars($featured['author_name']) ?></span>
                        </div>
                        <span class="text-xs text-slate-400"><?= date('d M Y', strtotime($featured['published_at'])) ?></span>
                    </div>
                </div>
            </div>
        </a>

        <!-- Rest of posts -->
        <?php if (count($posts) > 1): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach (array_slice($posts, 1) as $b): ?>
                    <a href="detail_blog.php?slug=<?= $b['slug'] ?>" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition group flex flex-col">
                        <div class="h-48 overflow-hidden">
                            <img src="uploads/<?= htmlspecialchars($b['thumbnail']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" onerror="this.src='https://via.placeholder.com/400x200?text=Blog'">
                        </div>
                        <div class="p-6 flex flex-col flex-1">
                            <p class="text-xs font-bold text-indigo-600 mb-2 uppercase tracking-wider"><?= date('d M Y', strtotime($b['published_at'])) ?></p>
                            <h2 class="text-lg font-bold text-slate-900 mb-3 group-hover:text-indigo-600 transition leading-snug flex-1"><?= htmlspecialchars($b['title']) ?></h2>
                            <p class="text-slate-500 text-sm line-clamp-2 mb-4">
                                <?= htmlspecialchars(substr(strip_tags($b['content']), 0, 120)) ?>...
                            </p>
                            <div class="mt-auto pt-4 border-t border-slate-100 flex items-center gap-2">
                                <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-xs">
                                    <?= strtoupper(substr($b['author_name'], 0, 1)) ?>
                                </div>
                                <span class="text-sm font-medium text-slate-600"><?= htmlspecialchars($b['author_name']) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Featured Sneakers Section -->
    <div class="mt-20 pt-12 border-t border-slate-200">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
            <div>
                <h3 class="text-2xl md:text-3xl font-bold text-slate-900">🔥 Featured Sneakers</h3>
                <p class="text-slate-500 mt-1">Koleksi pilihan yang lagi trending sekarang</p>
            </div>
            <a href="semua_produk.php" class="text-indigo-600 font-bold text-sm hover:underline flex items-center gap-1 shrink-0">
                Lihat Semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <?php
        $stmt_shoes = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 8");
        $shoes = $stmt_shoes->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            <?php foreach($shoes as $shoe): ?>
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl border border-slate-100 p-4 group transition">
                    <div class="aspect-square bg-slate-100 rounded-xl overflow-hidden mb-3">
                        <img src="uploads/<?= htmlspecialchars($shoe['image']) ?>" alt="<?= htmlspecialchars($shoe['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" onerror="this.src='https://via.placeholder.com/200?text=Sepatu'">
                    </div>
                    <h4 class="font-bold text-slate-900 text-sm mb-1 truncate"><?= htmlspecialchars($shoe['name']) ?></h4>
                    <p class="text-indigo-600 font-black text-base mb-3">Rp <?= number_format($shoe['price'], 0, ',', '.') ?></p>
                    <a href="detail.php?produk=<?= htmlspecialchars($shoe['slug']) ?>" class="w-full block bg-indigo-600 text-white text-xs font-bold py-2 px-3 rounded-xl text-center hover:bg-indigo-700 transition">Lihat Detail</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-slate-900 text-white py-10 mt-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <div class="bg-purple-600 text-white w-8 h-8 flex items-center justify-center rounded-lg font-bold text-sm">S</div>
                    <span class="font-extrabold text-lg tracking-wider text-white">SNEAKERS.</span>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed">Toko sepatu terpercaya dengan koleksi sneakers terlengkap dan terkini.</p>
            </div>
            <div>
                <h4 class="font-bold mb-3 text-slate-200">Navigasi</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="index.php" class="hover:text-white transition">Beranda</a></li>
                    <li><a href="semua_produk.php" class="hover:text-white transition">Semua Produk</a></li>
                    <li><a href="blog.php" class="hover:text-white transition">Blog</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-3 text-slate-200">Akun</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <?php if(isset($_SESSION['customer_id'])): ?>
                        <li><a href="pesanan_saya.php" class="hover:text-white transition">Pesanan Saya</a></li>
                        <li><a href="keranjang.php" class="hover:text-white transition">Keranjang</a></li>
                        <li><a href="logout.php" class="hover:text-white transition">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="hover:text-white transition">Masuk</a></li>
                        <li><a href="register.php" class="hover:text-white transition">Daftar</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 pt-6 text-center text-slate-500 text-sm">
            © <?= date('Y') ?> SNEAKERS. — All rights reserved.
        </div>
    </div>
</footer>

</body>
</html>
