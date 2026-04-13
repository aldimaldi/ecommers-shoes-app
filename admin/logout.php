<?php
// 1. Panggil atau mulai sesi yang sedang berjalan
session_start();

// 2. Kosongkan semua data sesi yang tersimpan (seperti admin_id dan admin_name)
session_unset();

// 3. Hancurkan sesinya secara total
session_destroy();

// 4. Arahkan pengguna kembali ke halaman login utama
header("Location: ../   ");
exit;
?>