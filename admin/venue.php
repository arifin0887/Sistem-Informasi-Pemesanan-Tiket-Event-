<?php

$message = "";
$message_type = "";

// PROSES UPDATE STATUS & DELETE (PRG PATTERN)
if (isset($_POST['submit'])) {
    $nama_venue = mysqli_real_escape_string($conn, $_POST['nama_venue']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kapasitas = (int)$_POST['kapasitas'];
    $id_venue = !empty($_POST['id_venue']) ? mysqli_real_escape_string($conn, $_POST['id_venue']) : null;

    // PROSES UPDATE JIKA ID VENUE ADA, JIKA TIDAK MAKA PROSES INSERT
    if ($id_venue) {
        $query = mysqli_query($conn, "UPDATE venue SET nama_venue='$nama_venue', alamat='$alamat', kapasitas='$kapasitas' WHERE id_venue='$id_venue'");
        $status = $query ? "updated" : "failed";
    } else {
        $query = mysqli_query($conn, "INSERT INTO venue (nama_venue, alamat, kapasitas) VALUES ('$nama_venue', '$alamat', '$kapasitas')");
        $status = $query ? "added" : "failed";
    }
    echo "<script>
        alert('Data venue berhasil disimpan!');
        window.location='index.php?page=venue';
    </script>";

    // header("Location: index.php?page=venue&status=$status");
    exit;
}

// PROSES DELETE VENUE
if (isset($_POST['delete'])) {
    $id_venue = mysqli_real_escape_string($conn, $_POST['id_venue']);
    $query = mysqli_query($conn, "DELETE FROM venue WHERE id_venue='$id_venue'");
    $status = $query ? "deleted" : "failed";
    header("Location: index.php?page=venue&status=$status");
    exit;
}

// HANDLING ALERT DARI URL (POST-REDIRECT-GET PATTERN)
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added': $message = "Venue berhasil ditambahkan!"; $message_type = "success"; break;
        case 'updated': $message = "Venue berhasil diperbarui!"; $message_type = "success"; break;
        case 'deleted': $message = "Venue berhasil dihapus!"; $message_type = "success"; break;
        case 'failed': $message = "Terjadi kesalahan pada database!"; $message_type = "danger"; break;
    }
}

// GET DATA VENUE
$venues = mysqli_query($conn, "SELECT * FROM venue ORDER BY id_venue DESC");
?>

<style>
    .card { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .card-title { color: #1d1145; font-weight: 700; }
    .btn-primary { background-color: #1d1145; border: none; }
    .btn-primary:hover { background-color: #e66c8a; }
    .btn-warning { color: #fff; background-color: #f39c12; border: none; }
    .btn-warning:hover { background-color: #e67e22; color: #fff; }
    .table thead th { background-color: #f8f9fa; color: #1d1145; border-bottom: 2px solid #eee; }
    .modal-header { background-color: #1d1145; color: #fff; border-radius: 12px 12px 0 0; }
    .modal-content { border-radius: 12px; border: none; }
    .btn-close { filter: invert(1); }
</style>

<div class="pagetitle">
    <h1>Manajemen Venue</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Venue</li>
        </ol>
    </nav>
</div>

<!-- ALERT UNTUK NOTIFIKASI UPDATE STATUS & DELETE -->
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
                        <h5 class="card-title mb-0">Daftar Venue Terdaftar</h5>
                        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#formModal" onclick="resetForm()">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Venue
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Venue</th>
                                    <th>Alamat</th>
                                    <th>Kapasitas</th>
                                    <th width="180" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($venue = mysqli_fetch_assoc($venues)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><strong><?= htmlspecialchars($venue['nama_venue']); ?></strong></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($venue['alamat']); ?></small></td>
                                        <td><span class="badge bg-light text-dark border"><?= number_format($venue['kapasitas'], 0, ',', '.'); ?> orang</span></td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm" onclick='editVenue(<?= json_encode($venue, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus venue ini?');">
                                                <input type="hidden" name="id_venue" value="<?= $venue['id_venue']; ?>">
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

<!-- MODAL FORM UNTUK TAMBAH & EDIT VENUE -->
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalLabel">Tambah Venue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="venueForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_venue" id="id_venue">

                    <div class="mb-3">
                        <label class="form-label">Nama Venue</label>
                        <input type="text" class="form-control" id="nama_venue" name="nama_venue" placeholder="Contoh: Gedung Serbaguna" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kapasitas Maksimal (Orang)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="kapasitas" name="kapasitas" required min="1">
                            <span class="input-group-text">Orang</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit" class="btn btn-primary px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // FUNGSI UNTUK RESET FORM SAAT MENAMBAH VENUE BARU
    function resetForm() {
        document.getElementById('venueForm').reset();
        document.getElementById('id_venue').value = '';
        document.getElementById('formModalLabel').innerText = 'Tambah Venue';
    }

    // FUNGSI UNTUK MENGISI FORM DENGAN DATA VENUE SAAT EDIT
    function editVenue(venue) {
        document.getElementById('id_venue').value = venue.id_venue;
        document.getElementById('nama_venue').value = venue.nama_venue;
        document.getElementById('alamat').value = venue.alamat;
        document.getElementById('kapasitas').value = venue.kapasitas;
        document.getElementById('formModalLabel').innerText = 'Edit Venue';
        
        // Trigger Bootstrap Modal
        var myModal = new bootstrap.Modal(document.getElementById('formModal'));
        myModal.show();
    }
</script>