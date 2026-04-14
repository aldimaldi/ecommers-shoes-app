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
    die("Artikel tidak ditemukan atau belum dipublikasikan.");
}

$keranjang_cookie = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];
$jumlah_keranjang = array_sum($keranjang_cookie);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title><?= htmlspecialchars($blog['title']) ?> | SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">
    
    <nav class="bg-white p-4 shadow mb-8">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-extrabold text-indigo-600">SNEAKERS.</a>
            <div class="flex items-center space-x-6">
                <a href="blog.php" class="text-slate-600 hover:text-indigo-600 transition font-bold">&larr; Kembali ke Blog</a>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 mb-16">
        
        <div class="text-center mb-8">
            <p class="text-indigo-600 font-bold uppercase tracking-widest mb-4"><?= date('d F Y', strtotime($blog['published_at'])) ?></p>
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight mb-6"><?= htmlspecialchars($blog['title']) ?></h1>
            <p class="text-slate-500 font-medium">Ditulis oleh <span class="text-slate-800 font-bold"><?= htmlspecialchars($blog['author_name']) ?></span></p>
        </div>

        <div class="rounded-2xl overflow-hidden shadow-lg mb-10">
            <img src="uploads/<?= htmlspecialchars($blog['thumbnail']) ?>" class="w-full h-auto object-cover max-h-[500px]">
        </div>

        <div class="prose prose-lg prose-indigo max-w-none text-slate-700 leading-relaxed text-lg">
            <?= nl2br(htmlspecialchars($blog['content'])) ?>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-200 flex justify-center">
            <a href="blog.php" class="bg-slate-900 text-white px-8 py-3 rounded-full font-bold hover:bg-slate-800 transition">Lihat Artikel Lainnya</a>
        </div>

    </div>

</body>
</html>