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
