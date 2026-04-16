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

<style>
    .info-card {
        border: none;
        border-radius: 20px;
        transition: transform 0.3s;
    }
    .info-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 4rem;
        opacity: 0.1;
    }
    /* Styling untuk Input Scanner Kasir */
    .scanner-input-group {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 15px;
        padding: 15px;
        transition: all 0.3s;
    }
    .scanner-input-group:focus-within {
        border-color: #0d6efd;
        background: #fff;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
    #manual_kode {
        border: none;
        background: transparent;
        font-weight: bold;
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    #manual_kode:focus {
        box-shadow: none;
    }

    .method-card {
        border: none;
        border-radius: 24px;
        background: #ffffff;
        transition: all 0.3s ease;
    }

    /* Efek tombol Kamera HP */
    .btn-camera {
        background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
        border: none;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 20px rgba(13, 110, 253, 0.2);
    }

    .btn-camera:hover {
        transform: scale(1.05);
        box-shadow: 0 15px 25px rgba(13, 110, 253, 0.3);
        background: linear-gradient(135deg, #0b5ed7 0%, #003687 100%);
    }

    /* Area Scanner Kasir */
    .scanner-container {
        background: #fcfdfe;
        border: 2px solid #edf2f7;
        border-radius: 20px;
        padding: 12px 20px;
        transition: all 0.3s ease;
        position: relative;
    }

    .scanner-container:focus-within {
        border-color: #0d6efd;
        background: #fff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .scanner-icon-box {
        width: 45px;
        height: 45px;
        background: #eef4ff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0d6efd;
    }

    #manual_kode {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 1.1rem;
        letter-spacing: 1px;
        color: #1e293b;
    }

    #manual_kode::placeholder {
        font-family: 'Inter', sans-serif;
        font-size: 0.9rem;
        letter-spacing: 0;
        color: #94a3b8;
    }

    /* Animasi pulse kecil untuk indikator standby */
    .standby-indicator {
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    .attendance-card {
        border: none;
        border-radius: 24px;
        background: linear-gradient(145deg, #1e293b, #0f172a); /* Gradient gelap yang elegan */
        overflow: hidden;
    }

    /* Efek pendaran pada angka utama */
    .glow-text {
        color: #fff;
        text-shadow: 0 0 20px rgba(13, 110, 253, 0.5);
        letter-spacing: -1px;
    }

    /* Progress Bar yang lebih modern */
    .progress-custom {
        background: rgba(255, 255, 255, 0.1) !important;
        height: 12px !important;
        border-radius: 20px !important;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
    }

    .progress-bar-glow {
        background: linear-gradient(90deg, #0d6efd, #6ea8fe);
        border-radius: 20px;
        box-shadow: 0 0 15px rgba(13, 110, 253, 0.4);
        position: relative;
        overflow: visible;
    }

    /* Indikator pendar di ujung progress bar */
    .progress-bar-glow::after {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 10px;
        height: 10px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 0 10px #fff;
    }

    /* Icon dekoratif di latar belakang */
    .bg-icon-decoration {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 8rem;
        color: rgba(255, 255, 255, 0.03); /* Sangat samar */
        transform: rotate(-15deg);
        pointer-events: none;
    }

    .capacity-badge {
        background: rgba(13, 110, 253, 0.15);
        color: #74b1ff;
        padding: 4px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .content-area {
        animation: fadeIn 0.4s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 576px) {
        .btn-camera { width: 100% !important; height: 80px !important; flex-direction: row !important; gap: 10px; }
        .btn-camera i { font-size: 1.5rem !important; }
    }
</style>

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
        
        <div class="col-xl-8 col-lg-12 mb-4">
            <div class="card method-card shadow-sm h-100 border-0">
                <div class="card-body p-4 p-xl-5">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <span class="standby-indicator"></span>
                                <h5 class="fw-bold mb-0 text-dark">Sistem Validasi Tiket</h5>
                            </div>
                            <p class="text-muted mb-4">Scan tiket peserta menggunakan kamera perangkat atau hubungkan alat scanner laser.</p>
                            
                            <div class="row g-3">
                                <!-- <div class="col-auto">
                                    <a href="?page=scan" class="btn btn-camera p-0 rounded-4 d-flex flex-column align-items-center justify-content-center text-white" style="width: 110px; height: 110px;">
                                        <i class="bi bi-camera-fill fs-2 mb-1"></i>
                                        <span class="fw-bold" style="font-size: 0.75rem;">KAMERA HP</span>
                                    </a>
                                </div> -->

                                <div class="col">
                                    <div class="scanner-container h-100 d-flex flex-column justify-content-center">
                                        <div class="d-flex align-items-center">
                                            <div class="scanner-icon-box me-3">
                                                <i class="bi bi-upc-scan fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <label class="small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Scanner Kasir / Manual</label>
                                                <form id="formScannerManual" class="d-flex align-items-center">
                                                    <input type="text" id="manual_kode"
                                                        class="form-control p-0 border-0 bg-transparent shadow-none"
                                                        placeholder="Siap scan tiket..."
                                                        autocomplete="off">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 d-none d-md-block text-end">
                            <div class="p-3 bg-light rounded-circle d-inline-block">
                                <i class="bi bi-shield-check text-primary" style="font-size: 4rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card attendance-card shadow-lg h-100 position-relative">
                <i class="bi bi-people bg-icon-decoration"></i>
                
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h5 class="card-title text-white fw-bold mb-1" style="letter-spacing: 0.5px;">Kehadiran</h5>
                            <span class="capacity-badge text-uppercase">Real-time Data</span>
                        </div>
                        <div class="text-white-50">
                            <i class="bi bi-broadcast fs-4"></i>
                        </div>
                    </div>

                    <div class="py-3">
                        <div class="d-flex align-items-baseline">
                            <h1 class="display-3 fw-bold mb-0 glow-text"><?= $total_checkin; ?></h1>
                            <span class="ms-2 text-white-50 fs-4">/ <?= $total_tiket; ?></span>
                        </div>
                        <p class="text-white-50 small mt-1">Peserta telah masuk ke venue</p>
                    </div>

                    <div class="mt-4">
                        <?php $persen = ($total_tiket > 0) ? ($total_checkin / $total_tiket) * 100 : 0; ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-white-50 small">Okupansi</span>
                            <span class="text-white fw-bold small"><?= round($persen, 1) ?>%</span>
                        </div>
                        
                        <div class="progress progress-custom">
                            <div class="progress-bar progress-bar-glow" 
                                role="progressbar" 
                                style="width: <?= $persen ?>%" 
                                aria-valuenow="<?= $persen ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 border-top border-secondary border-opacity-25">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check2-circle text-primary fs-4"></i>
                            </div>
                            <div class="ms-3">
                                <p class="mb-0 text-white-50" style="font-size: 0.75rem;">Status Gate</p>
                                <p class="mb-0 text-white fw-bold small">Pintu Masuk Terbuka</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- SCAN KAMERA -->
    <!-- <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0">
                            <i class="bi bi-clock-history me-2"></i>
                            <?= (isset($_GET['page']) && $_GET['page'] == 'scan') ? 'Scanner Kamera Aktif' : 'Aktivitas Terkini'; ?>
                        </h5>
                        <?php if(isset($_GET['page']) && $_GET['page'] == 'scan'): ?>
                            <a href="index.php" class="btn btn-sm btn-light">Tutup Scanner</a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="content-area">
                        <?php
                        if ($page == 'scan') {
                            include "scan.php";
                        } else {
                            include "riwayat.php";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const inputManual = document.getElementById('manual_kode');

    inputManual.focus();

    // selalu fokus (scanner mode)
    document.addEventListener('click', () => inputManual.focus());

    let scanBuffer = "";
    let scanTimeout;

    inputManual.addEventListener('input', function (e) {
        clearTimeout(scanTimeout);

        scanBuffer = e.target.value;

        // scanner biasanya kirim ENTER di akhir
        scanTimeout = setTimeout(() => {
            const kode = scanBuffer.trim();

            if (kode.length < 3) return;

            prosesCheckin(kode);

            inputManual.value = "";
            scanBuffer = "";
        }, 300); // delay kecil untuk deteksi selesai scan
    });

    function prosesCheckin(kode) {
        Swal.fire({
            title: 'Memvalidasi...',
            didOpen: () => { Swal.showLoading() },
            allowOutsideClick: false
        });

        fetch('proses_checkin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'kode_tiket=' + encodeURIComponent(kode)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Check-in Berhasil',
                    text: data.message,
                    timer: 1200,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message
                });
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Server tidak merespon', 'error');
        });
    }
});
</script>