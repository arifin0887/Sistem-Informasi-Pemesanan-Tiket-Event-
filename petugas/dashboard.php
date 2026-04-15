<?php

// CEK KONEKSI
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    require_once '../koneksi.php';
}

$hari_ini = date('Y-m-d');

// HITUNG TOTAL CHECK-IN HARI INI
$sql_checkin = "SELECT COUNT(*) as total FROM attendee WHERE status_checkin = 'sudah' AND DATE(waktu_checkin) = '$hari_ini'";
$query_checkin = mysqli_query($conn, $sql_checkin);

// JIKA QUERY GAGAL, TAMPILKAN ERROR
if (!$query_checkin) {
    die("Query Error: " . mysqli_error($conn));
}

$data_checkin = mysqli_fetch_assoc($query_checkin);
$total_checkin = $data_checkin['total'];

// HITUNG TOTAL TIKET TERDAFTAR (CHECK-IN + BELUM CHECK-IN)
$sql_total = "SELECT COUNT(*) as total FROM attendee";
$query_total = mysqli_query($conn, $sql_total);
$data_total = mysqli_fetch_assoc($query_total);
$total_tiket = $data_tiket['total'] ?? 0;
?>

<div class="pagetitle mb-4">
    <h1>Dashboard Petugas</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        
        <div class="col-md-6 col-lg-4">
            <div class="card info-card sales-card bg-primary text-white">
                <div class="card-body p-4">
                    <h5 class="card-title text-white-50 mb-3">Validasi Tiket</h5>
                    <div class="d-flex align-items-center">
                        <div class="ps-1">
                            <h6 class="text-white">Scan QR Code</h6>
                            <p class="small mb-2">Gunakan kamera untuk validasi</p>
                            <a href="?page=scan" class="btn btn-light btn-sm fw-bold">
                                <i class="bi bi-camera-fill me-1"></i> Buka Kamera
                            </a>
                        </div>
                    </div>
                    <i class="bi bi-qr-code-scan stat-icon text-white-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card info-card">
                <div class="card-body p-4">
                    <h5 class="card-title text-muted mb-3">Tiket Terpakai</h5>
                    <div class="d-flex align-items-center">
                        <div class="ps-1">
                            <h6 class="fs-2 mb-0"><?= $total_checkin; ?> <small class="text-muted fs-6">/ <?= $total_tiket; ?></small></h6>
                            <span class="text-success small pt-1 fw-bold">Update Realtime</span>
                        </div>
                    </div>
                    <i class="bi bi-people stat-icon text-primary" style="opacity: 0.1;"></i>
                </div>
            </div>
        </div>

    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title m-0">
                            <?= (isset($_GET['page']) && $_GET['page'] == 'scan') ? 'Scanner Aktif' : 'Riwayat Check-in Terbaru'; ?>
                        </h5>
                    </div>
                    
                    <?php
                    $page = isset($_GET['page']) ? $_GET['page'] : 'home';
                    if ($page == 'scan') {
                        include "scan.php";
                    } else {
                        // RIWAYAT CHECK-IN TERBARU
                        include "riwayat.php";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>