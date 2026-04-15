<?php

$message = "";
$message_type = "";

// PROSES CREATE & UPDATE
if (isset($_POST['submit'])) {
    $nama_tiket = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $harga = (int)$_POST['harga'];
    $kuota = (int)$_POST['kuota'];
    $id_event = mysqli_real_escape_string($conn, $_POST['id_event']);
    $id_tiket = !empty($_POST['id_tiket']) ? mysqli_real_escape_string($conn, $_POST['id_tiket']) : null;

    // LOGIC UPDATE JIKA ID TIKET ADA, INSERT JIKA TIDAK ADA
    if ($id_tiket) {
        $query = mysqli_query($conn, "UPDATE tiket SET nama_tiket='$nama_tiket', harga='$harga', kuota='$kuota', id_event='$id_event' WHERE id_tiket='$id_tiket'");
        $status = $query ? "updated" : "failed";
    } else {
        $query = mysqli_query($conn, "INSERT INTO tiket (nama_tiket, harga, kuota, id_event) VALUES ('$nama_tiket', '$harga', '$kuota', '$id_event')");
        $status = $query ? "added" : "failed";
    }
    header("Location: index.php?page=tiket&status=$status");
    exit;
}

// PROSES DELETE
if (isset($_POST['delete'])) {
    $id_tiket = mysqli_real_escape_string($conn, $_POST['id_tiket']);
    $query = mysqli_query($conn, "DELETE FROM tiket WHERE id_tiket='$id_tiket'");
    $status = $query ? "deleted" : "failed";
    header("Location: index.php?page=tiket&status=$status");
    exit;
}

// HANDLING ALERT DARI URL (POST-REDIRECT-GET PATTERN)
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added': $message = "Kategori tiket berhasil dibuat!"; $message_type = "success"; break;
        case 'updated': $message = "Data tiket berhasil diperbarui!"; $message_type = "success"; break;
        case 'deleted': $message = "Tiket telah dihapus!"; $message_type = "success"; break;
        case 'failed': $message = "Gagal memproses data database!"; $message_type = "danger"; break;
    }
}

//  GET DATA UNTUK TAMPILAN 
$tickets = mysqli_query($conn, "SELECT t.*, e.nama_event FROM tiket t JOIN event e ON t.id_event = e.id_event ORDER BY e.nama_event ASC, t.harga ASC");
$events = mysqli_query($conn, "SELECT id_event, nama_event FROM event ORDER BY nama_event ASC");
?>

<style>
    .card { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .card-title { color: #1d1145; font-weight: 700; }
    .btn-primary { background-color: #1d1145; border: none; }
    .btn-primary:hover { background-color: #e66c8a; }
    .table thead th { background-color: #f8f9fa; color: #1d1145; border-bottom: 2px solid #eee; }
    .badge-price { background-color: #fef1f4; color: #e66c8a; font-weight: 700; padding: 5px 10px; border-radius: 6px; }
    .modal-header { background-color: #1d1145; color: #fff; border-radius: 12px 12px 0 0; }
    .modal-content { border-radius: 12px; border: none; }
    .event-name { font-size: 0.85rem; color: #6c757d; display: block; }
</style>

<div class="pagetitle">
    <h1>Manajemen Tiket</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Tiket</li>
        </ol>
    </nav>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
        <?= $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="filter:none;"></button>
    </div>
<?php endif; ?>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Daftar Kategori Tiket</h5>
                        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#formModal" onclick="resetForm()">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Tiket
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Info Tiket</th>
                                    <th>Harga</th>
                                    <th>Kuota</th>
                                    <th>Nama Event</th>
                                    <th width="150" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($ticket = mysqli_fetch_assoc($tickets)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($ticket['nama_tiket']); ?></strong>
                                        </td>
                                        <td><span class="badge-price">Rp <?= number_format($ticket['harga'], 0, ',', '.'); ?></span></td>
                                        <td>
                                            <i class="bi bi-people me-1 text-muted"></i> 
                                            <?= number_format($ticket['kuota'], 0, ',', '.'); ?> <small class="text-muted">slot</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><i class="bi bi-calendar-event me-1"></i> <?= htmlspecialchars($ticket['nama_event']); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm text-white" onclick='editTicket(<?= json_encode($ticket, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus kategori tiket ini? Data penjualan mungkin akan terpengaruh.');">
                                                <input type="hidden" name="id_tiket" value="<?= $ticket['id_tiket']; ?>">
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

<!-- MODAL UNTUK PILIH TIKET DI HALAMAN EVENT.PHP -->
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="formModalLabel">Tambah Tiket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter:invert(1)"></button>
            </div>
            <form method="POST" id="ticketForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_tiket" id="id_tiket">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Event</label>
                        <select class="form-select" id="id_event" name="id_event" required>
                            <option value="">-- Pilih Event --</option>
                            <?php mysqli_data_seek($events, 0); ?>
                            <?php while($e = mysqli_fetch_assoc($events)): ?>
                                <option value="<?= $e['id_event']; ?>"><?= htmlspecialchars($e['nama_event']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama/Kategori Tiket</label>
                        <input type="text" class="form-control" id="nama_tiket" name="nama_tiket" placeholder="Contoh: VIP, Early Bird, Reguler" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Harga</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga" name="harga" required min="0">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kuota</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="kuota" name="kuota" required min="1">
                                <span class="input-group-text">Slot</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit" class="btn btn-primary px-4">Simpan Tiket</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    // FUNGSI UNTUK RESET FORM SAAT BUKA MODAL (UNTUK TAMBAH DATA)
    function resetForm() {
        document.getElementById('ticketForm').reset();
        document.getElementById('id_tiket').value = '';
        document.getElementById('formModalLabel').innerText = 'Tambah Tiket';
    }

    // FUNGSI UNTUK MENGISI FORM SAAT EDIT TIKET
    function editTicket(ticket) {
        document.getElementById('id_tiket').value = ticket.id_tiket;
        document.getElementById('nama_tiket').value = ticket.nama_tiket;
        document.getElementById('harga').value = ticket.harga;
        document.getElementById('kuota').value = ticket.kuota;
        document.getElementById('id_event').value = ticket.id_event;
        document.getElementById('formModalLabel').innerText = 'Edit Detail Tiket';
        
        var modal = new bootstrap.Modal(document.getElementById('formModal'));
        modal.show();
    }
</script>