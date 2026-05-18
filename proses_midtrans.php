<?php
session_start();
require 'koneksi.php';

if (isset($_GET['order_id']) && isset($_GET['status'])) {
    $order_id = $_GET['order_id'];
    $status = $_GET['status'];
    $waktu = date('Y-m-d H:i:s');

    if ($status == 'success') {
        // Update database pesanan jadi PAID
        $stmt = $pdo->prepare("UPDATE orders SET status = 'PAID', updated_at = ? WHERE id = ?");
        $stmt->execute([$waktu, $order_id]);
        
        $_SESSION['pesan'] = "Pembayaran berhasil dikonfirmasi!";
    }
}

header("Location: pesanan_saya.php");
exit;