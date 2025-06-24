<?php
$host = "localhost";       
$user = "root";            
$password = "";            
$database = "db_Laporanpkl";

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

function querys($query){
    global $koneksi;
    $result = mysqli_query($koneksi,$query);
    $rows = [];
    while ( $row = mysqli_fetch_assoc($result)){
        $rows[] = $row;
    }
    return $rows;
}

function tambahSiswa($data){
    global $koneksi;
    $nama_siswa             = htmlspecialchars($data['nama_siswa']);
    $no_induk               = htmlspecialchars($data['no_induk']);
    $nisn                   = htmlspecialchars($data['nisn']);
    $kelas                  = htmlspecialchars($data['kelas']);
    $jurusan                = htmlspecialchars($data['jurusan']);
    $jenis_kelamin          = htmlspecialchars($data['jenis_kelamin']);
    $kelas                  = htmlspecialchars($data['kelas']);
    $guru_pendamping        = htmlspecialchars($data['guru_pendamping']);
    $tempat_pkl             = htmlspecialchars($data['tempat_pkl']);
    $status                 = htmlspecialchars($data['status_siswa']);

    $id_guru_pendamping = querys("SELECT id_pembimbing FROM guru_pembimbing WHERE nama_pembimbing = '$guru_pendamping' ")[0];
    $id_guru = $id_guru_pendamping['id_pembimbing'];
    $id_tempat_pkl = querys("SELECT id_tempat_pkl FROM tempat_pkl WHERE nama_tempat_pkl = '$tempat_pkl' ")[0];
    $id_tempat = $id_tempat_pkl['id_tempat_pkl'];
    $query = "INSERT INTO `siswa` (`nama_siswa`, `no_induk`, `nisn`, `jenis_kelamin`, `kelas`, `status`, `jurusan_id`, `pembimbing_id`, `tempat_pkl_id`) 
    VALUES ('$nama_siswa', '$no_induk', '$nisn', '$jenis_kelamin', '$kelas', '$status', '$jurusan', '$id_guru', '$id_tempat')";
    

    mysqli_query($koneksi,$query);
    return mysqli_affected_rows($koneksi);

}