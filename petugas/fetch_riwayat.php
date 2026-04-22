<?php
if (!isset($conn)) {
    include '../koneksi.php'; 
}

$filter_event = isset($_GET['id_event']) ? $_GET['id_event'] : '';

// QUERY AMBIL DATA RIWAYAT 
$query = "
    SELECT a.kode_tiket, a.waktu_checkin, t.nama_tiket, u.nama AS nama_pembeli, e.nama_event
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    JOIN orders o ON od.id_order = o.id_order
    JOIN users u ON o.id_user = u.id_user
    WHERE a.status_checkin = 'sudah'
";

if ($filter_event != '') {
    $query .= " AND e.id_event = '" . mysqli_real_escape_string($conn, $filter_event) . "'";
}
$query .= " ORDER BY a.waktu_checkin DESC";
$result = mysqli_query($conn, $query);
?>

<div class="card history-card">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Peserta</th>
                        <th>Event & Tiket</th>
                        <th>Kode Tiket</th>
                        <th>Waktu Validasi</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td class="text-center text-muted small"><?= $no++; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar" style="width:35px; height:35px; background:#f0f2f5; display:flex; align-items:center; justify-content:center; border-radius:8px; font-weight:bold; margin-right:10px;">
                                        <?= strtoupper(substr($row['nama_pembeli'], 0, 1)) ?>
                                    </div>
                                    <div class="fw-bold"><?= htmlspecialchars($row['nama_pembeli']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="text-primary fw-bold small"><?= htmlspecialchars($row['nama_event']); ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($row['nama_tiket']); ?></div>
                            </td>
                            <td><code style="background:#fff8e1; color:#f57f17; padding:2px 5px;"><?= $row['kode_tiket'] ?></code></td>
                            <td><?= date('H:i:s', strtotime($row['waktu_checkin'])) ?></td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success">Checked-in</span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">Tidak ada data.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>