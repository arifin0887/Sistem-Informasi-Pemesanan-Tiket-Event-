<?php

// KONEKSI KE DATABASE
$servername = "localhost";
$username = "root";
$password = "";
$database = "event_tikett";

// MEMBUAT KONEKSI
$conn = new mysqli($servername, $username, $password, $database);

// CEK KONEKSI
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// echo "Koneksi berhasil";
?>