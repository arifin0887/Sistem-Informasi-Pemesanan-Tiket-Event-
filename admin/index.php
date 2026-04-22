<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$page_map = [
    'dashboard' => 'dashboard.php',
    'admin'     => 'dashboard.php', 
    'venue'     => 'venue.php',
    'event'     => 'event.php',
    'tiket'     => 'tiket.php',
    'voucher'   => 'voucher.php',
    'transaksi' => 'transaksi.php',
    'detail'    => 'detail_transaksi.php',
    'laporan'   => 'laporan.php'
];

$include_file = $page_map[$page] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin EventKu - <?= ucfirst(htmlspecialchars($page)); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <link href="../assets/css/style.css" rel="stylesheet">
    
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        #main {
            flex: 1; 
            padding: 20px 30px;
            transition: all 0.3s;
        }
        .pagetitle { 
            margin-bottom: 25px;
        }
    </style>
</head>

<body class="bg-light">

    <?php include "../partials/sidebar.php"; ?>

    <?php include "../partials/header.php"; ?>

    <main id="main" class="main">
        <?php 
        if ($include_file && file_exists($include_file)) {
            include $include_file;
        } else {
            echo "
            <div class='container mt-5'>
                <div class='card border-0 shadow-sm p-5 text-center'>
                    <i class='bi bi-exclamation-triangle text-warning display-1'></i>
                    <h3 class='mt-3 fw-bold' style='color: #1D1145;'>Halaman Tidak Ditemukan</h3>
                    <p class='text-muted'>Maaf, modul yang Anda cari tidak tersedia dalam sistem.</p>
                    <a href='index.php?page=dashboard' class='btn btn-primary px-4 shadow-sm' style='background-color: #1D1145; border:none;'>Kembali ke Dashboard</a>
                </div>
            </div>";
        }
        ?>
    </main>

    <?php include "../partials/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("EventKu Admin Loaded: " + "<?= $page ?>");
        });
    </script>

</body>
</html>