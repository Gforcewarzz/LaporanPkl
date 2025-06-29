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

$nama        = $_POST['nama_perusahaan'];
$alamat      = $_POST['alamat'];
$kontak      = $_POST['kontak'];
$kuota       = $_POST['kuota_siswa'];
$instruktur  = $_POST['nama_instruktur_lapangan'];
$jurusan_id  = $_POST['jurusan_id'];

if (!$nama || !$alamat || !$kontak || !$kuota || !$jurusan_id) {
    echo "<script>
        alert('Semua field wajib diisi!');
        window.history.back();
    </script>";
    exit;
}

$query = "INSERT INTO tempat_pkl (nama_tempat_pkl, alamat, alamat_kontak, nama_instruktur, kuota_siswa, jurusan_id)
          VALUES ('$nama', '$alamat', '$kontak', '$instruktur', '$kuota', '$jurusan_id')";

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
