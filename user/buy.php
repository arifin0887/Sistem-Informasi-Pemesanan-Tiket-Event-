<?php
// CEK LOGIN
if (!isset($_SESSION['user']['id'])) {
    echo "<script>alert('Silakan login terlebih dahulu'); window.location='login.php';</script>";
    exit;
}

// AMBIL ID EVENT DARI URL
$id_event = (int)($_GET['id_event'] ?? 0);
$id_tiket_selected = (int)($_GET['id_tiket'] ?? 0); // Tiket awal yang dipilih user

// 1. QUERY DETAIL EVENT & DAFTAR SEMUA TIKET DI EVENT TERSEBUT
$query_event = $conn->prepare("
    SELECT e.nama_event, e.tanggal, v.nama_venue 
    FROM event e 
    JOIN venue v ON e.id_venue = v.id_venue 
    WHERE e.id_event = ?
");
$query_event->bind_param("i", $id_event);
$query_event->execute();
$event = $query_event->get_result()->fetch_assoc();

if (!$event) {
    echo "<div class='alert alert-danger'>Event tidak ditemukan.</div>";
    return;
}

// 2. QUERY SEMUA KATEGORI TIKET UNTUK DROPDOWN
$query_tickets = $conn->prepare("SELECT id_tiket, kategori_tiket, nama_tiket, harga, kuota FROM tiket WHERE id_event = ? AND kuota > 0");
$query_tickets->bind_param("i", $id_event);
$query_tickets->execute();
$result_tickets = $query_tickets->get_result();

$tickets_data = [];
while($row = $result_tickets->fetch_assoc()) {
    $tickets_data[] = $row;
}

// PROSES PEMBELIAN TIKET
if (isset($_POST['proses_buy'])) {
    $id_tiket_final = (int)$_POST['id_tiket'];
    $jumlah = (int)$_POST['jumlah'];

    // Ambil data tiket yang dipilih untuk validasi akhir
    $check = $conn->prepare("SELECT * FROM tiket WHERE id_tiket = ?");
    $check->bind_param("i", $id_tiket_final);
    $check->execute();
    $t_data = $check->get_result()->fetch_assoc();

    if ($jumlah > $t_data['kuota']) {
        echo "<script>alert('Jumlah melebihi kuota tersedia!');</script>";
    } else if ($jumlah <= 0) {
        echo "<script>alert('Jumlah minimal 1 tiket!');</script>";
    } else {
        $_SESSION['cart'] = [
            'id_event'     => $id_event,
            'id_tiket'     => $id_tiket_final,
            'nama_event'   => $event['nama_event'],
            'nama_tiket'   => "[" . $t_data['kategori_tiket'] . "] " . $t_data['nama_tiket'],
            'harga_satuan' => $t_data['harga'],
            'jumlah'       => $jumlah,
            'subtotal'     => $t_data['harga'] * $jumlah,
            'diskon'       => 0,
            'total'        => $t_data['harga'] * $jumlah,
            'id_voucher'   => null,
            'kode_voucher' => ''
        ];

        echo "<script>
            alert('Tiket berhasil ditambahkan ke keranjang!');
            window.location='index.php?page=checkout';
        </script>";
        exit;
    }
}
?>

<section class="section section-buy">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card card-buy">
                    <div class="card-header text-white text-center">
                        <h4 class="mb-0 fw-bold">Konfirmasi Pemesanan Tiket</h4>
                        <p class="mb-0 opacity-75 small">Selesaikan detail pesanan Anda</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <div class="row g-4">
                            <div class="col-md-6 pe-md-4 border-end">
                                <div class="event-info-box mb-4">
                                    <h6 class="text-muted text-uppercase fw-bold small mb-3" style="letter-spacing: 1px;">Detail Event</h6>
                                    <h3 class="fw-bold text-navy mb-3"><?= htmlspecialchars($event['nama_event']) ?></h3>
                                    
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="icon-box me-3 text-pink"><i class="bi bi-calendar3 fs-5"></i></div>
                                        <div>
                                            <p class="mb-0 fw-bold text-navy"><?= date('d F Y', strtotime($event['tanggal'])) ?></p>
                                            <p class="mb-0 small text-muted"><?= date('H:i', strtotime($event['tanggal'])) ?> WIB</p>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center mb-4">
                                        <div class="icon-box me-3 text-pink"><i class="bi bi-geo-alt fs-5"></i></div>
                                        <div>
                                            <p class="mb-0 fw-bold text-navy"><?= htmlspecialchars($event['nama_venue']) ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="summary-box p-4">
                                    <h6 class="fw-bold text-navy mb-3">Ringkasan Pembayaran</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal</span>
                                        <span id="display_subtotal" class="fw-bold">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Potongan</span>
                                        <span class="text-success fw-bold">- Rp 0</span>
                                    </div>
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-navy">Total Bayar</span>
                                        <span id="display_total" class="fs-4 fw-bold text-pink">Rp 0</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 ps-md-4">
                                <form method="POST" class="form-buy">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-navy">Pilih Kategori Tiket</label>
                                        <select name="id_tiket" id="select_tiket" class="form-select form-select-lg" required onchange="updateTicketInfo()">
                                            <?php foreach($tickets_data as $t): ?>
                                                <option value="<?= $t['id_tiket'] ?>" 
                                                        data-harga="<?= $t['harga'] ?>" 
                                                        data-kuota="<?= $t['kuota'] ?>"
                                                        <?= ($t['id_tiket'] == $id_tiket_selected) ? 'selected' : '' ?>>
                                                    <?= $t['kategori_tiket'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold text-navy">Jumlah Tiket</label>
                                        <div class="d-flex align-items-center">
                                            <div class="input-group input-group-lg" style="width: 180px;">
                                                <button type="button" class="btn qty-btn" onclick="changeQty(-1)"><i class="bi bi-dash-lg"></i></button>
                                                <input type="number" name="jumlah" id="jumlah_tiket" class="form-control text-center fw-bold border-0" value="1" min="1" readonly>
                                                <button type="button" class="btn qty-btn" onclick="changeQty(1)"><i class="bi bi-plus-lg"></i></button>
                                            </div>
                                            <div class="ms-3">
                                                <span class="badge-category" id="display_harga_satuan">@ Rp 0</span>
                                            </div>
                                        </div>
                                        <div class="mt-3 p-2 rounded-3 bg-light border">
                                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Stok tersedia: <span id="display_kuota" class="fw-bold text-primary">0</span> tiket</small>
                                        </div>
                                    </div>


                                    <button type="submit" name="proses_buy" class="btn btn-checkout w-100 btn-lg mt-2">
                                        Lanjut Pembayaran <i class="bi bi-arrow-right-short ms-1 fs-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function updateTicketInfo() {
        const select = document.getElementById('select_tiket');
        const selectedOption = select.options[select.selectedIndex];
        
        const harga = parseInt(selectedOption.getAttribute('data-harga'));
        const kuota = parseInt(selectedOption.getAttribute('data-kuota'));
        const jumlahInput = document.getElementById('jumlah_tiket');

        // Update tampilan info tiket
        document.getElementById('display_kuota').innerText = kuota;
    document.getElementById('display_harga_satuan').innerText = new Intl.NumberFormat('id-ID').format(harga) + ' / tiket';
        
        // Pastikan jumlah tiket tidak melebihi kuota kategori baru
        if(parseInt(jumlahInput.value) > kuota) {
            jumlahInput.value = kuota;
        }

        calculateTotal();
    }

    function changeQty(step) {
        const select = document.getElementById('select_tiket');
        const selectedOption = select.options[select.selectedIndex];
        const maxQuota = parseInt(selectedOption.getAttribute('data-kuota'));
        
        const input = document.getElementById('jumlah_tiket');
        let newVal = parseInt(input.value) + step;
        
        if(newVal >= 1 && newVal <= maxQuota) {
            input.value = newVal;
            calculateTotal();
        }
    }

    function calculateTotal() {
        const select = document.getElementById('select_tiket');
        const selectedOption = select.options[select.selectedIndex];
        const harga = parseInt(selectedOption.getAttribute('data-harga'));
        const jumlah = parseInt(document.getElementById('jumlah_tiket').value);

        const total = harga * jumlah;

        document.getElementById('display_subtotal').innerText = "Rp " + total.toLocaleString('id-ID');
        document.getElementById('display_total').innerText = "Rp " + total.toLocaleString('id-ID');
    }

    // Jalankan saat halaman dibuka pertama kali
    document.addEventListener('DOMContentLoaded', updateTicketInfo);
</script>