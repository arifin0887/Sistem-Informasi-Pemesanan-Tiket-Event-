<?php

// CEK LOGIN
if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

// ID USER DARI SESSION
$id_order = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_user = (int)$_SESSION['user']['id'];

// QUERY UNTUK MENGAMBIL DATA TIKET USER BESERTA NAMA EVENT, TANGGAL, DAN VENUE
$query = "SELECT 
            o.id_order, 
            t.nama_tiket, 
            e.nama_event, 
            e.tanggal as tgl_event, 
            v.nama_venue, 
            a.kode_tiket
          FROM orders o
          JOIN order_detail od ON o.id_order = od.id_order
          JOIN tiket t ON od.id_tiket = t.id_tiket
          JOIN event e ON t.id_event = e.id_event
          JOIN venue v ON e.id_venue = v.id_venue
          JOIN attendee a ON od.id_detail = a.id_detail
          WHERE o.id_order = ? AND o.id_user = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_order, $id_user);
$stmt->execute();
$result = $stmt->get_result();

$tickets = [];
// AMBIL SEMUA TIKET YANG DITEMUKAN (BISA JADI SATU ORDER MEMILIKI BEBERAPA TIKET)
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}

// JIKA TIDAK ADA TIKET DITEMUKAN, TAMPILKAN PESAN ERROR
if (empty($tickets)) {
    echo "<div class='alert alert-danger shadow-sm rounded-4 m-5'>Tiket tidak ditemukan atau belum lunas.</div>";
    return;
}
?>

<div class="container py-5">
    <div class="text-center mb-5 no-print">
        <h3 class="fw-bold">E-Ticket Resmi</h3>
        <p class="text-muted">Tunjukkan QR Code di bawah ini saat memasuki area event.</p>
        <button onclick="window.print()" class="btn btn-dark btn-sm rounded-pill px-4">
            <i class="bi bi-printer me-1"></i> Cetak Tiket
        </button>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <?php foreach ($tickets as $t): ?>
                <div class="card mb-5 shadow-sm border-0 overflow-hidden" style="border-radius: 20px;">
                    <div class="p-3 text-center text-white" style="background-color: #1d1145;">
                        <h5 class="mb-0 fw-bold"><?= htmlspecialchars($t['nama_event']); ?></h5>
                    </div>
                    
                    <div class="card-body p-4 text-center">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="qrcode-container p-3 border rounded-4" style="background: #ffffff;">
                                <div class="qrcode" data-code="<?= $t['kode_tiket']; ?>"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Kode Tiket</small>
                            <span class="fw-bold text-primary fs-5"><?= $t['kode_tiket']; ?></span>
                        </div>

                        <hr class="dashed">

                        <div class="row text-start mt-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Jenis Tiket</small>
                                <span class="fw-bold text-uppercase"><?= $t['nama_tiket']; ?></span>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted d-block">Waktu</small>
                                <span class="fw-bold"><?= date('d M Y, H:i', strtotime($t['tgl_event'])); ?></span>
                            </div>
                            <div class="col-12 mt-3">
                                <small class="text-muted d-block">Lokasi</small>
                                <span class="fw-bold"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= $t['nama_venue']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- SCRIPT UNTUK GENERATE QR CODE MENGGUNAKAN LIBRARY QRCode.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    // Setelah halaman dimuat, cari semua elemen dengan class 'qrcode' dan generate QR Code berdasarkan data-code
    document.addEventListener("DOMContentLoaded", function() {
        // CARI SEMUA ELEMEN YANG MEMILIKI CLASS 'qrcode'
        var qrcodeElements = document.querySelectorAll('.qrcode');
        
        qrcodeElements.forEach(function(el) {
            var codeValue = el.getAttribute('data-code');
            // GENERATE QR CODE UNTUK SETIAP ELEMEN
            new QRCode(el, {
                text: codeValue,
                width: 160,
                height: 160,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        });
    });
</script>

<style>
    .dashed { border-top: 2px dashed #eee; opacity: 1; margin: 20px 0; }
    .qrcode img { margin: 0 auto; } 
    @media print {
        .no-print, nav, footer, .btn { display: none !important; }
        body { background: white !important; }
        .card { break-inside: avoid; border: 1px solid #eee !important; margin-bottom: 50px !important; }
    }
</style>