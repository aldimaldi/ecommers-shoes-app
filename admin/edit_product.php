<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header("Location: index.php");
    exit;
}

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php");
    exit;
}

// Fetch categories
$stmt_kat = $pdo->query("SELECT * FROM categories");
$kategori_list = $stmt_kat->fetchAll(PDO::FETCH_ASSOC);

$pesan = '';

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    $imageName = $product['image']; // Keep existing

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $imageName = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($file_tmp, '../uploads/' . $imageName);
    }

    $waktu_sekarang = date('Y-m-d H:i:s');

    try {
        $stmt_update = $pdo->prepare("UPDATE products SET name = ?, slug = ?, category_id = ?, description = ?, price = ?, image = ?, updated_at = ? WHERE id = ?");
        $stmt_update->execute([$name, $slug, $category_id, $description, $price, $imageName, $waktu_sekarang, $id]);
        $pesan = 'Produk berhasil diupdate!';
    } catch (PDOException $e) {
        $pesan = 'Gagal update: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Sepatu - <?= htmlspecialchars($product['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-8">

    <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h1 class="text-2xl font-bold text-indigo-600">Edit Sepatu</h1>
            <a href="index.php" class="text-sm font-bold text-slate-500 hover:text-indigo-600">Kembali</a>
        </div>

        <?php if ($pesan): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= $pesan ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-slate-700 font-bold mb-2">Nama Sepatu</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required class="w-full px-3 py-2 border rounded focus:outline-none focus:border-indigo-500">
            </div>

            <div class="mb-4">
                <label class="block text-slate-700 font-bold mb-2">Kategori</label>
                <select name="category_id" required class="w-full px-3 py-2 border rounded focus:outline-none focus:border-indigo-500 bg-white">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= $kat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= $kat['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-slate-700 font-bold mb-2">Deskripsi</label>
                <textarea name="description" rows="4" required class="w-full px-3 py-2 border rounded focus:outline-none focus:border-indigo-500"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-slate-700 font-bold mb-2">Harga (Rp)</label>
                <input type="number" name="price" value="<?= $product['price'] ?>" required class="w-full px-3 py-2 border rounded focus:outline-none focus:border-indigo-500">
            </div>

            <div class="mb-6">
                <label class="block text-slate-700 font-bold mb-2">Foto (kosongkan untuk keep lama)</label>
                <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 border rounded focus:outline-none">
                <?php if ($product['image']): ?>
                    <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="Current" class="mt-2 w-32 h-32 object-cover rounded">
                <?php endif; ?>
            </div>

            <div class="mb-6">
                <label class="block text-slate-700 font-bold mb-2">Kelola Varian</label>
                <a href="tambah_varian.php?product_id=<?= $id ?>" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold mr-3">Tambah Varian</a>
                <?php
                $stmt_var = $pdo->prepare("SELECT COUNT(*) FROM product_variants WHERE product_id = ?");
                $stmt_var->execute([$id]);
                $var_count = $stmt_var->fetchColumn();
                if ($var_count > 0): 
                ?>
                <a href="hapus_varian.php?product_id=<?= $id ?>" onclick="return confirm('Hapus SEMUA varian? (stok 0 only)')" class="inline-block bg-red-500 text-white px-4 py-2 rounded-lg font-bold">🗑️ Hapus Varian</a>
                <?php endif; ?>
            </div>

            <button type="submit" name="submit" class="w-full bg-indigo-600 text-white font-bold py-3 px-4 rounded hover:bg-indigo-700 transition">
                Update Produk
            </button>
        </form>
    </div>

</body>
</html>
