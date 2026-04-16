<?php

// SEARCH PARAMETER
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

// QUERY UNTUK MENGAMBIL DATA EVENT BESERTA TIKETNYA, DENGAN FILTER PENCARIAN JIKA ADA
$query = "SELECT 
            e.id_event, e.nama_event, e.tanggal, e.id_venue,
            v.nama_venue, v.alamat,
            t.id_tiket, t.nama_tiket, t.harga, t.kuota
          FROM event e 
          JOIN venue v ON e.id_venue = v.id_venue 
          LEFT JOIN tiket t ON e.id_event = t.id_event ";

// TAMBAHKAN KONDISI PENCARIAN JIKA PARAMETER SEARCH TIDAK KOSONG
if ($search != '') {
    $query .= " WHERE e.nama_event LIKE '%$search%' OR v.nama_venue LIKE '%$search%' ";
}

// QUERY DENGAN SORTING BERDASARKAN TANGGAL EVENT TERDEKAT DAN HARGA TERMURAH
$query .= " ORDER BY e.tanggal ASC, t.harga ASC";
$result = mysqli_query($conn, $query);

// GROUPING DATA EVENT
$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $eid = $row['id_event'];
    if (!isset($events[$eid])) {
        $events[$eid] = [
            'id_event'   => $row['id_event'],
            'nama_event' => $row['nama_event'],
            'tanggal'    => $row['tanggal'],
            'nama_venue' => $row['nama_venue'],
            'alamat'     => $row['alamat'],
            'tikets'     => []
        ];
    }
    if ($row['id_tiket']) {
        $events[$eid]['tikets'][] = [
            'id_tiket'   => $row['id_tiket'],
            'nama_tiket' => $row['nama_tiket'],
            'harga'      => (int)$row['harga'],
            'kuota'      => (int)$row['kuota']
        ];
    }
}

// AMBIL DATA VOUCHER AKTIF TERBARU
$query_voucher = mysqli_query($conn, "SELECT * FROM voucher WHERE status='aktif' AND kuota > 0 ORDER BY id_voucher DESC LIMIT 1");
$v = mysqli_fetch_assoc($query_voucher);
?>

<style>
    :root { 
        --navy: #1d1145; 
        --pink: #e66c8a; 
        --soft-bg: #f6f9ff; 
    }
    
    .search-card { 
        border: none; 
        border-radius: 15px; 
        color: white; 
        background: linear-gradient(135deg, #1d1145 0%, #2d1b6b 100%);
        border-radius: 20px; 
    }

    .search-card .form-control { 
        border: none; 
        padding: 12px 20px; 
        border-radius: 0 12px 12px 0 !important;
    }

    .btn-find-event {
        background: var(--pink);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 11px;
        transition: 0.3s;
    }

    .btn-find-event:hover {
        background: #c25470;
        color: white;
        transform: scale(1.02);
    }
    
    .event-card { 
        border: none; 
        border-radius: 20px; 
        transition: all 0.3s ease; 
        background: #fff; 
        overflow: hidden; 
    }

    .event-card:hover { 
        transform: translateY(-10px); 
        box-shadow: 0 15px 30px rgba(29, 17, 69, 0.1) !important; 
    }
    
    .event-date-badge { 
        background: var(--soft-bg); 
        color: var(--navy); 
        padding: 8px 15px; 
        border-radius: 12px; 
        font-weight: 700; 
        text-align: center; 
        line-height: 1.2;
    }

    .price-tag { 
        color: var(--pink); 
        font-weight: 800; 
        font-size: 1.2rem; 
    }

    .btn-book { 
        background: var(--navy); 
        color: white; 
        border-radius: 12px; 
        padding: 8px 20px; 
        font-weight: 600; 
        transition: 0.3s; 
    }

    .btn-book:hover { 
        background: var(--pink); 
        color: white; 
    }

    .modal-content { 
        border: none; 
        border-radius: 25px; 
        overflow: hidden; 
    }

    .modal-header { 
        background: var(--navy); 
        color: white; 
        border: none; 
    }

    .btn-close-white { 
        filter: brightness(0) invert(1); 
    }

    .promo-banner {
        background: linear-gradient(45deg, #1D1145, #0DB5BB);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        transition: 0.3s;
    }

    .promo-banner:hover {
        transform: scale(1.01);
    }

    .display-title {
        font-size: 1.75rem; /* Ukuran lebih besar dan tegas */
        line-height: 1.1;
        letter-spacing: -0.02em;
        color: #1a237e; /* Navy */
        cursor: pointer;
        transition: all 0.3s ease;
        word-wrap: break-word; /* Biar kalau kepanjangan tidak hancur */
        display: -webkit-box;
        -webkit-line-clamp: 2; /* Maksimal 2 baris */
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 3.5rem;
    }

    .display-title:hover {
        color: #ff4081; /* Pink accent */
    }

    .event-date-badge {
        background: #fdfdfd;
        min-width: 80px;
        text-align: center;
        padding: 12px 8px;
        border-radius: 18px;
        border: 1px dashed #ddd;
        flex-shrink: 0; /* Mencegah box tanggal menyusut */
    }

    .event-card.expired {
        opacity: 0.7;
        filter: grayscale(0.5);
    }

    .my-minus-1 {
        margin-top: -3px;
        margin-bottom: -3px;
    }

    .text-pink {
        color: #ff4081;
    }

    .input-group-text {
        border-radius: 12px 0 0 12px !important;
    }

</style>

<?php if($v): ?>
<div class="promo-banner mb-4 p-4 rounded-4 text-white d-flex justify-content-between align-items-center">
    <div>
        <h5 class="fw-bold mb-1">🎉 Promo Spesial!</h5>
        <p class="mb-0 small">
            Gunakan kode <b><?= $v['kode_voucher'] ?></b> untuk diskon Rp<?= number_format($v['potongan'], 0, ',', '.') ?> pada pembelian tiket! <b>Kuota Terbatas</b>, segera manfaatkan sebelum habis!
        </p>
    </div>
    <button class="btn btn-light fw-bold rounded-pill px-4" onclick="copyVoucher('<?= $v['kode_voucher'] ?>')">
        Gunakan
    </button>
</div>
<?php endif; ?>

<section class="section">
    <div class="card search-card mb-4 shadow-sm">
        <div class="card-body p-4">
            <form action="index.php" method="GET" class="row g-3 align-items-center">
                <input type="hidden" name="page" value="event">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari konser, workshop, atau lokasi..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-light w-100 fw-bold" style="color: var(--navy); border-radius: 10px; padding: 11px;">Temukan Event</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- JIKA TIDAK ADA DATA EVENT, TAMPILKAN PESAN KOSONG -->
        <?php if (empty($events)): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Maaf, event tidak ditemukan.</h5>
                <a href="index.php?page=event" class="text-pink">Lihat semua event</a>
            </div>
        <?php else: ?>
            <!-- LOOPING DATA EVENT DAN TAMPILKAN DALAM BENTUK KARTU -->
            <?php foreach ($events as $event): 
                $isExpired = strtotime($event['tanggal']) < time();
                $min_price = !empty($event['tikets']) ? min(array_column($event['tikets'], 'harga')) : 0;
                $total_kuota = array_sum(array_column($event['tikets'], 'kuota'));
            ?>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100 event-card shadow-sm border-0" style="border-radius: 20px;">
                        <div class="card-body p-4 position-relative"> <span class="badge rounded-pill <?= $isExpired ? 'bg-secondary' : 'bg-success' ?> position-absolute" 
                                style="top: 20px; right: 20px; font-size: 0.7rem; z-index: 10;">
                                <?= $isExpired ? 'Selesai' : 'Tersedia' ?>
                            </span>

                            <div class="d-flex align-items-center mb-4 pe-5"> <div class="event-date-badge me-3">
                                    <span class="d-block small text-uppercase fw-bold text-muted"><?= date('M', strtotime($event['tanggal'])) ?></span>
                                    <span class="fs-2 d-block fw-bold my-minus-1" style="color: var(--event-navy);"><?= date('d', strtotime($event['tanggal'])) ?></span>
                                    <span class="d-block small text-uppercase text-muted"><?= date('Y', strtotime($event['tanggal'])) ?></span>
                                </div>
                                
                                <div class="flex-grow-1">
                                    <h2 class="fw-bold mb-0 display-title" onclick='showDetail(<?= json_encode($event) ?>)'>
                                        <?= htmlspecialchars($event['nama_event']) ?>
                                    </h2>
                                </div>
                            </div>

                            <div class="text-muted small mb-3 ps-1">
                                <p class="mb-1"><i class="bi bi-geo-alt-fill text-pink me-2"></i><?= htmlspecialchars($event['nama_venue']) ?></p>
                                <p class="mb-0"><i class="bi bi-clock-fill text-pink me-2"></i><?= date('H:i', strtotime($event['tanggal'])) ?> WIB</p>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Mulai dari</small>
                                    <span class="price-tag fs-5 fw-bold" style="color: var(--event-accent);">Rp <?= number_format($min_price, 0, ',', '.') ?></span>
                                </div>
                                <button class="btn btn-book btn-sm shadow-sm px-4 rounded-pill fw-bold" onclick='showDetail(<?= json_encode($event) ?>)'>
                                    Beli Tiket
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- MODAL UNTUK MENAMPILKAN DETAIL TIKET -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-ticket-detailed me-2"></i>Pilih Tipe Tiket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="modalContent">
                    </div>
            </div>
        </div>
    </div>
</div>

<script>
    // FUNGSI UNTUK MENAMPILKAN DETAIL TIKET DALAM MODAL
    function showDetail(event) {
        const modalBody = document.getElementById('modalContent');
        let tiketHtml = '';

        if (event.tikets.length > 0) {
            tiketHtml = `
                <div class="mb-4">
                    <h4 class="fw-bold mb-1" style="color: var(--navy);">${event.nama_event} - ${event.tanggal}</h4>
                    <p class="text-muted small"><i class="bi bi-geo-alt me-1"></i> ${event.nama_venue}</p>
                </div>
                <div class="list-group list-group-flush border rounded-3 overflow-hidden">
            `;

            event.tikets.forEach(t => {
                const isSoldOut = t.kuota <= 0;
                tiketHtml += `
                    <div class="list-group-item p-3 d-flex justify-content-between align-items-center ${isSoldOut ? 'bg-light' : ''}">
                        <div>
                            <h6 class="mb-0 fw-bold">${t.nama_tiket}</h6>
                            <span class="text-pink fw-bold">Rp ${t.harga.toLocaleString('id-ID')}</span>
                            <small class="d-block text-muted">Sisa Kuota: ${t.kuota}</small>
                        </div>
                        ${isSoldOut 
                            ? '<span class="badge bg-danger">Habis Terjual</span>' 
                            : `<a href="index.php?page=buy&id_event=${event.id_event}&id_tiket=${t.id_tiket}" class="btn btn-primary btn-sm rounded-pill px-4">Pilih</a>`
                        }
                    </div>
                `;
            });
            tiketHtml += '</div>';
        } else {
            tiketHtml = '<div class="alert alert-warning">Maaf, tiket belum tersedia untuk event ini.</div>';
        }

        modalBody.innerHTML = tiketHtml;
        new bootstrap.Modal(document.getElementById('eventModal')).show();
    }

    // FUNGSI UNTUK MENYALIN KODE VOUCHER KE CLIPBOARD
    function copyVoucher(kode) {
        navigator.clipboard.writeText(kode);
        alert("Kode voucher berhasil disalin: " + kode);
    }
</script>