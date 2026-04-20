<?php
session_start();
require '../koneksi.php';

// Pengecekan admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// ==========================================
// 1. DATA UNTUK KARTU "SALES OVERVIEW" (Statistik Global)
// ==========================================
// Total Penjualan (Hanya yang berstatus PAID, SHIPPED, atau COMPLETED)
$stmt_sales = $pdo->query("SELECT SUM(final_price) FROM orders WHERE status != 'PENDING' AND status != 'CANCELLED'");
$total_sales = $stmt_sales->fetchColumn() ?: 0;

// Total Pesanan
$stmt_orders = $pdo->query("SELECT COUNT(id) FROM orders WHERE status != 'CANCELLED'");
$total_orders = $stmt_orders->fetchColumn() ?: 0;

// Total Pelanggan Terdaftar
$stmt_users = $pdo->query("SELECT COUNT(id) FROM users WHERE role = 'customer'");
$total_users = $stmt_users->fetchColumn() ?: 0;

// ==========================================
// 2. DATA UNTUK KARTU "WEEKLY STATS" (5 Pesanan Terbaru)
// ==========================================
$stmt_recent = $pdo->query("
    SELECT orders.invoice_number, orders.final_price, orders.status, users.name AS customer_name 
    FROM orders 
    JOIN users ON orders.user_id = users.id 
    ORDER BY orders.created_at DESC 
    LIMIT 4
");
$recent_orders = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

// Helper function
function getStatusIconColor($status) {
    switch ($status) {
        case 'PENDING': return 'btn-warning';
        case 'PAID': return 'btn-primary';
        case 'SHIPPED': return 'btn-info';
        case 'COMPLETED': return 'btn-success';
        default: return 'btn-secondary';
    }
}
function getStatusIcon($status) {
    switch ($status) {
        case 'PENDING': return 'ti-clock';
        case 'PAID': return 'ti-box';
        case 'SHIPPED': return 'ti-truck';
        case 'COMPLETED': return 'ti-check';
        default: return 'ti-help';
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
        <div class="card bg-primary-subtle border-0 shadow-sm">
            <div class="card-body p-4">
                <h3 class="fw-bolder mb-1 text-primary">Selamat datang kembali, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>!</h3>
                <p class="mb-0 text-muted fs-4">Berikut adalah ringkasan performa toko sepatumu hari ini.</p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
      <div class="card w-100 shadow-sm border-0 h-100">
        <div class="card-body d-flex flex-column">
          <div class="d-md-flex align-items-center mb-4">
            <div>
              <h4 class="card-title fw-bold">Ringkasan Penjualan</h4>
              <p class="card-subtitle text-muted">Total akumulasi sepanjang waktu</p>
            </div>
          </div>
          
          <div class="row text-center flex-grow-1 align-items-stretch">
              <div class="col-md-4 mb-3 mb-md-0">
                  <div class="p-3 bg-light rounded-4 border h-100 d-flex flex-column justify-content-center">
                      <i class="ti ti-cash text-success mb-2" style="font-size: 2.5rem;"></i>
                      <h3 class="fw-bolder text-dark mb-1">Rp <?= number_format($total_sales, 0, ',', '.') ?></h3>
                      <p class="text-muted mb-0 fw-semibold fs-3">Total Pendapatan</p>
                  </div>
              </div>
              <div class="col-md-4 mb-3 mb-md-0">
                  <div class="p-3 bg-light rounded-4 border h-100 d-flex flex-column justify-content-center">
                      <i class="ti ti-shopping-cart text-primary mb-2" style="font-size: 2.5rem;"></i>
                      <h3 class="fw-bolder text-dark mb-1"><?= number_format($total_orders) ?></h3>
                      <p class="text-muted mb-0 fw-semibold fs-3">Total Transaksi</p>
                  </div>
              </div>
              <div class="col-md-4">
                  <div class="p-3 bg-light rounded-4 border h-100 d-flex flex-column justify-content-center">
                      <i class="ti ti-users text-info mb-2" style="font-size: 2.5rem;"></i>
                      <h3 class="fw-bolder text-dark mb-1"><?= number_format($total_users) ?></h3>
                      <p class="text-muted mb-0 fw-semibold fs-3">Pelanggan Aktif</p>
                  </div>
              </div>
          </div>
          
          <div class="text-center mt-4 border-top pt-3">
              <a href="kelola_pesanan.php" class="btn btn-outline-primary fw-bold px-4 rounded-pill">Lihat Laporan Lengkap <i class="ti ti-arrow-right"></i></a>
          </div>

        </div>
      </div>
    </div>

    <div class="col-lg-4 mt-4 mt-lg-0">
      <div class="card overflow-hidden shadow-sm border-0 h-100">
        <div class="card-body pb-4">
          
          <div class="d-flex align-items-start mb-4">
            <div>
              <h4 class="card-title fw-bold">Pesanan Terbaru</h4>
              <p class="card-subtitle text-muted">Transaksi yang baru masuk</p>
            </div>
            <div class="ms-auto">
              <a href="kelola_pesanan.php" class="text-primary fw-bold text-decoration-none" title="Lihat Semua">
                  <i class="ti ti-external-link fs-6"></i>
              </a>
            </div>
          </div>

          <?php if (count($recent_orders) > 0): ?>
              <?php foreach ($recent_orders as $ro): ?>
                  <div class="py-3 d-flex align-items-center border-bottom border-light">
                    <span class="btn <?= getStatusIconColor($ro['status']) ?> rounded-circle round-48 hstack justify-content-center shadow-sm">
                      <i class="ti <?= getStatusIcon($ro['status']) ?> fs-6"></i>
                    </span>
                    
                    <div class="ms-3 flex-grow-1">
                      <h6 class="mb-0 fw-bolder text-dark text-truncate" style="max-width: 130px;"><?= htmlspecialchars($ro['customer_name']) ?></h6>
                      <span class="text-muted fs-3"><?= htmlspecialchars($ro['invoice_number']) ?></span>
                    </div>
                    
                    <div class="ms-auto text-end">
                      <span class="d-block fw-bold text-dark fs-3">Rp <?= number_format($ro['final_price'], 0, ',', '.') ?></span>
                      <small class="badge bg-light text-muted border border-light-subtle rounded-pill fw-medium"><?= $ro['status'] ?></small>
                    </div>
                  </div>
              <?php endforeach; ?>
          <?php else: ?>
              <div class="text-center py-5">
                  <i class="ti ti-receipt text-muted mb-2" style="font-size: 2.5rem;"></i>
                  <p class="text-muted fw-semibold">Belum ada transaksi.</p>
              </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

</div>

<?php 
include 'layouts/footer.php'; 
?>