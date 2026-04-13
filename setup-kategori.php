<?php
require 'koneksi.php';
$waktu = date('Y-m-d H:i:s');

try {
    // Memasukkan 3 kategori dasar
    $pdo->query("INSERT INTO categories (name, slug, created_at, updated_at) VALUES ('Sneakers', 'sneakers', '$waktu', '$waktu')");
    $pdo->query("INSERT INTO categories (name, slug, created_at, updated_at) VALUES ('Running Shoes', 'running-shoes', '$waktu', '$waktu')");
    $pdo->query("INSERT INTO categories (name, slug, created_at, updated_at) VALUES ('Boots', 'boots', '$waktu', '$waktu')");
    
    echo "<h1>3 Kategori berhasil ditambahkan!</h1>";
    echo "<p>Silakan kembali ke halaman Tambah Produk.</p>";
} catch (PDOException $e) {
    echo "Gagal: " . $e->getMessage();
}
?>