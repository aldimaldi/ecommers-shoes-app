<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: pesanan_saya.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['customer_id'];

// 1. Ambil Data Induk Pesanan
$stmt_order = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt_order->execute([$order_id, $user_id]);
$order = $stmt_order->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Pesanan tidak ditemukan atau bukan milik Anda.");
}

// 2. Ambil Rincian Barang (JOIN ke tabel order_items, product_variants, dan products)
$stmt_items = $pdo->prepare("
    SELECT oi.quantity, oi.price AS subtotal, 
           v.size, v.color, 
           p.name, p.image, p.price AS original_price
    FROM order_items oi
    JOIN product_variants v ON oi.product_variant_id = v.id
    JOIN products p ON v.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

function getStatusText($status) {
    switch ($status) {
        case 'PENDING': return 'Belum Bayar';
        case 'PAID': return 'Dikemas';
        case 'SHIPPED': return 'Sedang Dikirim';
        case 'COMPLETED': return 'Selesai';
        default: return $status;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Detail Pesanan | SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-4 md:p-8">

    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg overflow-hidden">
        
        <div class="bg-indigo-600 p-6 text-white flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-black mb-1">Rincian Pesanan</h1>
                <p class="text-indigo-200 text-sm">Invoice: <?= htmlspecialchars($order['invoice_number']) ?></p>
            </div>
            <div class="mt-4 md:mt-0 text-right">
                <span class="bg-white text-indigo-700 px-4 py-1.5 rounded-full text-sm font-bold uppercase tracking-wider shadow-sm">
                    <?= getStatusText($order['status']) ?>
                </span>
                <p class="text-indigo-200 text-xs mt-2 text-left md:text-right">Tgl: <?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
            </div>
        </div>

        <div class="p-6">
            <h3 class="font-bold text-slate-700 mb-4 border-b pb-2">Daftar Produk</h3>
            <div class="space-y-4">
                <?php foreach ($items as $item): ?>
                    <div class="flex items-center gap-4">
                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Produk" class="w-20 h-20 object-cover rounded-xl border border-slate-200 shadow-sm">
                        <div class="flex-1">
                            <h4 class="font-bold text-slate-900"><?= htmlspecialchars($item['name']) ?></h4>
                            <p class="text-sm text-slate-500">Varian: EU <?= htmlspecialchars($item['size']) ?> - <?= htmlspecialchars($item['color']) ?></p>
                            <p class="text-sm text-slate-500">Jumlah: <?= $item['quantity'] ?> x Rp <?= number_format($item['original_price'], 0, ',', '.') ?></p>
                        </div>
                        <div class="font-black text-indigo-600">
                            Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-slate-50 p-6 border-t border-slate-100">
            <div class="flex justify-between text-slate-600 mb-2">
                <span>Subtotal Produk</span>
                <span>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
            </div>
            
            <?php if ($order['discount_amount'] > 0): ?>
                <div class="flex justify-between text-green-600 font-bold mb-2">
                    <span>Diskon Voucher</span>
                    <span>- Rp <?= number_format($order['discount_amount'], 0, ',', '.') ?></span>
                </div>
            <?php endif; ?>
            
            <div class="flex justify-between text-slate-900 font-black text-xl mt-4 pt-4 border-t border-slate-200">
                <span>Total Belanja</span>
                <span class="text-indigo-600">Rp <?= number_format($order['final_price'], 0, ',', '.') ?></span>
            </div>
        </div>

        <div class="p-6 border-t border-slate-100 text-center">
            <a href="pesanan_saya.php" class="text-indigo-600 font-bold hover:text-indigo-800">&larr; Kembali ke Daftar Pesanan</a>
        </div>

    </div>

</body>
</html>