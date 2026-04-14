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

// 1. PROSES HAPUS KATEGORI
if (isset($_GET['hapus'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$_GET['hapus']]);
        $pesan = "Kategori berhasil dihapus!";
    } catch (PDOException $e) {
        // Error biasanya muncul jika kategori sedang dipakai oleh sepatu di tabel products (Foreign Key Restrict)
        $pesan_error = "Gagal menghapus! Pastikan kategori ini tidak sedang dipakai oleh produk sepatu manapun.";
    }
}

// 2. PROSES TAMBAH KATEGORI BARU
if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    
    // Validasi agar tidak kosong
    if (!empty($name)) {
        // Membuat slug otomatis (mengubah spasi jadi strip, huruf kecil semua)
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        
        try {
            // Tambahkan kolom slug ke dalam query INSERT
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
            $pesan = "Kategori '$name' berhasil ditambahkan!";
        } catch (PDOException $e) {
            // Jika nama kategori kembar, slug juga akan kembar dan memicu error ini
            if ($e->getCode() == 23000) {
                $pesan_error = "Kategori '$name' sudah ada di database!";
            } else {
                $pesan_error = "Gagal menyimpan: " . $e->getMessage();
            }
        }
    } else {
        $pesan_error = "Nama kategori tidak boleh kosong!";
    }
}

// 3. AMBIL DATA SEMUA KATEGORI
$stmt_list = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Kategori | Admin SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-8">

    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-slate-200 pb-4">
            <h1 class="text-3xl font-extrabold text-indigo-600">Manajemen Kategori Produk</h1>
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
                    <h2 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Tambah Kategori Baru</h2>
                    <form method="POST" action="">
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Kategori</label>
                            <input type="text" name="name" required placeholder="Contoh: Sneakers Pria" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <button type="submit" name="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition shadow-md">
                            Simpan Kategori
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
                                    <th class="p-4 font-bold w-16 text-center">ID</th>
                                    <th class="p-4 font-bold">Nama Kategori</th>
                                    <th class="p-4 font-bold w-32 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <?php if (count($categories) > 0): ?>
                                    <?php foreach ($categories as $c): ?>
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="p-4 text-center text-slate-500 font-medium">
                                                #<?= $c['id'] ?>
                                            </td>
                                            <td class="p-4 font-bold text-slate-800 text-lg">
                                                <?= htmlspecialchars($c['name']) ?>
                                            </td>
                                            <td class="p-4 text-center">
                                                <a href="?hapus=<?= $c['id'] ?>" onclick="return confirm('Yakin ingin menghapus kategori <?= htmlspecialchars($c['name']) ?>?');" class="text-red-500 font-bold hover:text-red-700 bg-red-50 px-4 py-1.5 rounded-lg border border-red-100 transition">
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="p-8 text-center text-slate-500">Belum ada data kategori.</td>
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