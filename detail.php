<?php
session_start();
require 'koneksi.php';

// 1. Cek apakah ada ID di URL. Kalau tidak ada, lempar kembali ke index
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// 2. Ambil data sepatu berdasarkan ID tersebut
// Kita gunakan LEFT JOIN untuk mengambil nama kategori dari tabel categories
$stmt = $pdo->prepare("
    SELECT products.*, categories.name AS category_name 
    FROM products 
    LEFT JOIN categories ON products.category_id = categories.id 
    WHERE products.id = ?
");
$stmt->execute([$id]);
$sepatu = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Cek apakah sepatunya ada di database
if (!$sepatu) {
    die("<h1>Maaf, produk tidak ditemukan.</h1><a href='index.php'>Kembali ke Toko</a>");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($sepatu['name']) ?> | SNEAKERS</title>
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
        
        <a href="index.php" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-bold mb-8 transition">
            &larr; Kembali ke Katalog
        </a>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8 md:p-12">
                
                <div class="rounded-xl overflow-hidden bg-slate-100 flex items-center justify-center aspect-square">
                    <img src="uploads/<?= htmlspecialchars($sepatu['image']) ?>" alt="<?= htmlspecialchars($sepatu['name']) ?>" class="w-full h-full object-cover">
                </div>

                <div class="flex flex-col justify-center">
                    
                    <span class="inline-block bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-xs font-bold tracking-widest uppercase w-max mb-4">
                        <?= htmlspecialchars($sepatu['category_name'] ?? 'Uncategorized') ?>
                    </span>

                    <h1 class="text-4xl font-extrabold text-slate-900 mb-4"><?= htmlspecialchars($sepatu['name']) ?></h1>
                    <p class="text-3xl font-black text-indigo-600 mb-6">Rp <?= number_format($sepatu['price'], 0, ',', '.') ?></p>

                    <hr class="border-slate-200 mb-6">

                    <h3 class="text-lg font-bold text-slate-800 mb-2">Deskripsi Produk</h3>
                    <p class="text-slate-600 leading-relaxed mb-8 whitespace-pre-wrap"><?= htmlspecialchars($sepatu['description']) ?></p>

                    <div class="flex gap-4 mt-auto">
                        <?php if (isset($_SESSION['customer_name'])): ?>
                            <button class="flex-1 bg-slate-900 text-white font-bold py-4 px-8 rounded-xl hover:bg-slate-800 transition shadow-lg hover:shadow-slate-900/20 active:scale-95">
                                Tambahkan ke Keranjang
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="flex-1 text-center bg-slate-900 text-white font-bold py-4 px-8 rounded-xl hover:bg-slate-800 transition shadow-lg hover:shadow-slate-900/20 active:scale-95">
                                Login untuk Membeli
                            </a>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
        </div>

    </div>

</body>
</html>