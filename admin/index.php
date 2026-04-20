<?php
session_start();
require '../koneksi.php';

// Pengecekan krusial: Wajib admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil semua data sepatu beserta nama kategorinya dari database
$stmt = $pdo->query("
    SELECT products.*, categories.name AS category_name 
    FROM products 
    LEFT JOIN categories ON products.category_id = categories.id 
    ORDER BY products.id DESC
");
$produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard Admin | SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-8">

    <div class="max-w-7xl mx-auto">
        
        <div class="bg-white p-6 rounded-xl shadow mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-indigo-600">Dashboard Panel</h1>
                <p class="text-slate-500 text-sm mt-1">Pusat kendali e-commerce Anda.</p>
            </div>
            <div class="flex items-center gap-6">
                <p class="font-medium text-slate-700">Halo, <b><?= htmlspecialchars($_SESSION['admin_name']) ?></b></p>

                <a href="kelola_blog.php" class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-100 transition border border-indigo-200 shadow-sm">
                    ✍️ Kelola Blog
                </a>
                
                <a href="kelola_kategori.php" class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-100 transition border border-indigo-200 shadow-sm">
                    📁 Kategori
                </a>
                
                <a href="kelola_voucher.php" class="bg-indigo-100 text-indigo-700 px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-indigo-200 transition border border-indigo-200">
                    🎟️ Kelola Voucher
                </a>

                <a href="kelola_pesanan.php" class="bg-indigo-600 text-white px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-indigo-700 transition shadow-sm">
                    📦 Kelola Pesanan
                </a>

                <a href="logout.php" class="bg-red-500 text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-red-600 transition shadow-sm">Logout</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h2 class="text-lg font-bold text-slate-800">Daftar Sepatu</h2>
                <a href="tambah_product.php" class="bg-slate-900 text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-slate-800 transition shadow-sm">
                    + Tambah Sepatu Baru
                </a>
            </div>

            <div class="p-8">
                <?php if (count($produk_list) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($produk_list as $p): ?>
                            <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl border border-slate-100 transition-all duration-300 group">
                                <div class="relative mb-4">
                                    <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" alt="Foto" class="w-full aspect-square object-cover rounded-xl border-2 border-slate-200 group-hover:border-indigo-300 transition">
                                    <span class="absolute top-3 left-3 bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full text-xs font-bold">
                                        <?= htmlspecialchars($p['category_name'] ?? 'No Cat') ?>
                                    </span>
                                </div>
                                
                                <h3 class="font-bold text-lg text-slate-900 mb-2 truncate leading-tight"><?= htmlspecialchars($p['name']) ?></h3>
                                
                                <p class="text-2xl font-black text-indigo-600 mb-4">Rp <?= number_format($p['price'], 0, ',', '.') ?></p>
                                
                                <div class="flex gap-2">
                                    <a href="tambah_varian.php?product_id=<?= $p['id'] ?>" class="flex-1 bg-indigo-600 text-white text-sm font-bold py-2 px-4 rounded-xl text-center hover:bg-indigo-700 transition shadow-sm">
                                        Kelola Varian
                                    </a>
                                    <a href="edit_product.php?id=<?= $p['id'] ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold p-2 rounded-xl transition flex items-center justify-center shadow-sm w-12 h-12" title="Edit">
                                        ✏️
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-16 text-center border-2 border-dashed border-slate-200 rounded-2xl">
                        <div class="w-20 h-20 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4l-8-4m0 0v10l-8 4m0-4V7m16 0h-4m-4 0H4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-600 mb-2">Belum ada produk</h3>
                        <p class="text-slate-500 mb-6">Silakan tambah sepatu baru terlebih dahulu.</p>
                        <a href="tambah_product.php" class="bg-indigo-600 text-white font-bold py-3 px-8 rounded-xl hover:bg-indigo-700 transition shadow-lg">
                            + Tambah Produk Pertama
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</body>
</html>