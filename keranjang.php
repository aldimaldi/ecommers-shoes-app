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
    <title>Keranjang Belanja | SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white p-4 shadow mb-8">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-extrabold text-indigo-600">SNEAKERS.</a>
            <div class="flex items-center space-x-4">
                <span class="text-slate-700 font-medium">Halo, <?= htmlspecialchars($_SESSION['customer_name']) ?></span>
                <a href="index.php" class="text-indigo-600 font-bold hover:text-indigo-800">Lanjut Belanja</a>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4">
        <h1 class="text-3xl font-extrabold text-slate-900 mb-8">Keranjang Belanja Anda</h1>

        <?php if (empty($keranjang)): ?>
            <div class="bg-white p-12 rounded-xl shadow text-center">
                <p class="text-slate-500 mb-4 text-lg">Keranjang Anda masih kosong.</p>
                <a href="index.php" class="inline-block bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-indigo-700 transition">Mulai Belanja</a>
            </div>
        <?php else: ?>
            
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 text-sm uppercase tracking-wider">
                            <th class="p-4 font-bold">Produk</th>
                            <th class="p-4 font-bold">Varian</th>
                            <th class="p-4 font-bold text-center">Qty</th>
                            <th class="p-4 font-bold text-right">Subtotal</th>
                            <th class="p-4 text-center">Aksi</th>
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
                                <tr class="hover:bg-slate-50">
                                    <td class="p-4 flex items-center gap-4">
                                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Foto" class="w-16 h-16 object-cover rounded-lg border border-slate-200">
                                        <span class="font-bold text-slate-800"><?= htmlspecialchars($item['name']) ?></span>
                                    </td>
                                    <td class="p-4 text-slate-600">
                                        EU <?= htmlspecialchars($item['size']) ?> <br>
                                        <span class="text-sm"><?= htmlspecialchars($item['color']) ?></span>
                                    </td>
                                    <td class="p-4 font-bold text-center text-slate-800">
                                        <?= $qty ?>
                                    </td>
                                    <td class="p-4 font-bold text-indigo-600 text-right">
                                        Rp <?= number_format($subtotal, 0, ',', '.') ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <a href="hapus_keranjang.php?id=<?= $variant_id ?>" class="text-red-500 font-bold hover:text-red-700 text-sm">Hapus</a>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                    <div>
                        <p class="text-slate-500 text-sm font-bold uppercase tracking-wider">Total Pembayaran</p>
                        <p class="text-3xl font-black text-slate-900">Rp <?= number_format($total_belanja, 0, ',', '.') ?></p>
                    </div>
                    <div>
                        <a href="checkout.php" class="bg-slate-900 text-white font-bold py-4 px-10 rounded-xl hover:bg-slate-800 transition shadow-lg active:scale-95 text-lg">
                            Checkout Sekarang
                        </a>
                    </div>
                </div>

            </div>

        <?php endif; ?>
    </div>

</body>
</html>