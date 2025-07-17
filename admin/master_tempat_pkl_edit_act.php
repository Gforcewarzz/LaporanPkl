<?php
// Mulai sesi
session_start();

// Validasi akses
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Redirect jika bukan admin
if (!$is_admin) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php');
    } elseif ($is_guru) {
        header('Location: dashboard_guru.php');
    } else {
        header('Location: ../login.php');
    }
    exit();
}

// Koneksi ke database
include 'partials/db.php';

// Ambil data dari form
$id         = $_POST['id_tempat_pkl'] ?? null;
$nama       = $_POST['nama_tempat_pkl'] ?? null;
$alamat     = $_POST['alamat'] ?? null;
$kontak     = $_POST['alamat_kontak'] ?? null;
$instruktur = $_POST['nama_instruktur'] ?? null;

// Validasi
if (!$id || !$nama || !$alamat || !$kontak || !$instruktur) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire('Gagal!', 'Semua field wajib diisi.', 'error').then(() => {
            window.history.back();
        });
    </script>";
    exit;
}

// Query update
$query = "UPDATE tempat_pkl SET 
            nama_tempat_pkl = '" . htmlspecialchars($nama) . "',
            alamat = '" . htmlspecialchars($alamat) . "',
            alamat_kontak = '" . htmlspecialchars($kontak) . "',
            nama_instruktur = '" . htmlspecialchars($instruktur) . "'
          WHERE id_tempat_pkl = '" . htmlspecialchars($id) . "'";

$result = mysqli_query($koneksi, $query);
?>

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
                text: 'Terjadi kesalahan saat update data.',
                icon: 'error',
                confirmButtonText: 'Kembali'
            }).then(() => {
                window.history.back();
            });
        </script>
    <?php endif; ?>
</body>
</html>
