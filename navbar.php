<?php
// navbar.php - Include di setiap halaman
// Pastikan session_start() dan koneksi sudah dipanggil sebelum include ini

$keranjang_cookie = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];
$jumlah_keranjang = array_sum($keranjang_cookie);

$jumlah_pesanan_aktif = 0;
if (isset($_SESSION['customer_id'])) {
    $stmt_pesanan_nav = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status != 'COMPLETED'");
    $stmt_pesanan_nav->execute([$_SESSION['customer_id']]);
    $jumlah_pesanan_aktif = $stmt_pesanan_nav->fetchColumn();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center space-x-2">
                <div class="bg-purple-600 text-white w-8 h-8 flex items-center justify-center rounded-lg font-bold text-sm">S</div>
                <a href="index.php" class="font-extrabold text-lg text-indigo-600 tracking-wider">SNEAKERS.</a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-1">
                <a href="index.php" class="px-4 py-2 rounded-lg text-sm font-bold transition <?= $current_page == 'index.php' ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600 hover:text-indigo-600 hover:bg-slate-50' ?>">
                    Beranda
                </a>
                <a href="semua_produk.php" class="px-4 py-2 rounded-lg text-sm font-bold transition <?= $current_page == 'semua_produk.php' ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600 hover:text-indigo-600 hover:bg-slate-50' ?>">
                    Produk
                </a>
                <a href="blog.php" class="px-4 py-2 rounded-lg text-sm font-bold transition <?= $current_page == 'blog.php' ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600 hover:text-indigo-600 hover:bg-slate-50' ?>">
                    Blog
                </a>
            </div>

            <!-- Right Side -->
            <div class="flex items-center space-x-3">
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <a href="pesanan_saya.php" class="relative hidden md:flex items-center gap-1.5 text-slate-600 hover:text-indigo-600 font-bold text-sm px-3 py-2 rounded-lg hover:bg-slate-50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Pesanan
                        <?php if($jumlah_pesanan_aktif > 0): ?>
                            <span class="absolute top-0.5 right-0.5 bg-red-500 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full"><?= $jumlah_pesanan_aktif ?></span>
                        <?php endif; ?>
                    </a>

                    <a href="keranjang.php" class="relative text-slate-600 hover:text-indigo-600 transition p-2 rounded-lg hover:bg-slate-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <?php if($jumlah_keranjang > 0): ?>
                            <span id="cart-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full"><?= $jumlah_keranjang ?></span>
                        <?php else: ?>
                            <span id="cart-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold w-4 h-4 hidden items-center justify-center rounded-full">0</span>
                        <?php endif; ?>
                    </a>

                    <div class="w-px h-6 bg-slate-200"></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['customer_name'])): ?>
                    <span class="hidden md:block text-slate-700 font-medium text-sm">Halo, <?= htmlspecialchars(explode(' ', $_SESSION['customer_name'])[0]) ?></span>
                    <a href="logout.php" class="bg-red-500 text-white px-3 py-1.5 rounded-lg text-sm font-bold hover:bg-red-600 transition">Logout</a>
                <?php elseif (isset($_SESSION['admin_name'])): ?>
                    <a href="admin/index.php" class="text-indigo-600 font-bold text-sm hover:text-indigo-800 px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition">Panel Admin</a>
                    <a href="logout.php" class="bg-red-500 text-white px-3 py-1.5 rounded-lg text-sm font-bold hover:bg-red-600 transition">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-slate-600 text-sm hover:text-indigo-600 font-bold px-3 py-1.5 rounded-lg hover:bg-slate-50 transition">Masuk</a>
                    <a href="register.php" class="bg-[#9b51e0] hover:bg-[#8a42cf] text-white px-4 py-1.5 rounded-full text-sm font-bold transition shadow-sm">Daftar</a>
                <?php endif; ?>

                <!-- Hamburger Mobile -->
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-slate-100 pb-3">
        <div class="px-4 pt-3 space-y-1">
            <a href="index.php" class="block px-3 py-2 rounded-lg text-sm font-bold <?= $current_page == 'index.php' ? 'text-indigo-600 bg-indigo-50' : 'text-slate-700 hover:bg-slate-50' ?>">🏠 Beranda</a>
            <a href="semua_produk.php" class="block px-3 py-2 rounded-lg text-sm font-bold <?= $current_page == 'semua_produk.php' ? 'text-indigo-600 bg-indigo-50' : 'text-slate-700 hover:bg-slate-50' ?>">👟 Semua Produk</a>
            <a href="blog.php" class="block px-3 py-2 rounded-lg text-sm font-bold <?= $current_page == 'blog.php' ? 'text-indigo-600 bg-indigo-50' : 'text-slate-700 hover:bg-slate-50' ?>">📰 Blog</a>
            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="pesanan_saya.php" class="block px-3 py-2 rounded-lg text-sm font-bold text-slate-700 hover:bg-slate-50">
                    📦 Pesanan Saya <?php if($jumlah_pesanan_aktif > 0): ?><span class="bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full ml-1"><?= $jumlah_pesanan_aktif ?></span><?php endif; ?>
                </a>
                <a href="keranjang.php" class="block px-3 py-2 rounded-lg text-sm font-bold text-slate-700 hover:bg-slate-50">
                    🛒 Keranjang <?php if($jumlah_keranjang > 0): ?><span class="bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full ml-1"><?= $jumlah_keranjang ?></span><?php endif; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
});
</script>
