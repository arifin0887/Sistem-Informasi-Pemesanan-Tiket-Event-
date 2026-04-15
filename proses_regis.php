<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama     = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']); // sesuai kebutuhan kamu
    $role     = 'user'; // default role

    // Validasi
    if (empty($nama) || empty($email) || empty($_POST['password'])) {
        echo "<script>alert('Data tidak boleh kosong!');window.location='regis.php';</script>";
        exit;
    }

    // Cek email
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Email sudah digunakan!');window.location='regis.php';</script>";
        exit;
    }

    // Simpan data
    $insert = mysqli_query($conn, "
        INSERT INTO users (nama, email, password, role)
        VALUES ('$nama', '$email', '$password', '$role')
    ");

    if ($insert) {

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Akun berhasil dibuat, silahkan login',
                confirmButtonColor: '#E66C8A'
            }).then(() => {
                window.location = 'login.php';
            });
        </script>
        ";

    } else {
        echo "<script>alert('Registrasi gagal!');window.location='regis.php';</script>";
    }
}
?>