<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['product_id'])) {
    die("Pilih sepatu dulu dari halaman produk!");
}
$product_id = $_GET['product_id'];

$pesan = '';
$waktu = date('Y-m-d H:i:s');

if (isset($_POST['submit'])) {
    $size = $_POST['size'];
    $color = $_POST['color'];
    $stock = $_POST['stock'];

    try {
        // Query disesuaikan dengan gambar tabel: tambah kolom 'color'
        $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, size, color, stock, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $size, $color, $stock, $waktu, $waktu]);
        $pesan = "Ukuran $size warna $color dengan stok $stock berhasil ditambahkan!";
    } catch (PDOException $e) {
        $pesan = "Gagal: " . $e->getMessage();
    }
}

// Ambil varian yang sudah ada
$stmt_list = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY size ASC");
$stmt_list->execute([$product_id]);
$list_varian = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Varian Sepatu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-8">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow">
        <h1 class="text-2xl font-bold text-indigo-600 mb-6 border-b pb-4">Tambah Varian (Ukuran & Warna)</h1>

        <?php if ($pesan): ?>
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4"><?= $pesan ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="flex gap-4 items-end mb-8">
            <div class="flex-1">
                <label class="block font-bold mb-2">Ukuran</label>
                <input type="number" name="size" required placeholder="Cth: 42" class="w-full px-3 py-2 border rounded">
            </div>
            <div class="flex-1">
                <label class="block font-bold mb-2">Warna</label>
                <input type="text" name="color" required placeholder="Cth: Hitam" class="w-full px-3 py-2 border rounded">
            </div>
            <div class="flex-1">
                <label class="block font-bold mb-2">Stok</label>
                <input type="number" name="stock" required placeholder="Cth: 15" class="w-full px-3 py-2 border rounded">
            </div>
            <button type="submit" name="submit" class="bg-indigo-600 text-white font-bold py-2 px-6 rounded hover:bg-indigo-700">Simpan</button>
        </form>

        <h3 class="font-bold mb-3">Varian Saat Ini:</h3>
        <ul class="divide-y border rounded-lg p-4">
            <?php foreach ($list_varian as $v): ?>
                <li class="py-3 flex justify-between items-center">
                    <div>
                        <span class="block">Ukuran: <b>EU <?= htmlspecialchars($v['size']) ?></b></span>
                        <span class="block text-sm text-slate-500">Warna: <?= htmlspecialchars($v['color']) ?></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span>Stok: <b class="text-indigo-600"><?= htmlspecialchars($v['stock']) ?></b></span>
                        <?php if ($v['stock'] == 0): ?>
                            <a href="hapus_varian.php?id=<?= $v['id'] ?>&product_id=<?= $product_id ?>" 
                               class="text-red-600 hover:text-red-800 font-bold text-sm bg-red-50 px-3 py-1 rounded border border-red-200 hover:bg-red-100 transition"
                               onclick="return confirm('Hapus varian ini? Stok sudah 0.');">
                                Hapus
                            </a>
                        <?php else: ?>
                            <span class="text-xs text-orange-600 bg-orange-50 px-2 py-1 rounded font-bold">Stok Aktif</span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <div class="mt-6">
            <a href="index.php" class="text-slate-500 hover:text-indigo-600">&larr; Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>