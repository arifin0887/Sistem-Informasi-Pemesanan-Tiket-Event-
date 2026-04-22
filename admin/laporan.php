<?php
require_once '../koneksi.php';

// Filter default (Bulan ini)
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');

// Query list event untuk dropdown filter
$query_events = mysqli_query($conn, "SELECT id_event, nama_event FROM event ORDER BY nama_event ASC");
?>

<div class="pagetitle mb-4">
    <h1 style="color: #1D1145; font-weight: 700;">Laporan Penjualan</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=admin">Home</a></li>
            <li class="breadcrumb-item active">Laporan</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-3">
            <form id="filterForm" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="laporan">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Mulai Tanggal</label>
                    <input type="date" name="tgl_mulai" class="form-control border-0 bg-light" value="<?= $tgl_mulai ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Sampai Tanggal</label>
                    <input type="date" name="tgl_selesai" class="form-control border-0 bg-light" value="<?= $tgl_selesai ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Event</label>
                    <select name="id_event" class="form-select border-0 bg-light">
                        <option value="">Semua Event</option>
                        <?php while($ev = mysqli_fetch_assoc($query_events)): ?>
                            <option value="<?= $ev['id_event'] ?>"><?= htmlspecialchars($ev['nama_event']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1 fw-bold" style="background-color: #1D1145; border: none; height: 45px;">
                            <i class="bi bi-funnel-fill me-2"></i>Filter
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-success fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown" style="height: 45px; background-color: #0DB5BB; border: none;">
                                <i class="bi bi-cloud-download-fill"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item py-2" id="linkExcel" href="ekspor_excel.php?tgl_mulai=<?= $tgl_mulai ?>&tgl_selesai=<?= $tgl_selesai ?>"><i class="bi bi-file-earmark-excel text-success me-2"></i>Excel</a></li>
                                <li><a class="dropdown-item py-2" href="#" onclick="window.print()"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Cetak PDF</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="displayArea">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Memuat data...</p>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const displayArea = document.getElementById('displayArea');
        const linkExcel = document.getElementById('linkExcel');

        function fetchData() {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData).toString();
            
            // Update link excel agar sesuai filter
            linkExcel.href = `ekspor_excel.php?${params}`;

            fetch(`laporan_render.php?${params}`)
                .then(response => response.text())
                .then(html => {
                    displayArea.innerHTML = html;
                })
                .catch(err => {
                    displayArea.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>';
                });
        }

        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchData();
        });

        // Load data pertama kali saat halaman dibuka
        fetchData();
    });
</script>