<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "u673945095_db_laporanpkl";


// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
