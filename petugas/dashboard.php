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

<section class="section dashboard">
    <div class="row g-4">
        
        <div class="col-xl-8 col-lg-12">
            <div class="card card-buy h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <span class="standby-indicator"></span>
                                <h4 class="fw-bold mb-0 text-navy">Sistem Validasi Tiket</h4>
                            </div>
                            <p class="text-muted mb-4">Arahkan scanner pada QR Code tiket peserta untuk melakukan validasi kedatangan.</p>
                            
                            <div class="scanner-container p-3 rounded-4 border bg-light shadow-sm">
                                <div class="d-flex align-items-center">
                                    <div class="scanner-icon-box me-3">
                                        <i class="bi bi-qr-code-scan fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.7rem; letter-spacing: 1px;">Input Kode / Laser Scanner</label>
                                        <form id="formScannerManual">
                                            <input type="text" id="manual_kode"
                                                class="form-control form-control-lg border-0 bg-transparent p-0 shadow-none fw-bold"
                                                placeholder="Menunggu scan..."
                                                autocomplete="off" autofocus>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 d-none d-md-block text-center">
                            <div class="p-4 rounded-circle bg-light d-inline-block">
                                <i class="bi bi-shield-check text-navy" style="font-size: 5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-4">
            <div class="card attendance-card h-100 shadow">
                <div class="card-body p-4 p-xl-5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="fw-bold mb-0">Kehadiran</h5>
                            <i class="bi bi-broadcast text-danger pulse"></i>
                        </div>
                        
                        <div class="py-2">
                            <h1 class="display-3 fw-bold mb-0"><?= $total_checkin; ?></h1>
                            <span class="opacity-50 fs-5">dari <?= $total_tiket; ?> Peserta</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <?php $persen = ($total_tiket > 0) ? ($total_checkin / $total_tiket) * 100 : 0; ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small opacity-75">Okupansi Venue</span>
                            <span class="fw-bold small"><?= round($persen, 1) ?>%</span>
                        </div>
                        <div class="progress progress-custom">
                            <div class="progress-bar progress-bar-glow" style="width: <?= $persen ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
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