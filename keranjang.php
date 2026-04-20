<?php
session_start();
require 'koneksi.php';

/// KEMBALIKAN SATPAM: Jika belum login, tendang ke halaman login
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

// Baca data keranjang dari Cookie
$keranjang = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];
$total_belanja = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Keranjang Belanja | SNEAKERS.</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans">

    <nav class="bg-white p-4 shadow mb-8">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-extrabold text-indigo-600 tracking-wider">SNEAKERS.</a>
            <div class="flex items-center space-x-4">
                <span class="text-slate-700 font-medium text-sm">Halo, <?= htmlspecialchars($_SESSION['customer_name'] ?? 'Guest') ?></span>
                <a href="index.php" class="text-indigo-600 font-bold hover:text-indigo-800 text-sm">Lanjut Belanja</a>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 pb-16">
        <h1 class="text-3xl font-extrabold text-slate-900 mb-8">Keranjang Belanja Anda</h1>

        <?php if (empty($keranjang)): ?>
            <div class="bg-white p-12 rounded-xl shadow border border-gray-100 text-center">
                <div class="text-6xl mb-4">🛒</div>
                <p class="text-slate-500 mb-6 text-lg font-medium">Keranjang Anda masih kosong.</p>
                <a href="index.php" class="inline-block bg-[#9b51e0] text-white font-bold py-3 px-8 rounded-full hover:bg-[#8a42cf] transition shadow-md">Mulai Belanja</a>
            </div>
        <?php else: ?>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-max">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-200 text-slate-500 text-xs uppercase tracking-wider font-bold">
                                <th class="p-5">Produk</th>
                                <th class="p-5">Varian</th>
                                <th class="p-5 text-center">Qty</th>
                                <th class="p-5 text-right">Subtotal</th>
                                <th class="p-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($keranjang as $variant_id => $qty): ?>
                                <?php 
                                    // Ambil data gabungan dari tabel product_variants dan products
                                    $stmt = $pdo->prepare("
                                        SELECT v.id as variant_id, v.size, v.color, p.name, p.price, p.image 
                                        FROM product_variants v 
                                        JOIN products p ON v.product_id = p.id 
                                        WHERE v.id = ?
                                    ");
                                    $stmt->execute([$variant_id]);
                                    $item = $stmt->fetch(PDO::FETCH_ASSOC);

                                    if ($item) {
                                        $subtotal = $item['price'] * $qty;
                                        $total_belanja += $subtotal;
                                    }
                                ?>
                                
                                <?php if ($item): ?>
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="p-5 flex items-center gap-4">
                                            <div class="w-20 h-20 flex-shrink-0 bg-gray-100 rounded-xl overflow-hidden border border-gray-200">
                                                <img src="uploads/<?= htmlspecialchars($item['image']) ?>" onerror="this.src='https://via.placeholder.com/150?text=No+Image'" alt="Foto" class="w-full h-full object-cover">
                                            </div>
                                            <span class="font-bold text-slate-800 text-base"><?= htmlspecialchars($item['name']) ?></span>
                                        </td>
                                        <td class="p-5 text-slate-600">
                                            <div class="font-semibold text-sm">EU <?= htmlspecialchars($item['size']) ?></div>
                                            <div class="text-xs text-gray-400 mt-1 uppercase"><?= htmlspecialchars($item['color']) ?></div>
                                        </td>
                                        <td class="p-5 font-bold text-center text-slate-800">
                                            <span class="bg-gray-100 px-3 py-1 rounded-lg border border-gray-200"><?= $qty ?></span>
                                        </td>
                                        <td class="p-5 font-bold text-[#9b51e0] text-right text-lg">
                                            Rp <?= number_format($subtotal, 0, ',', '.') ?>
                                        </td>
                                        <td class="p-5 text-center">
                                            <a href="hapus_keranjang.php?id=<?= $variant_id ?>" class="text-gray-400 hover:text-red-500 transition font-bold text-sm bg-white border border-gray-200 px-3 py-1.5 rounded-lg hover:border-red-200 hover:bg-red-50">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="p-6 md:p-8 bg-slate-50 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-center md:text-left">
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Pembayaran</p>
                        <p class="text-3xl md:text-4xl font-black text-slate-900">Rp <?= number_format($total_belanja, 0, ',', '.') ?></p>
                    </div>
                    <div class="w-full md:w-auto">
                        <a href="checkout.php" class="block w-full text-center bg-[#9b51e0] text-white font-bold py-4 px-10 rounded-xl hover:bg-[#8a42cf] transition shadow-lg shadow-purple-200 active:scale-95 text-lg">
                            Checkout Sekarang
                        </a>
                    </div>
                </div>

            </div>

        <?php endif; ?>
    </div>

</body>
</html>