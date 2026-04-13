<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$pesan = '';

// Ambil data kategori
$stmt_kategori = $pdo->query("SELECT * FROM categories");
$kategori_list = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['submit'])) {
    // 1. Tangkap semua inputan, termasuk deskripsi
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description']; // <-- Tangkap deskripsi
    $price = $_POST['price'];
    
    $waktu_sekarang = date('Y-m-d H:i:s');
    $imageName = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $imageName = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($file_tmp, '../uploads/' . $imageName);
    }

    try {
        // 2. Query INSERT diperbarui (tambah kolom description dan 7 tanda tanya)
        $stmt = $pdo->prepare("INSERT INTO products (name, category_id, description, price, image, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // 3. Eksekusi query dengan memasukkan variabel $description
        $stmt->execute([$name, $category_id, $description, $price, $imageName, $waktu_sekarang, $waktu_sekarang]);
        
        $pesan = 'Sepatu berhasil ditambahkan!';
    } catch (PDOException $e) {
        $pesan = 'Gagal menyimpan: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Sepatu Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-8">

    <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h1 class="text-2xl font-bold text-indigo-600">Tambah Sepatu</h1>
            <a href="index.php" class="text-sm font-bold text-slate-500 hover:text-indigo-600">Kembali ke Dashboard</a>
        </div>

        <?php if ($pesan): ?>
            <div class="bg-indigo-100 border border-indigo-400 text-indigo-700 px-4 py-3 rounded mb-4">
                <?= $pesan ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            
            <div class="mb-4">
                <label class="block text-slate-700 font-bold mb-2">Nama Sepatu</label>
                <input type="text" name="name" required placeholder="Contoh: Nike Air Jordan 1" class="w-full px-3 py-2 border rounded focus:outline-none focus:border-indigo-500">
            </div>

            <div class="mb-4">
                <label class="block text-slate-700 font-bold mb-2">Kategori</label>
                <select name="category_id" required class="w-full px-3 py-2 border rounded focus:outline-none focus:border-indigo-500 bg-white">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?= $kat['id'] ?>"><?= $kat['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-slate-700 font-bold mb-2">Deskripsi Produk</label>
                <textarea name="description" rows="4" required placeholder="Jelaskan material, keunggulan, atau desain sepatu ini..." class="w-full px-3 py-2 border rounded focus:outline-none focus:border-indigo-500"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-slate-700 font-bold mb-2">Harga (Rp)</label>
                <input type="number" name="price" required placeholder="Contoh: 1500000" class="w-full px-3 py-2 border rounded focus:outline-none focus:border-indigo-500">
            </div>

            <div class="mb-6">
                <label class="block text-slate-700 font-bold mb-2">Foto Sepatu</label>
                <input type="file" name="image" accept="image/*" required class="w-full px-3 py-2 border rounded focus:outline-none">
            </div>

            <button type="submit" name="submit" class="w-full bg-indigo-600 text-white font-bold py-3 px-4 rounded hover:bg-indigo-700 transition">
                Simpan Produk
            </button>
            
        </form>
    </div>

</body>
</html>