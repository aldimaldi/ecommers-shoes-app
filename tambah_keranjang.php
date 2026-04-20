<?php
session_start();
require 'koneksi.php';

// 1. Keamanan: Pastikan user sudah login
if (!isset($_SESSION['customer_id'])) {
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

    // 3. Cek stok varian di database + get product details for direct buy
    $stmt = $pdo->prepare("
        SELECT pv.stock, pv.product_id, p.price 
        FROM product_variants pv 
        JOIN products p ON pv.product_id = p.id 
        WHERE pv.id = ?
    ");
    $stmt->execute([$variant_id]);
    $varian = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Pastikan stok mencukupi
    if ($varian && $varian['stock'] >= $qty_diminta) {
        
        // NEW: Direct Buy Logic - Skip cart, create order directly
        if ($action === 'buy_direct') {
            $user_id = $_SESSION['customer_id'];
            $waktu = date('Y-m-d H:i:s');
            $invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            
            try {
                $pdo->beginTransaction();
                
                // Create order (no voucher/discount for direct buy)
                $stmt_order = $pdo->prepare("
                    INSERT INTO orders (user_id, invoice_number, total_price, final_price, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, 'PENDING', ?, ?)
                ");
                $total_price = $varian['price'] * $qty_diminta;
                $stmt_order->execute([$user_id, $invoice_number, $total_price, $total_price, $waktu, $waktu]);
                $order_id = $pdo->lastInsertId();
                
                // Create order_item
                $stmt_item = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_variant_id, quantity, price, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt_item->execute([$order_id, $variant_id, $qty_diminta, $varian['price'], $waktu, $waktu]);
                
                // Reduce stock
                $stmt_stock = $pdo->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ?");
                $stmt_stock->execute([$qty_diminta, $variant_id]);
                
                $pdo->commit();
                
                // Redirect to payment
                header("Location: pembayaran.php?id=" . $order_id);
                exit;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                echo "<script>alert('Gagal membuat pesanan langsung: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
                exit;
            }
        }
        
        // Existing cart logic
        // Inisialisasi Session Keranjang jika belum ada
        if (!isset($_SESSION['keranjang'])) {
            $_SESSION['keranjang'] = [];
        }

        // 5. Tambahkan ke keranjang (Session) & Limitasi Stok
        if (isset($_SESSION['keranjang'][$variant_id])) {
            $total_qty = $_SESSION['keranjang'][$variant_id] + $qty_diminta;
            
            // Cegah melebihi batas stok
            if ($total_qty <= $varian['stock']) {
                $_SESSION['keranjang'][$variant_id] = $total_qty;
            } else {
                $_SESSION['keranjang'][$variant_id] = $varian['stock'];
            }
        } else {
            $_SESSION['keranjang'][$variant_id] = $qty_diminta;
        }

        // SYNC SESSION TO COOKIE for keranjang.php
        setcookie('keranjang', json_encode($_SESSION['keranjang']), time() + (30 * 24 * 60 * 60), '/');

        // 6. Arahkan halaman sesuai tombol yang diklik
        if ($action === 'checkout') {
            header("Location: checkout.php");
        } else {
            header("Location: keranjang.php");
        }
        exit;
        
    } else {
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