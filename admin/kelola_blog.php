<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$pesan = '';
$pesan_error = '';

// 1. PROSES UPDATE STATUS (DRAFT <-> PUBLISHED)
if (isset($_POST['update_status'])) {
    $blog_id = $_POST['blog_id'];
    $new_status = $_POST['status'];
    $waktu = date('Y-m-d H:i:s');

    try {
        // Jika diubah jadi PUBLISHED, kita juga update waktu published_at nya jadi sekarang
        if ($new_status == 'PUBLISHED') {
            $stmt = $pdo->prepare("UPDATE posts SET status = ?, published_at = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$new_status, $waktu, $waktu, $blog_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE posts SET status = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$new_status, $waktu, $blog_id]);
        }
        $pesan = "Status artikel berhasil diubah menjadi $new_status!";
    } catch (PDOException $e) {
        $pesan_error = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// 2. PROSES HAPUS ARTIKEL
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Ambil nama file gambar untuk dihapus dari folder uploads
    $stmt_img = $pdo->prepare("SELECT thumbnail FROM posts WHERE id = ?");
    $stmt_img->execute([$id]);
    $img = $stmt_img->fetchColumn();
    
    if ($img && file_exists("../uploads/" . $img)) {
        unlink("../uploads/" . $img); // Hapus gambar fisik
    }

    try {
        $stmt_del = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt_del->execute([$id]);
        $pesan = "Artikel berhasil dihapus!";
    } catch (PDOException $e) {
        $pesan_error = "Gagal menghapus artikel.";
    }
}

// 3. AMBIL SEMUA BLOG
$stmt = $pdo->query("
    SELECT posts.*, users.name AS author_name 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Blog | Admin SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b pb-4">
            <h1 class="text-3xl font-extrabold text-indigo-600">Manajemen Blog</h1>
            <div class="flex gap-4">
                <a href="index.php" class="text-slate-500 font-bold hover:text-indigo-600 mt-2">&larr; Dashboard</a>
                <a href="tambah_blog.php" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-indigo-700">+ Tulis Artikel Baru</a>
            </div>
        </div>

        <?php if ($pesan): ?>
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6 font-bold shadow-sm"><?= $pesan ?></div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6 font-bold shadow-sm"><?= $pesan_error ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow overflow-hidden border border-slate-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 text-sm uppercase">
                            <th class="p-4 font-bold w-24">Thumbnail</th>
                            <th class="p-4 font-bold">Judul & Penulis</th>
                            <th class="p-4 font-bold text-center">Ubah Status</th>
                            <th class="p-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (count($posts) > 0): ?>
                            <?php foreach ($posts as $b): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-4">
                                        <img src="../uploads/<?= htmlspecialchars($b['thumbnail']) ?>" class="w-20 h-16 object-cover rounded shadow-sm border border-slate-200">
                                    </td>
                                    <td class="p-4">
                                        <p class="font-bold text-lg text-slate-800"><?= htmlspecialchars($b['title']) ?></p>
                                        <p class="text-sm text-slate-500">Oleh: <?= htmlspecialchars($b['author_name']) ?> | Ditulis: <?= date('d M Y', strtotime($b['created_at'])) ?></p>
                                    </td>
                                    
                                    <td class="p-4 text-center">
                                        <form method="POST" action="" class="flex items-center justify-center gap-2">
                                            <input type="hidden" name="blog_id" value="<?= $b['id'] ?>">
                                            <select name="status" class="px-2 py-1.5 border rounded text-sm focus:outline-indigo-500 bg-white font-bold <?= $b['status'] == 'PUBLISHED' ? 'text-green-600' : 'text-yellow-600' ?>">
                                                <option value="DRAFT" <?= $b['status'] == 'DRAFT' ? 'selected' : '' ?>>DRAFT</option>
                                                <option value="PUBLISHED" <?= $b['status'] == 'PUBLISHED' ? 'selected' : '' ?>>PUBLISHED</option>
                                            </select>
                                            <button type="submit" name="update_status" class="bg-slate-800 text-white px-3 py-1.5 rounded text-sm font-bold hover:bg-slate-900 transition">
                                                Update
                                            </button>
                                        </form>
                                    </td>

                                    <td class="p-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="edit_blog.php?id=<?= $b['id'] ?>" class="text-indigo-600 font-bold hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded border border-indigo-100 transition">Edit</a>
                                            
                                            <a href="?hapus=<?= $b['id'] ?>" onclick="return confirm('Yakin hapus artikel ini?');" class="text-red-500 font-bold hover:text-red-700 bg-red-50 px-3 py-1.5 rounded border border-red-100 transition">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-500">Belum ada artikel. Silakan tulis artikel baru.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>