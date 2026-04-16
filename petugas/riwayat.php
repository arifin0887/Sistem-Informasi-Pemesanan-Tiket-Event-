<?php

// QUERY RIWAYAT CHECK-IN (SUDAH DIPERBAIKI & DIOPTIMALKAN)
$query = "
    SELECT 
        a.kode_tiket, 
        a.waktu_checkin, 
        a.status_checkin,
        t.nama_tiket,
        u.nama AS nama_pembeli
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN orders o ON od.id_order = o.id_order
    JOIN users u ON o.id_user = u.id_user
    WHERE a.status_checkin = 'sudah'
    ORDER BY a.waktu_checkin DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>

<div class="pagetitle mb-4">
    <h1>Riwayat Check-in</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Riwayat Check-in</li>
        </ol>
    </nav>
</div>

<div class="card history-card">
    <div class="card-body p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="text-muted small mb-0">Daftar peserta yang baru saja memvalidasi tiket</p>
            </div>
            <span class="badge bg-primary rounded-pill px-3">
                <?= mysqli_num_rows($result); ?> Total Data
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle border-0">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Peserta</th>
                        <th>Detail Tiket</th>
                        <th>Kode</th>
                        <th>Waktu Validasi</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : 
                        // Ambil inisial nama
                        $inisial = strtoupper(substr($row['nama_pembeli'], 0, 1));
                    ?>
                        <tr>
                            <td class="text-center text-muted small"><?= $no++; ?></td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar"><?= $inisial ?></div>
                                    <div>
                                        <div class="fw-bold mb-0"><?= htmlspecialchars($row['nama_pembeli']); ?></div>
                                        <small class="text-muted">Verified Guest</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold small"><?= htmlspecialchars($row['nama_tiket']); ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">Reguler Access</div>
                            </td>

                            <td>
                                <code class="ticket-code"><?= htmlspecialchars($row['kode_tiket']); ?></code>
                            </td>

                            <td>
                                <div class="checkin-time"><?= date('H:i:s', strtotime($row['waktu_checkin'])); ?></div>
                                <small class="text-muted small"><?= date('d M Y', strtotime($row['waktu_checkin'])); ?></small>
                            </td>

                            <td class="text-center">
                                <span class="badge-status">
                                    Checked-in
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox text-light d-block mb-2" style="font-size: 3rem;"></i>
                            <div class="text-muted">Belum ada aktivitas check-in hari ini</div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Card Styling */
    .history-card {
        border: none;
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03) !important;
    }

    /* Table Headers */
    .table thead th {
        background-color: #f8f9fa;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        color: #94a3b8;
        border: none;
        padding: 15px 20px;
    }

    /* Table Body */
    .table tbody td {
        padding: 18px 20px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .table-hover tbody tr:hover {
        background-color: #f8fbff;
        transition: all 0.2s ease;
    }

    /* Avatar atau Initial */
    .user-avatar {
        width: 38px;
        height: 38px;
        background: #eef2ff;
        color: #4f46e5;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 12px;
    }

    /* Custom Badge */
    .badge-status {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-status::before {
        content: '';
        width: 6px;
        height: 6px;
        background: #22c55e;
        border-radius: 50%;
    }

    .ticket-code {
        font-family: 'Monaco', monospace;
        background: #f1f5f9;
        color: #475569;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    /* Waktu Check-in */
    .checkin-time {
        font-size: 0.9rem;
        font-weight: 600;
    }
</style>