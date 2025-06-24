<?php
include 'partials/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($koneksi, $_POST['id_pembimbing']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pembimbing']);
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE guru_pembimbing SET 
                    nama_pembimbing = '$nama', 
                    nip = '$nip', 
                    password = '$password_hash'
                  WHERE id_pembimbing = '$id'";
    } else {
        $query = "UPDATE guru_pembimbing SET 
                    nama_pembimbing = '$nama', 
                    nip = '$nip'
                  WHERE id_pembimbing = '$id'";
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data guru berhasil diperbarui.'); window.location.href = 'master_guru_pendamping.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.'); window.history.back();</script>";
    }
} else {
    header('Location: master_guru_pendamping.php');
    exit;
}
?>
