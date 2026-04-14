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

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 text-sm uppercase tracking-wider">
                            <th class="p-4 font-bold">Foto</th>
                            <th class="p-4 font-bold">Nama Sepatu</th>
                            <th class="p-4 font-bold">Kategori</th>
                            <th class="p-4 font-bold">Harga</th>
                            <th class="p-4 font-bold text-center">Aksi / Varian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (count($produk_list) > 0): ?>
                            <?php foreach ($produk_list as $p): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-4">
                                        <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" alt="Foto" class="w-16 h-16 object-cover rounded-lg border border-slate-200">
                                    </td>
                                    <td class="p-4 font-bold text-slate-800">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </td>
                                    <td class="p-4 text-slate-600">
                                        <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-semibold">
                                            <?= htmlspecialchars($p['category_name'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="p-4 font-bold text-indigo-600">
                                        Rp <?= number_format($p['price'], 0, ',', '.') ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <a href="tambah_varian.php?product_id=<?= $p['id'] ?>" class="inline-block bg-indigo-50 text-indigo-700 border border-indigo-200 px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-600 hover:text-white transition">
                                            + Kelola Varian
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">
                                    Belum ada produk. Silakan tambah sepatu baru terlebih dahulu.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>