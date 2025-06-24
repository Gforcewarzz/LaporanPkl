<?php
include 'partials/db.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Cek apakah data ada
    $check = mysqli_query($koneksi, "SELECT * FROM guru_pembimbing WHERE id_pembimbing = '$id'");
    if (mysqli_num_rows($check) === 0) {
        echo "<script>
                alert('Data guru tidak ditemukan!');
                window.location.href = 'master_guru_pendamping.php';
              </script>";
        exit;
    }

    // Lanjutkan delete
    $delete = mysqli_query($koneksi, "DELETE FROM guru_pembimbing WHERE id_pembimbing = '$id'");

    if ($delete) {
        echo "<script>
                alert('Data guru berhasil dihapus.');
                window.location.href = 'master_guru_pendamping.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data guru.');
                window.history.back();
              </script>";
    }
} else {
    echo "<script>
            alert('ID guru tidak ditemukan.');
            window.location.href = 'master_guru_pendamping.php';
          </script>";
}
?>
