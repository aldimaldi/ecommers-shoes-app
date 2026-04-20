<?php
session_start();
require '../koneksi.php';

// Pengecekan krusial: Wajib admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['product_id'])) {
    die("Pilih sepatu dulu dari halaman produk!");
}
$product_id = $_GET['product_id'];

$pesan = '';
$pesan_error = '';
$waktu = date('Y-m-d H:i:s');

// Ambil info produk yang sedang dikelola
$stmt_prod = $pdo->prepare("SELECT name FROM products WHERE id = ?");
$stmt_prod->execute([$product_id]);
$product_info = $stmt_prod->fetch(PDO::FETCH_ASSOC);

if (!$product_info) {
    die("Produk tidak ditemukan.");
}

// Proses Tambah Varian Baru
if (isset($_POST['submit'])) {
    $size = $_POST['size'];
    $color = $_POST['color'];
    $stock = $_POST['stock'];

    try {
        $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, size, color, stock, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $size, $color, $stock, $waktu, $waktu]);
        $pesan = "Varian Ukuran $size ($color) berhasil ditambahkan!";
    } catch (PDOException $e) {
        $pesan_error = "Gagal menambahkan varian: " . $e->getMessage();
    }
}

// Proses Update Stok (Inline)
if (isset($_POST['update_stock'])) {
    $var_id = $_POST['variant_id'];
    $new_stock = $_POST['new_stock'];
    
    try {
        $stmt_up = $pdo->prepare("UPDATE product_variants SET stock = ?, updated_at = ? WHERE id = ?");
        $stmt_up->execute([$new_stock, $waktu, $var_id]);
        $pesan = "Stok berhasil diperbarui!";
    } catch (PDOException $e) {
        $pesan_error = "Gagal update stok.";
    }
}

// Ambil daftar varian yang sudah ada
$stmt_list = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY size ASC");
$stmt_list->execute([$product_id]);
$list_varian = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
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
                <h5 class="card-title fw-bold mb-4">Tambah Varian Baru</h5>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ukuran (EU)</label>
                        <input type="number" name="size" required placeholder="Cth: 42" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Warna</label>
                        <input type="text" name="color" required placeholder="Cth: Hitam Putih" class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Jumlah Stok Awal</label>
                        <input type="number" name="stock" required placeholder="Cth: 15" class="form-control">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                        <i class="ti ti-plus fs-5"></i> Simpan Varian
                    </button>
                </form>
                
                <div class="mt-4 pt-3 border-top text-center">
                    <a href="product.php" class="text-primary text-decoration-underline">&larr; Kembali ke Daftar Produk</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card w-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-1">Varian Sepatu</h5>
                <p class="text-muted mb-4">Produk: <span class="fw-bolder text-dark"><?= htmlspecialchars($product_info['name']) ?></span></p>

                <div class="table-responsive">
                    <table class="table align-middle text-nowrap mb-0 table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-dark fw-bold px-3">Ukuran & Warna</th>
                                <th class="text-dark fw-bold text-center">Stok Tersedia</th>
                                <th class="text-dark fw-bold text-end px-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($list_varian) > 0): ?>
                                <?php foreach ($list_varian as $v): ?>
                                    <tr>
                                        <td class="px-3">
                                            <h6 class="mb-1 fw-bolder text-dark">EU <?= htmlspecialchars($v['size']) ?></h6>
                                            <span class="text-muted fs-3"><?= htmlspecialchars($v['color']) ?></span>
                                        </td>
                                        
                                        <td class="text-center">
                                            <form method="POST" action="" class="d-flex justify-content-center align-items-center gap-2">
                                                <input type="hidden" name="variant_id" value="<?= $v['id'] ?>">
                                                <input type="number" name="new_stock" value="<?= $v['stock'] ?>" class="form-control form-control-sm text-center fw-bold <?= $v['stock'] == 0 ? 'text-danger border-danger' : 'text-dark' ?>" style="width: 80px;" min="0">
                                                <button type="submit" name="update_stock" class="btn btn-sm btn-light border shadow-sm" title="Simpan Stok Baru">
                                                    <i class="ti ti-check text-success"></i>
                                                </button>
                                            </form>
                                        </td>
                                        
                                        <td class="text-end px-3">
                                            <?php if ($v['stock'] == 0): ?>
                                                <a href="hapus_varian.php?id=<?= $v['id'] ?>&product_id=<?= $product_id ?>" 
                                                   class="btn btn-sm btn-danger shadow-sm d-inline-flex align-items-center gap-1"
                                                   onclick="return confirm('Yakin hapus varian ini permanen?');">
                                                   <i class="ti ti-trash"></i> Hapus
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Hanya bisa hapus stok 0</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        Belum ada varian tersimpan. Silakan tambah di samping.
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

<?php 
include 'layouts/footer.php'; 
?>