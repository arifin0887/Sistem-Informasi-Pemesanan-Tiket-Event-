<?php

// QUERY RIWAYAT CHECK-IN UNTUK MENAMPILKAN DATA CHECK-IN YANG SUDAH DILAKUKAN, DIURUTKAN DARI YANG TERBARU
$query_riwayat = mysqli_query($conn, "
    SELECT * FROM attendee 
    WHERE status_checkin = 'sudah' 
    ORDER BY waktu_checkin DESC
");

// QUERY DATA RIWAYAT USER CHECK-IN DENGAN JOIN KE TABEL LAIN UNTUK MENDAPATKAN NAMA PEMBELI DAN NAMA TIKET
$query_riwayat = mysqli_query($conn, "
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
");

?>

<!-- <div class="pagetitle">
    <h1>Riwayat Check-in</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Riwayat Check-in</li>
        </ol>
    </nav>
</div> -->

<div class="table-responsive">
    <table class="table table-hover datatable">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Waktu</th>
                <th>Pembeli / Pemegang Tiket</th>
                <th>Kode Tiket</th>
                <th>Tipe Tiket</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($row = mysqli_fetch_assoc($query_riwayat)) : 
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= date('H:i', strtotime($row['waktu_checkin'])); ?></td>
                <td>
                    <strong><?= htmlspecialchars($row['nama_pembeli']); ?></strong>
                </td>
                <td><span class="badge bg-primary"><?= $row['kode_tiket']; ?></span></td>
                <td><?= htmlspecialchars($row['nama_tiket']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<style>
    .bg-secondary-light { background-color: #f8f9fa; }
    .table thead th { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge { padding: 0.5em 0.8em; }
</style>