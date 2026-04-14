<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$pesan = '';

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $status = $_POST['status'];
    $user_id = $_SESSION['admin_id'];
    $waktu = date('Y-m-d H:i:s');
    
    // Otomatis buat slug dari judul
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    // Upload Thumbnail
    $thumbnail = $_FILES['thumbnail']['name'];
    $tmp_name = $_FILES['thumbnail']['tmp_name'];
    $ext = pathinfo($thumbnail, PATHINFO_EXTENSION);
    $new_filename = 'blog_' . time() . '.' . $ext;
    
    if (move_uploaded_file($tmp_name, '../uploads/' . $new_filename)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, slug, content, thumbnail, status, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // published_at diisi waktu sekarang juga
            $stmt->execute([$user_id, $title, $slug, $content, $new_filename, $status, $waktu, $waktu, $waktu]);
            
            header("Location: kelola_blog.php");
            exit;
        } catch (PDOException $e) {
            $pesan = "Gagal menyimpan: " . $e->getMessage();
        }
    } else {
        $pesan = "Gagal upload gambar thumbnail!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tulis Artikel | Admin SNEAKERS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 p-8">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow">
        <h1 class="text-3xl font-extrabold text-indigo-600 mb-6 border-b pb-4">Tulis Artikel Baru</h1>

        <?php if ($pesan): ?>
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6 font-bold shadow-sm"><?= $pesan ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block font-bold mb-2">Judul Artikel</label>
                <input type="text" name="title" required class="w-full px-4 py-2 border rounded-lg focus:outline-indigo-500 text-lg font-bold">
            </div>

            <div class="mb-4">
                <label class="block font-bold mb-2">Isi Konten</label>
                <textarea name="content" required rows="10" class="w-full px-4 py-2 border rounded-lg focus:outline-indigo-500"></textarea>
                <p class="text-sm text-slate-500 mt-1">Gunakan Enter untuk membuat paragraf baru.</p>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-8">
                <div>
                    <label class="block font-bold mb-2">Gambar Thumbnail</label>
                    <input type="file" name="thumbnail" accept="image/*" required class="w-full px-4 py-2 border rounded-lg bg-slate-50">
                </div>
                <div>
                    <label class="block font-bold mb-2">Status Publikasi</label>
                    <select name="status" class="w-full px-4 py-3 border rounded-lg bg-white">
                        <option value="PUBLISHED">Langsung Terbitkan (PUBLISHED)</option>
                        <option value="DRAFT">Simpan sebagai Draft</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="kelola_blog.php" class="bg-slate-200 text-slate-700 px-6 py-3 rounded-lg font-bold hover:bg-slate-300">Batal</a>
                <button type="submit" name="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-indigo-700 flex-1">Simpan Artikel</button>
            </div>
        </form>
    </div>
</body>
</html>