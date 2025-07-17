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
?>
<?php
include 'partials/db.php';

$id           = $_POST['id_tempat_pkl'];
$nama         = $_POST['nama_tempat_pkl'];
$alamat       = $_POST['alamat'];
$kontak       = $_POST['alamat_kontak'];
$instruktur   = $_POST['nama_instruktur'];
$kuota        = $_POST['kuota_siswa'];
$jurusan_id   = $_POST['jurusan_id'];

// Validasi dasar
if (!$id || !$nama || !$alamat || !$kontak || !$kuota || !$jurusan_id) {
    echo "<script>
        Swal.fire('Gagal!', 'Semua field wajib diisi.', 'error').then(() => {
            window.history.back();
        });
    </script>";
    exit;
}

$query = "UPDATE tempat_pkl SET 
            nama_tempat_pkl = '$nama',
            alamat = '$alamat',
            alamat_kontak = '$kontak',
            nama_instruktur = '$instruktur',
            kuota_siswa = '$kuota',
            jurusan_id = '$jurusan_id'
          WHERE id_tempat_pkl = '$id'";

$result = mysqli_query($koneksi, $query);
?>

<!-- Tambahkan ini di akhir file proses -->
<!DOCTYPE html>
<html>

<head>
    <title>Update Tempat PKL</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php if ($result): ?>
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Data tempat PKL berhasil diperbarui.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'master_tempat_pkl.php';
            });
        </script>
    <?php else: ?>
        <script>
            Swal.fire({
                title: 'Gagal!',
                text: 'Terjadi kesalahan: <?= mysqli_error($koneksi) ?>',
                icon: 'error',
                confirmButtonText: 'Kembali'
            }).then(() => {
                window.history.back();
            });
        </script>
    <?php endif; ?>
</body>

</html>