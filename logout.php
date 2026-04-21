<?php
session_start();

// 1. Hancurkan session user
session_unset();
session_destroy();

// 2. HANCURKAN COOKIE KERANJANG (Set waktunya ke masa lalu)
setcookie('keranjang', '', time() - 3600, '/'); 

// 3. Kembalikan ke halaman login
header("Location: login.php");
exit;
?>