<?php
include 'partials/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pembimbing']);
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $password_plain = $_POST['password'];
    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

    $query = "INSERT INTO guru_pembimbing (nama_pembimbing, nip, password)
              VALUES ('$nama', '$nip', '$password_hash')";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>
                alert('Data guru berhasil ditambahkan.');
                window.location.href = 'master_guru_pendamping.php';
              </script>";
    } else {
        echo "<script>
                alert('Terjadi kesalahan saat menyimpan data.');
                window.history.back();
              </script>";
    }
} else {
    header("Location: master_guru_pendamping_add.php");
    exit;
}
?>
