<?php

// CEK LOGIN
if (!isset($_SESSION['user']['id'])) {
    echo "<div class='alert alert-info shadow-sm'>Silakan login untuk melihat riwayat tiket Anda.</div>";
    return;
}

// ID USER DARI SESSION
$id_user = (int)$_SESSION['user']['id'];

// PROSES CANCEL TIKET
if (isset($_POST['submit_cancel'])) {
    $id = (int)$_POST['id_transaksi'];
    
    // CEK SESSION USER DAN VALIDASI KEPEMILIKAN ORDER
    if (!isset($_SESSION['user']['id'])) {
        echo "<script>alert('Sesi habis, silakan login kembali.'); window.location='login.php';</script>";
        exit;
    }
    $id_user = (int)$_SESSION['user']['id'];

    // CEK STATUS ORDER DAN KEPEMILIKAN USER DENGAN PREPARED STATEMENT UNTUK MENCEGAH SQL INJECTION
    $stmt = mysqli_prepare($conn, "SELECT id_order FROM orders WHERE id_order=? AND id_user=? AND status='pending'");
    mysqli_stmt_bind_param($stmt, "ii", $id, $id_user);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        mysqli_begin_transaction($conn);

        try {
            // AMBIL DETAIL ORDER UNTUK MENDAPATKAN ID TIKET DAN QTY YANG DIBELI
            $query_detail = mysqli_query($conn, "SELECT id_tiket, qty FROM order_detail WHERE id_order=$id");
            
            while ($item = mysqli_fetch_assoc($query_detail)) {
                $id_tiket = $item['id_tiket'];
                $qty = $item['qty'];
                
                // KEMBALIKAN STOK TIKET
                mysqli_query($conn, "UPDATE tiket SET kuota = kuota + $qty WHERE id_tiket = $id_tiket");
            }

            // UPDATE STATUS ORDER MENJADI 'cancel'
            mysqli_query($conn, "UPDATE orders SET status='cancel' WHERE id_order=$id");

            mysqli_commit($conn);
            echo "<script>alert('Pesanan berhasil dibatalkan.'); window.location='index.php?page=riwayat';</script>";

        } catch (Exception $e) {
            // JIKA TERJADI ERROR, ROLLBACK TRANSAKSI
            mysqli_rollback($conn);
            echo "<script>alert('Gagal membatalkan pesanan: " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('Pesanan tidak valid atau sudah dibatalkan sebelumnya.');</script>";
    }
    exit;
}

// AUTO CANCEL ORDER > 24 JAM
$auto_expire = mysqli_query($conn, "
    SELECT id_order 
    FROM orders 
    WHERE status = 'pending'
    AND tanggal_order <= NOW() - INTERVAL 24 HOUR
");

// JIKA QUERY GAGAL, TAMPILKAN ERROR
if (!$auto_expire) {
    die("Query error: " . mysqli_error($conn));
}

// PROSES AUTO CANCEL DAN KEMBALIKAN STOK UNTUK SETIAP ORDER YANG EXPIRED
while ($row = mysqli_fetch_assoc($auto_expire)) {
    $id_order = (int)$row['id_order'];

    mysqli_begin_transaction($conn);

    try {
        // AMBIL DETAIL ORDER UNTUK MENDAPATKAN ID TIKET DAN QTY YANG DIBELI
        $detail = mysqli_query($conn, "
            SELECT id_tiket, qty 
            FROM order_detail 
            WHERE id_order = $id_order
        ");

        // KEMBALIKAN STOK TIKET UNTUK SETIAP ITEM YANG DIBELI
        while ($d = mysqli_fetch_assoc($detail)) {
            $id_tiket = (int)$d['id_tiket'];
            $qty = (int)$d['qty'];

            // KEMBALIKAN STOK TIKET
            mysqli_query($conn, "
                UPDATE tiket 
                SET kuota = kuota + $qty 
                WHERE id_tiket = $id_tiket
            ");
        }

        // UPDATE STATUS ORDER MENJADI 'cancel'
        mysqli_query($conn, "
            UPDATE orders 
            SET status = 'cancelled' 
            WHERE id_order = $id_order
        ");

        mysqli_commit($conn);

    } catch (Exception $e) {
        mysqli_rollback($conn);
    }
}

// QUERY UNTUK MENGAMBIL SEMUA ORDER 30 HARI TERAKHIR
$query = "SELECT 
            o.id_order, o.tanggal_order, o.total, o.status,
            od.id_detail, 
            od.qty, t.nama_tiket, t.harga,
            e.nama_event, e.tanggal, v.nama_venue
          FROM orders o
          JOIN order_detail od ON o.id_order = od.id_order
          JOIN tiket t ON od.id_tiket = t.id_tiket
          JOIN event e ON t.id_event = e.id_event
          JOIN venue v ON e.id_venue = v.id_venue
          WHERE o.id_user = $id_user
          AND o.status IN ('pending', 'paid')
          AND o.tanggal_order >= NOW() - INTERVAL 30 DAY
          ORDER BY o.tanggal_order DESC";

$result = mysqli_query($conn, $query);

// GROUPING DATA ORDER DAN DETAILNYA KE DALAM ARRAY MULTIDIMENSI UNTUK MEMUDAHKAN TAMPILAN DI FRONTEND
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[$row['id_order']]['info'] = [
        'tanggal' => $row['tanggal_order'],
        'total'   => $row['total'],
        'status'  => $row['status']
    ];
    $orders[$row['id_order']]['items'][] = $row;
}
?>


<style>
/* Custom styles untuk modal e-tiket */
.ticket-card {
    border-radius: 15px;
    border: none;
    transition: transform 0.2s ease;
}

.ticket-card:hover {
    transform: translateY(-2px);
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
}

.status-pending {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-paid {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.status-cancelled {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.event-info-box {
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
}

.ticket-code-badge {
    border: 1px solid #dee2e6 !important;
    transition: all 0.2s ease;
}

.ticket-code-badge:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.qr-box {
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 2px solid #e9ecef;
}

.ticket-quantity {
    font-size: 1.1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.event-ticket-item {
    transition: all 0.2s ease;
}

.event-ticket-item:hover {
    background-color: rgba(255,255,255,0.5);
    border-radius: 8px;
    padding: 8px;
    margin: -8px;
}

.qr-codes-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    max-width: 100%;
}

.qr-item {
    text-align: center;
    min-width: 180px;
}

.qr-item .qr-box {
    margin-bottom: 8px;
}

.qr-item code {
    font-size: 0.85rem;
    word-break: break-all;
}

/* Responsive untuk multiple QR codes */
@media (max-width: 768px) {
    .qr-codes-container {
        gap: 15px;
    }

    .qr-item {
        min-width: 150px;
    }

    .qr-item .qr-box img {
        width: 120px !important;
        height: 120px !important;
    }
}

/* Print styles */
@media print {
    .modal-header,
    .modal-footer,
    .btn-close {
        display: none !important;
    }

    .modal-content {
        border: none !important;
        box-shadow: none !important;
    }

    .modal-body {
        padding: 20px !important;
    }

    body * {
        visibility: hidden;
    }

    .modal-content,
    .modal-content * {
        visibility: visible;
    }

    .modal-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: none;
    }

    .qr-codes-container {
        flex-direction: row !important;
        justify-content: space-around !important;
        gap: 10px !important;
    }

    .qr-item {
        page-break-inside: avoid;
        margin-bottom: 15px !important;
    }

    .qr-item .qr-box img {
        width: 120px !important;
        height: 120px !important;
    }
}
</style>


<div class="pagetitle">
    <h1>Tiket Saya</h1>
    <p class="text-muted">Kelola semua pesanan dan tiket event Anda di sini.</p>
</div>

<section class="section">
    <?php if (empty($orders)): ?>
        <div class="col-12 text-center py-5">
            <div class="card border-0 shadow-sm p-5" style="border-radius: 20px;">
                <img src="assets/img/empty-ticket.svg" alt="Empty" style="width: 120px; opacity: 0.5;" class="mx-auto mb-3">
                <h4 class="fw-bold" style="color: #1d1145;">Belum Ada Tiket</h4>
                <p class="text-muted">Sepertinya Anda belum memiliki rencana seru. <br>Ayo cari event menarik untuk akhir pekan Anda!</p>
                <a href="index.php?page=event" class="btn btn-primary px-4 py-2 mt-2" style="background-color: #1d1145; border: none; border-radius: 10px;">
                    Cari Event Sekarang
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- LOOPING DATA ORDER DAN TAMPILKAN -->
            <?php foreach ($orders as $id_order => $data): 
                $status = $data['info']['status'];
                $statusClass = "status-$status";
                $statusLabel = ($status == 'pending' ? 'Menunggu Pembayaran' : ($status == 'paid' ? 'Lunas' : 'Dibatalkan'));
            ?>
                <!-- CARD ORDER -->
                <div class="col-12 mb-4">
                    <div class="card ticket-card shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                                <div>
                                    <span class="text-muted small d-block">ID PESANAN</span>
                                    <h5 class="order-id fw-bold mb-0">#<?= ($id_order) ?></h5>
                                </div>
                                <div class="text-end">
                                    <span class="badge status-badge <?= $statusClass ?> mb-1"><?= $statusLabel ?></span>
                                    <span class="text-muted d-block small"><?= date('d M Y, H:i', strtotime($data['info']['tanggal'])) ?></span>
                                </div>
                            </div>

                            <!-- DETAIL ORDER -->
                            <div class="row">
                                <div class="col-lg-8">
                                    <?php foreach ($data['items'] as $item): ?>
                                        <div class="event-info-box shadow-sm mb-3">
                                            <div class="d-flex align-items-center p-3">
                                                <div class="me-3 text-center border-end pe-3">
                                                    <h4 class="mb-0 fw-bold text-primary"><?= $item['qty'] ?></h4>
                                                    <small class="text-muted">Tiket</small>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['nama_event']) ?></h6>
                                                    <div class="small text-muted">
                                                        <span class="me-3"><i class="bi bi-tag me-1"></i><?= $item['nama_tiket'] ?></span>
                                                        <span><i class="bi bi-geo-alt me-1"></i><?= $item['nama_venue'] ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if ($status == 'paid'): ?>
                                                <div class="bg-white mx-3 mb-3 p-3 rounded">
                                                    <small class="text-muted d-block mb-2">Kode Tiket:</small>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <?php 
                                                        $id_det_m = $item['id_detail'];
                                                        $q_att_m = mysqli_query($conn, "SELECT kode_tiket FROM attendee WHERE id_detail = $id_det_m");
                                                        while($att_m = mysqli_fetch_assoc($q_att_m)):
                                                        ?>
                                                            <div class="ticket-code-badge bg-light border px-2 py-1 rounded-pill small">
                                                                <code class="fw-bold text-primary"><?= $att_m['kode_tiket'] ?></code>
                                                            </div>
                                                        <?php endwhile; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="col-lg-4 mt-3 mt-lg-0 border-start-lg ps-lg-4 text-center text-lg-start">
                                    <span class="text-muted small">Total Pembayaran</span>
                                    <h3 class="fw-bold text-dark mb-3">Rp <?= number_format($data['info']['total'], 0, ',', '.') ?></h3>
                                    
                                    <?php if ($status == 'pending'): ?>
                                        <a href="index.php?page=payment&id_order=<?= $id_order ?>" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm mb-2">
                                            <i class="bi bi-wallet2 me-2"></i>Bayar Sekarang
                                        </a>
                                        <button class="btn btn-outline-danger w-100 rounded-pill fw-bold" 
                                                onclick="cancelTiket(<?= $id_order; ?>)">
                                            Batalkan Pesanan
                                        </button>

                                    <?php elseif ($status == 'paid'): ?>
                                        <button class="btn btn-outline-success w-100 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#modalTiket<?= $id_order ?>">
                                            <i class="bi bi-qr-code me-2"></i>Lihat Kode E-Tiket
                                        </button>
                                        <p class="text-muted small mt-2 text-center">Pesanan lunas tidak dapat dibatalkan.</p>

                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100 rounded-pill fw-bold" disabled>
                                            Sudah Dibatalkan
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- MODAL E-TICKET UNTUK SETIAP ORDER YANG STATUSNYA 'PAID' -->
                <div class="modal fade" id="modalTiket<?= $id_order ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                            <div class="modal-header text-white" style="border-radius: 20px 20px 0 0;">
                                <h5 class="modal-title fw-bold mb-0">
                                    <i class="bi bi-ticket-perforated-fill me-2"></i>E-Ticket Event
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body p-4">
                                <!-- Header Info -->
                                <div class="text-center mb-4">
                                    <h5 class="fw-bold text-primary mb-3">E-Ticket Resmi</h5>
                                    <p class="text-muted small mb-4">Order #<?= str_pad($id_order, 6, '0', STR_PAD_LEFT) ?> • Tunjukkan QR Code ke petugas untuk Check-in</p>

                                    <!-- QR Codes untuk semua kode tiket -->
                                    <div class="qr-codes-container">
                                        <?php
                                        $ticket_codes = [];
                                        foreach ($data['items'] as $item) {
                                            $id_det_qr = $item['id_detail'];
                                            $q_att_qr = mysqli_query($conn, "SELECT kode_tiket FROM attendee WHERE id_detail = $id_det_qr");
                                            while($att_qr = mysqli_fetch_assoc($q_att_qr)) {
                                                $ticket_codes[] = $att_qr['kode_tiket'];
                                            }
                                        }

                                        if (!empty($ticket_codes)):
                                            foreach ($ticket_codes as $index => $ticket_code):
                                        ?>
                                            <div class="qr-item mb-3">
                                                <div class="qr-box p-3 bg-white rounded-3 shadow-sm d-inline-block">
                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($ticket_code) ?>" 
                                                         alt="QR Code" class="img-fluid">
                                                </div>
                                                <div class="mt-2">
                                                    <small class="text-muted d-block">Kode Tiket</small>
                                                    <code class="fw-bold text-primary bg-light px-2 py-1 rounded"><?= $ticket_code ?></code>
                                                </div>
                                            </div>
                                        <?php
                                            endforeach;
                                        else:
                                        ?>
                                            <div class="qr-box mb-3 p-3 bg-white rounded-3 shadow-sm d-inline-block">
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=ORDER-<?= $id_order ?>" 
                                                     alt="QR Code" class="img-fluid">
                                            </div>
                                            <p class="text-muted small">QR Code Order (Belum ada kode tiket)</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Event Details -->
                                <div class="bg-light p-3 rounded-3 mb-3">
                                    <?php foreach ($data['items'] as $index => $item): ?>
                                        <div class="event-ticket-item <?= $index > 0 ? 'border-top pt-3 mt-3' : '' ?>">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <h6 class="fw-bold mb-1 text-primary"><?= htmlspecialchars($item['nama_event']) ?></h6>
                                                    <p class="mb-1 small text-muted">
                                                        <i class="bi bi-tag me-1"></i><?= htmlspecialchars($item['nama_tiket']) ?> 
                                                        • <i class="bi bi-calendar-event me-1"></i><?= date('d M Y H:i', strtotime($item['tanggal'])) ?>
                                                        • <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($item['nama_venue']) ?>
                                                    </p>
                                                    <div class="ticket-codes">
                                                        <small class="text-muted d-block mb-1">Kode Tiket:</small>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <?php 
                                                            $id_det_m = $item['id_detail'];
                                                            $q_att_m = mysqli_query($conn, "SELECT kode_tiket, status_checkin FROM attendee WHERE id_detail = $id_det_m");
                                                            while($att_m = mysqli_fetch_assoc($q_att_m)):
                                                            ?>
                                                                <div class="ticket-code-badge bg-white border px-2 py-1 rounded-pill small d-flex align-items-center">
                                                                    <code class="fw-bold text-primary me-2"><?= $att_m['kode_tiket'] ?></code>
                                                                    <span class="badge <?= $att_m['status_checkin'] == 'sudah' ? 'bg-success' : 'bg-secondary' ?>" style="font-size: 0.6rem;">
                                                                        <i class="bi bi-<?= $att_m['status_checkin'] == 'sudah' ? 'check-circle' : 'circle' ?>"></i>
                                                                    </span>
                                                                </div>
                                                            <?php endwhile; ?>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Footer Info -->
                                <div class="text-center">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Total Pembayaran</small>
                                            <span class="fw-bold text-success">Rp <?= number_format($data['info']['total'], 0, ',', '.') ?></span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Tanggal Order</small>
                                            <span class="fw-bold"><?= date('d M Y', strtotime($data['info']['tanggal'])) ?></span>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="alert alert-info py-2 small mb-0">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>Informasi:</strong> Tiket berlaku untuk 1x penggunaan. Pastikan datang tepat waktu sesuai jadwal event.
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-light" style="border-radius: 0 0 20px 20px;">
                                <div class="w-100 d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary flex-fill rounded-pill" data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle me-1"></i>Tutup
                                    </button>
                                    <button type="button" class="btn btn-primary flex-fill rounded-pill" onclick="printTicket(<?= $id_order ?>)">
                                        <i class="bi bi-printer me-1"></i>Cetak E-Ticket
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL CANCEL TIKET -->
                <div class="modal fade" id="modalCancel" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 15px;">
                            <form method="POST">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title fw-bold">Konfirmasi Pembatalan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                                    <p class="mt-3">Apakah Anda yakin ingin membatalkan pesanan <strong>#<?= str_pad($id_order, 6, '0', STR_PAD_LEFT) ?></strong>?</p>
                                    <input type="hidden" name="id_transaksi" id="id_transaksi">
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" name="submit_cancel" class="btn btn-danger rounded-pill px-4"> Ya, Batalkan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
    // FUNGSI UNTUK MEMUNCULKAN MODAL KONFIRMASI CANCEL
   function cancelTiket(id) {
    document.getElementById('id_transaksi').value = id;
    let modal = new bootstrap.Modal(document.getElementById('modalCancel'));
    modal.show();
   }

   function printTicket(orderId) {
    // Buka modal e-ticket
    const modal = new bootstrap.Modal(document.getElementById('modalTiket' + orderId));
    modal.show();
    
    // Tunggu sebentar lalu print
    setTimeout(() => {
        window.print();
    }, 500);
   }
</script>