<?php
session_start();
require '../koneksi.php';

// Pengecekan krusial: Wajib admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// --- FITUR ANTI-REFRESH BUG (FLASH MESSAGES) ---
$pesan = $_SESSION['pesan'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';
unset($_SESSION['pesan']);
unset($_SESSION['pesan_error']);

// ==========================================
// 1. PROSES HAPUS KATEGORI
// ==========================================
if (isset($_POST['delete_category'])) {
    $id_hapus = $_POST['category_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id_hapus]);
        $_SESSION['pesan'] = "Kategori berhasil dihapus permanen!";
    } catch (PDOException $e) {
        // Error muncul jika kategori sedang dipakai oleh produk sepatu
        $_SESSION['pesan_error'] = "Gagal menghapus! Pastikan kategori ini tidak sedang dipakai oleh produk sepatu manapun.";
    }
    header("Location: kelola_kategori.php");
    exit;
}

// ==========================================
// 2. PROSES TAMBAH KATEGORI BARU
// ==========================================
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    
    if (!empty($name)) {
        // Membuat slug otomatis dengan tambahan 5 karakter random agar unik
        $base_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = $base_slug . '-' . substr(uniqid(), -5);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
            $_SESSION['pesan'] = "Kategori '$name' berhasil ditambahkan!";
        } catch (PDOException $e) {
            $_SESSION['pesan_error'] = "Gagal menyimpan: " . $e->getMessage();
        }
    } else {
        $_SESSION['pesan_error'] = "Nama kategori tidak boleh kosong!";
    }
    header("Location: kelola_kategori.php");
    exit;
}

// ==========================================
// 3. AMBIL DATA SEMUA KATEGORI
// ==========================================
$stmt_list = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
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

    <div class="col-lg-4">
        <div class="card w-100 shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title fw-bold mb-4">Tambah Kategori</h4>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Nama Kategori</label>
                        <input type="text" name="name" required placeholder="Contoh: Sneakers Pria" class="form-control">
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                        <i class="ti ti-plus fs-5"></i> Simpan Kategori
                    </button>
                </form>
                
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card w-100 shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title fw-bold mb-4">Daftar Kategori</h4>
                
                <div class="table-responsive">
                    <table class="table mb-0 text-nowrap varient-table align-middle table-hover">
                        <thead class="text-dark fs-4 bg-light">
                            <tr>
                                <th scope="col" class="px-3 border-bottom-0"><h6 class="fw-semibold mb-0">ID</h6></th>
                                <th scope="col" class="px-0 border-bottom-0"><h6 class="fw-semibold mb-0">Nama Kategori</h6></th>
                                <th scope="col" class="px-3 border-bottom-0 text-end"><h6 class="fw-semibold mb-0">Aksi</h6></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($categories) > 0): ?>
                                <?php foreach ($categories as $c): ?>
                                    <tr>
                                        <td class="px-3 border-bottom-0">
                                            <h6 class="fw-semibold mb-0 text-muted">#<?= $c['id'] ?></h6>
                                        </td>
                                        <td class="px-0 border-bottom-0">
                                            <p class="mb-0 fw-bold text-dark fs-4"><?= htmlspecialchars($c['name']) ?></p>
                                        </td>
                                        <td class="px-3 border-bottom-0 text-end">
                                            <button type="button" class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#hapusKategoriModal<?= $c['id'] ?>" title="Hapus Kategori">
                                                <i class="ti ti-trash fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-0 text-center py-5">
                                        <div class="text-muted mb-2"><i class="ti ti-folder fs-8"></i></div>
                                        <h6 class="fw-bolder">Belum ada kategori</h6>
                                        <p class="text-muted mb-0">Silakan buat kategori baru di form sebelah kiri.</p>
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

<?php foreach ($categories as $c): ?>
<div class="modal fade" id="hapusKategoriModal<?= $c['id'] ?>" tabindex="-1" aria-labelledby="hapusLabel<?= $c['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title fw-bold text-white" id="hapusLabel<?= $c['id'] ?>">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="" class="modal-content border-0">
          <div class="modal-body text-center py-4">
              <i class="ti ti-alert-triangle text-danger mb-3" style="font-size: 4rem;"></i>
              <h5 class="fw-bold mb-2">Hapus Kategori "<?= htmlspecialchars($c['name']) ?>"?</h5>
              <p class="text-muted mb-0">Tindakan ini tidak dapat dibatalkan. Pastikan tidak ada produk sepatu yang menggunakan kategori ini.</p>
              
              <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
          </div>
          <div class="modal-footer justify-content-center bg-light border-top-0">
              <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
              <button type="submit" name="delete_category" class="btn btn-danger px-4 fw-bold">Ya, Hapus!</button>
          </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php 
include 'layouts/footer.php'; 
?>