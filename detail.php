<?php
session_start();
require 'koneksi.php';

// 1. Cek apakah ada slug (produk) di URL. Kalau tidak ada, lempar kembali ke index
if (!isset($_GET['produk']) || empty($_GET['produk'])) {
    header("Location: index.php");
    exit;
}

$slug = $_GET['produk'];

// 2. Ambil data sepatu berdasarkan slug tersebut
$stmt = $pdo->prepare("
    SELECT products.*, categories.name AS category_name 
    FROM products 
    LEFT JOIN categories ON products.category_id = categories.id 
    WHERE products.slug = ?
");
$stmt->execute([$slug]);
$sepatu = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Cek apakah sepatunya ada di database
if (!$sepatu) {
    die("<h1>Maaf, produk tidak ditemukan.</h1><a href='index.php'>Kembali ke Toko</a>");
}

// 4. MENGAMBIL DATA VARIAN (TAMBAHAN BARU)
// Mencari varian berdasarkan ID sepatu yang baru saja ditemukan, dan pastikan stoknya > 0
$stmt_varian = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? AND stock > 0 ORDER BY size ASC");
$stmt_varian->execute([$sepatu['id']]);
$varian_sepatu = $stmt_varian->fetchAll(PDO::FETCH_ASSOC);
// Hitung jumlah barang di keranjang
$keranjang_cookie = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];
$jumlah_keranjang = array_sum($keranjang_cookie); // Menjumlahkan semua qty barang

// TAMBAHAN: Hitung jumlah pesanan yang belum COMPLETED
$jumlah_pesanan_aktif = 0;
if (isset($_SESSION['customer_id'])) {
    $stmt_pesanan = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status != 'COMPLETED'");
    $stmt_pesanan->execute([$_SESSION['customer_id']]);
    $jumlah_pesanan_aktif = $stmt_pesanan->fetchColumn();
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
            
            <div class="flex items-center space-x-6">

                <a href="blog.php" class="relative text-slate-600 hover:text-indigo-600 font-bold text-sm flex items-center transition pr-2">
                    Our Blog
                </a>
                
                <?php if (isset($_SESSION['customer_id'])): ?>
                    
                    <a href="pesanan_saya.php" class="relative text-slate-600 hover:text-indigo-600 font-bold text-sm flex items-center transition pr-2">
                        📦 Pesanan Saya
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

                    <div class="mt-auto">
                        <h3 class="text-lg font-bold text-slate-800 mb-3">Pilih Ukuran & Warna</h3>
                        
                        <?php if(count($varian_sepatu) > 0): ?>
                            <form action="tambah_keranjang.php" method="POST" class="flex flex-col gap-4">
                                <input type="hidden" name="product_id" value="<?= $sepatu['id'] ?>">
                                
                                <div class="flex flex-wrap gap-3 mb-2">
                                    <?php foreach ($varian_sepatu as $v): ?>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="variant_id" value="<?= $v['id'] ?>" required class="peer sr-only">
                                            <div class="px-4 py-2 border-2 border-slate-200 rounded-lg text-slate-600 font-bold peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 hover:border-indigo-300 transition">
                                                EU <?= htmlspecialchars($v['size']) ?> - <?= htmlspecialchars($v['color']) ?>
                                                <span class="block text-xs font-normal text-slate-400 mt-1">Sisa: <?= $v['stock'] ?> pasang</span>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Jumlah Pembelian</label>
                                    <div class="flex items-center">
                                        <input type="number" name="qty" value="1" min="1" required class="w-24 px-4 py-3 border-2 border-slate-200 rounded-xl focus:outline-none focus:border-indigo-600 text-center font-bold text-lg text-slate-800">
                                    </div>
                                </div>
                                
                                <div class="flex gap-4 mt-4">
                                    <?php if (isset($_SESSION['customer_name'])): ?>
                                        
                                        <button type="submit" name="action" value="cart" class="flex-1 bg-white text-indigo-600 border-2 border-indigo-600 font-bold py-4 px-4 rounded-xl hover:bg-indigo-50 transition shadow-sm active:scale-95 flex justify-center items-center gap-2">
                                            🛒 Masukkan Keranjang
                                        </button>

                                        <button type="submit" name="action" value="checkout" class="flex-1 bg-indigo-600 text-white font-bold py-4 px-4 rounded-xl hover:bg-indigo-700 transition shadow-lg hover:shadow-indigo-600/20 active:scale-95">
                                            Beli Langsung
                                        </button>

                                    <?php else: ?>
                                        <a href="login.php" class="w-full text-center bg-slate-900 text-white font-bold py-4 px-8 rounded-xl hover:bg-slate-800 transition shadow-lg active:scale-95">
                                            Login untuk Membeli
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>

                        <?php else: ?>
                            <p class="text-red-500 font-bold bg-red-50 p-4 rounded-lg border border-red-100">
                                Maaf, stok semua varian sedang kosong.
                            </p>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
        </div>

    </div>

</body>
</html>