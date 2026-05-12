<?php
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil 5 pesanan terbaru yang statusnya PENDING

$stmt_notif = $pdo->query("
    SELECT orders.invoice_number, orders.created_at, users.name AS customer_name 
    FROM orders 
    JOIN users ON orders.user_id = users.id 
    WHERE orders.status = 'PENDING' 
    ORDER BY orders.created_at DESC 
    LIMIT 5
");
$notif_pesanan = $stmt_notif->fetchAll(PDO::FETCH_ASSOC);
$jumlah_notif = count($notif_pesanan);
?>

    <div class="body-wrapper">
      <!--  Header Start -->
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
              <a class="nav-link sidebartoggler " id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti ti-bell"></i>
                    <?php if ($jumlah_notif > 0): ?>
                        <div class="notification bg-primary rounded-circle"></div>
                    <?php endif; ?>
                </a>
                
                <div class="dropdown-menu dropdown-menu-animate-up shadow-sm border-0 mt-2" aria-labelledby="drop1" style="width: 320px;">
                    <div class="message-body p-2">
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom mb-2">
                        <h6 class="mb-0 fw-bold text-dark">Pesanan Masuk</h6>
                        <?php if ($jumlah_notif > 0): ?>
                            <span class="badge bg-primary rounded-pill"><?= $jumlah_notif ?> Baru</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="notif-list" style="max-height: 300px; overflow-y: auto;">
                        <?php if ($jumlah_notif > 0): ?>
                            <?php foreach ($notif_pesanan as $notif): ?>
                                <a href="kelola_pesanan.php?search=<?= htmlspecialchars($notif['invoice_number']) ?>" class="dropdown-item d-flex align-items-center gap-3 py-2 px-3 rounded hover-bg-light transition">
                                    <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                    <i class="ti ti-shopping-cart fs-5"></i>
                                    </div>
                                    <div class="w-75">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate"><?= htmlspecialchars($notif['invoice_number']) ?></h6>
                                    <p class="mb-0 fs-3 text-muted text-truncate">Dari: <?= htmlspecialchars($notif['customer_name']) ?></p>
                                    <small class="text-primary fs-2 fw-semibold">
                                        <?= date('d M, H:i', strtotime($notif['created_at'])) ?>
                                    </small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="ti ti-bell-z fs-7 text-muted mb-2 d-block"></i>
                                <p class="mb-0 fs-3 text-muted">Belum ada pesanan baru.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($jumlah_notif > 0): ?>
                        <div class="px-3 pt-2 mt-2 border-top">
                            <a href="kelola_pesanan.php?status=PENDING" class="btn btn-outline-primary w-100 fw-bold btn-sm">Lihat Semua</a>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </li>
          </ul>
          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
               
              <li class="nav-item dropdown">
                <a class="nav-link " href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded-circle shadow-sm border fw-bolder" style="width: 35px; height: 35px; font-size: 16px;">
                      <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?>
                  </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    <a href="./users.php" class="btn btn-outline-primary mx-3 mt-2 d-block">Kelola Users</a>
                  </div>
                  <div class="message-body">
                    <a href="./logout.php" class="btn btn-outline-danger mx-3 mt-2 d-block">Logout</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->
        <div class="body-wrapper-inner">
        <div class="container-fluid">