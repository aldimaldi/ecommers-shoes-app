<?php
session_start();
require 'koneksi.php';

// 1. Keamanan: Pastikan user sudah login
if (!isset($_SESSION['customer_id'])) {
    // Jika belum login, simpan pesan error dan tendang ke login
    $_SESSION['pesan_error'] = "Silakan login terlebih dahulu untuk berbelanja.";
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $action = isset($_POST['action']) ? $_POST['action'] : 'cart'; 
    $qty_diminta = isset($_POST['qty']) ? (int)$_POST['qty'] : 1; 
    
    // 2. Validasi: Cegah error jika user lupa memilih ukuran
    if (!isset($_POST['variant_id']) || empty($_POST['variant_id'])) {
        echo "<script>
            alert('Pilih ukuran dan warna terlebih dahulu!');
            window.history.back();
        </script>";
        exit;
    }
    
    $variant_id = $_POST['variant_id'];

    // 3. Cek stok varian di database
    $stmt = $pdo->prepare("SELECT stock FROM product_variants WHERE id = ?");
    $stmt->execute([$variant_id]);
    $varian = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Pastikan stok mencukupi
    if ($varian && $varian['stock'] >= $qty_diminta) {
        
        // Ambil data keranjang lama dari Cookie (jika ada)
        $keranjang = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];

        // 5. Tambahkan ke keranjang & Limitasi Stok
        if (isset($keranjang[$variant_id])) {
            $total_qty = $keranjang[$variant_id] + $qty_diminta;
            
            // Cegah jumlah di keranjang melebihi batas stok asli di database
            if ($total_qty <= $varian['stock']) {
                $keranjang[$variant_id] = $total_qty;
            } else {
                $keranjang[$variant_id] = $varian['stock'];
            }
        } else {
            $keranjang[$variant_id] = $qty_diminta;
        }

        // 6. Simpan pembaruan keranjang ke Cookie (Berlaku 30 hari)
        setcookie('keranjang', json_encode($keranjang), time() + (86400 * 30), '/');

        // 7. Arahkan halaman sesuai tombol yang diklik
        if ($action === 'buy_direct') {
            // Jika klik "Beli Langsung", arahkan ke Checkout (belum memotong stok/pesanan)
            header("Location: checkout.php");
            exit;
        } else {
            // Jika klik "Masukkan Keranjang", arahkan ke Keranjang
            $_SESSION['pesan'] = "Produk berhasil ditambahkan ke keranjang!";
            header("Location: keranjang.php");
            exit;
        }
        
    } else {
        // Jika stok kurang
        $sisa_stok = $varian ? $varian['stock'] : 0;
        echo "<script>
            alert('Maaf, stok tidak mencukupi. Sisa stok: " . $sisa_stok . " pasang.');
            window.history.back();
        </script>";
        exit;
    }
    
} else {
    header("Location: index.php");
    exit;
}
?>