<?php
session_start();
require '../koneksi.php';

// Wajib admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$pesan = '';

// PROSES UPDATE STATUS PESANAN
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $waktu = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([$new_status, $waktu, $order_id]);
        $pesan = "Status pesanan berhasil diperbarui!";
    } catch (PDOException $e) {
        $pesan = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// AMBIL SEMUA DATA PESANAN (Digabung dengan nama pembeli dari tabel users)
$stmt_orders = $pdo->query("
    SELECT orders.*, users.name AS customer_name 
    FROM orders 
    JOIN users ON orders.user_id = users.id 
    ORDER BY orders.created_at DESC
");
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

// Fungsi warna status
function getStatusStyle($status) {
    switch ($status) {
        case 'PENDING': return 'bg-yellow-100 text-yellow-700';
        case 'PAID': return 'bg-blue-100 text-blue-700';
        case 'SHIPPED': return 'bg-purple-100 text-purple-700';
        case 'COMPLETED': return 'bg-green-100 text-green-700';
        default: return 'bg-slate-100 text-slate-700';
    }
}
function getStatusText($status) {
    switch ($status) {
        case 'PENDING': return 'Belum Bayar';
        case 'PAID': return 'Dikemas (Lunas)';
        case 'SHIPPED': return 'Dikirim';
        case 'COMPLETED': return 'Selesai';
        default: return $status;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Pesanan | Admin SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-8">

    <div class="max-w-7xl mx-auto">
        
        <div class="flex justify-between items-center mb-8 border-b border-slate-200 pb-4">
            <div>
                <h1 class="text-3xl font-extrabold text-indigo-600">Manajemen Pesanan</h1>
                <p class="text-slate-500 mt-1">Pantau dan update status pengiriman ke pembeli.</p>
            </div>
            <a href="index.php" class="text-slate-500 font-bold hover:text-indigo-600">&larr; Kembali ke Dashboard</a>
        </div>

        <?php if ($pesan): ?>
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6 font-bold shadow-sm"><?= $pesan ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 text-sm uppercase tracking-wider">
                            <th class="p-4 font-bold">Invoice & Tanggal</th>
                            <th class="p-4 font-bold">Pelanggan</th>
                            <th class="p-4 font-bold">Total Tagihan</th>
                            <th class="p-4 font-bold text-center">Status Saat Ini</th>
                            <th class="p-4 font-bold text-center">Ubah Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $o): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    
                                    <td class="p-4">
                                        <span class="font-bold text-slate-800 block mb-1"><?= htmlspecialchars($o['invoice_number']) ?></span>
                                        <span class="text-xs text-slate-500"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></span>
                                    </td>
                                    
                                    <td class="p-4 font-medium text-slate-700">
                                        👤 <?= htmlspecialchars($o['customer_name']) ?>
                                    </td>
                                    
                                    <td class="p-4 font-black text-indigo-600">
                                        Rp <?= number_format($o['final_price'], 0, ',', '.') ?>
                                    </td>
                                    
                                    <td class="p-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold <?= getStatusStyle($o['status']) ?>">
                                            <?= getStatusText($o['status']) ?>
                                        </span>
                                    </td>
                                    
                                    <td class="p-4">
                                        <form method="POST" action="" class="flex items-center justify-center gap-2">
                                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                            <select name="status" class="px-2 py-1.5 border rounded text-sm focus:outline-indigo-500 bg-white">
                                                <option value="PENDING" <?= $o['status'] == 'PENDING' ? 'selected' : '' ?>>Belum Bayar</option>
                                                <option value="PAID" <?= $o['status'] == 'PAID' ? 'selected' : '' ?>>Dikemas (PAID)</option>
                                                <option value="SHIPPED" <?= $o['status'] == 'SHIPPED' ? 'selected' : '' ?>>Dikirim (SHIPPED)</option>
                                                <option value="COMPLETED" <?= $o['status'] == 'COMPLETED' ? 'selected' : '' ?>>Selesai</option>
                                            </select>
                                            <button type="submit" name="update_status" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm font-bold hover:bg-indigo-700 transition shadow-sm">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                    
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">Belum ada pesanan masuk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>