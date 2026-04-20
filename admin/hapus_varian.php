<?php
session_start();
require '../koneksi.php';

// 1. Proteksi: Wajib login sebagai Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// 2. Validasi Parameter URL
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['product_id']) || empty($_GET['product_id'])) {
    echo "<script>
        alert('Aksi ditolak! Parameter data tidak lengkap.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

$variant_id = (int)$_GET['id'];
$product_id = (int)$_GET['product_id'];

// 3. Pengecekan Data Varian di Database
$stmt = $pdo->prepare("SELECT stock, size, color FROM product_variants WHERE id = ?");
$stmt->execute([$variant_id]);
$variant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$variant) {
    echo "<script>
        alert('Varian sepatu tidak ditemukan di database.');
        window.location.href = 'tambah_varian.php?product_id=" . $product_id . "';
    </script>";
    exit;
}

// 4. Aturan Bisnis: Jangan hapus jika masih ada stok
if ($variant['stock'] > 0) {
    echo "<script>
        alert('GAGAL MENGHAPUS: Varian (Size " . htmlspecialchars($variant['size']) . ") masih memiliki stok " . $variant['stock'] . " pasang.\\\\n\\\\nUbah stok menjadi 0 terlebih dahulu jika ingin menghapus data ini secara permanen.');
        window.location.href = 'tambah_varian.php?product_id=" . $product_id . "';
    </script>";
    exit;
}

// 5. Eksekusi Hapus Varian dengan Try-Catch (Anti Database Crash)
try {
    $stmt_delete = $pdo->prepare("DELETE FROM product_variants WHERE id = ?");
    $stmt_delete->execute([$variant_id]);

    // Berhasil dihapus, kembalikan ke halaman kelola varian
    echo "<script>
        alert('Berhasil! Varian sepatu telah dihapus secara permanen.');
        window.location.href = 'tambah_varian.php?product_id=" . $product_id . "';
    </script>";
    exit;
    
} catch (PDOException $e) {
    // Tangkap error jika varian ini nyangkut di tabel pesanan (Foreign Key Constraint)
    echo "<script>
        alert('Sistem menolak penghapusan! Varian ini sedang terikat dengan data pesanan pelanggan.');
        window.location.href = 'tambah_varian.php?product_id=" . $product_id . "';
    </script>";
    exit;
}
?>