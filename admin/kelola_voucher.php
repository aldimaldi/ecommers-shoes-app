<?php
session_start();
require '../koneksi.php';

// Wajib admin
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
// 1. PROSES HAPUS VOUCHER (VIA MODAL)
// ==========================================
if (isset($_POST['delete_voucher'])) {
    $id_hapus = $_POST['voucher_id'];
    
    try {
        // Mengubah status voucher menjadi terhapus
        $stmt = $pdo->prepare("UPDATE vouchers SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id_hapus]);
        
        $_SESSION['pesan'] = "Voucher berhasil dihapus!";
    } catch (PDOException $e) {
        // Tangkapan error disederhanakan karena UPDATE tidak akan memicu error relasi (Constraint 23000)
        $_SESSION['pesan_error'] = "Gagal menghapus voucher: " . $e->getMessage();
    }
    
    header("Location: kelola_voucher.php");
    exit;
}

// ==========================================
// 2. PROSES TAMBAH VOUCHER BARU
// ==========================================
if (isset($_POST['add_voucher'])) {
    $code = strtoupper(trim($_POST['code'])); // Otomatis huruf besar
    $type = $_POST['type'];
    $value = $_POST['value'];
    
    // Jika kosong, ubah jadi null agar tidak error di database
    $min_purchase = !empty($_POST['min_purchase']) ? $_POST['min_purchase'] : null;
    $max_uses = !empty($_POST['max_uses']) ? $_POST['max_uses'] : null;
    
    // Mengubah format "YYYY-MM-DDTHH:MM" dari form HTML menjadi format MySQL standar
    $valid_until = date('Y-m-d H:i:s', strtotime($_POST['valid_until']));
    
    $waktu_sekarang = date('Y-m-d H:i:s');
    $used_count = 0; // Mulai dari 0

    try {
        $stmt = $pdo->prepare("INSERT INTO vouchers (code, type, value, min_purchase, max_uses, used_count, valid_until, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $type, $value, $min_purchase, $max_uses, $used_count, $valid_until, $waktu_sekarang, $waktu_sekarang]);
        $_SESSION['pesan'] = "Voucher '$code' berhasil ditambahkan!";
    } catch (PDOException $e) {
        // Error biasanya terjadi kalau kode voucher duplikat (unique constraint)
        if ($e->getCode() == 23000) {
            $_SESSION['pesan_error'] = "Kode Voucher '$code' sudah pernah dibuat!";
        } else {
            $_SESSION['pesan_error'] = "Gagal menyimpan: " . $e->getMessage();
        }
    }
    header("Location: kelola_voucher.php");
    exit;
}

// ==========================================
// 3. AMBIL DATA SEMUA VOUCHER
// ==========================================
$stmt_list = $pdo->query("SELECT * FROM vouchers WHERE deleted_at IS NULL ORDER BY valid_until DESC");
$vouchers = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
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
                <h4 class="card-title fw-bold mb-4">Buat Voucher Baru</h4>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode Voucher (Unik)</label>
                        <input type="text" name="code" required placeholder="Cth: MERDEKA50" class="form-control text-uppercase">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipe Diskon</label>
                        <select name="type" required class="form-select">
                            <option value="fixed">Nominal Tetap (Rp)</option>
                            <option value="percent">Persentase (%)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Besar Potongan</label>
                        <input type="number" name="value" required placeholder="Cth: 50000 atau 15" class="form-control">
                        <div class="form-text text-muted">Jika tipe %, cukup isi 15 untuk 15%.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Minimal Belanja (Opsional)</label>
                        <input type="number" name="min_purchase" placeholder="Kosongkan jika tidak ada" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Batas Kuota (Opsional)</label>
                        <input type="number" name="max_uses" placeholder="Kosongkan jika unlimited" class="form-control">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Berlaku Sampai</label>
                        <input type="datetime-local" name="valid_until" required class="form-control">
                    </div>

                    <button type="submit" name="add_voucher" class="btn btn-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                        <i class="ti ti-plus fs-5"></i> Simpan Voucher
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card w-100 shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title fw-bold mb-4">Daftar Voucher Promo</h4>
                
                <div class="table-responsive">
                    <table class="table mb-0 text-nowrap varient-table align-middle table-hover">
                        <thead class="text-dark fs-4 bg-light">
                            <tr>
                                <th scope="col" class="px-3 border-bottom-0"><h6 class="fw-semibold mb-0">Kode</h6></th>
                                <th scope="col" class="px-0 border-bottom-0"><h6 class="fw-semibold mb-0">Potongan</h6></th>
                                <th scope="col" class="px-0 border-bottom-0 text-center"><h6 class="fw-semibold mb-0">Pemakaian</h6></th>
                                <th scope="col" class="px-0 border-bottom-0"><h6 class="fw-semibold mb-0">Masa Berlaku</h6></th>
                                <th scope="col" class="px-3 border-bottom-0 text-end"><h6 class="fw-semibold mb-0">Aksi</h6></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($vouchers) > 0): ?>
                                <?php foreach ($vouchers as $v): ?>
                                    <tr>
                                        <td class="px-3 border-bottom-0">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-3 px-2 py-1">
                                                <i class="ti ti-ticket"></i> <?= htmlspecialchars($v['code']) ?>
                                            </span>
                                        </td>
                                        
                                        <td class="px-0 border-bottom-0">
                                            <h6 class="fw-bold mb-1 text-dark">
                                                <?php if ($v['type'] == 'percent'): ?>
                                                    <?= htmlspecialchars($v['value']) ?>%
                                                <?php else: ?>
                                                    Rp <?= number_format($v['value'], 0, ',', '.') ?>
                                                <?php endif; ?>
                                            </h6>
                                            <?php if ($v['min_purchase']): ?>
                                                <span class="text-muted fs-2">Min. Rp <?= number_format($v['min_purchase'], 0, ',', '.') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="px-0 border-bottom-0 text-center">
                                            <span class="fw-bolder text-dark fs-4"><?= $v['used_count'] ?></span>
                                            <span class="text-muted">/ <?= $v['max_uses'] ? $v['max_uses'] : '&infin;' ?></span>
                                        </td>
                                        
                                        <td class="px-0 border-bottom-0">
                                            <?php 
                                                $is_expired = strtotime($v['valid_until']) < time();
                                            ?>
                                            <span class="d-block <?= $is_expired ? 'text-danger fw-bold' : 'text-dark fw-medium' ?>">
                                                <?= date('d M Y, H:i', strtotime($v['valid_until'])) ?>
                                            </span>
                                            <?php if ($is_expired): ?>
                                                <span class="badge bg-danger-subtle text-danger mt-1">Kadaluarsa</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success mt-1">Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="px-3 border-bottom-0 text-end">
                                            <button type="button" class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#hapusVoucherModal<?= $v['id'] ?>" title="Hapus Voucher">
                                                <i class="ti ti-trash fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-0 text-center py-5">
                                        <div class="text-muted mb-2"><i class="ti ti-ticket fs-8"></i></div>
                                        <h6 class="fw-bolder">Belum ada voucher</h6>
                                        <p class="text-muted mb-0">Silakan buat promo voucher baru di form sebelah kiri.</p>
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

<?php foreach ($vouchers as $v): ?>
<div class="modal fade" id="hapusVoucherModal<?= $v['id'] ?>" tabindex="-1" aria-labelledby="hapusLabel<?= $v['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title fw-bold text-white" id="hapusLabel<?= $v['id'] ?>">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="" class="modal-content border-0">
          <div class="modal-body text-center py-4">
              <i class="ti ti-alert-triangle text-danger mb-3" style="font-size: 4rem;"></i>
              <h5 class="fw-bold mb-2">Hapus Voucher "<?= htmlspecialchars($v['code']) ?>"?</h5>
              <p class="text-muted mb-0">Pelanggan tidak akan bisa menggunakan kode ini lagi setelah dihapus.</p>
              
              <input type="hidden" name="voucher_id" value="<?= $v['id'] ?>">
          </div>
          <div class="modal-footer justify-content-center bg-light border-top-0">
              <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
              <button type="submit" name="delete_voucher" class="btn btn-danger px-4 fw-bold">Ya, Hapus!</button>
          </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php 
include 'layouts/footer.php'; 
?>