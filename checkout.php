<?php
session_start();
require 'koneksi.php';

// 1. WAJIB LOGIN
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['customer_id'];
$keranjang = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];

// 2. KEMBALI JIKA KERANJANG KOSONG
if (empty($keranjang)) {
    header("Location: keranjang.php");
    exit;
}

$pesan_error = '';
$pesan_sukses = '';
$total_price = 0;
$discount_amount = 0;
$final_price = 0;
$voucher_id = null;
$voucher_code_applied = '';

// 3. HITUNG TOTAL BELANJA AWAL (Membaca keranjang)
$items_checkout = [];
foreach ($keranjang as $variant_id => $qty) {
    $stmt = $pdo->prepare("SELECT v.id as variant_id, v.size, v.color, v.stock, p.name, p.price FROM product_variants v JOIN products p ON v.product_id = p.id WHERE v.id = ?");
    $stmt->execute([$variant_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $subtotal = $item['price'] * $qty;
        $total_price += $subtotal;
        $item['qty'] = $qty;
        $item['subtotal'] = $subtotal;
        $items_checkout[] = $item;
    }
}
$final_price = $total_price;

// 4. CEK VOUCHER JIKA TOMBOL "TERAPKAN" DIKLIK
if (isset($_POST['cek_voucher'])) {
    $kode = trim($_POST['voucher_code']);
    $waktu_sekarang = date('Y-m-d H:i:s');

    $stmt_voc = $pdo->prepare("SELECT * FROM vouchers WHERE code = ? AND valid_until >= ? AND (max_uses IS NULL OR used_count < max_uses) AND (min_purchase IS NULL OR min_purchase <= ?) AND deleted_at IS NULL");
    $stmt_voc->execute([$kode, $waktu_sekarang, $total_price]);
    $voucher = $stmt_voc->fetch(PDO::FETCH_ASSOC);

    if ($voucher) {
        $voucher_id = $voucher['id'];
        $voucher_code_applied = $voucher['code'];
        $pesan_sukses = "Voucher berhasil diterapkan!";
        
        // Hitung diskon berdasarkan tipe (persen atau nominal)
        if ($voucher['type'] == 'percent') {
            $discount_amount = $total_price * ($voucher['value'] / 100);
        } else {
            $discount_amount = $voucher['value'];
        }
        
        // Pastikan final price tidak minus
        $final_price = max(0, $total_price - $discount_amount);
    } else {
        $pesan_error = "Voucher tidak valid, kadaluarsa, atau syarat minimum belanja belum terpenuhi.";
    }
}

// 5. PROSES BUAT PESANAN JIKA TOMBOL "BUAT PESANAN" DIKLIK
if (isset($_POST['buat_pesanan'])) {
    // Tangkap ulang data jika voucher sudah teraplikasi sebelumnya
    $final_price_post = $_POST['final_price_post'];
    $discount_post = $_POST['discount_post'];
    $total_post = $_POST['total_post'];
    $voucher_id_post = !empty($_POST['voucher_id_post']) ? $_POST['voucher_id_post'] : null;
    
    $invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    $waktu = date('Y-m-d H:i:s');

    try {
        // MULAI DATABASE TRANSACTION (Mencegah data korup jika mati lampu tengah jalan)
        $pdo->beginTransaction();

        // A. Insert ke tabel `orders`
        $stmt_order = $pdo->prepare("INSERT INTO orders (user_id, voucher_id, invoice_number, total_price, discount_amount, final_price, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'PENDING', ?, ?)");
        $stmt_order->execute([$user_id, $voucher_id_post, $invoice_number, $total_post, $discount_post, $final_price_post, $waktu, $waktu]);
        
        $order_id = $pdo->lastInsertId(); // Mengambil ID order yang baru saja dibuat

        // B. Looping keranjang untuk insert ke `order_items` dan KURANGI STOK
        foreach ($items_checkout as $it) {
            // Cek stok terakhir untuk jaga-jaga
            if ($it['stock'] < $it['qty']) {
                throw new Exception("Stok untuk " . $it['name'] . " tidak mencukupi.");
            }

            // Insert ke order_items
            $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_variant_id, quantity, price, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_item->execute([$order_id, $it['variant_id'], $it['qty'], $it['price'], $waktu, $waktu]);

            // Potong stok di product_variants
            $stmt_stock = $pdo->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ?");
            $stmt_stock->execute([$it['qty'], $it['variant_id']]);
        }

        // C. Update used_count di tabel `vouchers` jika ada voucher yang dipakai
        if ($voucher_id_post) {
            $stmt_update_voc = $pdo->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?");
            $stmt_update_voc->execute([$voucher_id_post]);
        }

        // COMMIT: Simpan semua perubahan secara permanen!
        $pdo->commit();

        // D. Hancurkan Cookie keranjang karena sudah diproses
        setcookie('keranjang', '', time() - 3600, "/");

        // ARAHKAN KE HALAMAN PEMBAYARAN MEMBAWA ID ORDER
        header("Location: pembayaran.php?id=" . $order_id);
        exit;

    } catch (Exception $e) {
        // ROLLBACK: Batalkan semua jika ada 1 saja yang gagal
        $pdo->rollBack();
        $pesan_error = "Gagal memproses pesanan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Checkout | SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-8">

    <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-3xl font-extrabold text-slate-900 mb-8 border-b pb-4">Checkout Pesanan</h1>

        <?php if ($pesan_error): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-6 font-bold"><?= $pesan_error ?></div>
        <?php endif; ?>
        <?php if ($pesan_sukses): ?>
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-6 font-bold"><?= $pesan_sukses ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            
            <div>
                <h3 class="text-lg font-bold mb-4 text-slate-700">Ringkasan Belanja</h3>
                <ul class="divide-y divide-slate-100 mb-6 border rounded-lg p-4 bg-slate-50">
                    <?php foreach ($items_checkout as $it): ?>
                        <li class="py-3 flex justify-between">
                            <div>
                                <p class="font-bold text-slate-800"><?= htmlspecialchars($it['name']) ?></p>
                                <p class="text-sm text-slate-500">EU <?= htmlspecialchars($it['size']) ?> - <?= htmlspecialchars($it['color']) ?> (x<?= $it['qty'] ?>)</p>
                            </div>
                            <p class="font-bold text-indigo-600">Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <form method="POST" action="" class="flex gap-2">
                    <input type="text" name="voucher_code" placeholder="Punya kode promo?" value="<?= htmlspecialchars($voucher_code_applied) ?>" class="flex-1 px-4 py-2 border rounded-lg focus:outline-indigo-500">
                    <button type="submit" name="cek_voucher" class="bg-slate-900 text-white px-4 py-2 rounded-lg font-bold hover:bg-slate-800">Terapkan</button>
                </form>
            </div>

            <div class="bg-slate-900 text-white p-6 rounded-xl flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold mb-6 text-slate-300 border-b border-slate-700 pb-2">Detail Pembayaran</h3>
                    <div class="flex justify-between mb-2">
                        <span>Total Harga</span>
                        <span>Rp <?= number_format($total_price, 0, ',', '.') ?></span>
                    </div>
                    
                    <?php if ($discount_amount > 0): ?>
                        <div class="flex justify-between mb-2 text-green-400 font-bold">
                            <span>Diskon Voucher</span>
                            <span>- Rp <?= number_format($discount_amount, 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between mt-6 pt-4 border-t border-slate-700">
                        <span class="text-xl font-bold">Total Tagihan</span>
                        <span class="text-2xl font-black text-indigo-400">Rp <?= number_format($final_price, 0, ',', '.') ?></span>
                    </div>
                </div>

                <form method="POST" action="" class="mt-8">
                    <input type="hidden" name="total_post" value="<?= $total_price ?>">
                    <input type="hidden" name="discount_post" value="<?= $discount_amount ?>">
                    <input type="hidden" name="final_price_post" value="<?= $final_price ?>">
                    <input type="hidden" name="voucher_id_post" value="<?= $voucher_id ?>">
                    
                    <button type="submit" name="buat_pesanan" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-lg hover:bg-indigo-500 transition shadow-lg text-lg">
                        Selesaikan Pembayaran
                    </button>
                </form>
            </div>

        </div>
    </div>

</body>
</html>