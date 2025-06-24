<?php
include 'partials/db.php';

if (isset($_GET['id'])) {
    $id_siswa = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Cek apakah siswa ada
    $check = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_siswa = '$id_siswa'");
    if (mysqli_num_rows($check) === 0) {
        echo "<script>
            alert('Data siswa tidak ditemukan.');
            window.location.href = 'master_data_siswa.php';
        </script>";
        exit;
    }

    // Lakukan penghapusan
    $delete = mysqli_query($koneksi, "DELETE FROM siswa WHERE id_siswa = '$id_siswa'");

    if ($delete) {
        echo "<script>
            alert('Data siswa berhasil dihapus.');
            window.location.href = 'master_data_siswa.php';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus data siswa.');
            window.history.back();
        </script>";
    }

} else {
    // Jika tidak ada parameter id
    echo "<script>
        alert('Permintaan tidak valid.');
        window.location.href = 'master_data_siswa.php';
    </script>";
}
?>
