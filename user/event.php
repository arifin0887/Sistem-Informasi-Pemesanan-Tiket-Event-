<?php

// SEARCH PARAMETER
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

// BASE QUERY
$query = "SELECT 
            e.id_event, 
            e.nama_event, 
            e.tanggal, 
            e.id_venue,
            v.nama_venue, 
            v.alamat,
            t.id_tiket, 
            t.nama_tiket, 
            t.harga, 
            t.kuota
          FROM event e 
          JOIN venue v ON e.id_venue = v.id_venue 
          LEFT JOIN tiket t ON e.id_event = t.id_event
          WHERE e.tanggal >= NOW()"; 

// TAMBAHKAN SEARCH
if ($search != '') {
    $query .= " AND (
                    e.nama_event LIKE '%$search%' 
                    OR v.nama_venue LIKE '%$search%'
                )";
}

// SORTING
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

// AMBIL VOUCHER AKTIF
$query_voucher = mysqli_query($conn, "
    SELECT * FROM voucher 
    WHERE status='aktif' AND kuota > 0 
    ORDER BY id_voucher DESC 
    LIMIT 1
");

$v = mysqli_fetch_assoc($query_voucher);
?>

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

<style>
    .event-card {
        border-radius: 18px;
        height: 100%;
        transition: 0.3s;
    }

    .event-card:hover {
        transform: translateY(-5px);
    }

    .badge-status-custom {
        top: 15px;
        right: 15px;
        font-size: 0.7rem;
    }

    .event-title-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 2; 
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 42px; 
    }

    .price-tag {
        color: var(--event-accent);
        font-size: 1rem;
    }

    .event-date-badge {
        text-align: center;
        min-width: 50px;
    }

    .card-body {
        height: 100%;
    }
</style>

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
                <div class="col-xl-4 col-md-6 mb-4 d-flex">
                    <div class="card event-card shadow-sm border-0 w-100">
                        <div class="card-body d-flex flex-column p-4 position-relative">

                            <span class="badge rounded-pill <?= $isExpired ? 'bg-secondary' : 'bg-success' ?> position-absolute badge-status-custom">
                                <?= $isExpired ? 'Selesai' : 'Tersedia' ?>
                            </span>

                            <div class="d-flex align-items-start mb-3 pe-5">
                                <div class="event-date-badge me-3">
                                    <span class="small text-uppercase fw-bold text-muted"><?= date('M', strtotime($event['tanggal'])) ?></span>
                                    <span class="fs-3 fw-bold text-navy"><?= date('d', strtotime($event['tanggal'])) ?></span>
                                    <span class="small text-muted"><?= date('Y', strtotime($event['tanggal'])) ?></span>
                                </div>

                                <div class="flex-grow-1">
                                    <h6 class="fw-bold event-title-clamp mb-0"
                                        onclick='showDetail(<?= json_encode($event) ?>)'>
                                        <?= htmlspecialchars($event['nama_event']) ?>
                                    </h6>
                                </div>
                            </div>

                            <div class="text-muted small mb-3">
                                <p class="mb-1">
                                    <i class="bi bi-geo-alt-fill text-pink me-2"></i>
                                    <?= htmlspecialchars($event['nama_venue']) ?>
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-clock-fill text-pink me-2"></i>
                                    <?= date('H:i', strtotime($event['tanggal'])) ?> WIB
                                </p>
                            </div>

                            <!-- PUSH FOOTER KE BAWAH -->
                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Mulai dari</small>
                                    <span class="price-tag fw-bold">
                                        Rp <?= number_format($min_price, 0, ',', '.') ?>
                                    </span>
                                </div>

                                <button class="btn btn-book btn-sm px-4 rounded-pill fw-bold"
                                        onclick='showDetail(<?= json_encode($event) ?>)'>
                                    Beli
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
    async function showDetail(event) {
        const modalBody = document.getElementById('modalContent');

        // Loading UI
        modalBody.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Memeriksa ketersediaan tiket...</p>
            </div>
        `;

        try {
            const response = await fetch(`check_ticket_limit.php?id_event=${event.id_event}`);

            // 🔥 CEK RESPONSE
            if (!response.ok) {
                throw new Error("HTTP error " + response.status);
            }

            // 🔥 AMBIL TEXT DULU (ANTI ERROR JSON)
            const text = await response.text();
            console.log("DEBUG RESPONSE:", text);

            const data = JSON.parse(text);

            let tiketHtml = `
                <div class="text-center mb-4">
                    <h4 class="fw-bold mb-1" style="color: var(--navy);">${event.nama_event}</h4>
                    <p class="text-muted"><i class="bi bi-geo-alt me-1"></i> ${event.nama_venue}</p>
                    <hr>
                </div>

                <div class="p-3 border rounded-4 bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold">Konfirmasi Pemesanan</h6>
                            <p class="small text-muted mb-0">Kamu akan diarahkan ke halaman pemilihan kategori tiket.</p>
                        </div>
                        <i class="bi bi-ticket-perforated fs-1 text-pink opacity-50"></i>
                    </div>
                </div>
            `;

            if (event.tikets.length === 0) {
                tiketHtml += `
                    <div class="text-center py-4">
                        <i class="bi bi-emoji-frown fs-1 text-muted"></i>
                        <h5 class="mt-3">Tiket Belum Tersedia</h5>
                    </div>
                `;
            } else {
                const mainTicket = event.tikets[0];
                const isSoldOut = event.tikets.every(t => t.kuota <= 0);

                if (!data.allow) {
                    tiketHtml += `
                        <div class="text-center py-4 p-4">
                            <i class="bi bi-exclamation-triangle-fill fs-1 text-danger mb-3"></i>
                            <h5 class="fw-bold text-danger mb-2">${data.message}</h5>
                            <p class="text-muted small mb-0">Hubungi admin jika ada kendala.</p>
                        </div>

                        <div class="d-grid mt-4">
                            <button class="btn btn-secondary btn-lg rounded-pill fw-bold" data-bs-dismiss="modal">
                                Tutup
                            </button>
                        </div>
                    `;
                } else if (isSoldOut) {
                    tiketHtml += `
                        <div class="d-grid mt-4">
                            <button class="btn btn-danger btn-lg rounded-pill fw-bold" disabled>
                                Maaf, Tiket Habis
                            </button>
                        </div>
                    `;
                } else {
                    tiketHtml += `
                        <div class="d-grid mt-4">
                            <a href="index.php?page=buy&id_event=${event.id_event}&id_tiket=${mainTicket.id_tiket}" 
                            class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                            Pesan Sekarang <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    `;
                }

                tiketHtml += `
                    <p class="text-center mt-3 small text-muted">
                        Harga mulai dari 
                        <span class="text-pink fw-bold">
                            Rp ${parseInt(mainTicket.harga).toLocaleString('id-ID')}
                        </span>
                    </p>
                `;
            }

            modalBody.innerHTML = tiketHtml;

        } catch (error) {
            console.error("ERROR:", error);

            // 🔥 FIX RETRY BUTTON (PENTING)
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-wifi-off fs-1 text-muted"></i>
                    <h5 class="mt-3 text-danger">Koneksi bermasalah</h5>
                    <button class="btn btn-outline-primary mt-2" id="retryBtn">
                        Coba Lagi
                    </button>
                </div>
            `;

            // 🔥 EVENT LISTENER RETRY (AMAN)
            document.getElementById('retryBtn').onclick = function() {
                showDetail(event);
            };
        }

        new bootstrap.Modal(document.getElementById('eventModal')).show();
    }

    // FUNGSI UNTUK MENYALIN KODE VOUCHER KE CLIPBOARD
    function copyVoucher(kode) {
        navigator.clipboard.writeText(kode);
        alert("Kode voucher berhasil disalin: " + kode);
    }

    // Contoh logika di frontend
    fetch(`cek_limit.php?id_event=${idEvent}`)
    .then(res => res.json())
    .then(data => {
        if (!data.allow) {
            alert(data.message);
            btnBeli.disabled = true; // Matikan tombol beli
        } else {
            inputQty.max = data.sisa; // Batasi input jumlah maksimal
        }
    });
</script>