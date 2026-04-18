<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['customer_id'];
$waktu_sekarang = date('Y-m-d H:i:s');

// ==========================================
// FITUR BARU: PROSES "PESANAN DITERIMA"
// ==========================================
if (isset($_GET['terima_id'])) {
    $order_id = $_GET['terima_id'];
    
    // Pastikan pesanan itu milik user ini dan statusnya memang sedang SHIPPED
    $cek_order = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'SHIPPED'");
    $cek_order->execute([$order_id, $user_id]);
    
    if ($cek_order->rowCount() > 0) {
        // Ubah status menjadi COMPLETED
        $stmt_update = $pdo->prepare("UPDATE orders SET status = 'COMPLETED', updated_at = ? WHERE id = ?");
        $stmt_update->execute([$waktu_sekarang, $order_id]);
        
        // Redirect ke tab Selesai dengan pesan sukses
        header("Location: pesanan_saya.php?tab=COMPLETED&pesan=selesai");
        exit;
    }
}
// ==========================================

// TANGKAP TAB YANG SEDANG AKTIF
$tab_aktif = isset($_GET['tab']) ? $_GET['tab'] : 'SEMUA';

if ($tab_aktif === 'SEMUA') {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND status = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id, $tab_aktif]);
}
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// HITUNG JUMLAH PESANAN UNTUK BADGE
$stmt_counts = $pdo->prepare("SELECT status, COUNT(*) as total FROM orders WHERE user_id = ? GROUP BY status");
$stmt_counts->execute([$user_id]);
$hitung = ['PENDING' => 0, 'PAID' => 0, 'SHIPPED' => 0, 'COMPLETED' => 0];
$total_semua = 0;
while ($row = $stmt_counts->fetch()) {
    $hitung[$row['status']] = $row['total'];
    $total_semua += $row['total'];
}

$jumlah_pesanan_aktif = $total_semua - $hitung['COMPLETED'];
$keranjang_cookie = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];
$jumlah_keranjang = array_sum($keranjang_cookie);

function getStatusStyle($status) {
    switch ($status) {
        case 'PENDING': return 'bg-yellow-100 text-yellow-700';
        case 'PAID': return 'bg-blue-100 text-blue-700';
        case 'SHIPPED': return 'bg-purple-100 text-purple-700';
        case 'COMPLETED': return 'bg-green-100 text-green-700';
        default: return 'bg-slate-100 text-slate-700';
    }
}
function getStatusText($status) {
    switch ($status) {
        case 'PENDING': return 'Belum Bayar';
        case 'PAID': return 'Dikemas';
        case 'SHIPPED': return 'Dikirim';
        case 'COMPLETED': return 'Selesai';
        default: return $status;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pesanan Saya | SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>.hide-scrollbar::-webkit-scrollbar { display: none; } .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }</style>
</head>
<body class="bg-slate-50 text-slate-800 pb-12">

    <nav class="bg-white p-4 shadow mb-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-extrabold text-indigo-600">SNEAKERS.</a>
            <div class="flex items-center space-x-6">

                <a href="blog.php" class="relative text-slate-600 hover:text-indigo-600 font-bold text-sm flex items-center transition pr-2">
                    Our Blog
                </a>
                
                <a href="pesanan_saya.php" class="relative text-indigo-600 font-bold text-sm flex items-center transition pr-2">
                    📦 Pesanan Saya
                    <?php if($jumlah_pesanan_aktif > 0): ?>
                        <span class="absolute -top-3 -right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-md"><?= $jumlah_pesanan_aktif ?></span>
                    <?php endif; ?>
                </a>
                <a href="keranjang.php" class="relative text-slate-600 hover:text-indigo-600 transition ml-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <?php if($jumlah_keranjang > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-md"><?= $jumlah_keranjang ?></span>
                    <?php endif; ?>
                </a>
                <div class="w-px h-6 bg-slate-300 ml-4"></div>
                <span class="text-slate-700 font-medium text-sm">Halo, <?= htmlspecialchars($_SESSION['customer_name']) ?></span>
                <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded text-sm font-bold hover:bg-red-600">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4">
        
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'selesai'): ?>
            <div class="bg-green-100 text-green-800 px-4 py-4 rounded-xl mb-6 font-bold text-center border border-green-200 shadow-sm">
                🎉 Terima kasih! Pesanan telah selesai. Selamat menikmati sepatu baru Anda!
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm mb-6 flex overflow-x-auto border border-slate-100 hide-scrollbar">
            <a href="pesanan_saya.php?tab=SEMUA" class="flex-1 min-w-[100px] flex flex-col items-center py-4 px-2 border-b-4 transition <?= $tab_aktif == 'SEMUA' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-indigo-600' ?>">
                <span class="font-bold text-sm mb-1">Semua</span>
                <span class="bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full"><?= $total_semua ?></span>
            </a>
            <a href="pesanan_saya.php?tab=PAID" class="flex-1 min-w-[100px] flex flex-col items-center py-4 px-2 border-b-4 transition <?= $tab_aktif == 'PAID' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-indigo-600' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                <span class="font-bold text-sm">Dikemas</span>
                <?php if($hitung['PAID'] > 0): ?>
                    <span class="absolute mt-[-5px] ml-[50px] bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?= $hitung['PAID'] ?></span>
                <?php endif; ?>
            </a>
            <a href="pesanan_saya.php?tab=SHIPPED" class="flex-1 min-w-[100px] flex flex-col items-center py-4 px-2 border-b-4 transition <?= $tab_aktif == 'SHIPPED' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-indigo-600' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>
                <span class="font-bold text-sm">Dikirim</span>
                <?php if($hitung['SHIPPED'] > 0): ?>
                    <span class="absolute mt-[-5px] ml-[50px] bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?= $hitung['SHIPPED'] ?></span>
                <?php endif; ?>
            </a>
            <a href="pesanan_saya.php?tab=COMPLETED" class="flex-1 min-w-[100px] flex flex-col items-center py-4 px-2 border-b-4 transition <?= $tab_aktif == 'COMPLETED' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-indigo-600' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                <span class="font-bold text-sm">Selesai</span>
            </a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="bg-white p-16 rounded-xl shadow-sm border border-slate-100 text-center">
                <div class="bg-slate-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4"><span class="text-4xl">📭</span></div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Belum ada pesanan</h3>
                <p class="text-slate-500 mb-6">Sepertinya Anda belum memiliki pesanan di kategori ini.</p>
                <a href="index.php" class="inline-block bg-indigo-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-indigo-700 transition">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:border-indigo-200 transition">
                        
                        <div class="bg-slate-50 p-4 border-b border-slate-100 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-800 tracking-wide">📦 <?= htmlspecialchars($order['invoice_number']) ?></span>
                                <span class="text-xs text-slate-500 ml-3"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold <?= getStatusStyle($order['status']) ?>">
                                <?= getStatusText($order['status']) ?>
                            </span>
                        </div>

                        <div class="p-5 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div>
                                <p class="text-slate-500 text-sm mb-1">Total Tagihan</p>
                                <p class="text-xl font-black text-slate-900">Rp <?= number_format($order['final_price'], 0, ',', '.') ?></p>
                            </div>
                            
                            <div class="flex gap-3 w-full sm:w-auto">
                                <a href="detail_pesanan.php?id=<?= $order['id'] ?>" class="flex-1 sm:flex-none text-center bg-white text-slate-700 font-bold py-2.5 px-6 rounded-lg hover:bg-slate-50 border border-slate-300 transition">
                                    Lihat Detail
                                </a>

                                <?php if ($order['status'] == 'PENDING'): ?>
                                    <a href="pembayaran.php?id=<?= $order['id'] ?>" class="flex-1 sm:flex-none text-center bg-indigo-600 text-white font-bold py-2.5 px-6 rounded-lg hover:bg-indigo-700 transition shadow-md active:scale-95">
                                        Bayar Sekarang
                                    </a>
                                <?php elseif ($order['status'] == 'SHIPPED'): ?>
                                    <a href="pesanan_saya.php?terima_id=<?= $order['id'] ?>" onclick="return confirm('Apakah Anda yakin pesanan sudah diterima dengan baik?')" class="flex-1 sm:flex-none text-center bg-green-500 text-white font-bold py-2.5 px-6 rounded-lg hover:bg-green-600 transition shadow-md active:scale-95">
                                        Pesanan Diterima
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>