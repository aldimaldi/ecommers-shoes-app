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
// 1. PROSES UPDATE STATUS PESANAN
// ==========================================
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $waktu = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([$new_status, $waktu, $order_id]);
        $_SESSION['pesan'] = "Status pesanan berhasil diperbarui menjadi " . getStatusText($new_status) . "!";
    } catch (PDOException $e) {
        $_SESSION['pesan_error'] = "Gagal memperbarui status: " . $e->getMessage();
    }
    
    // Redirect kembali ke halaman yang sama (menyimpan parameter pencarian jika ada)
    $redirect_url = "kelola_pesanan.php";
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect_url .= '?' . $_SERVER['QUERY_STRING'];
    }
    header("Location: $redirect_url");
    exit;
}

// ==========================================
// 2. LOGIKA PENCARIAN & FILTER
// ==========================================
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Query Dasar
$query = "SELECT orders.*, users.name AS customer_name 
          FROM orders 
          JOIN users ON orders.user_id = users.id 
          WHERE 1=1";
$params = [];

// Jika ada pencarian (Cari berdasarkan Invoice atau Nama Pelanggan)
if ($search) {
    $query .= " AND (orders.invoice_number LIKE :search OR users.name LIKE :search)";
    $params[':search'] = "%$search%";
}

// Jika ada filter status
if ($filter_status) {
    $query .= " AND orders.status = :status";
    $params[':status'] = $filter_status;
}

$query .= " ORDER BY orders.created_at DESC";

// Eksekusi Query Dinamis
$stmt_orders = $pdo->prepare($query);
$stmt_orders->execute($params);
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// 3. FUNGSI HELPER (Disesuaikan dengan Bootstrap)
// ==========================================
function getStatusStyle($status) {
    switch ($status) {
        case 'PENDING': return 'bg-warning-subtle text-warning border-warning-subtle';
        case 'PAID': return 'bg-primary-subtle text-primary border-primary-subtle';
        case 'SHIPPED': return 'bg-info-subtle text-info border-info-subtle';
        case 'COMPLETED': return 'bg-success-subtle text-success border-success-subtle';
        default: return 'bg-light text-dark';
    }
}
function getStatusText($status) {
    switch ($status) {
        case 'PENDING': return 'Belum Bayar';
        case 'PAID': return 'Dikemas (Lunas)';
        case 'SHIPPED': return 'Dikirim';
        case 'COMPLETED': return 'Selesai';
        default: return $status;
    }
}
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
                
                <div class="mb-4">
                    <h4 class="card-title fw-bold mb-1">Manajemen Pesanan</h4>
                    <p class="card-subtitle text-muted">Pantau dan kelola proses pengiriman sepatu ke pelanggan.</p>
                </div>

                <form method="GET" action="" class="row g-3 mb-4 p-3 bg-light rounded border">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-dark mb-1">Cari Pesanan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="ti ti-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="No. Invoice atau Nama Pelanggan..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark mb-1">Filter Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="PENDING" <?= $filter_status == 'PENDING' ? 'selected' : '' ?>>Belum Bayar (PENDING)</option>
                            <option value="PAID" <?= $filter_status == 'PAID' ? 'selected' : '' ?>>Dikemas (PAID)</option>
                            <option value="SHIPPED" <?= $filter_status == 'SHIPPED' ? 'selected' : '' ?>>Dikirim (SHIPPED)</option>
                            <option value="COMPLETED" <?= $filter_status == 'COMPLETED' ? 'selected' : '' ?>>Selesai (COMPLETED)</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                            Terapkan Filter
                        </button>
                        <?php if($search || $filter_status): ?>
                            <a href="kelola_pesanan.php" class="btn btn-light ms-2 border" title="Reset Filter">
                                <i class="ti ti-refresh"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="table-responsive mt-4">
                    <table class="table mb-0 text-nowrap varient-table align-middle table-hover">
                        <thead class="text-dark fs-4 bg-light">
                            <tr>
                                <th class="border-bottom-0 px-3"><h6 class="fw-semibold mb-0">Invoice & Tgl</h6></th>
                                <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Pelanggan</h6></th>
                                <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Total Tagihan</h6></th>
                                <th class="border-bottom-0 text-center"><h6 class="fw-semibold mb-0">Status Saat Ini</h6></th>
                                <th class="border-bottom-0 text-center px-3"><h6 class="fw-semibold mb-0">Ubah Status</h6></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders) > 0): ?>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td class="px-3 border-bottom-0">
                                            <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($o['invoice_number']) ?></h6>
                                            <span class="text-muted fs-3"><i class="ti ti-calendar-time"></i> <?= date('d M Y, H:i', strtotime($o['created_at'])) ?></span>
                                        </td>
                                        
                                        <td class="border-bottom-0">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                    <i class="ti ti-user fs-5"></i>
                                                </div>
                                                <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($o['customer_name']) ?></h6>
                                            </div>
                                        </td>
                                        
                                        <td class="border-bottom-0">
                                            <h6 class="fw-bolder text-primary mb-0">Rp <?= number_format($o['final_price'], 0, ',', '.') ?></h6>
                                        </td>
                                        
                                        <td class="border-bottom-0 text-center">
                                            <span class="badge border <?= getStatusStyle($o['status']) ?> px-3 py-2 fs-3 rounded-pill fw-semibold">
                                                <?= getStatusText($o['status']) ?>
                                            </span>
                                        </td>
                                        
                                        <td class="border-bottom-0 px-3 text-end">
                                            <form method="POST" action="" class="d-flex justify-content-end align-items-center gap-2">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <select name="status" class="form-select form-select-sm shadow-sm" style="width: 140px; cursor: pointer;">
                                                    <option value="PENDING" <?= $o['status'] == 'PENDING' ? 'selected' : '' ?>>Belum Bayar</option>
                                                    <option value="PAID" <?= $o['status'] == 'PAID' ? 'selected' : '' ?>>Dikemas</option>
                                                    <option value="SHIPPED" <?= $o['status'] == 'SHIPPED' ? 'selected' : '' ?>>Dikirim</option>
                                                    <option value="COMPLETED" <?= $o['status'] == 'COMPLETED' ? 'selected' : '' ?>>Selesai</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-sm btn-dark shadow-sm fw-bold">
                                                    Update
                                                </button>
                                            </form>
                                        </td>
                                        
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-0 text-center py-5">
                                        <div class="text-muted mb-3"><i class="ti ti-box fs-8" style="font-size: 3rem;"></i></div>
                                        <h5 class="fw-bolder text-dark">Pesanan Tidak Ditemukan</h5>
                                        <p class="text-muted mb-0">Belum ada pesanan yang sesuai dengan kriteria filter tersebut.</p>
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