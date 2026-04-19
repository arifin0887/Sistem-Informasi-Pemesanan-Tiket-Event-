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
    
    // Pastikan session user ID benar-benar ada
    if (!isset($_SESSION['user']['id'])) {
        echo "<script>alert('Sesi habis, silakan login kembali.'); window.location='login.php';</script>";
        exit;
    }
    $id_user = (int)$_SESSION['user']['id'];

    // 1. Cek kepemilikan dan pastikan status saat ini adalah 'pending'
    $stmt = mysqli_prepare($conn, "SELECT id_order FROM orders WHERE id_order=? AND id_user=? AND status='pending'");
    mysqli_stmt_bind_param($stmt, "ii", $id, $id_user);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        mysqli_begin_transaction($conn);

        try {
            // 2. Ambil detail tiket
            $query_detail = mysqli_query($conn, "SELECT id_tiket, qty FROM order_detail WHERE id_order=$id");
            
            while ($item = mysqli_fetch_assoc($query_detail)) {
                $id_tiket = $item['id_tiket'];
                $qty = $item['qty'];
                
                // 3. Kembalikan stok
                mysqli_query($conn, "UPDATE tiket SET kuota = kuota + $qty WHERE id_tiket = $id_tiket");
            }

            // 4. Update status order
            mysqli_query($conn, "UPDATE orders SET status='cancel' WHERE id_order=$id");

            mysqli_commit($conn);
            echo "<script>alert('Pesanan berhasil dibatalkan.'); window.location='index.php?page=riwayat';</script>";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            // Debug: Uncomment baris di bawah ini jika ingin melihat error aslinya
            // die($e->getMessage()); 
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

if (!$auto_expire) {
    die("Query error: " . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($auto_expire)) {
    $id_order = (int)$row['id_order'];

    mysqli_begin_transaction($conn);

    try {
        // Ambil detail order
        $detail = mysqli_query($conn, "
            SELECT id_tiket, qty 
            FROM order_detail 
            WHERE id_order = $id_order
        ");

        while ($d = mysqli_fetch_assoc($detail)) {
            $id_tiket = (int)$d['id_tiket'];
            $qty = (int)$d['qty'];

            // Kembalikan stok
            mysqli_query($conn, "
                UPDATE tiket 
                SET kuota = kuota + $qty 
                WHERE id_tiket = $id_tiket
            ");
        }

        // Update status
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

// QUERY UNTUK MENGAMBIL SEMUA ORDER YANG DILAKUKAN OLEH USER INI BESERTA DETAILNYA
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
                <div class="col-12 mb-4">
                    <div class="card ticket-card shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                                <div>
                                    <span class="text-muted small d-block">ID PESANAN</span>
                                    <h5 class="order-id fw-bold mb-0">#<?= str_pad($id_order, 6, '0', STR_PAD_LEFT) ?></h5>
                                </div>
                                <div class="text-end">
                                    <span class="badge status-badge <?= $statusClass ?> mb-1"><?= $statusLabel ?></span>
                                    <span class="text-muted d-block small"><?= date('d M Y, H:i', strtotime($data['info']['tanggal'])) ?></span>
                                </div>
                            </div>

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
                                                <div class="bg-white mx-3 mb-3 p-3 rounded border">
                                                    <p class="small fw-bold text-muted mb-2"><i class="bi bi-qr-code-scan me-1"></i> Kode Attendee:</p>
                                                    <div class="row g-2">
                                                        <?php 
                                                            $id_det = $item['id_detail'];
                                                            $q_att = mysqli_query($conn, "SELECT kode_tiket, status_checkin FROM attendee WHERE id_detail = $id_det");
                                                            while($att = mysqli_fetch_assoc($q_att)):
                                                        ?>
                                                            <div class="col-md-6">
                                                                <div class="d-flex justify-content-between align-items-center border p-2 rounded bg-light">
                                                                    <code class="fw-bold text-primary"><?= $att['kode_tiket'] ?></code>
                                                                    <span class="badge <?= $att['status_checkin'] == 'sudah' ? 'bg-success' : 'bg-secondary' ?>" style="font-size: 0.6rem;">
                                                                        <?= ucfirst($att['status_checkin']) ?>
                                                                    </span>
                                                                </div>
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
                                            <i class="bi bi-qr-code me-2"></i>Lihat E-Ticket
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
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: 25px; border: none;">
                            <div class="modal-header border-0 pb-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4 text-center">
                                <div class="qr-box mb-3 bg-white">
                                    <!-- GENERATE QR CODE DENGAN DATA ORDER ID (BISA DIGANTI DENGAN KODE TIKET UNIK JIKA INGIN LEBIH RINCI) -->
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=ORDER-<?= $id_order ?>" 
                                         alt="QR Code" class="img-fluid">
                                </div>
                                <h5 class="fw-bold mb-1">E-Ticket Resmi</h5>
                                <p class="text-muted small mb-4">Tunjukkan QR Code ini ke petugas untuk Check-in.</p>
                                
                                <div class="text-start bg-light p-3 rounded-4">
                                    <?php foreach ($data['items'] as $item): ?>
                                        <div class="mb-3 border-bottom pb-2">
                                            <label class="small text-muted d-block">Event & Tiket</label>
                                            <span class="fw-bold d-block text-navy"><?= htmlspecialchars($item['nama_event']) ?></span>
                                            <span class="badge bg-primary"><?= $item['nama_tiket'] ?> (<?= $item['qty'] ?>x)</span>
                                            
                                            <div class="mt-2">
                                                <label class="small text-muted d-block mb-1">Kode Tiket:</label>
                                                <?php 
                                                // AMBIL KODE TIKET UNTUK DETAIL
                                                    $id_det_m = $item['id_detail'];
                                                    $q_att_m = mysqli_query($conn, "SELECT kode_tiket FROM attendee WHERE id_detail = $id_det_m");
                                                    while($att_m = mysqli_fetch_assoc($q_att_m)):
                                                ?>
                                                    <div class="bg-white border p-1 px-2 rounded mb-1 small d-flex justify-content-between">
                                                        <code class="fw-bold"><?= $att_m['kode_tiket'] ?></code>
                                                        <i class="bi bi-check2-circle text-success"></i>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="text-center mt-2 small text-muted">
                                        <i class="bi bi-info-circle me-1"></i> Tiket berlaku untuk 1x penggunaan.
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light w-100 rounded-pill fw-bold" onclick="window.print()">
                                    <i class="bi bi-printer me-2"></i>Cetak / Simpan PDF
                                </button>
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
   function cancelTiket(id) {
    document.getElementById('id_transaksi').value = id;
    let modal = new bootstrap.Modal(document.getElementById('modalCancel'));
    modal.show();
}
</script>