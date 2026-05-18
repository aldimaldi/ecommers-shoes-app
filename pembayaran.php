<?php
session_start();
require 'koneksi.php';
require_once dirname(__FILE__) . '/vendor/autoload.php'; 
require 'config_midtrans.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['customer_id'];

// Ambil data pesanan (Pastikan snap_token ikut terpanggil)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

if ($order['status'] !== 'PENDING') {
    header("Location: pesanan_saya.php");
    exit;
}

// ==================================================================
// 2. KONFIGURASI MIDTRANS
// ==================================================================
\Midtrans\Config::$serverKey = $server_key; 
\Midtrans\Config::$isProduction = false; 
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// ==================================================================
// 3 & 4. LOGIKA PENGAMBILAN SNAP TOKEN (DIPERBARUI)
// ==================================================================

// Cek apakah kolom snap_token di database masih kosong
if (empty($order['snap_token'])) {
    
    // JIKA KOSONG: Minta token baru ke Midtrans
    $params = array(
        'transaction_details' => array(
            'order_id' => $order['invoice_number'], 
            
            // PERBAIKAN DI SINI: Tambahkan (int) dan round()r
            'gross_amount' => (int) round($order['final_price']), 
        ),
        'customer_details' => array(
            'first_name' => $_SESSION['customer_name'] ?? 'Customer',
        ),
    );

    try {
        $snapToken = \Midtrans\Snap::getSnapToken($params);
        
        // Simpan token yang didapat ke database agar tidak request ulang
        $stmt_save = $pdo->prepare("UPDATE orders SET snap_token = ? WHERE id = ?");
        $stmt_save->execute([$snapToken, $order_id]);
        
    } catch (Exception $e) {
        die("Gagal menghubungi Midtrans: " . $e->getMessage());
    }

} else {
    // JIKA SUDAH ADA: Gunakan token lama dari database
    $snapToken = $order['snap_token'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pembayaran | SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= $client_key ?>"></script>
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

            <button id="pay-button" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl hover:bg-slate-800 transition shadow-lg active:scale-95 text-lg">
                Pilih Metode Pembayaran
            </button>
        </div>
    </div>

    <script type="text/javascript">
      document.getElementById('pay-button').onclick = function(){
        snap.pay('<?= $snapToken ?>', {
          onSuccess: function(result){
            window.location.href = "proses_midtrans.php?order_id=<?= $order['id'] ?>&status=success";
          },
          onPending: function(result){
            window.location.href = "pesanan_saya.php";
          },
          onError: function(result){
            alert("Pembayaran gagal!");
          },
          onClose: function(){
            console.log('Customer menutup popup');
          }
        });
      };
    </script>
</body>
</html>