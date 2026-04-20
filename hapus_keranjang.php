<?php
session_start();

// 1. PROTEKSI: Pastikan user sudah login
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Cek apakah ada parameter 'id' (variant_id) di URL dan tidak kosong
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $variant_id = $_GET['id'];
    
    // 3. Pastikan session keranjang sudah ada dan barang tersebut ada di dalamnya
    if (isset($_SESSION['keranjang']) && isset($_SESSION['keranjang'][$variant_id])) {
        
        // 4. Hapus barang dari array Session
        unset($_SESSION['keranjang'][$variant_id]);
        
    }
}
        
// 5. Lempar kembali ke halaman keranjang
header("Location: keranjang.php");
exit;
?>