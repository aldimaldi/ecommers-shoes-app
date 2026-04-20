<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// --- FITUR ANTI-REFRESH BUG (FLASH MESSAGES) ---
$pesan = $_SESSION['pesan'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';
unset($_SESSION['pesan']);
unset($_SESSION['pesan_error']);

$waktu_sekarang = date('Y-m-d H:i:s');

// ==========================================
// 1. PROSES UPDATE STATUS (DRAFT <-> PUBLISHED)
// ==========================================
if (isset($_POST['update_status'])) {
    $blog_id = $_POST['blog_id'];
    $new_status = $_POST['status'];

    try {
        if ($new_status == 'PUBLISHED') {
            $stmt = $pdo->prepare("UPDATE posts SET status = ?, published_at = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$new_status, $waktu_sekarang, $waktu_sekarang, $blog_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE posts SET status = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$new_status, $waktu_sekarang, $blog_id]);
        }
        $_SESSION['pesan'] = "Status artikel berhasil diubah menjadi $new_status!";
    } catch (PDOException $e) {
        $_SESSION['pesan_error'] = "Gagal memperbarui status: " . $e->getMessage();
    }
    header("Location: kelola_blog.php");
    exit;
}

// ==========================================
// 2. PROSES TAMBAH ARTIKEL
// ==========================================
if (isset($_POST['add_blog'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $status = $_POST['status'];
    $user_id = $_SESSION['admin_id'];
    
    $base_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $slug = $base_slug . '-' . substr(uniqid(), -5);

    $imageName = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $file_tmp = $_FILES['thumbnail']['tmp_name'];
        $file_name = $_FILES['thumbnail']['name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $imageName = 'blog_' . time() . '_' . uniqid() . '.' . $ext;
        
        if (move_uploaded_file($file_tmp, '../uploads/' . $imageName)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, slug, content, thumbnail, status, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $published_at = ($status == 'PUBLISHED') ? $waktu_sekarang : null;
                $stmt->execute([$user_id, $title, $slug, $content, $imageName, $status, $published_at, $waktu_sekarang, $waktu_sekarang]);
                
                $_SESSION['pesan'] = "Artikel baru berhasil disimpan!";
            } catch (PDOException $e) {
                $_SESSION['pesan_error'] = "Gagal menyimpan artikel: " . $e->getMessage();
            }
        } else {
            $_SESSION['pesan_error'] = "Gagal upload gambar thumbnail!";
        }
    } else {
        $_SESSION['pesan_error'] = "Harap masukkan gambar thumbnail!";
    }
    header("Location: kelola_blog.php");
    exit;
}

// ==========================================
// 3. PROSES EDIT ARTIKEL
// ==========================================
if (isset($_POST['edit_blog'])) {
    $blog_id = $_POST['blog_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    $imageName = $_POST['old_thumbnail']; 

    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $file_tmp = $_FILES['thumbnail']['tmp_name'];
        $file_name = $_FILES['thumbnail']['name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $imageName = 'blog_edit_' . time() . '_' . uniqid() . '.' . $ext;
        
        if (move_uploaded_file($file_tmp, '../uploads/' . $imageName)) {
            if ($_POST['old_thumbnail'] && file_exists("../uploads/" . $_POST['old_thumbnail'])) {
                unlink("../uploads/" . $_POST['old_thumbnail']);
            }
        }
    }

    try {
        $stmt_update = $pdo->prepare("UPDATE posts SET title = ?, content = ?, thumbnail = ?, updated_at = ? WHERE id = ?");
        $stmt_update->execute([$title, $content, $imageName, $waktu_sekarang, $blog_id]);
        $_SESSION['pesan'] = "Artikel berhasil diperbarui!";
    } catch (PDOException $e) {
        $_SESSION['pesan_error'] = "Gagal update: " . $e->getMessage();
    }
    header("Location: kelola_blog.php");
    exit;
}

// ==========================================
// 4. PROSES HAPUS ARTIKEL
// ==========================================
if (isset($_POST['delete_blog'])) {
    $id_hapus = $_POST['blog_id'];
    $img_hapus = $_POST['blog_thumbnail'];
    
    try {
        if ($img_hapus && file_exists("../uploads/" . $img_hapus)) {
            unlink("../uploads/" . $img_hapus);
        }
        $stmt_del = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt_del->execute([$id_hapus]);
        $_SESSION['pesan'] = "Artikel berhasil dihapus permanen.";
    } catch (PDOException $e) {
        $_SESSION['pesan_error'] = "Gagal menghapus artikel.";
    }
    header("Location: kelola_blog.php");
    exit;
}

// ==========================================
// 5. AMBIL SEMUA DATA BLOG
// ==========================================
$stmt = $pdo->query("
    SELECT posts.*, users.name AS author_name 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php 
include 'layouts/header.php'; 
include 'layouts/sidebar.php'; 
include 'layouts/navbar.php'; 
?>

<div class="row">

    <div class="col-12 mb-4">
        <?php if ($pesan): ?>
            <div class="alert alert-success border-0 bg-success-subtle text-success alert-dismissible fade show" role="alert">
                <?= $pesan ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
            <div class="alert alert-danger border-0 bg-danger-subtle text-danger alert-dismissible fade show" role="alert">
                <?= $pesan_error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-12">
        <div class="card w-100 shadow-sm border-0">
            <div class="card-body">
                
                <div class="d-md-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="card-title fw-bold mb-1">Manajemen Blog</h4>
                        <p class="card-subtitle text-muted">Tulis dan kelola artikel jurnal/berita SNEAKERS.</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <button type="button" class="btn btn-primary shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#tambahBlogModal">
                            <i class="ti ti-pencil fs-5"></i> Tulis Artikel Baru
                        </button>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table mb-0 text-nowrap varient-table align-middle table-hover">
                        <thead class="text-dark fs-4 bg-light">
                            <tr>
                                <th scope="col" class="px-3 border-bottom-0"><h6 class="fw-semibold mb-0">Artikel</h6></th>
                                <th scope="col" class="px-0 border-bottom-0"><h6 class="fw-semibold mb-0">Status Publikasi</h6></th>
                                <th scope="col" class="px-3 border-bottom-0 text-end"><h6 class="fw-semibold mb-0">Aksi</h6></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($posts) > 0): ?>
                                <?php foreach ($posts as $b): ?>
                                    <tr>
                                        <td class="px-3 border-bottom-0">
                                            <div class="d-flex align-items-center">
                                                <img src="../uploads/<?= htmlspecialchars($b['thumbnail']) ?>" class="rounded-3 shadow-sm border" width="90" height="60" style="object-fit: cover;" alt="Thumbnail" />
                                                <div class="ms-3">
                                                    <h6 class="mb-1 fw-bolder text-dark text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($b['title']) ?>">
                                                        <?= htmlspecialchars($b['title']) ?>
                                                    </h6>
                                                    <span class="text-muted fs-2">Oleh: <?= htmlspecialchars($b['author_name']) ?> &bull; <?= date('d M Y', strtotime($b['created_at'])) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-0 border-bottom-0">
                                            <form method="POST" action="" class="d-flex align-items-center gap-2">
                                                <input type="hidden" name="blog_id" value="<?= $b['id'] ?>">
                                                <select name="status" class="form-select form-select-sm shadow-sm fw-bold <?= $b['status'] == 'PUBLISHED' ? 'text-success border-success-subtle bg-success-subtle' : 'text-warning border-warning-subtle bg-warning-subtle' ?>" style="width: 140px; cursor: pointer;">
                                                    <option value="DRAFT" <?= $b['status'] == 'DRAFT' ? 'selected' : '' ?>>DRAFT</option>
                                                    <option value="PUBLISHED" <?= $b['status'] == 'PUBLISHED' ? 'selected' : '' ?>>PUBLISHED</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-sm btn-dark shadow-sm fw-bold" title="Update Status">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                            </form>
                                        </td>
                                        
                                        <td class="px-3 border-bottom-0 text-end">
                                            <button type="button" class="btn btn-sm btn-light border text-dark shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#editBlogModal<?= $b['id'] ?>" title="Edit Artikel">
                                                <i class="ti ti-pencil fs-5"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#hapusBlogModal<?= $b['id'] ?>" title="Hapus Artikel">
                                                <i class="ti ti-trash fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-0 text-center py-5">
                                        <div class="text-muted mb-3"><i class="ti ti-file-description fs-8" style="font-size: 3rem;"></i></div>
                                        <h5 class="fw-bolder text-dark">Belum ada artikel</h5>
                                        <p class="text-muted mb-0">Bagikan cerita pertamamu sekarang.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahBlogModal" tabindex="-1" aria-labelledby="tambahLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <form method="POST" action="" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="tambahLabel">Tulis Artikel Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label class="form-label fw-semibold">Judul Artikel</label>
              <input type="text" name="title" class="form-control fw-bold" required placeholder="Ketik judul di sini...">
          </div>
          
          <div class="mb-3">
              <label class="form-label fw-semibold">Isi Konten</label>
              <textarea name="content" class="form-control lh-lg" rows="8" required placeholder="Tulis cerita atau berita di sini... (Gunakan Enter untuk paragraf baru)"></textarea>
          </div>

          <div class="row">
              <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Gambar Thumbnail</label>
                  <input type="file" name="thumbnail" class="form-control" accept="image/*" required>
              </div>
              <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Simpan Sebagai</label>
                  <select name="status" class="form-select border-primary text-primary fw-bold" required>
                      <option value="DRAFT">DRAFT (Sembunyikan dulu)</option>
                      <option value="PUBLISHED">PUBLISHED (Langsung tampil)</option>
                  </select>
              </div>
          </div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="add_blog" class="btn btn-primary fw-bold">Simpan Artikel</button>
      </div>
    </form>
  </div>
</div>

<?php foreach ($posts as $b): ?>

<div class="modal fade" id="editBlogModal<?= $b['id'] ?>" tabindex="-1" aria-labelledby="editLabel<?= $b['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"> 
    <form method="POST" action="" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="editLabel<?= $b['id'] ?>">Edit Artikel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="blog_id" value="<?= $b['id'] ?>">
          <input type="hidden" name="old_thumbnail" value="<?= htmlspecialchars($b['thumbnail']) ?>">

          <div class="mb-3">
              <label class="form-label fw-semibold">Judul Artikel</label>
              <input type="text" name="title" class="form-control fw-bold" value="<?= htmlspecialchars($b['title']) ?>" required>
          </div>
          
          <div class="mb-4">
              <label class="form-label fw-semibold">Isi Konten</label>
              <textarea name="content" class="form-control lh-lg" rows="10" required><?= htmlspecialchars($b['content']) ?></textarea>
          </div>
          
          <div class="mb-2 p-3 bg-light rounded border d-flex gap-3 align-items-center">
              <?php if ($b['thumbnail']): ?>
                  <img src="../uploads/<?= htmlspecialchars($b['thumbnail']) ?>" class="rounded shadow-sm border" width="120" height="80" style="object-fit: cover;">
              <?php endif; ?>
              <div class="flex-grow-1">
                  <label class="form-label fw-semibold text-primary mb-1">Ganti Thumbnail (Opsional)</label>
                  <input type="file" name="thumbnail" class="form-control form-control-sm bg-white" accept="image/*">
                  <div class="form-text text-muted mt-1">Kosongkan jika tidak ingin mengubah gambar.</div>
              </div>
          </div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="edit_blog" class="btn btn-primary fw-bold">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="hapusBlogModal<?= $b['id'] ?>" tabindex="-1" aria-labelledby="hapusLabel<?= $b['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title fw-bold text-white" id="hapusLabel<?= $b['id'] ?>">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="" class="modal-content border-0">
          <div class="modal-body text-center py-4">
              <i class="ti ti-alert-triangle text-danger mb-3" style="font-size: 4rem;"></i>
              <h5 class="fw-bold mb-2">Hapus Artikel Ini?</h5>
              <p class="text-muted mb-0">"<?= htmlspecialchars($b['title']) ?>" akan dihapus selamanya beserta gambar thumbnail-nya.</p>
              
              <input type="hidden" name="blog_id" value="<?= $b['id'] ?>">
              <input type="hidden" name="blog_thumbnail" value="<?= htmlspecialchars($b['thumbnail']) ?>">
          </div>
          <div class="modal-footer justify-content-center bg-light border-top-0">
              <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
              <button type="submit" name="delete_blog" class="btn btn-danger px-4 fw-bold">Ya, Hapus!</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php endforeach; ?>

<?php 
include 'layouts/footer.php'; 
?>