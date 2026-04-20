<?php
session_start();
require 'koneksi.php';

// Beritahu browser bahwa file ini mengembalikan data JSON, bukan HTML
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? 0;

    if ($product_id) {
        // Cari varian pertama (default) dari produk ini untuk dimasukkan ke keranjang
        $stmt = $pdo->prepare("SELECT id FROM product_variants WHERE product_id = ? LIMIT 1");
        $stmt->execute([$product_id]);
        $variant = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($variant) {
            $variant_id = $variant['id'];
            
            // Baca keranjang dari cookie
            $keranjang = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];
            
            // Tambah qty jika sudah ada, buat baru jika belum
            if (isset($keranjang[$variant_id])) {
                $keranjang[$variant_id] += 1;
            } else {
                $keranjang[$variant_id] = 1;
            }

            // Simpan kembali ke cookie (Berlaku 7 hari)
            setcookie('keranjang', json_encode($keranjang), time() + (86400 * 7), "/");
            
            // Hitung total item untuk di-update di navbar merah
            $total_items = array_sum($keranjang);
            
            echo json_encode(['status' => 'success', 'total_items' => $total_items]);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Varian ukuran/warna produk ini belum diset di database.']);
            exit;
        }
    }
}
echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
?>