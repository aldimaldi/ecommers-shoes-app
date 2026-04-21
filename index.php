<?php
session_start();
require 'koneksi.php'; 

// Mengambil produk dari database
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 8");
$sepatu = $stmt->fetchAll(PDO::FETCH_ASSOC);

$keranjang_cookie = isset($_COOKIE['keranjang']) ? json_decode($_COOKIE['keranjang'], true) : [];
$jumlah_keranjang = array_sum($keranjang_cookie);

$jumlah_pesanan_aktif = 0;
if (isset($_SESSION['customer_id'])) {
    $stmt_pesanan = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status != 'COMPLETED'");
    $stmt_pesanan->execute([$_SESSION['customer_id']]);
    $jumlah_pesanan_aktif = $stmt_pesanan->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SNEAKERS.</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.slide { position: absolute; inset: 0; background-size: cover; background-position: center; opacity: 0; transition: opacity 0.7s ease; }
.slide.active { opacity: 1; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans">

<nav class="bg-white p-4 shadow mb-0">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <div class="bg-purple-600 text-white w-8 h-8 flex items-center justify-center rounded-lg font-bold">S</div>
            <a href="index.php" class="font-extrabold text-lg text-indigo-600 tracking-wider">SNEAKERS.</a>
        </div>
        
        <div class="flex items-center space-x-6">
            <a href="blog.php" class="text-slate-600 hover:text-indigo-600 font-bold text-sm">Blog</a>
            
            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="pesanan_saya.php" class="relative text-slate-600 hover:text-indigo-600 font-bold text-sm">
                    📦 Pesanan
                    <?php if($jumlah_pesanan_aktif > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] px-2 rounded-full">
                            <?= $jumlah_pesanan_aktif ?>
                        </span>
                    <?php endif; ?>
                </a>

                <a href="keranjang.php" class="relative text-xl">
                    🛒
                    <span id="cart-badge" class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] px-2 rounded-full <?= ($jumlah_keranjang > 0) ? '' : 'hidden' ?>">
                        <?= $jumlah_keranjang ?>
                    </span>
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['customer_name'])): ?>
                <span class="text-sm font-medium text-slate-700">Halo, <?= htmlspecialchars($_SESSION['customer_name']) ?></span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-sm transition font-bold">Logout</a>
            <?php else: ?>
                <a href="login.php" class="text-sm font-semibold text-slate-600 hover:text-indigo-600">Masuk</a>
                <a href="register.php" class="bg-[#9b51e0] hover:bg-[#8a42cf] text-white px-4 py-1.5 rounded-full text-sm font-bold transition">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<section class="relative h-[80vh] min-h-[600px] bg-slate-900 overflow-hidden flex flex-col">
    <div class="absolute inset-0 z-0">
        <div class="slide active" style="background-image:url('uploads/PUMA.png');"></div>
        <div class="slide" style="background-image:url('uploads/ADIDAS.png');"></div>
        <div class="slide" style="background-image:url('uploads/NIKE.png');"></div>
        <div class="absolute inset-0 bg-gray-900/60 mix-blend-multiply"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-gray-900/90 via-gray-900/60 to-transparent"></div>
    </div>
    <div class="relative z-10 flex-1 flex items-center max-w-7xl mx-auto px-6 w-full">
        <div class="text-white max-w-xl">
            <span class="inline-block border border-white/30 rounded-full px-4 py-1 text-[11px] font-bold tracking-widest uppercase mb-6 text-gray-300">
                PUMA • ADIDAS • NIKE 
            </span>
            
            <h1 class="text-6xl md:text-[5.5rem] font-extrabold leading-[1.1] tracking-tight mb-4">
                Langkah Ke <br><span class="text-[#b785ff]">Masa Depan</span>
            </h1>
            
            <p class="mt-4 text-gray-300 text-lg md:text-xl font-medium max-w-md leading-relaxed">
                Temukan rilis paling eksklusif, ulasan terbaru, dan tren dari deretan sneakers ikonik dunia hingga produk lokal kebanggaan Indonesia.
            </p>
            
            <div class="mt-8 flex items-center space-x-4 sm:space-x-6">
                <a href="#latest" class="bg-[#9b51e0] hover:bg-[#8a42cf] text-white px-7 py-3.5 rounded-full font-bold flex items-center transition shadow-lg">
                    Jelajahi Koleksi 
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                
                <a href="blog.php" class="border-2 border-white/40 hover:bg-white/10 hover:border-white/80 text-white px-7 py-3 rounded-full font-bold transition flex items-center">
                    Blog Kami
                </a>
            </div>
        </div>
    </div>
</section>

<div class="relative z-20 -mt-2 bg-[#9b51e0] text-white shadow-[0_-4px_10px_rgba(0,0,0,0.1)]">
    <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div class="flex items-center space-x-4">
            <div class="bg-white/20 p-2.5 rounded-full"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg></div>
            <div>
                <div class="text-xl font-bold">500+</div>
                <div class="text-xs text-white/80">Sneakers Diulas</div>
            </div>
        </div>
        <div class="hidden md:block w-px h-10 bg-white/30"></div>
        <div class="flex items-center space-x-4">
            <div class="bg-white/20 p-2.5 rounded-full"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></div>
            <div>
                <div class="text-xl font-bold">2,4 Jt</div>
                <div class="text-xs text-white/80">Pembaca Bulanan</div>
            </div>
        </div>
        <div class="hidden md:block w-px h-10 bg-white/30"></div>
        <div class="flex items-center space-x-4">
            <div class="bg-white/20 p-2.5 rounded-full"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
            <div>
                <div class="text-xl font-bold">120+</div>
                <div class="text-xs text-white/80">Rilis Eksklusif</div>
            </div>
        </div>
    </div>
</div>

<div id="latest" class="max-w-7xl mx-auto px-6 py-16">
    <div class="flex justify-between items-end mb-6">
        <h2 class="text-4xl font-extrabold text-slate-900">Koleksi Sepatu Terbaru</h2>
        <a href="semua_produk.php" class="text-[#9b51e0] font-bold text-sm flex items-center hover:underline">View All <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg></a>
    </div>

    <div class="flex space-x-3 mb-10 overflow-x-auto no-scrollbar pb-2">
        <button class="bg-[#9b51e0] text-white px-6 py-2 rounded-full text-sm font-bold shadow-md">All</button>
        <button class="border border-gray-200 text-gray-600 hover:border-[#9b51e0] hover:text-[#9b51e0] px-6 py-2 rounded-full text-sm font-bold transition">Running</button>
        <button class="border border-gray-200 text-gray-600 hover:border-[#9b51e0] hover:text-[#9b51e0] px-6 py-2 rounded-full text-sm font-bold transition">Basketball</button>
        <button class="border border-gray-200 text-gray-600 hover:border-[#9b51e0] hover:text-[#9b51e0] px-6 py-2 rounded-full text-sm font-bold transition">Lifestyle</button>
        <button class="border border-gray-200 text-gray-600 hover:border-[#9b51e0] hover:text-[#9b51e0] px-6 py-2 rounded-full text-sm font-bold transition">Limited</button>
    </div>
   
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
    <?php if(!empty($sepatu)): ?>
        <?php foreach($sepatu as $item): ?>
            <?php 
                // Semua deklarasi variabel dibungkus dengan aman di dalam satu tag PHP
                $gambar = !empty($item['image']) ? 'uploads/' . $item['image'] : 'uploads/sepatu_default.jpg'; 
                $slug = htmlspecialchars($item['slug'] ?? '');
                $nama = htmlspecialchars($item['name'] ?? 'Produk Tidak Diketahui');
                $harga = isset($item['price']) ? number_format($item['price'], 0, ',', '.') : '0';
            ?>
            
            <div class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-xl border border-gray-100 transition duration-300 flex flex-col h-full group">
                
                <div class="relative w-full aspect-square bg-gray-100 rounded-xl overflow-hidden mb-4">
                    <div class="absolute top-3 left-3 bg-[#9b51e0] text-white text-[10px] font-bold px-2.5 py-1 rounded-md z-10">NEW</div>
                    
                    <button class="absolute top-3 right-3 bg-white w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 shadow z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    </button>
                    
                    <a href="detail.php?produk=<?= $slug ?>" class="w-full h-full block">
                        <img src="<?= $gambar ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    </a>

                    <div class="absolute inset-0 bg-[#312e81]/40 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center z-0 pointer-events-none">
                        <a href="detail.php?produk=<?= $slug ?>" class="bg-white text-[#9b51e0] px-4 py-2 rounded-full text-xs font-bold flex items-center shadow-lg pointer-events-auto hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Pilih Varian
                        </a>
                    </div>
                </div>

                <div class="flex flex-col flex-grow">
                    <div class="text-[#9b51e0] text-[10px] font-extrabold uppercase mb-1 tracking-widest">SNEAKER</div>
                    <a href="detail.php?produk=<?= $slug ?>">
                        <h3 class="text-base font-bold text-slate-900 mb-2 truncate"><?= $nama ?></h3>
                    </a>

                    <div class="flex text-yellow-400 mb-4">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <svg class="w-3.5 h-3.5 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    </div>

                    <div class="flex justify-between items-center border-t border-gray-100 pt-3 mt-auto">
                        <p class="font-extrabold text-lg text-slate-900">Rp <?= $harga ?></p>
                        <a href="detail.php?produk=<?= $slug ?>" class="bg-[#f3e8ff] text-[#9b51e0] w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#e9d5ff]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-1 sm:col-span-2 lg:col-span-4 bg-white rounded-xl p-10 text-center border border-gray-200">
            <p class="text-slate-500 text-lg">Belum ada produk sepatu di database.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// --- LOGIC SLIDER ---
let slides = document.querySelectorAll(".slide");
let current = 0;
function showSlide(i) {
    slides.forEach(s => s.classList.remove("active"));
    if(slides[i]) slides[i].classList.add("active");
}
function nextSlide() {
    if(slides.length === 0) return;
    current = (current + 1) % slides.length;
    showSlide(current);
}
setInterval(nextSlide, 1000);

// --- LOGIC AJAX ADD TO CART ---
function tambahKeKeranjang(productId) {
    // 1. Cek apakah user sudah login (Optional: PHP auth check di ajax_tambah lebih aman, tapi ini cegah klik)
    <?php if(!isset($_SESSION['customer_id'])): ?>
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Silakan login terlebih dahulu untuk berbelanja.',
            confirmButtonColor: '#9b51e0',
            confirmButtonText: 'Login Sekarang'
        }).then((result) => {
            if (result.isConfirmed) { window.location.href = 'login.php'; }
        });
        return;
    <?php endif; ?>

    // 2. Kirim data ke file PHP secara asinkron
    fetch('ajax_tambah.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update angka di badge navbar
            let cartBadge = document.getElementById('cart-badge');
            if (cartBadge) {
                cartBadge.innerText = data.total_items;
                cartBadge.classList.remove('hidden');
            }

            // Tampilkan notifikasi pop-up cantik dari SweetAlert
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Sepatu masuk ke keranjang.',
                showConfirmButton: false,
                timer: 1500,
                toast: true,
                position: 'top-end' 
            });
        } else {
            // Tampilkan error dari backend (misal varian belum ada)
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message,
                confirmButtonColor: '#9b51e0'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>

</body>
</html>