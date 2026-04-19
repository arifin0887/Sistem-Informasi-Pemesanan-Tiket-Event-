<?php

// CEK LOGIN
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    echo "<div class='alert alert-danger shadow-sm border-0'>
            <i class='bi bi-exclamation-triangle me-2'></i>
            Silakan login untuk melihat tiket Anda.
          </div>";
    exit;
}

$id_user = (int)$_SESSION['user']['id'];

// QUERY RIWAYAT TIKET (FILTER EVENT BELUM LEWAT)
$query = "SELECT 
            o.id_order, 
            o.tanggal_order, 
            o.total, 
            o.status, 
            od.qty, 
            t.nama_tiket, 
            e.nama_event, 
            e.tanggal AS tanggal_event,
            v.nama_venue
          FROM orders o
          JOIN order_detail od ON o.id_order = od.id_order
          JOIN tiket t ON od.id_tiket = t.id_tiket
          JOIN event e ON t.id_event = e.id_event
          JOIN venue v ON e.id_venue = v.id_venue
          WHERE o.id_user = ?
          AND e.tanggal >= NOW() 
          ORDER BY o.tanggal_order ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="pagetitle">
    <h1 style="color: #1d1145;">Tiket Saya</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Koleksi Tiket</li>
        </ol>
    </nav>
</div>

<section class="section mt-4">
    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-xl-4 col-md-6">
                    <div class="card-ticket shadow-sm hover-up">
                        <div class="ticket-header d-flex justify-content-between align-items-center">
                            <span class="small fw-bold opacity-75">ID #<?= $row['id_order']; ?></span>
                            <?php 
                                $s = $row['status'];
                                $badge_class = ($s == 'paid' || $s == 'success') ? 'bg-success' : ($s == 'pending' ? 'bg-warning text-dark' : 'bg-danger');
                            ?>
                            <span class="badge <?= $badge_class; ?> status-badge"><?= ucfirst($s); ?></span>
                        </div>

                        <div class="ticket-body">
                            <div class="mb-3">
                                <small class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem;">Event</small>
                                <div class="event-title text-truncate"><?= htmlspecialchars($row['nama_event']); ?></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Tanggal</small>
                                    <span class="fw-bold small"><i class="bi bi-calendar-event text-pink me-1"></i><?= date('d M Y', strtotime($row['tanggal_event'])); ?></span>
                                </div>
                                <div class="col-6 ps-3">
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">Waktu</small>
                                    <span class="fw-bold small"><i class="bi bi-clock text-pink me-1"></i><?= date('H:i', strtotime($row['tanggal_event'])); ?> WIB</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block" style="font-size: 0.65rem;">Lokasi</small>
                                <span class="small fw-semibold"><i class="bi bi-geo-alt text-pink me-1"></i><?= htmlspecialchars($row['nama_venue']); ?></span>
                            </div>

                            <div class="dashed-line"></div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-light text-dark border fw-normal" style="font-size: 0.75rem;">
                                        <?= $row['qty']; ?>x <?= htmlspecialchars($row['nama_tiket']); ?>
                                    </span>
                                </div>
                                <div class="text-end">
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <a href="index.php?page=payment&id=<?= $row['id_order']; ?>" class="btn btn-warning btn-sm fw-bold px-3">
                                            BAYAR SEKARANG
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?page=e-tiket&id=<?= $row['id_order']; ?>" class="btn btn-outline-primary btn-sm px-3">
                                            <i class="bi bi-qr-code me-1"></i> E-TIKET
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
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
    <?php endif; ?>
</section>