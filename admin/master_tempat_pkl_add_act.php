<?php
session_start();

// Hanya admin yang boleh mengakses dashboard ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru  = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php');
        exit();
    } elseif ($is_guru) {
        header('Location: ../halaman_guru.php');
        exit();
    } else {
        header('Location: ../login.php');
        exit();
    }
}

include 'partials/db.php';

$nama       = $_POST['nama_perusahaan'];
$alamat     = $_POST['alamat'];
$kontak     = $_POST['kontak'];
$instruktur = $_POST['nama_instruktur_lapangan'];

// Validasi sederhana
if (!$nama || !$alamat || !$kontak || !$instruktur) {
    echo "<script>
        alert('Semua field wajib diisi!');
        window.history.back();
    </script>";
    exit;
}

// Karena kolom kuota_siswa dan jurusan_id sudah dihapus, kita sesuaikan query-nya
$query = "INSERT INTO tempat_pkl (nama_tempat_pkl, alamat, alamat_kontak, nama_instruktur)
          VALUES ('$nama', '$alamat', '$kontak', '$instruktur')";

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Proses Tambah Tempat PKL</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php if ($result): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data tempat PKL berhasil ditambahkan.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'master_tempat_pkl.php';
            });
        </script>
    <?php else: ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: 'Data gagal disimpan.<br><?= addslashes(mysqli_error($koneksi)) ?>',
                confirmButtonText: 'Kembali'
            }).then(() => {
                window.history.back();
            });
        </script>
    <?php endif; ?>
</body>

</html>
