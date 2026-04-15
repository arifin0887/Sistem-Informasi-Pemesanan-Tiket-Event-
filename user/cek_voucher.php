<?php
include '../koneksi.php'; 

header('Content-Type: application/json');

if (isset($_POST['kode_voucher'])) {
    $kode = mysqli_real_escape_string($conn, trim($_POST['kode_voucher']));
    
    // QUERY UNTUK MENGECEK VALIDITAS VOUCHER: HARUS AKTIF DAN KUOTA HARUS LEBIH DARI 0
    $query = "SELECT * FROM voucher WHERE kode_voucher = '$kode' AND status = 'aktif' AND kuota > 0";
    $sql = mysqli_query($conn, $query);

    // JIKA VOUCHER DITEMUKAN, KEMBALIKAN DATA POTONGAN DAN ID VOUCHER
    if (mysqli_num_rows($sql) > 0) {
        $data = mysqli_fetch_assoc($sql);
        echo json_encode([
            'valid' => true,
            'diskon' => (int)$data['potongan'],
            'id_voucher' => (int)$data['id_voucher'],
            'message' => 'Voucher berhasil dipasang!'
        ]);
    } else {
        // JIKA VOUCHER TIDAK DITEMUKAN ATAU TIDAK VALID, KEMBALIKAN RESPON ERROR
        echo json_encode([
            'valid' => false,
            'diskon' => 0,
            'message' => 'Voucher tidak ditemukan atau sudah habis.'
        ]);
    }
}
exit;