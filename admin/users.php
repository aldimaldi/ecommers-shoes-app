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
// 2. PROSES TAMBAH users
// ==========================================
if (isset($_POST['add_users'])) {
    // 1. Tambahkan trim() untuk membersihkan spasi
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // 2. NEW: Ubah role menjadi huruf kecil semua (lowercase)
    $role = strtolower(trim($_POST['role'])); 
    
    $waktu = date('Y-m-d H:i:s');
    
    // 3. Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['pesan_error'] = 'Format email tidak valid!';
        header("Location: users.php");
        exit;
    }

    try {
        // Cek apakah email sudah terdaftar
        $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $cek->execute([$email]);
        
        if ($cek->rowCount() > 0) {
            $_SESSION['pesan_error'] = 'Email sudah digunakan, silakan gunakan email lain!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $role, $waktu, $waktu]);
            
            // Arahkan dengan pesan sukses
            $_SESSION['pesan'] = 'Pengguna baru berhasil ditambahkan!';
            header("Location: users.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['pesan_error'] = 'Terjadi kesalahan database: ' . $e->getMessage();
    }
    
    header("Location: users.php");
    exit;
}

// ==========================================
// 3. PROSES EDIT Users
// ==========================================
if (isset($_POST['update_users'])) {
    // 1. Tangkap ID user yang diedit (biasanya dari input hidden di form HTML)
    $id_user = $_POST['user_id']; 
    
    // 2. Bersihkan input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = strtolower(trim($_POST['role'])); 
    $password = $_POST['password']; // Biarkan asli, jangan di-trim karena spasi bisa jadi bagian password
    $waktu = date('Y-m-d H:i:s');
    
    // 3. Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['pesan_error'] = 'Format email tidak valid!';
        header("Location: users.php");
        exit;
    }

    try {
        // 4. Cek apakah email dipakai oleh user LAIN (perhatikan "AND id != ?")
        $cek = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $cek->execute([$email, $id_user]);
        
        if ($cek->rowCount() > 0) {
            $_SESSION['pesan_error'] = 'Email sudah digunakan oleh pengguna lain!';
        } else {
            
            // 5. Cek apakah admin mengisi kolom password baru
            if (!empty($password)) {
                // JIKA PASSWORD DIISI: Update semua data termasuk password baru
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ?, updated_at = ? WHERE id = ?");
                $stmt->execute([$name, $email, $hashed_password, $role, $waktu, $id_user]);
            } else {
                // JIKA PASSWORD KOSONG: Update data lain saja, biarkan password lama utuh
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, updated_at = ? WHERE id = ?");
                $stmt->execute([$name, $email, $role, $waktu, $id_user]);
            }
            
            $_SESSION['pesan'] = 'Data pengguna berhasil diperbarui!';
        }
    } catch (PDOException $e) {
        $_SESSION['pesan_error'] = 'Terjadi kesalahan database: ' . $e->getMessage();
    }
    
    header("Location: users.php");
    exit;
}

// ==========================================
// 4. PROSES HAPUS users
// ==========================================
if (isset($_POST['delete_users'])) {
    // 1. Tangkap ID user yang akan dihapus dari form
    $id_hapus = $_POST['user_id'];
    
    // Asumsi: Kamu menyimpan ID admin yang sedang login di $_SESSION['admin_id'] 
    // Sesuaikan nama session ini dengan yang kamu gunakan saat proses login admin
    $id_admin_login = $_SESSION['admin_id']; 

    // 2. Validasi Keamanan: Cegah admin menghapus akunnya sendiri
    if ($id_hapus == $id_admin_login) {
        $_SESSION['pesan_error'] = 'Akses ditolak! Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.';
        header("Location: users.php");
        exit;
    }

    try {
        // 3. Eksekusi Soft Delete (Update kolom deleted_at)
        $stmt = $pdo->prepare("UPDATE users SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id_hapus]);
        
        $_SESSION['pesan'] = 'Data pengguna berhasil dihapus!';
    } catch (PDOException $e) {
        $_SESSION['pesan_error'] = 'Terjadi kesalahan database: ' . $e->getMessage();
    }
    
    header("Location: users.php");
    exit;
}

// ==========================================
// 5. AMBIL SEMUA DATA Users
// ==========================================
$stmt = $pdo->query("
    SELECT * FROM users 
    WHERE deleted_at IS NULL 
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <h4 class="card-title fw-bold mb-1">Manajemen Users</h4>
                        <p class="card-subtitle text-muted">Anda dapat membuat atau mengelola akun users di halaman ini.</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <button type="button" class="btn btn-primary shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#tambahUserModal">
                            <i class="ti ti-pencil fs-5"></i> Tambah Users Baru
                        </button>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table mb-0 text-nowrap varient-table align-middle table-hover">
                        <thead class="text-dark fs-4 bg-light">
                            <tr>
                                <th scope="col" class="px-3 border-bottom-0" style="width: 50%;">
                                    <h6 class="fw-bolder mb-0 text-dark">Profil Pengguna</h6>
                                </th>
                                
                                <th scope="col" class="px-0 border-bottom-0">
                                    <h6 class="fw-bolder mb-0 text-dark">Role</h6>
                                </th>
                                
                                <th scope="col" class="px-3 border-bottom-0 text-end">
                                    <h6 class="fw-bolder mb-0 text-dark">Aksi</h6>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($users) > 0): ?>
                                <?php $no = 1; foreach ($users as $user): ?>
                                    <tr>
                                        <td class="px-3 border-bottom-0">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded-3 shadow-sm border fw-bolder" style="width: 50px; height: 50px; font-size: 22px;">
                                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                                </div>
                                                <div class="ms-3">
                                                    <h6 class="mb-1 fw-bolder text-dark text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($user['name']) ?>">
                                                        <?= htmlspecialchars($user['name']) ?>
                                                    </h6>
                                                    <span class="text-muted fs-2"><?= htmlspecialchars($user['email']) ?> &bull; Bergabung: <?= date('d M Y', strtotime($user['created_at'])) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-0 border-bottom-0 align-middle">
                                            <?php if($user['role'] == 'admin'): ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 1px;">
                                                    ADMIN
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold" style="letter-spacing: 1px;">
                                                    CUSTOMER
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="px-3 border-bottom-0 text-end align-middle">
                                            <button type="button" class="btn btn-sm btn-light border text-dark shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $user['id'] ?>" title="Edit Pengguna">
                                                <i class="ti ti-pencil fs-5"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#hapusUserModal<?= $user['id'] ?>" title="Hapus Pengguna">
                                                <i class="ti ti-trash fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-0 text-center py-5">
                                        <div class="text-muted mb-3"><i class="ti ti-users fs-8" style="font-size: 3rem;"></i></div>
                                        <h5 class="fw-bolder text-dark">Belum ada data pengguna</h5>
                                        <p class="text-muted mb-0">Tambahkan akun pengguna baru untuk mengelola sistem.</p>
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

<div class="modal fade" id="tambahUserModal" tabindex="-1" aria-labelledby="tambahLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <form method="POST" action="" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="tambahLabel">Tambah Pengguna Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
          <div class="mb-3">
              <label class="form-label fw-semibold">Nama Lengkap</label>
              <input type="text" name="name" class="form-control fw-bold" required placeholder="Masukkan nama lengkap...">
          </div>
          
          <div class="mb-3">
              <label class="form-label fw-semibold">Alamat Email</label>
              <input type="email" name="email" class="form-control" required placeholder="contoh@email.com">
          </div>

          <div class="row">
              <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Password</label>
                  <input type="password" name="password" class="form-control" required placeholder="Buat password...">
              </div>
              
              <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Role / Hak Akses</label>
                  <select name="role" class="form-select border-primary text-primary fw-bold" required>
                      <option value="customer">Customer</option>
                      <option value="admin">Admin</option>
                  </select>
              </div>
          </div>
      </div>
      
      <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="add_users" class="btn btn-primary fw-bold">Simpan Pengguna</button>
      </div>
    </form>
  </div>
</div>

<?php foreach ($users as $user): ?>

<div class="modal fade" id="editUserModal<?= $user['id'] ?>" tabindex="-1" aria-labelledby="editLabel<?= $user['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"> 
    <form method="POST" action="" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="editLabel<?= $user['id'] ?>">Edit Pengguna</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
          <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

          <div class="mb-3">
              <label class="form-label fw-semibold">Nama Lengkap</label>
              <input type="text" name="name" class="form-control fw-bold" value="<?= htmlspecialchars($user['name']) ?>" required>
          </div>
          
          <div class="mb-3">
              <label class="form-label fw-semibold">Alamat Email</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
          </div>
          
          <div class="row">
              <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Password Baru (Opsional)</label>
                  <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                  <div class="form-text text-muted mt-1">Biarkan kosong jika tetap menggunakan password lama.</div>
              </div>
              
              <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Role / Hak Akses</label>
                  <select name="role" class="form-select border-primary text-primary fw-bold" required>
                      <option value="customer" <?= $user['role'] == 'customer' ? 'selected' : '' ?>>Customer</option>
                      <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                  </select>
              </div>
          </div>
      </div>
      
      <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="update_users" class="btn btn-primary fw-bold">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>


<div class="modal fade" id="hapusUserModal<?= $user['id'] ?>" tabindex="-1" aria-labelledby="hapusLabel<?= $user['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title fw-bold text-white" id="hapusLabel<?= $user['id'] ?>">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="" class="modal-content border-0">
          <div class="modal-body text-center py-4">
              <i class="ti ti-alert-triangle text-danger mb-3" style="font-size: 4rem;"></i>
              <h5 class="fw-bold mb-2">Hapus Pengguna Ini?</h5>
              <p class="text-muted mb-0">Akun atas nama <strong>"<?= htmlspecialchars($user['name']) ?>"</strong> akan dihapus (Soft Delete).</p>
              
              <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
          </div>
          <div class="modal-footer justify-content-center bg-light border-top-0">
              <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
              <button type="submit" name="delete_users" class="btn btn-danger px-4 fw-bold">Ya, Hapus!</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php endforeach; ?>

<?php 
include 'layouts/footer.php'; 
?>