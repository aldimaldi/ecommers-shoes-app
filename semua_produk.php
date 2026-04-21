<?php
session_start();
require 'koneksi.php';

$stmt = $pdo->query("SELECT * FROM products WHERE deleted_at IS NULL ORDER BY id DESC");
$sepatu = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Produk | SNEAKERS.</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">

<?php include 'navbar.php'; ?>

<div class="max-w-7xl mx-auto px-4 py-10 pb-16">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-10">
        <div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900">Semua Sepatu</h1>
            <p class="text-slate-500 mt-1"><?= count($sepatu) ?> produk tersedia</p>
        </div>
        <a href="index.php" class="text-indigo-600 font-bold text-sm hover:underline flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Beranda
        </a>
    </div>

    <?php if(empty($sepatu)): ?>
        <div class="bg-white rounded-2xl p-16 text-center border border-slate-100 shadow-sm">
            <div class="text-6xl mb-4">👟</div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Belum ada produk</h3>
            <p class="text-slate-500">Produk akan ditampilkan setelah admin menambahkan.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
            <?php foreach($sepatu as $s): ?>
                <div class="bg-white rounded-2xl p-4 md:p-6 shadow-sm hover:shadow-xl border border-slate-100 group transition duration-300 flex flex-col">
                    <div class="aspect-square bg-slate-100 rounded-xl overflow-hidden mb-4">
                        <img src="uploads/<?= htmlspecialchars($s['image']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" onerror="this.src='https://via.placeholder.com/200?text=Sepatu'" alt="<?= htmlspecialchars($s['name']) ?>">
                    </div>
                    <div class="flex-1 flex flex-col">
                        <h3 class="font-bold text-sm md:text-base mb-2 truncate text-slate-900"><?= htmlspecialchars($s['name']) ?></h3>
                        <p class="text-xl font-black text-indigo-600 mb-4">Rp <?= number_format($s['price'], 0, ',', '.') ?></p>
                        <a href="detail.php?produk=<?= htmlspecialchars($s['slug']) ?>" class="mt-auto w-full bg-indigo-600 text-white py-2.5 rounded-xl font-bold text-center hover:bg-indigo-700 transition text-sm block">Lihat Detail</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="bg-slate-900 text-white py-10">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <div class="bg-purple-600 text-white w-8 h-8 flex items-center justify-center rounded-lg font-bold text-sm">S</div>
                    <span class="font-extrabold text-lg tracking-wider">SNEAKERS.</span>
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
