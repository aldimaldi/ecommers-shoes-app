<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Sneakers</title>
  <!-- <link rel="shortcut icon" type="image/png" href="./assets/images/logos/favicon.png" /> -->
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <style>
    /* 1. Menarik Sidebar ke ujung paling atas layar */
    #main-wrapper[data-layout="vertical"][data-sidebar-position="fixed"] .left-sidebar {
        top: 0 !important;
    }

    /* 2. Menarik Navbar Putih ke ujung paling atas layar */
    .app-header {
        top: 0 !important;
    }

    /* 3. Membuang jarak raksasa (180px) di atas Konten Utama */
    /* Kita sisakan 100px agar konten tidak tertutup oleh navbar putih */
    .body-wrapper .container-fluid, 
    #main-wrapper[data-layout="vertical"][data-header-position="fixed"] .body-wrapper > .container-fluid {
        padding-top: 100px !important; 
    }

    /* 4. Memperbaiki tinggi scroll area di sidebar yang sebelumnya terpotong (ng-bug) */
    .left-sidebar .scroll-sidebar {
        height: calc(100vh - 70px) !important;
    }

    /* 5. Menghilangkan garis abu-abu scrollbar yang memotong menu */
    .simplebar-track.simplebar-vertical {
        visibility: hidden !important;
    }
  </style>
</head>
</head>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">