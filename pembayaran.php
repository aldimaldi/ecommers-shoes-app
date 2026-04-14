<?php
session_start();
require 'koneksi.php';

// Wajib login
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

// Pastikan ada ID pesanan
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['customer_id'];

// Ambil data pesanan, pastikan itu milik user yang sedang login
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

// Jika status sudah dibayar, langsung lempar ke histori
if ($order['status'] !== 'PENDING') {
    header("Location: pesanan_saya.php");
    exit;
}

// PROSES PEMBAYARAN DUMMY
if (isset($_POST['bayar_sekarang'])) {
    $metode = $_POST['metode_pembayaran']; // Dummy, tidak kita simpan ke DB saat ini
    $waktu = date('Y-m-d H:i:s');
    
    // Ubah status menjadi PAID
    $stmt_update = $pdo->prepare("UPDATE orders SET status = 'PAID', updated_at = ? WHERE id = ?");
    $stmt_update->execute([$waktu, $order_id]);
    
    // Lempar ke halaman histori pesanan
    header("Location: pesanan_saya.php?pesan=berhasil_bayar");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pembayaran | SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 flex items-center justify-center min-h-screen p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-indigo-600 p-6 text-center text-white">
            <h1 class="text-2xl font-extrabold mb-1">Penyelesaian Pembayaran</h1>
            <p class="text-indigo-200 text-sm">Selesaikan pembayaran agar pesanan diproses.</p>
        </div>

        <div class="p-8">
            <div class="text-center mb-8">
                <p class="text-slate-500 font-bold mb-1">Total Tagihan</p>
                <h2 class="text-4xl font-black text-slate-900">Rp <?= number_format($order['final_price'], 0, ',', '.') ?></h2>
                <p class="text-sm text-slate-400 mt-2">Invoice: <?= htmlspecialchars($order['invoice_number']) ?></p>
            </div>

            <form method="POST" action="">
                <h3 class="font-bold text-slate-700 mb-3">Pilih Metode Pembayaran</h3>
                <div class="space-y-3 mb-8">
                    <label class="flex items-center p-4 border rounded-xl cursor-pointer hover:bg-slate-50 transition">
                        <input type="radio" name="metode_pembayaran" value="BCA" required class="w-5 h-5 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-3 font-bold text-slate-700">Transfer Bank BCA</span>
                    </label>
                    <label class="flex items-center p-4 border rounded-xl cursor-pointer hover:bg-slate-50 transition">
                        <input type="radio" name="metode_pembayaran" value="GOPAY" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-3 font-bold text-slate-700">GoPay / QRIS</span>
                    </label>
                    <label class="flex items-center p-4 border rounded-xl cursor-pointer hover:bg-slate-50 transition">
                        <input type="radio" name="metode_pembayaran" value="COD" class="w-5 h-5 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-3 font-bold text-slate-700">Bayar di Tempat (COD)</span>
                    </label>
                </div>

                <button type="submit" name="bayar_sekarang" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl hover:bg-slate-800 transition shadow-lg active:scale-95 text-lg">
                    Bayar Sekarang
                </button>
            </form>
        </div>
    </div>

</body>
</html>