<?php

// GENERATOR KODE VOUCHER UNIK
function generateVoucherCode() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < 8; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// FUNGSI UNTUK MENGHASILKAN KODE VOUCHER UNIK (CEK KE DATABASE UNTUK MENGHINDARI DUPLIKAT)
function getUniqueVoucherCode($conn) {
    do {
        $code = generateVoucherCode();
        $check = mysqli_query($conn, "SELECT id_voucher FROM voucher WHERE kode_voucher = '$code'");
    } while (mysqli_num_rows($check) > 0);
    return $code;
}

// PROSES CRUD (PRG PATTERN)
if (isset($_POST['submit'])) {
    $potongan = (int)$_POST['potongan'];
    $kuota = (int)$_POST['kuota'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $id_voucher = !empty($_POST['id_voucher']) ? (int)$_POST['id_voucher'] : null;

    if ($id_voucher) {
        // UPDATE: KODE VOUCHER TIDAK DIUBAH AGAR RIWAYAT TRANSAKSI TETAP KONSISTEN
        $query = mysqli_query($conn, "UPDATE voucher SET potongan=$potongan, kuota=$kuota, status='$status' WHERE id_voucher=$id_voucher");
        $res = $query ? "updated" : "failed";
    } else {
        // CREATE: GENERATE KODE VOUCHER UNIK OTOMATIS
        $kode_voucher = getUniqueVoucherCode($conn);
        $query = mysqli_query($conn, "INSERT INTO voucher (kode_voucher, potongan, kuota, status) VALUES ('$kode_voucher', $potongan, $kuota, '$status')");
        $res = $query ? "added" : "failed";
    }

    echo "<script>
        alert('Data voucher berhasil disimpan!');
        window.location='index.php?page=voucher';
    </script>";

    // header("Location: index.php?page=voucher&status=$res");
    exit;
}

// PROSES DELETE VOUCHER
if (isset($_POST['delete'])) {
    $id_voucher = (int)$_POST['id_voucher'];
    $query = mysqli_query($conn, "DELETE FROM voucher WHERE id_voucher = $id_voucher");
    $res = $query ? "deleted" : "failed";
    header("Location: index.php?page=voucher&status=$res");
    exit;
}

// HANDLING ALERT DARI URL (POST-REDIRECT-GET PATTERN) 
$message = ""; $message_type = "";
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added': $message = "Voucher baru berhasil dibuat!"; $message_type = "success"; break;
        case 'updated': $message = "Data voucher berhasil diperbarui!"; $message_type = "success"; break;
        case 'deleted': $message = "Voucher telah dihapus!"; $message_type = "success"; break;
        case 'failed': $message = "Terjadi kesalahan sistem!"; $message_type = "danger"; break;
    }
}

// GET DATA VOUCHER
$vouchers = mysqli_query($conn, "SELECT * FROM voucher ORDER BY id_voucher DESC");
?>

<div class="pagetitle">
    <h1>Manajemen Voucher</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Voucher</li>
        </ol>
    </nav>
</div>

<!-- ALERT UNTUK NOTIFIKASI UPDATE STATUS & DELETE -->
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
                        <h5 class="card-title mb-0">Daftar Voucher Promosi</h5>
                        <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#formModal" onclick="resetForm()">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Voucher
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Voucher</th>
                                    <th>Potongan Harga</th>
                                    <th>Sisa Kuota</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($v = mysqli_fetch_assoc($vouchers)): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><span class="voucher-code"><?= $v['kode_voucher']; ?></span></td>
                                        <td><strong>Rp <?= number_format($v['potongan'], 0, ',', '.'); ?></strong></td>
                                        <td><i class="bi bi-ticket-perforated me-1"></i> <?= number_format($v['kuota']); ?> <small class="text-muted">penggunaan</small></td>
                                        <td>
                                            <span class="<?= $v['status'] == 'aktif' ? 'status-active' : 'status-inactive'; ?>">
                                                <?= ucfirst($v['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm text-white" onclick='editVoucher(<?= json_encode($v); ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus voucher ini secara permanen?');">
                                                <input type="hidden" name="id_voucher" value="<?= $v['id_voucher']; ?>">
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

<!-- MODAL FORM UNTUK TAMBAH & EDIT VOUCHER -->
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="formModalLabel">Buat Voucher Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter:invert(1)"></button>
            </div>
            <form method="POST" id="voucherForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_voucher" id="id_voucher">
                    
                    <div class="mb-4 text-center" id="edit_code_preview" style="display:none;">
                        <small class="text-muted d-block">Kode Voucher</small>
                        <h4 class="voucher-code d-inline-block px-4 py-2" id="display_kode"></h4>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nominal Potongan (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="potongan" name="potongan" placeholder="Contoh: 50000" required min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kuota</label>
                            <input type="number" class="form-control" id="kuota" name="kuota" placeholder="Jml penggunaan" required min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="new_voucher_info" class="alert alert-info py-2" style="font-size: 0.85rem;">
                        <i class="bi bi-info-circle me-1"></i> Kode unik 8 karakter akan dibuat otomatis oleh sistem.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit" class="btn btn-primary px-4">Simpan Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // FUNGSI UNTUK RESET FORM SAAT MENAMBAH VOUCHER BARU
    function resetForm() {
        document.getElementById('voucherForm').reset();
        document.getElementById('id_voucher').value = '';
        document.getElementById('formModalLabel').innerText = 'Buat Voucher Baru';
        document.getElementById('edit_code_preview').style.display = 'none';
        document.getElementById('new_voucher_info').style.display = 'block';
    }

    // FUNGSI UNTUK MENGISI FORM DENGAN DATA VOUCHER SAAT EDIT
    function editVoucher(v) {
        document.getElementById('id_voucher').value = v.id_voucher;
        document.getElementById('potongan').value = v.potongan;
        document.getElementById('kuota').value = v.kuota;
        document.getElementById('status').value = v.status;
        
        document.getElementById('display_kode').innerText = v.kode_voucher;
        document.getElementById('edit_code_preview').style.display = 'block';
        document.getElementById('new_voucher_info').style.display = 'none';
        document.getElementById('formModalLabel').innerText = 'Edit Detail Voucher';
        
        var modal = new bootstrap.Modal(document.getElementById('formModal'));
        modal.show();
    }
</script>