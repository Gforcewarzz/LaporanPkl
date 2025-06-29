<?php
// 1. Selalu mulai sesi di baris paling awal sebelum output lainnya
session_start();
// Keamanan: Hanya admin yang boleh mengakses dashboard ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php'); // Redirect siswa ke dashboard siswa
        exit();
    } elseif ($is_guru) {
        header('Location: ../halaman_guru.php'); // Redirect guru ke halaman guru
        exit();
    } else {
        header('Location: ../login.php'); // Jika tidak login sama sekali, redirect ke halaman login
        exit();
    }
}

// 5. Jika lolos semua pemeriksaan di atas, maka dia adalah ADMIN yang sah.
// Tampilkan semua konten halaman ini.
// --> Kode HTML atau PHP selanjutnya hanya akan dieksekusi jika pengguna adalah admin <--
?>
<?php
include 'partials/db.php';

// Ambil ID dari parameter URL
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Validasi awal
if (!$id) {
    echo "<script>
        alert('ID tidak ditemukan.');
        window.location.href = 'master_tempat_pkl.php';
    </script>";
    exit;
}

// Cek apakah ID valid
$cek = mysqli_query($koneksi, "SELECT * FROM tempat_pkl WHERE id_tempat_pkl = '$id'");
if (mysqli_num_rows($cek) == 0) {
    echo "<script>
        alert('Data dengan ID tersebut tidak ditemukan.');
        window.location.href = 'master_tempat_pkl.php';
    </script>";
    exit;
}

// Proses hapus
$hapus = mysqli_query($koneksi, "DELETE FROM tempat_pkl WHERE id_tempat_pkl = '$id'");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hapus Tempat PKL</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php if ($hapus): ?>
    <script>
        Swal.fire({
            title: 'Berhasil Dihapus!',
            text: 'Data tempat PKL telah dihapus.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'master_tempat_pkl.php';
        });
    </script>
<?php else: ?>
    <script>
        Swal.fire({
            title: 'Gagal Menghapus!',
            text: 'Terjadi kesalahan: <?= mysqli_error($koneksi) ?>',
            icon: 'error',
            confirmButtonText: 'Kembali'
        }).then(() => {
            window.location.href = 'master_tempat_pkl.php';
        });
    </script>
<?php endif; ?>
</body>
</html>
