<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$pesan = '';
$pesan_error = '';

// Cek apakah ada ID artikel di URL
if (!isset($_GET['id'])) {
    header("Location: kelola_blog.php");
    exit;
}

$blog_id = $_GET['id'];

// Ambil data artikel yang mau diedit
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$blog_id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    die("Artikel tidak ditemukan!");
}

// PROSES UPDATE ARTIKEL
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $waktu = date('Y-m-d H:i:s');
    
    // Update slug jika judul berubah
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    // Mengecek apakah admin mengupload gambar baru
    if ($_FILES['thumbnail']['name'] != '') {
        $thumbnail = $_FILES['thumbnail']['name'];
        $tmp_name = $_FILES['thumbnail']['tmp_name'];
        $ext = pathinfo($thumbnail, PATHINFO_EXTENSION);
        $new_filename = 'blog_' . time() . '.' . $ext;
        
        if (move_uploaded_file($tmp_name, '../uploads/' . $new_filename)) {
            // Hapus gambar lama jika ada
            if ($blog['thumbnail'] && file_exists("../uploads/" . $blog['thumbnail'])) {
                unlink("../uploads/" . $blog['thumbnail']);
            }
            
            // Update database DENGAN gambar baru
            $stmt_update = $pdo->prepare("UPDATE posts SET title = ?, slug = ?, content = ?, thumbnail = ?, updated_at = ? WHERE id = ?");
            $stmt_update->execute([$title, $slug, $content, $new_filename, $waktu, $blog_id]);
            $pesan = "Artikel dan gambar berhasil diperbarui!";
            
            // Update variabel $blog agar langsung tampil perubahan di form
            $blog['thumbnail'] = $new_filename; 
        } else {
            $pesan_error = "Gagal upload gambar thumbnail baru!";
        }
    } else {
        // Update database TANPA merubah gambar
        $stmt_update = $pdo->prepare("UPDATE posts SET title = ?, slug = ?, content = ?, updated_at = ? WHERE id = ?");
        $stmt_update->execute([$title, $slug, $content, $waktu, $blog_id]);
        $pesan = "Artikel berhasil diperbarui!";
    }
    
    // Update variabel $blog untuk form
    $blog['title'] = $title;
    $blog['content'] = $content;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Artikel | Admin SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-8">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow border border-slate-100">
        <h1 class="text-3xl font-extrabold text-indigo-600 mb-6 border-b pb-4">Edit Artikel Blog</h1>

        <?php if ($pesan): ?>
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6 font-bold shadow-sm"><?= $pesan ?></div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6 font-bold shadow-sm"><?= $pesan_error ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block font-bold mb-2">Judul Artikel</label>
                <input type="text" name="title" value="<?= htmlspecialchars($blog['title']) ?>" required class="w-full px-4 py-2 border rounded-lg focus:outline-indigo-500 text-lg font-bold">
            </div>

            <div class="mb-6">
                <label class="block font-bold mb-2">Isi Konten</label>
                <textarea name="content" required rows="12" class="w-full px-4 py-3 border rounded-lg focus:outline-indigo-500 leading-relaxed"><?= htmlspecialchars($blog['content']) ?></textarea>
                <p class="text-sm text-slate-500 mt-1">Gunakan Enter untuk membuat paragraf baru.</p>
            </div>

            <div class="mb-8 p-4 bg-slate-50 border border-slate-200 rounded-lg flex items-center gap-6">
                <div>
                    <label class="block font-bold mb-2">Gambar Saat Ini</label>
                    <img src="../uploads/<?= htmlspecialchars($blog['thumbnail']) ?>" class="w-32 h-24 object-cover rounded shadow-sm">
                </div>
                <div class="flex-1">
                    <label class="block font-bold mb-2 text-indigo-600">Ganti Gambar Thumbnail (Opsional)</label>
                    <input type="file" name="thumbnail" accept="image/*" class="w-full px-4 py-2 border rounded-lg bg-white">
                    <p class="text-xs text-slate-500 mt-2">Biarkan kosong jika tidak ingin mengubah gambar.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="kelola_blog.php" class="bg-slate-200 text-slate-700 px-6 py-3 rounded-lg font-bold hover:bg-slate-300">Kembali</a>
                <button type="submit" name="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-indigo-700 flex-1 transition shadow-lg">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</body>
</html>