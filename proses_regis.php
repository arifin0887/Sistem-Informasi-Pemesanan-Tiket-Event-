<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $password = md5($_POST['password']);
    $role     = 'user';

    if (empty($nama) || empty($email) || empty($_POST['password'])) {
        $_SESSION['error'] = "Data tidak boleh kosong!";
        header("Location: regis.php");
        exit;
    }

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['error'] = "Email sudah digunakan!";
        header("Location: regis.php");
        exit;
    }

    $insert = mysqli_query($conn, "
        INSERT INTO users (nama, email, password, role)
        VALUES ('$nama', '$email', '$password', '$role')
    ");

    if ($insert) {
        $_SESSION['success'] = "Akun berhasil dibuat!";
        header("Location: login.php");
        exit;
    } else {
        die("Error: " . mysqli_error($conn));
    }
}
?>