<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Tangkap semua data dari form detail.php
    $product_id = $_POST['product_id'];
    $qty_diminta = isset($_POST['qty']) ? (int)$_POST['qty'] : 1; // Default 1 jika error
    $action = isset($_POST['action']) ? $_POST['action'] : 'cart'; // Deteksi tombol mana yang diklik
    
    if (!isset($_POST['variant_id']) || empty($_POST['variant_id'])) {
        die("Pilih ukuran dan warna terlebih dahulu! <a href='javascript:history.back()'>Kembali</a>");
    }
    
    $variant_id = $_POST['variant_id'];

    // 2. Cek stok di database
    $stmt = $pdo->prepare("SELECT stock FROM product_variants WHERE id = ?");
    $stmt->execute([$variant_id]);
    $varian = $stmt->fetch();

    // Pastikan stok mencukupi dengan jumlah yang diminta
    if ($varian && $varian['stock'] >= $qty_diminta) {
        
        $keranjang = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];

        // 3. Tambahkan ke keranjang sesuai jumlah (qty)
        if (isset($keranjang[$variant_id])) {
            $total_qty = $keranjang[$variant_id] + $qty_diminta;
            // Cegah melampaui batas stok maksimal
            if ($total_qty <= $varian['stock']) {
                $keranjang[$variant_id] = $total_qty;
            } else {
                $keranjang[$variant_id] = $varian['stock'];
            }
        } else {
            $keranjang[$variant_id] = $qty_diminta;
        }

        setcookie('keranjang', json_encode($keranjang), time() + (86400 * 30), "/");

        // 4. ARAHKAN BERDASARKAN TOMBOL YANG DIKLIK
        if ($action == 'checkout') {
            // Jika klik Beli Langsung, langsung lempar ke halaman Checkout
            header("Location: checkout.php");
        } else {
            // Jika klik Masukkan Keranjang, arahkan ke halaman Keranjang
            header("Location: keranjang.php");
        }
        exit;
        
    } else {
        die("Maaf, stok tidak mencukupi untuk jumlah pesanan tersebut. Sisa stok: " . $varian['stock'] . " pasang. <a href='javascript:history.back()'>Kembali</a>");
    }
} else {
    // Jika ada yang mengakses file ini langsung lewat URL tanpa klik tombol, lempar ke index
    header("Location: index.php");
    exit;
}
?>