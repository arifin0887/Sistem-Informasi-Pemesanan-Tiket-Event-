<?php
if (!isset($conn)) {
    require_once '../koneksi.php';
}

// AMBIL ID EVENT DARI URL (OPTIONAL)
$id_event = isset($_GET['id_event']) ? (int)$_GET['id_event'] : 0;

// JIKA TIDAK ADA ID EVENT → AMBIL EVENT PERTAMA (DEFAULT)
if ($id_event <= 0) {
    $q = mysqli_query($conn, "SELECT id_event FROM event ORDER BY tanggal ASC LIMIT 1");
    $d = mysqli_fetch_assoc($q);
    $id_event = $d['id_event'] ?? 0;
}

// CEK EVENT VALID
$cek_event = mysqli_query($conn, "SELECT * FROM event WHERE id_event = $id_event");
$event = mysqli_fetch_assoc($cek_event);

if (!$event) {
    echo "<div class='alert alert-danger'>Event tidak ditemukan.</div>";
    return;
}

// TOTAL CHECKIN PER EVENT
$sql_checkin = "
    SELECT COUNT(*) as total 
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN tiket t ON od.id_tiket = t.id_tiket
    WHERE a.status_checkin = 'sudah'
    AND t.id_event = $id_event
";
$total_checkin = mysqli_fetch_assoc(mysqli_query($conn, $sql_checkin))['total'];

// TOTAL PESERTA EVENT
$sql_total = "
    SELECT COUNT(*) as total 
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN tiket t ON od.id_tiket = t.id_tiket
    WHERE t.id_event = $id_event
";
$total_tiket = mysqli_fetch_assoc(mysqli_query($conn, $sql_total))['total'];

// HITUNG PERSENTASE
$persen = ($total_tiket > 0) ? ($total_checkin / $total_tiket) * 100 : 0;
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
            <div class="card attendance-card h-100 shadow-sm border-0">

                <div class="card-body p-4 d-flex flex-column justify-content-between">

                    <!-- HEADER -->
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="text-muted small mb-1">Kehadiran Event</h6>
                                <h5 class="fw-bold mb-0"><?= htmlspecialchars($event['nama_event']) ?></h5>
                            </div>
                            <i class="bi bi-people-fill text-success fs-4"></i>
                        </div>

                        <!-- TOTAL -->
                        <div class="py-2">
                            <h1 class="display-5 fw-bold mb-0"><?= $total_checkin ?></h1>
                            <span class="text-muted small">dari <?= $total_tiket ?> peserta</span>
                        </div>
                    </div>

                    <!-- PROGRESS -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-muted">Okupansi</span>
                            <span class="fw-bold small"><?= round($persen, 1) ?>%</span>
                        </div>

                        <div class="progress rounded-pill" style="height: 10px;">
                            <div class="progress-bar bg-success"
                                style="width: <?= $persen ?>%">
                            </div>
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
        // Counter animation
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const increment = target / 100;
            let current = 0;
            const updateCounter = () => {
                if (current < target) {
                    current += increment;
                    counter.textContent = Math.floor(current);
                    setTimeout(updateCounter, 20);
                } else {
                    counter.textContent = target;
                }
            };
            updateCounter();
        });

        // Progress bar animation
        const progressBars = document.querySelectorAll('.progress-bar-glow');
        progressBars.forEach(bar => {
            bar.style.width = bar.style.width;
        });

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