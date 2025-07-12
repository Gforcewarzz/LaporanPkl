<?php

session_start();

// Standarisasi pengecekan peran
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

// Keamanan: Hanya admin atau guru yang boleh mengakses fungsi ini
if (!$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// 5. Jika lolos semua pemeriksaan di atas, maka dia adalah ADMIN yang sah.
// Tampilkan semua konten halaman ini.
include 'partials/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hapus Data Siswa</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php
if (isset($_GET['id'])) {
    $id_siswa = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Cek apakah data siswa ada
    $check = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_siswa = '$id_siswa'");
    if (mysqli_num_rows($check) === 0) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Data siswa tidak ditemukan.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'master_data_siswa.php';
        });
        </script>";
        exit;
    }

    // Hapus data siswa
    $delete = mysqli_query($koneksi, "DELETE FROM siswa WHERE id_siswa = '$id_siswa'");

    if ($delete) {
        echo "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data siswa berhasil dihapus.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'master_data_siswa.php';
        });
        </script>";
    } else {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Gagal menghapus data siswa.',
            confirmButtonText: 'Kembali'
        }).then(() => {
            window.history.back();
        });
        </script>";
    }

} else {
    // Tidak ada parameter id
    echo "
    <script>
    Swal.fire({
        icon: 'warning',
        title: 'Permintaan Tidak Valid',
        text: 'ID siswa tidak ditemukan dalam permintaan.',
        confirmButtonText: 'OK'
    }).then(() => {
        window.location.href = 'master_data_siswa.php';
    });
    </script>";
}
?>
</body>
</html>
