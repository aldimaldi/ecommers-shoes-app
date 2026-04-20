<?php
session_start();
require 'koneksi.php';

if (!isset($_GET['slug'])) {
    header("Location: blog.php");
    exit;
}

$slug = $_GET['slug'];
$stmt = $pdo->prepare("
    SELECT posts.*, users.name AS author_name 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.slug = ? AND posts.status = 'PUBLISHED'
");
$stmt->execute([$slug]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    header("Location: blog.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($blog['title']) ?> | SNEAKERS.</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">

<?php include 'navbar.php'; ?>

<div class="max-w-3xl mx-auto px-4 py-10 mb-16">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-slate-400 mb-8">
        <a href="index.php" class="hover:text-indigo-600 transition">Beranda</a>
        <span>/</span>
        <a href="blog.php" class="hover:text-indigo-600 transition">Blog</a>
        <span>/</span>
        <span class="text-slate-600 truncate max-w-[200px]"><?= htmlspecialchars($blog['title']) ?></span>
    </nav>

    <!-- Article Header -->
    <div class="mb-8">
        <p class="text-indigo-600 font-bold uppercase tracking-widest text-xs mb-4"><?= date('d F Y', strtotime($blog['published_at'])) ?></p>
        <h1 class="text-3xl md:text-4xl font-black text-slate-900 leading-tight mb-6"><?= htmlspecialchars($blog['title']) ?></h1>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold">
                <?= strtoupper(substr($blog['author_name'], 0, 1)) ?>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($blog['author_name']) ?></p>
                <p class="text-xs text-slate-400">Penulis</p>
            </div>
        </div>
    </div>

    <!-- Thumbnail -->
    <div class="rounded-2xl overflow-hidden shadow-lg mb-10">
        <img src="uploads/<?= htmlspecialchars($blog['thumbnail']) ?>" class="w-full h-auto object-cover max-h-[500px]" onerror="this.src='https://via.placeholder.com/800x400?text=Blog'">
    </div>

    <!-- Content -->
    <div class="bg-white rounded-2xl p-6 md:p-10 shadow-sm border border-slate-100 mb-10">
        <div class="text-slate-700 leading-relaxed text-base md:text-lg">
            <?= nl2br(htmlspecialchars($blog['content'])) ?>
        </div>
    </div>

    <!-- Back + Share -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-4 border-t border-slate-200">
        <a href="blog.php" class="flex items-center gap-2 text-slate-600 hover:text-indigo-600 font-bold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Blog
        </a>
        <a href="blog.php" class="bg-slate-900 text-white px-6 py-2.5 rounded-full font-bold hover:bg-slate-800 transition text-sm">Artikel Lainnya →</a>
    </div>

    <!-- Recommended Shoes -->
    <div class="mt-20">
        <h3 class="text-2xl font-bold text-slate-900 mb-2">💎 Recommended Sneakers</h3>
        <p class="text-slate-500 text-sm mb-8">Koleksi pilihan yang mungkin kamu suka</p>
        <?php
        $stmt_shoes = $pdo->query("SELECT * FROM products ORDER BY RAND() LIMIT 4");
        $shoes = $stmt_shoes->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($shoes as $shoe): ?>
                <div class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-lg border border-slate-100 group transition">
                    <div class="aspect-square bg-slate-100 rounded-xl overflow-hidden mb-3">
                        <img src="uploads/<?= htmlspecialchars($shoe['image']) ?>" alt="<?= htmlspecialchars($shoe['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition" onerror="this.src='https://via.placeholder.com/200?text=Sepatu'">
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
<footer class="bg-slate-900 text-white py-10">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <div class="flex items-center justify-center space-x-2 mb-4">
            <div class="bg-purple-600 text-white w-8 h-8 flex items-center justify-center rounded-lg font-bold text-sm">S</div>
            <span class="font-extrabold text-lg tracking-wider">SNEAKERS.</span>
        </div>
        <div class="flex justify-center gap-6 text-sm text-slate-400 mb-4">
            <a href="index.php" class="hover:text-white transition">Beranda</a>
            <a href="semua_produk.php" class="hover:text-white transition">Produk</a>
            <a href="blog.php" class="hover:text-white transition">Blog</a>
        </div>
        <p class="text-slate-500 text-sm">© <?= date('Y') ?> SNEAKERS. — All rights reserved.</p>
    </div>
</footer>

</body>
</html>
