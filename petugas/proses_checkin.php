<?php

// ERROR REPORTING OFF UNTUK MENCEGAH PESAN ERROR MENGACAUKAN OUTPUT JSON
error_reporting(0);
header('Content-Type: application/json');

// KONEKSI DATABASE
$host = "localhost";
$user = "root";
$pass = "";
$db   = "event_tikett"; 

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Gagal terhubung ke database: ' . mysqli_connect_error()
    ]);
    exit;
}

//PROSES CHECK-IN TIKET BERDASARKAN KODE YANG DITERIMA DARI SCANNER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kode_tiket'])) {
    
    $kode = mysqli_real_escape_string($conn, trim($_POST['kode_tiket']));
    
    // CARI TIKET DI DATABASE
    $sql = "SELECT * FROM attendee WHERE kode_tiket = '$kode' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        // CEK STATUS CHECK-IN
        if ($data['status_checkin'] === 'sudah') {
            echo json_encode([
                'status' => 'error', 
                'message' => "Tiket $kode sudah digunakan sebelumnya!"
            ]);
        } else {
            // UPDATE STATUS CHECK-IN MENJADI 'sudah' DAN SIMPAN WAKTU CHECK-IN
            $update = mysqli_query($conn, "UPDATE attendee SET status_checkin = 'sudah', waktu_checkin = NOW() WHERE kode_tiket = '$kode'");
            
            if ($update) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => "BERHASIL! Selamat datang, " . ($data['nama'] ?? $kode)
                ]);
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Gagal memperbarui database: ' . mysqli_error($conn)
                ]);
            }
        }
    } else {
        // JIKA TIKET TIDAK DITEMUKAN
        echo json_encode([
            'status' => 'error', 
            'message' => "Kode [$kode] tidak terdaftar."
        ]);
    }
} else {
    // JIKA REQUEST BUKAN POST ATAU KODE TIDAK DITERIMA
    echo json_encode([
        'status' => 'error', 
        'message' => 'Data barcode tidak ditemukan.'
    ]);
}