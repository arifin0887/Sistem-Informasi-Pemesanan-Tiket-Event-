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
            t.kategori_tiket,
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

<style>
    .card-ticket {
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid #eee;
    }

    .card-ticket:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }

    .ticket-header {
        background: linear-gradient(135deg, #1D1145, #2a1a5e);
        color: #fff;
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .order-id {
        font-size: 0.8rem;
        opacity: 0.8;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .badge-success {
        background: #28a745;
    }

    .badge-warning {
        background: #ffc107;
        color: #000;
    }

    .badge-danger {
        background: #dc3545;
    }

    .ticket-body {
        padding: 18px;
    }

    .event-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1D1145;
        margin-bottom: 4px;
    }

    .ticket-type {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 12px;
    }

    .ticket-info {
        font-size: 0.8rem;
        color: #555;
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 15px;
    }

    .ticket-info i {
        color: #0DB5BB;
        margin-right: 6px;
    }

    .ticket-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .qty-badge {
        background: #f1f3f5;
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .btn-warning {
        background: #ff9f1c;
        border: none;
    }

    .btn-primary {
        background: #0DB5BB;
        border: none;
    }
</style>

<section class="section mt-4">
    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card-ticket shadow-sm">

                        <!-- HEADER -->
                        <div class="ticket-header">
                            <span class="order-id">Order #<?= $row['id_order']; ?></span>

                            <?php 
                                $s = $row['status'];
                                $badge_class = ($s == 'paid' || $s == 'success') 
                                    ? 'badge-success' 
                                    : ($s == 'pending' ? 'badge-warning' : 'badge-danger');
                            ?>
                            <span class="status-badge <?= $badge_class; ?>">
                                <?= ucfirst($s); ?>
                            </span>
                        </div>

                        <!-- BODY -->
                        <div class="ticket-body">

                            <h5 class="event-title">
                                <?= htmlspecialchars($row['nama_event']); ?>
                            </h5>

                            <p class="ticket-type">
                                <?= htmlspecialchars($row['kategori_tiket']); ?> - <?= htmlspecialchars($row['nama_tiket']); ?>
                            </p>

                            <div class="ticket-info">
                                <div>
                                    <i class="bi bi-calendar-event"></i>
                                    <?= date('d M Y', strtotime($row['tanggal_event'])); ?>
                                </div>
                                <div>
                                    <i class="bi bi-clock"></i>
                                    <?= date('H:i', strtotime($row['tanggal_event'])); ?> WIB
                                </div>
                                <div>
                                    <i class="bi bi-geo-alt"></i>
                                    <?= htmlspecialchars($row['nama_venue']); ?>
                                </div>
                            </div>

                            <div class="ticket-footer">
                                <span class="qty-badge">
                                    <?= $row['qty']; ?> Tiket
                                </span>

                                <?php if ($row['status'] == 'pending'): ?>
                                    <a href="index.php?page=payment&id_order=<?= $row['id_order']; ?>" 
                                        class="btn btn-warning btn-sm fw-bold">
                                        Bayar
                                    </a>
                                <?php else: ?>
                                    <a href="index.php?page=e-tiket&id=<?= $row['id_order']; ?>" 
                                        class="btn btn-primary btn-sm">
                                        E-Tiket
                                    </a>
                                <?php endif; ?>
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