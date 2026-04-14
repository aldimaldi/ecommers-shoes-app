<?php
// Baca data dari Cookie
$keranjang = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];

if (isset($_GET['id']) && isset($keranjang[$_GET['id']])) {
    // Hapus barang dari array
    unset($keranjang[$_GET['id']]);
    
    // Perbarui Cookie-nya
    setcookie('keranjang', json_encode($keranjang), time() + (86400 * 30), "/");
}

header("Location: keranjang.php");
exit;
?>