<?php

$message = "";
$message_type = "";

// PROSES CREATE & UPDATE
if (isset($_POST['submit'])) {
    // Tambahkan kategori_tiket
    $kategori_tiket = mysqli_real_escape_string($conn, $_POST['kategori_tiket']);
    $nama_tiket = mysqli_real_escape_string($conn, $_POST['nama_tiket']);
    $harga = (int)$_POST['harga'];
    $kuota = (int)$_POST['kuota'];
    $id_event = mysqli_real_escape_string($conn, $_POST['id_event']);
    $id_tiket = !empty($_POST['id_tiket']) ? mysqli_real_escape_string($conn, $_POST['id_tiket']) : null;

    // AMBIL KAPASITAS VENUE DARI EVENT
    $get_kapasitas = mysqli_query($conn, "
        SELECT v.kapasitas 
        FROM event e
        JOIN venue v ON e.id_venue = v.id_venue
        WHERE e.id_event = '$id_event'
    ");

    $data_kapasitas = mysqli_fetch_assoc($get_kapasitas);
    $kapasitas = $data_kapasitas['kapasitas'] ?? 0;

    // HITUNG TOTAL KUOTA TIKET YANG SUDAH ADA DI EVENT INI
    if ($id_tiket) {
        $get_total = mysqli_query($conn, "
            SELECT SUM(kuota) as total 
            FROM tiket 
            WHERE id_event = '$id_event' AND id_tiket != '$id_tiket'
        ");
    } else {
        $get_total = mysqli_query($conn, "
            SELECT SUM(kuota) as total 
            FROM tiket 
            WHERE id_event = '$id_event'
        ");
    }

    $data_total = mysqli_fetch_assoc($get_total);
    $total_kuota = $data_total['total'] ?? 0;

    // CEK APAKAH MELEBIHI KAPASITAS
    if (($total_kuota + $kuota) > $kapasitas) {
        echo "<script>
            alert('Kuota tiket melebihi kapasitas venue! Maksimal kapasitas: $kapasitas');
            window.history.back();
        </script>";
        exit;
    }

    // UPDATE QUERY UNTUK MENYERTAKAN kategori_tiket
    if ($id_tiket) {
        $query = mysqli_query($conn, "UPDATE tiket SET kategori_tiket='$kategori_tiket', nama_tiket='$nama_tiket', harga='$harga', kuota='$kuota', id_event='$id_event' WHERE id_tiket='$id_tiket'");
        $status = $query ? "updated" : "failed";
    } else {
        $query = mysqli_query($conn, "INSERT INTO tiket (kategori_tiket, nama_tiket, harga, kuota, id_event) VALUES ('$kategori_tiket', '$nama_tiket', '$harga', '$kuota', '$id_event')");
        $status = $query ? "added" : "failed";
    }
    
    echo "<script>
        alert('Tiket berhasil disimpan!');
        window.location='index.php?page=tiket';
    </script>";
    exit;
}

// PROSES DELETE
if (isset($_POST['delete'])) {
    $id_tiket = mysqli_real_escape_string($conn, $_POST['id_tiket']);
    $query = mysqli_query($conn, "DELETE FROM tiket WHERE id_tiket='$id_tiket'");
    echo "<script>
        alert('Tiket berhasil dihapus!');
        window.location='index.php?page=tiket';
    </script>";
    exit;
}

// GET DATA UNTUK TAMPILAN 
$tickets = mysqli_query($conn, "SELECT t.*, e.nama_event FROM tiket t JOIN event e ON t.id_event = e.id_event ORDER BY e.nama_event ASC, t.harga ASC");
$events = mysqli_query($conn, "SELECT e.id_event, e.nama_event, v.kapasitas FROM event e JOIN venue v ON e.id_venue = v.id_venue");
?>

<div class="pagetitle">
    <h1>Management Tiket</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Management Tiket</li>
        </ol>
    </nav>
</div>

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
                                    <th>Kelas</th>
                                    <th>Kategori Tiket</th>
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
                                            <?php 
                                                $badge_color = 'bg-success';
                                                if($ticket['kategori_tiket'] == 'VIP') $badge_color = 'bg-warning';
                                                if($ticket['kategori_tiket'] == 'VVIP') $badge_color = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $badge_color ?>"><?= $ticket['kategori_tiket']; ?></span>
                                        </td>
                                        <td><strong><?= htmlspecialchars($ticket['nama_tiket']); ?></strong></td>
                                        <td><span class="badge-price fw-bold">Rp <?= number_format($ticket['harga'], 0, ',', '.'); ?></span></td>
                                        <td><?= number_format($ticket['kuota'], 0, ',', '.'); ?> <small class="text-muted">slot</small></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($ticket['nama_event']); ?></span></td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm text-white" onclick='editTicket(<?= json_encode($ticket); ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus tiket ini?');">
                                                <input type="hidden" name="id_tiket" value="<?= $ticket['id_tiket']; ?>">
                                                <button type="submit" name="delete" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
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

<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="formModalLabel">Tambah Tiket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <option value="<?= $e['id_event']; ?>" data-kapasitas="<?= $e['kapasitas']; ?>">
                                    <?= htmlspecialchars($e['nama_event']); ?>
                                </option>                            
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kelas</label>
                        <select class="form-select" id="kategori_tiket" name="kategori_tiket" required>
                            <option value="Reguler">Reguler</option>
                            <option value="VIP">VIP</option>
                            <option value="VVIP">VVIP</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kategori</label>
                        <input type="text" class="form-control" id="nama_tiket" name="nama_tiket" placeholder="Contoh: Early Bird / Sesi 1">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Harga</label>
                            <input type="number" class="form-control" id="harga" name="harga" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kuota</label>
                            <input type="number" class="form-control" id="kuota" name="kuota" required min="1">
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
    function resetForm() {
        document.getElementById('ticketForm').reset();
        document.getElementById('id_tiket').value = '';
        document.getElementById('formModalLabel').innerText = 'Tambah Tiket';
    }

    function editTicket(ticket) {
        document.getElementById('id_tiket').value = ticket.id_tiket;
        document.getElementById('kategori_tiket').value = ticket.kategori_tiket;
        document.getElementById('nama_tiket').value = ticket.nama_tiket;
        document.getElementById('harga').value = ticket.harga;
        document.getElementById('kuota').value = ticket.kuota;
        document.getElementById('id_event').value = ticket.id_event;
        document.getElementById('formModalLabel').innerText = 'Edit Detail Tiket';
        
        var modal = new bootstrap.Modal(document.getElementById('formModal'));
        modal.show();
    }

    // Logic kapasitas tetap sama seperti sebelumnya
    let kapasitas = 0;
    document.getElementById('id_event').addEventListener('change', function() {
        let selected = this.options[this.selectedIndex];
        kapasitas = parseInt(selected.getAttribute('data-kapasitas')) || 0;
    });

    document.getElementById('kuota').addEventListener('input', function() {
        let val = parseInt(this.value) || 0;
        if (kapasitas > 0 && val > kapasitas) {
            this.value = kapasitas;
            alert("Kuota melebihi kapasitas venue!");
        }
    });
</script>