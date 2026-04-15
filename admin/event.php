<?php

$message = "";
$message_type = "";

// PROSES CREATE, UPDATE & DELETE
if (isset($_POST['submit'])) {
    $nama_event = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $id_venue = (int)$_POST['id_venue'];
    
    $tanggal_raw = $_POST['tanggal']; 
    $tanggal_fix = str_replace('T', ' ', $tanggal_raw); 
    
    // Format "YYYY-MM-DDTHH:MM" tanpa detik, tambahkan ":00"
    if (strlen($tanggal_fix) == 16) {
        $tanggal_fix .= ":00";
    }

    if (!empty($_POST['id_event'])) {
        // LOGIC UPDATE
        $id = (int)$_POST['id_event'];
        $query = "UPDATE event SET nama_event='$nama_event', tanggal='$tanggal_fix', id_venue='$id_venue' WHERE id_event=$id";
    } else {
        // LOGIC INSERT
        $query = "INSERT INTO event (nama_event, tanggal, id_venue) VALUES ('$nama_event', '$tanggal_fix', '$id_venue')";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Berhasil disimpan!'); window.location='index.php?page=event';</script>";
    }
}

// LOGIC DELETE
if (isset($_POST['delete'])) {
    $id_event = mysqli_real_escape_string($conn, $_POST['id_event']);
    $query = mysqli_query($conn, "DELETE FROM event WHERE id_event='$id_event'");
    $status = $query ? "deleted" : "failed";
    header("Location: index.php?page=event&status=$status");
    exit;
}

// HANDLING ALERT DARI URL (Post-Redirect-Get Pattern)
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added': $message = "Event berhasil ditambahkan!"; $message_type = "success"; break;
        case 'updated': $message = "Event berhasil diperbarui!"; $message_type = "success"; break;
        case 'deleted': $message = "Event berhasil dihapus!"; $message_type = "success"; break;
        case 'failed': $message = "Terjadi kesalahan database!"; $message_type = "danger"; break;
    }
}

// GET DATA UNTUK TAMPILAN
$events = mysqli_query($conn, "SELECT e.*, v.nama_venue FROM event e JOIN venue v ON e.id_venue = v.id_venue ORDER BY e.tanggal ASC");
$venues = mysqli_query($conn, "SELECT * FROM venue ORDER BY nama_venue ASC");
?>

<style>
    .card { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .card-title { color: #1d1145; font-weight: 700; }
    .btn-primary { background-color: #1d1145; border: none; }
    .btn-primary:hover { background-color: #e66c8a; }
    .table thead th { background-color: #f8f9fa; color: #1d1145; }
    .modal-header { background-color: #1d1145; color: #fff; border-radius: 12px 12px 0 0; }
    .modal-content { border-radius: 12px; border: none; }
    .event-date { font-weight: 600; color: #1d1145; }
    .venue-tag { font-size: 0.85rem; color: #6c757d; }
</style>

<div class="pagetitle">
    <h1>Manajemen Event</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Event</li>
        </ol>
    </nav>
</div>

 <!-- TAMPILKAN ALERT JIKA ADA PESAN DARI PROSES CRUD -->
<?php if ($message): ?>
    <div class="alert alert-<?= $message_type; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
        <?= $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Jadwal Event Mendatang</h5>
                        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#formModal" onclick="resetForm()">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Event
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Event</th>
                                    <th>Waktu Pelaksanaan</th>
                                    <th>Lokasi (Venue)</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($event = mysqli_fetch_assoc($events)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><strong><?= htmlspecialchars($event['nama_event']); ?></strong></td>
                                        <td>
                                            <div class="event-date">
                                                <i class="bi bi-calendar3 me-1 text-muted"></i> 
                                                <?= date('d M Y', strtotime($event['tanggal'])); ?>
                                            </div>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i> Pukul <?= date('H:i', strtotime($event['tanggal'])); ?> WIB</small>
                                        </td>
                                        <td>
                                            <span class="venue-tag"><i class="bi bi-geo-alt-fill me-1 text-danger"></i> <?= htmlspecialchars($event['nama_venue']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm text-white" onclick='editEvent(<?= json_encode($event, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus event ini? Semua tiket terkait mungkin akan terpengaruh.');">
                                                <input type="hidden" name="id_event" value="<?= $event['id_event']; ?>">
                                                <button type="submit" name="delete" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MODAL UNTUK TAMBAH & EDIT EVENT -->
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalLabel">Tambah Event Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter:invert(1)"></button>
            </div>
            <form method="POST" id="eventForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_event" id="id_event">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Event</label>
                        <input type="text" class="form-control" id="nama_event" name="nama_event" placeholder="Masukkan nama event..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal & Waktu</label>
                        <input type="datetime-local" class="form-control" id="tanggal" name="tanggal" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Lokasi Venue</label>
                        <select class="form-select" id="id_venue" name="id_venue" required>
                            <option value="">-- Pilih Lokasi --</option>
                            <?php mysqli_data_seek($venues, 0); // Reset pointer query venue ?>
                            <?php while($v = mysqli_fetch_assoc($venues)): ?>
                                <option value="<?= $v['id_venue']; ?>">
                                    <?= htmlspecialchars($v['nama_venue']); ?> (Kapasitas: <?= number_format($v['kapasitas']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit" class="btn btn-primary px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // FUNGSI UNTUK RESET FORM SAAT BUKA MODAL (UNTUK TAMBAH DATA)
    function resetForm() {
        document.getElementById('eventForm').reset();
        document.getElementById('id_event').value = '';
        document.getElementById('formModalLabel').innerText = 'Tambah Event Baru';
    }

    // FUNGSI UNTUK MENGISI FORM SAAT EDIT EVENT
    function editEvent(data) {
        document.getElementById('id_event').value = data.id_event;
        document.getElementById('nama_event').value = data.nama_event;
        document.getElementById('id_venue').value = data.id_venue;

        // Konversi "YYYY-MM-DD HH:MM:SS" ke "YYYY-MM-DDTHH:MM"
        if(data.tanggal) {
            let dateFormated = data.tanggal.replace(" ", "T").substring(0, 16);
            document.getElementById('tanggal').value = dateFormated;
        }
    }
</script>