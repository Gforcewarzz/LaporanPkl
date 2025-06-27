<?php
// 1. Selalu mulai sesi di baris paling awal sebelum output lainnya
session_start();
// 1. Aturan utama: Cek apakah pengguna yang mengakses BUKAN seorang ADMIN.
if (!isset($_SESSION['admin_status_login']) || $_SESSION['admin_status_login'] !== 'logged_in') {

    // 2. Jika bukan admin, cek apakah dia adalah SISWA.
    if (isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in') {
        // Jika benar siswa, kembalikan ke halaman siswa.
        header('Location: master_kegiatan_harian.php');
        exit();
    }
    // 3. TAMBAHAN: Jika bukan siswa, cek apakah dia adalah GURU.
    elseif (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        // Jika benar guru, kembalikan ke halaman guru.
        header('Location: ../../halaman_guru.php'); //belum di atur
        exit();
    }
    // 4. Jika bukan salah satu dari role di atas (admin, siswa, guru),
    // artinya pengguna belum login sama sekali. Arahkan ke halaman login.
    else {
        header('Location: ../../login.php');
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
