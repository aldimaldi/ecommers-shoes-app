<?php
session_start();
require '../koneksi.php';

// Wajib admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$pesan = '';
$pesan_error = '';

// 1. PROSES HAPUS VOUCHER
if (isset($_GET['hapus'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM vouchers WHERE id = ?");
        $stmt->execute([$_GET['hapus']]);
        $pesan = "Voucher berhasil dihapus!";
    } catch (PDOException $e) {
        $pesan_error = "Gagal menghapus: " . $e->getMessage();
    }
}

// 2. PROSES TAMBAH VOUCHER BARU
if (isset($_POST['submit'])) {
    $code = strtoupper(trim($_POST['code'])); // Otomatis huruf besar
    $type = $_POST['type'];
    $value = $_POST['value'];
    
    // Jika kosong, ubah jadi null agar tidak error di database
    $min_purchase = !empty($_POST['min_purchase']) ? $_POST['min_purchase'] : null;
    $max_uses = !empty($_POST['max_uses']) ? $_POST['max_uses'] : null;
    
    // Mengubah format "YYYY-MM-DDTHH:MM" dari form HTML menjadi format MySQL standar
    $valid_until = date('Y-m-d H:i:s', strtotime($_POST['valid_until']));
    
    $waktu_sekarang = date('Y-m-d H:i:s');
    $used_count = 0; // Mulai dari 0

    try {
        $stmt = $pdo->prepare("INSERT INTO vouchers (code, type, value, min_purchase, max_uses, used_count, valid_until, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $type, $value, $min_purchase, $max_uses, $used_count, $valid_until, $waktu_sekarang, $waktu_sekarang]);
        $pesan = "Voucher $code berhasil ditambahkan!";
    } catch (PDOException $e) {
        // Error biasanya terjadi kalau kode voucher duplikat (tergantung setingan unique di database)
        $pesan_error = "Gagal menyimpan: " . $e->getMessage();
    }
}

// 3. AMBIL DATA SEMUA VOUCHER
$stmt_list = $pdo->query("SELECT * FROM vouchers ORDER BY valid_until DESC");
$vouchers = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Voucher | Admin SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-8">

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-slate-200 pb-4">
            <h1 class="text-3xl font-extrabold text-indigo-600">Manajemen Voucher Promo</h1>
            <a href="index.php" class="text-slate-500 font-bold hover:text-indigo-600">&larr; Kembali ke Dashboard</a>
        </div>

        <?php if ($pesan): ?>
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6 font-bold shadow-sm"><?= $pesan ?></div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6 font-bold shadow-sm"><?= $pesan_error ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow border border-slate-100">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Buat Voucher Baru</h2>
                    <form method="POST" action="">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Kode Voucher (Unik)</label>
                            <input type="text" name="code" required placeholder="Cth: MERDEKA50" class="w-full px-3 py-2 border rounded focus:outline-indigo-500 uppercase">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Tipe Diskon</label>
                            <select name="type" required class="w-full px-3 py-2 border rounded focus:outline-indigo-500 bg-white">
                                <option value="fixed">Nominal Tetap (Rp)</option>
                                <option value="percent">Persentase (%)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Besar Potongan</label>
                            <input type="number" name="value" required placeholder="Cth: 50000 atau 15" class="w-full px-3 py-2 border rounded focus:outline-indigo-500">
                            <p class="text-xs text-slate-500 mt-1">Jika tipe %, cukup isi 15 untuk 15%.</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Minimal Belanja (Opsional)</label>
                            <input type="number" name="min_purchase" placeholder="Kosongkan jika tidak ada" class="w-full px-3 py-2 border rounded focus:outline-indigo-500">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Batas Kuota Pemakaian (Opsional)</label>
                            <input type="number" name="max_uses" placeholder="Kosongkan jika unlimited" class="w-full px-3 py-2 border rounded focus:outline-indigo-500">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Berlaku Sampai</label>
                            <input type="datetime-local" name="valid_until" required class="w-full px-3 py-2 border rounded focus:outline-indigo-500">
                        </div>

                        <button type="submit" name="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition">
                            Simpan Voucher
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-600 text-sm uppercase tracking-wider">
                                    <th class="p-4 font-bold">Kode</th>
                                    <th class="p-4 font-bold">Potongan</th>
                                    <th class="p-4 font-bold">Pemakaian</th>
                                    <th class="p-4 font-bold">Masa Berlaku</th>
                                    <th class="p-4 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <?php if (count($vouchers) > 0): ?>
                                    <?php foreach ($vouchers as $v): ?>
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="p-4">
                                                <span class="bg-indigo-100 text-indigo-700 font-black px-2 py-1 rounded border border-indigo-200">
                                                    <?= htmlspecialchars($v['code']) ?>
                                                </span>
                                            </td>
                                            <td class="p-4 font-bold text-slate-700">
                                                <?php if ($v['type'] == 'percent'): ?>
                                                    <?= htmlspecialchars($v['value']) ?>%
                                                <?php else: ?>
                                                    Rp <?= number_format($v['value'], 0, ',', '.') ?>
                                                <?php endif; ?>
                                                <br>
                                                <?php if ($v['min_purchase']): ?>
                                                    <span class="text-xs font-normal text-slate-500">Min. Rp <?= number_format($v['min_purchase'], 0, ',', '.') ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="p-4 text-slate-600">
                                                <b><?= $v['used_count'] ?></b> / <?= $v['max_uses'] ? $v['max_uses'] : '&infin; (Unlimited)' ?>
                                            </td>
                                            <td class="p-4">
                                                <?php 
                                                    $is_expired = strtotime($v['valid_until']) < time();
                                                ?>
                                                <span class="<?= $is_expired ? 'text-red-500 font-bold' : 'text-slate-600' ?>">
                                                    <?= date('d M Y, H:i', strtotime($v['valid_until'])) ?>
                                                    <?= $is_expired ? '<br>(Kadaluarsa)' : '' ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-center">
                                                <a href="?hapus=<?= $v['id'] ?>" onclick="return confirm('Yakin ingin menghapus voucher ini?');" class="text-red-500 font-bold hover:text-red-700 bg-red-50 px-3 py-1 rounded">
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-slate-500">Belum ada voucher promo.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>