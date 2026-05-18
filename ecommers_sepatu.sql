-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2026 at 12:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommers_sepatu`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Sneakers', 'sneakers', '2026-04-13 09:23:48', '2026-04-13 09:23:48'),
(2, 'Running Shoes', 'running-shoes', '2026-04-13 09:23:48', '2026-04-13 09:23:48'),
(3, 'Boots', 'boots', '2026-04-13 09:23:48', '2026-04-13 09:23:48');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2026_04_12_144654_create_categories_table', 1),
(6, '2026_04_12_145042_create_products_table', 1),
(7, '2026_04_12_150314_create_product_variants_table', 1),
(8, '2026_04_12_150856_create_vouchers_table', 1),
(9, '2026_04_12_151445_create_orders_table', 1),
(10, '2026_04_12_152518_create_order_items_table', 1),
(11, '2026_04_12_152822_create_posts_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `voucher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `total_price` decimal(8,2) NOT NULL,
  `discount_amount` decimal(8,2) DEFAULT NULL,
  `final_price` decimal(8,2) NOT NULL,
  `status` enum('PENDING','PAID','SHIPPED','COMPLETED') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `voucher_id`, `invoice_number`, `total_price`, `discount_amount`, `final_price`, `status`, `created_at`, `updated_at`, `snap_token`) VALUES
(1, 4, 1, 'INV-20260414-DE9C4', 500000.00, 10000.00, 490000.00, 'COMPLETED', '2026-04-13 23:33:57', '2026-04-14 00:21:24', NULL),
(2, 4, NULL, 'INV-20260414-AB5CD', 500000.00, 0.00, 500000.00, 'COMPLETED', '2026-04-13 23:49:56', '2026-04-20 12:11:03', NULL),
(3, 5, NULL, 'INV-20260421-67D4D', 499999.00, NULL, 499999.00, 'PAID', '2026-04-21 01:14:18', '2026-04-21 01:14:22', NULL),
(4, 5, 1, 'INV-20260421-BDE26', 499999.00, 10000.00, 489999.00, 'COMPLETED', '2026-04-21 02:50:14', '2026-04-21 03:12:58', NULL),
(5, 5, NULL, 'INV-20260421-A8835', 499999.00, NULL, 499999.00, 'PAID', '2026-04-21 02:56:02', '2026-04-21 02:56:05', NULL),
(6, 5, NULL, 'INV-20260421-71CAE', 499999.00, 0.00, 499999.00, 'PAID', '2026-04-21 03:09:23', '2026-04-27 22:13:41', NULL),
(7, 5, NULL, 'INV-20260421-36686', 499999.00, 0.00, 499999.00, 'PAID', '2026-04-21 03:12:21', '2026-04-21 03:12:26', NULL),
(9, 5, NULL, 'INV-20260512-D76B7', 499999.00, 0.00, 499999.00, 'PAID', '2026-05-11 22:45:50', '2026-05-11 22:49:05', 'ac5a315f-687d-4bf1-95b6-381b6a79e106'),
(10, 5, NULL, 'INV-20260512-397C2', 999999.99, 0.00, 999999.99, 'PAID', '2026-05-11 23:58:10', '2026-05-12 00:14:11', '8c211b31-bc75-4472-9161-dbf27ed33b18');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_variant_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 500000.00, '2026-04-13 23:33:57', '2026-04-13 23:33:57'),
(2, 2, 1, 1, 500000.00, '2026-04-13 23:49:56', '2026-04-13 23:49:56'),
(3, 3, 7, 1, 499999.00, '2026-04-21 01:14:18', '2026-04-21 01:14:18'),
(4, 4, 7, 1, 499999.00, '2026-04-21 02:50:14', '2026-04-21 02:50:14'),
(5, 5, 7, 1, 499999.00, '2026-04-21 02:56:02', '2026-04-21 02:56:02'),
(6, 6, 7, 1, 499999.00, '2026-04-21 03:09:23', '2026-04-21 03:09:23'),
(7, 7, 7, 1, 499999.00, '2026-04-21 03:12:21', '2026-04-21 03:12:21'),
(9, 9, 7, 1, 499999.00, '2026-05-11 22:45:50', '2026-05-11 22:45:50'),
(10, 10, 18, 1, 999999.99, '2026-05-11 23:58:10', '2026-05-11 23:58:10');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `status` enum('DRAFT','PUBLISHED') NOT NULL,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `slug`, `content`, `thumbnail`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(2, 4, 'Cara mencuci sepatu yang baik dan benar', 'cara-mencuci-sepatu-yang-baik-dan-benar-6120f', 'Cara mencuci sepatu yang benar adalah dengan melepas tali dan insole, membersihkan noda kering dengan sikat lembut, lalu menggunakan cleaner khusus atau sabun lembut dengan sikat nilon pada bagian outsole. Sikat searah, hindari merendam terlalu lama, dan keringkan di tempat teduh, bukan di bawah matahari langsung untuk mencegah kuning atau merusak bahan. Hello Sehat +3Langkah-langkah Mencuci Sepatu yang Benar:Persiapan: Lepaskan tali sepatu dan insole (alas dalam) agar kering merata dan lebih bersih.Pembersihan Kering: Sikat kotoran kering, debu, atau tanah yang menempel di seluruh permukaan sepatu menggunakan sikat halus atau sikat gigi.Pencucian (Upper & Midsole): Gunakan cairan pembersih khusus sepatu (cleaner foam) atau campuran sabun lembut/deterjen cair dengan air hangat. Sikat dengan lembut searah untuk menghindari kerusakan bahan.Pembersihan Sol (Outsole): Sikat bagian sol bawah menggunakan sikat nilon yang lebih kaku agar kotoran membandel hilang.Pembilasan: Lap dengan kain microfiber basah sampai busa hilang. Hindari merendam seluruh sepatu dalam air, terutama bahan kulit atau suede.Pengeringan: Keringkan sepatu dengan cara diangin-anginkan di tempat teduh. Hindari sinar matahari langsung agar sepatu tidak kaku atau kuning.Finishing: Gunakan parfum sepatu agar wangi dan terhindar dari bau apek. YouTube·Tirta PengPengPeng +9Tips Tambahan Sesuai Bahan:Kanvas: Bisa sikat nilon dan campuran sabun cair.Suede/Kulit: Gunakan sikat khusus suede dan hindari air berlebihan; gunakan cleaner khusus kulit.Sepatu Putih: Pastikan membilas bersih untuk mencegah noda kuning (yellowing).', 'blog_edit_1778567829_6a02ca9577199.png', 'PUBLISHED', '2026-05-12 06:37:09', '2026-05-12 00:17:29', '2026-05-12 01:37:09');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `image`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 1, 'New Balance blue', 'new-balance-blue', 'Stylish shoes for work or hangout', 500000.00, '1776137750_69ddb616474a6.jpg', '2026-04-13 22:35:50', '2026-04-13 22:35:50', '2026-04-21 07:07:24'),
(5, 2, 'Nike Air Jordan', 'nike-air-jordan', 'Air Jordan adalah lini sepatu basket dan lifestyle premium yang diproduksi oleh Nike, dirancang khusus untuk legenda NBA, Michael Jordan, sejak 1984. Sepatu ini ikonik karena menggabungkan teknologi bantalan udara (\"Air\"), desain timeless (seperti seri 1, 3, 4), logo Jumpman, dan gaya streetwear yang sangat populer di kalangan sneakerhead. ', 999999.99, '1776144248_69ddcf78d7f5a.jpg', '2026-04-14 00:24:08', '2026-04-14 00:24:08', '2026-04-21 07:07:18'),
(6, 1, 'Vans', 'vans-a8c47', 'Sepatu sylish cocok dipakai untuk sekolah atau pun hangout bareng teman atau pasangan', 499999.00, 'sepatu_1776704040_69e65a28a8c63.jpg', '2026-04-20 11:54:00', '2026-04-20 11:54:00', NULL),
(7, 1, 'Nike Air Jordan', 'nike-air-jordan-eec0a', 'Air Jordan adalah lini sepatu basket dan lifestyle premium yang diproduksi oleh Nike, dirancang khusus untuk legenda NBA, Michael Jordan. Dikenal karena desain ikonik (terutama model 1-14), material kulit berkualitas tinggi, bantalan udara (Air-Sole) yang empuk, dan logo \"Jumpman\" yang khas, sepatu ini menggabungkan performa atletik dengan gaya streetwear yang tak lekang oleh waktu.', 999999.99, 'sepatu_1778560099_6a02ac63eec39.jpg', '2026-05-11 23:28:19', '2026-05-11 23:28:19', NULL),
(8, 1, 'New Balance', 'new-balance-52604', 'Sepatu New Balance dikenal sebagai perpaduan antara gaya retro/klasik, kenyamanan superior, dan performa tinggi, sering menggunakan material premium seperti mesh dan suede.', 500000.00, 'sepatu_1778560180_6a02acb452632.jpg', '2026-05-11 23:29:40', '2026-05-11 23:29:40', NULL),
(9, 1, 'Converse', 'converse-84401', 'Converse All Star pertama kali diperkenalkan sebagai sepatu basket dan hadir dengan warna neutral brown disertai strip hitam. Perlu kamu ketahui, sepatu Converse All Star yang sekarang ini kita kenal, pada bagian upper-nya masih menggunakan bahan kanvas dan rubber yang sama seperti old generation All Star.\r\nPada awal kemunculannya, Converse All Star diproduksi dengan fokus untuk memberikan performa terbaik ketika digunakan bermain bola basket.', 999999.99, 'sepatu_1778560574_6a02ae3e84432.jpg', '2026-05-11 23:36:14', '2026-05-11 23:36:14', NULL),
(10, 2, 'Adidas', 'adidas-62eae', 'Sepatu running Adidas dikenal dengan kombinasi teknologi bantalan responsif (seperti Boost, Lightstrike, atau Cloudfoam) dan material upper mesh yang ringan dan sejuk, dirancang untuk meningkatkan performa lari, stabilitas, serta kenyamanan jangka panjang.', 2000000.00, 'sepatu_1778561370_6a02b15a62edb.jpg', '2026-05-11 23:49:30', '2026-05-11 23:49:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `size` int(11) NOT NULL,
  `color` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `size`, `color`, `stock`, `created_at`, `updated_at`) VALUES
(1, 4, 43, 'biru', 13, '2026-04-13 22:51:04', '2026-04-13 22:51:04'),
(2, 4, 40, 'Hitam', 10, '2026-04-14 00:11:59', '2026-04-14 00:11:59'),
(4, 5, 40, 'Merah', 20, '2026-04-14 00:25:01', '2026-04-14 00:25:01'),
(5, 5, 41, 'Putih', 15, '2026-04-14 00:25:15', '2026-04-14 00:25:15'),
(6, 5, 42, 'Hitam Pekat', 6, '2026-04-20 11:44:50', '2026-04-20 11:44:50'),
(7, 6, 40, 'Hitam Putih', 20, '2026-04-21 01:02:44', '2026-05-11 23:31:34'),
(8, 8, 42, 'Biru', 20, '2026-05-11 23:30:10', '2026-05-11 23:30:10'),
(9, 8, 40, 'Hitam', 15, '2026-05-11 23:30:27', '2026-05-11 23:30:27'),
(10, 8, 42, 'Putih', 25, '2026-05-11 23:30:39', '2026-05-11 23:30:39'),
(11, 7, 40, 'Hitam', 20, '2026-05-11 23:30:58', '2026-05-11 23:30:58'),
(12, 7, 41, 'Putih', 20, '2026-05-11 23:31:09', '2026-05-11 23:31:09'),
(13, 7, 42, 'Merah', 20, '2026-05-11 23:31:18', '2026-05-11 23:31:18'),
(14, 9, 39, 'Hitam Putih', 25, '2026-05-11 23:50:21', '2026-05-11 23:50:21'),
(15, 9, 40, 'Putih', 20, '2026-05-11 23:50:32', '2026-05-11 23:50:32'),
(16, 9, 41, 'Hitam', 20, '2026-05-11 23:51:11', '2026-05-11 23:51:11'),
(17, 10, 38, 'Biru', 20, '2026-05-11 23:51:34', '2026-05-11 23:51:34'),
(18, 10, 40, 'Putih', 24, '2026-05-11 23:51:49', '2026-05-11 23:51:49'),
(19, 10, 41, 'Hitam', 20, '2026-05-11 23:55:00', '2026-05-11 23:55:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'customer',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Aldi Maulana', 'aldima0904@gamil.com', NULL, '$2y$10$Coo5Ne0OUBquggdk.8nYH.mYJa/vrzXCQdrCncnDr7e.S4iOTFbhS', 'customer', NULL, '2026-04-13 06:18:25', '2026-04-13 06:18:25', '2026-05-12 03:17:18'),
(2, 'Administrator', 'admin@sepatu.com', NULL, '$2y$10$wUXA3TH8DpDYdv8yEeMmWefCgXMYMN/.bacPazJcH.Sz1RilYdm7q', 'admin', NULL, NULL, NULL, '2026-05-12 03:17:14'),
(3, 'Jordan Jumadi', 'Jordankyu@sepatu.com', NULL, '$2y$10$/P5.vYkizJrVYodDUGth/O6jDrQh7iFy2Vctbv8mRJ3eeJ3ecxUEG', 'customer', NULL, '2026-04-13 09:38:19', '2026-04-13 09:38:19', '2026-05-12 03:17:09'),
(4, 'Rendi Libero', 'rendilibero@sepatu.com', NULL, '$2y$10$QAU3pVkokeMuLHX6MDYJH.NabKm/ZnJN9fGn0kd1pS.ViO3bPadcS', 'admin', NULL, '2026-04-13 22:53:17', '2026-04-13 22:53:17', NULL),
(5, 'Testing kun', 'testing@gmail.com', NULL, '$2y$10$kFRWBeJgA4XSYqWArEKMcuts0eMV.sqwYHQjGpi2tEMXNmpikUtnm', 'customer', NULL, '2026-04-21 01:09:47', '2026-04-21 01:09:47', NULL),
(6, 'Rizaldi', 'rizal123@gmail.com', NULL, '$2y$10$Ec.0KlJ.aoqW4fZoMKLBVOmZT6MkBkLbhHSfMlNaTl1y9/lPcQFCm', 'customer', NULL, '2026-04-27 21:57:16', '2026-04-27 21:57:16', '2026-05-12 03:16:38');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `value` decimal(8,2) NOT NULL,
  `min_purchase` int(11) DEFAULT NULL,
  `max_uses` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL,
  `valid_until` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `type`, `value`, `min_purchase`, `max_uses`, `used_count`, `valid_until`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'IDULADHA26', 'fixed', 10000.00, 100000, 5, 2, '2026-04-25 12:00:00', '2026-04-13 23:25:19', '2026-04-13 23:25:19', '2026-04-21 07:11:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_invoice_number_unique` (`invoice_number`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_voucher_id_foreign` (`voucher_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `posts_slug_unique` (`slug`),
  ADD KEY `posts_user_id_foreign` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_variants_product_id_foreign` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vouchers_code_unique` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
