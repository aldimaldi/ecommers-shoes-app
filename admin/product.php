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
// Hapus pesan dari session setelah dipanggil agar tidak muncul lagi
unset($_SESSION['pesan']);
unset($_SESSION['pesan_error']);


// ==========================================
// 1. PROSES TAMBAH PRODUK
// ==========================================
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $base_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $slug = $base_slug . '-' . substr(uniqid(), -5);
    $waktu_sekarang = date('Y-m-d H:i:s');
    $imageName = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $imageName = 'sepatu_' . time() . '_' . uniqid() . '.' . $ext;
        
        if (move_uploaded_file($file_tmp, '../uploads/' . $imageName)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (name, slug, category_id, description, price, image, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $category_id, $description, $price, $imageName, $waktu_sekarang, $waktu_sekarang]);
                $_SESSION['pesan'] = "Sepatu '$name' berhasil ditambahkan!";
            } catch (PDOException $e) {
                $_SESSION['pesan_error'] = 'Gagal menyimpan: ' . $e->getMessage();
            }
        } else {
            $_SESSION['pesan_error'] = 'Gagal upload gambar.';
        }
    } else {
        $_SESSION['pesan_error'] = 'Harap masukkan foto sepatu!';
    }
    
    // Redirect untuk menghindari form resubmission
    header("Location: product.php");
    exit;
}

// ==========================================
// 2. PROSES EDIT PRODUK
// ==========================================
if (isset($_POST['edit_product'])) {
    $id = $_POST['product_id'];
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $waktu_sekarang = date('Y-m-d H:i:s');
    
    $imageName = $_POST['old_image']; 

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $imageName = 'sepatu_edit_' . time() . '_' . uniqid() . '.' . $ext;
        
        if (move_uploaded_file($file_tmp, '../uploads/' . $imageName)) {
            if ($_POST['old_image'] && file_exists("../uploads/" . $_POST['old_image'])) {
                unlink("../uploads/" . $_POST['old_image']);
            }
        }
    }

    try {
        $stmt_update = $pdo->prepare("UPDATE products SET name = ?, slug = ?, category_id = ?, description = ?, price = ?, image = ?, updated_at = ? WHERE id = ?");
        $stmt_update->execute([$name, $slug, $category_id, $description, $price, $imageName, $waktu_sekarang, $id]);
        $_SESSION['pesan'] = "Data sepatu '$name' berhasil diperbarui!";
    } catch (PDOException $e) {
        $_SESSION['pesan_error'] = 'Gagal update: ' . $e->getMessage();
    }
    
    header("Location: product.php");
    exit;
}

// ==========================================
// 3. PROSES HAPUS PRODUK
// ==========================================
if (isset($_POST['delete_product'])) {
    $id_hapus = $_POST['product_id'];
    
    try {
        // Kita menggunakan UPDATE, bukan DELETE. 
        // Mengisi kolom deleted_at dengan waktu saat ini (CURRENT_TIMESTAMP)
        $stmt_del = $pdo->prepare("UPDATE products SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt_del->execute([$id_hapus]);
        
        $_SESSION['pesan'] = "Produk berhasil dihapus.";
    } catch (PDOException $e) {
        $_SESSION['pesan_error'] = "Gagal menghapus produk: " . $e->getMessage();
    }
    
    header("Location: product.php");
    exit;
}

// ==========================================
// 4. AMBIL DATA UNTUK DITAMPILKAN
// ==========================================
$stmt_kategori = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$kategori_list = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT products.*, categories.name AS category_name 
    FROM products 
    LEFT JOIN categories ON products.category_id = categories.id 
    WHERE products.deleted_at IS NULL
    ORDER BY products.id DESC
");
$produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <h4 class="card-title fw-bold mb-1">Manajemen Produk</h4>
                        <p class="card-subtitle text-muted">Kelola katalog sepatu, harga, dan varian ukuran.</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <button type="button" class="btn btn-primary shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#tambahProdukModal">
                            <i class="ti ti-plus fs-5"></i> Tambah Sepatu
                        </button>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table mb-0 text-nowrap varient-table align-middle fs-3 table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th scope="col" class="px-3 text-dark fw-bold">Produk</th>
                                <th scope="col" class="px-0 text-dark fw-bold">Kategori</th>
                                <th scope="col" class="px-0 text-dark fw-bold">Harga Dasar</th>
                                <th scope="col" class="px-3 text-dark fw-bold text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($produk_list) > 0): ?>
                                <?php foreach ($produk_list as $p): ?>
                                    <tr>
                                        <td class="px-3">
                                            <div class="d-flex align-items-center">
                                                <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" class="rounded-2 border" width="50" height="50" style="object-fit: cover;" alt="Foto" />
                                                <div class="ms-3">
                                                    <h6 class="mb-0 fw-bolder text-dark"><?= htmlspecialchars($p['name']) ?></h6>
                                                    <span class="text-muted fs-2">ID: #<?= $p['id'] ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-0">
                                            <?php if ($p['category_name']): ?>
                                                <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1"><?= htmlspecialchars($p['category_name']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted px-2 py-1">Tanpa Kategori</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="px-0 text-dark fw-medium">
                                            Rp <?= number_format($p['price'], 0, ',', '.') ?>
                                        </td>
                                        
                                        <td class="px-3 text-end">
                                            <a href="tambah_varian.php?product_id=<?= $p['id'] ?>" class="btn btn-sm btn-primary shadow-sm me-1" title="Kelola Varian">
                                                <i class="ti ti-list-details fs-5"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-warning text-white shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#editProdukModal<?= $p['id'] ?>" title="Edit">
                                                <i class="ti ti-pencil fs-5"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#hapusProdukModal<?= $p['id'] ?>" title="Hapus">
                                                <i class="ti ti-trash fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-0 text-center py-5">
                                        <div class="text-muted mb-2"><i class="ti ti-shoe fs-8"></i></div>
                                        <h6 class="fw-bolder">Belum ada produk</h6>
                                        <p class="text-muted mb-0">Silakan tambah sepatu baru terlebih dahulu.</p>
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

<div class="modal fade" id="tambahProdukModal" tabindex="-1" aria-labelledby="tambahLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form method="POST" action="" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="tambahLabel">Tambah Sepatu Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label class="form-label fw-semibold">Nama Sepatu</label>
              <input type="text" name="name" class="form-control" required placeholder="Contoh: Nike Air Force 1">
          </div>
          <div class="mb-3">
              <label class="form-label fw-semibold">Kategori</label>
              <select name="category_id" class="form-select" required>
                  <option value="">-- Pilih Kategori --</option>
                  <?php foreach ($kategori_list as $kat): ?>
                      <option value="<?= $kat['id'] ?>"><?= $kat['name'] ?></option>
                  <?php endforeach; ?>
              </select>
          </div>
          <div class="mb-3">
              <label class="form-label fw-semibold">Harga (Rp)</label>
              <input type="number" name="price" class="form-control" required placeholder="Contoh: 1500000">
          </div>
          <div class="mb-3">
              <label class="form-label fw-semibold">Deskripsi</label>
              <textarea name="description" class="form-control" rows="3" required></textarea>
          </div>
          <div class="mb-3">
              <label class="form-label fw-semibold">Foto Sepatu</label>
              <input type="file" name="image" class="form-control" accept="image/*" required>
          </div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="add_product" class="btn btn-primary">Simpan Produk</button>
      </div>
    </form>
  </div>
</div>

<?php foreach ($produk_list as $p): ?>

<div class="modal fade" id="editProdukModal<?= $p['id'] ?>" tabindex="-1" aria-labelledby="editLabel<?= $p['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"> 
    <form method="POST" action="" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="editLabel<?= $p['id'] ?>">Edit Sepatu: <?= htmlspecialchars($p['name']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          <input type="hidden" name="old_image" value="<?= htmlspecialchars($p['image']) ?>">

          <div class="mb-3">
              <label class="form-label fw-semibold">Nama Sepatu</label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required>
          </div>
          <div class="mb-3">
              <label class="form-label fw-semibold">Kategori</label>
              <select name="category_id" class="form-select" required>
                  <option value="">-- Pilih Kategori --</option>
                  <?php foreach ($kategori_list as $kat): ?>
                      <option value="<?= $kat['id'] ?>" <?= $kat['id'] == $p['category_id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($kat['name']) ?>
                      </option>
                  <?php endforeach; ?>
              </select>
          </div>
          <div class="mb-3">
              <label class="form-label fw-semibold">Harga (Rp)</label>
              <input type="number" name="price" class="form-control" value="<?= $p['price'] ?>" required>
          </div>
          <div class="mb-3">
              <label class="form-label fw-semibold">Deskripsi</label>
              <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($p['description']) ?></textarea>
          </div>
          
          <div class="mb-4 p-3 bg-light rounded border">
              <label class="form-label fw-semibold d-block">Gambar Saat Ini</label>
              <?php if ($p['image']): ?>
                  <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" class="rounded shadow-sm mb-3 border" width="100" height="100" style="object-fit: cover;">
              <?php endif; ?>
              <label class="form-label fw-semibold text-primary">Ganti Gambar (Opsional)</label>
              <input type="file" name="image" class="form-control bg-white" accept="image/*">
          </div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="edit_product" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="hapusProdukModal<?= $p['id'] ?>" tabindex="-1" aria-labelledby="hapusLabel<?= $p['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title fw-bold text-white" id="hapusLabel<?= $p['id'] ?>">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="" class="modal-content border-0">
          <div class="modal-body text-center py-4">
              <i class="ti ti-alert-triangle text-danger mb-3" style="font-size: 4rem;"></i>
              <h5 class="fw-bold mb-2">Hapus "<?= htmlspecialchars($p['name']) ?>"?</h5>
              <p class="text-muted mb-0">Tindakan ini tidak dapat dibatalkan. Pastikan tidak ada data yang berkaitan dengan produk ini.</p>
              
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="product_image" value="<?= htmlspecialchars($p['image']) ?>">
          </div>
          <div class="modal-footer justify-content-center bg-light border-top-0">
              <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
              <button type="submit" name="delete_product" class="btn btn-danger px-4 fw-bold">Ya, Hapus!</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php endforeach; ?>

<?php 
include 'layouts/footer.php'; 
?>