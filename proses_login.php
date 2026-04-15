<?php
session_start();
include 'koneksi.php';

// PROSES LOGIN
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = md5($_POST['password']); //md5

    // QUERY UNTUK MENCARI USER DENGAN EMAIL DAN PASSWORD YANG SESUAI
    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    $data = mysqli_fetch_assoc($query);

    // JIKA USER DITEMUKAN, SIMPAN DATA KE SESSION DAN REDIRECT BERDASARKAN ROLE
    if($data){
        $_SESSION['user'] = $data;
        // SIMPAN ID USER KE SESSION UNTUK MEMUDAHKAN PENGAMBILAN DATA DI HALAMAN LAIN
        if(isset($data['id_user'])) {
            $_SESSION['user']['id'] = $data['id_user'];
        } elseif(isset($data['id'])) {
            $_SESSION['user']['id'] = $data['id'];
        }

        // AMBIL DATA VOUCHER AKTIF DAN SIMPAN KE SESSION UNTUK DIGUNAKAN DI HALAMAN LAIN
        $voucher_result = mysqli_query($conn, "SELECT * FROM voucher WHERE status = 'aktif' ORDER BY id_voucher DESC");
        $_SESSION['voucher_data'] = [];
        while($voucher = mysqli_fetch_assoc($voucher_result)) {
            $_SESSION['voucher_data'][] = $voucher;
        }

        // REDIRECT BERDASARKAN ROLE USER
        if($data['role'] == 'admin'){
            header("Location: admin/index.php?page=admin");
        } elseif($data['role'] == 'petugas'){
            header("Location: petugas/index.php?page=petugas"); 
        } else {
            header("Location: user/index.php?page=user");
        }
    } else {
        echo "<script>alert('Email atau password salah!');window.location='login.php';</script>";
    }
}
?>