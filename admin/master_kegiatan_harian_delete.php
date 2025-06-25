<?php
session_start();
include 'partials/db.php';

if (!isset($_SESSION['id_siswa'])) {
    header("Location: login.php");
    exit;
}

$siswa_id = $_SESSION['id_siswa'];
$id_jurnal_harian = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validasi kepemilikan data
$cek = mysqli_query($koneksi, "SELECT * FROM jurnal_harian WHERE id_jurnal_harian = $id_jurnal_harian AND siswa_id = $siswa_id");

if (mysqli_num_rows($cek) == 0) {
    $status = 'error';
    $message = 'Data tidak ditemukan atau bukan milik Anda.';
} else {
    // Hapus data
    $delete = mysqli_query($koneksi, "DELETE FROM jurnal_harian WHERE id_jurnal_harian = $id_jurnal_harian AND siswa_id = $siswa_id");

    if ($delete) {
        $status = 'success';
        $message = 'Data berhasil dihapus.';
    } else {
        $status = 'error';
        $message = 'Gagal menghapus data: ' . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Status Hapus</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
    Swal.fire({
        icon: '<?php echo $status; ?>',
        title: '<?php echo ($status == "success") ? "Berhasil!" : "Gagal!"; ?>',
        text: '<?php echo $message; ?>',
        showConfirmButton: false,
        timer: 2500
    }).then(() => {
        window.location.href = 'master_kegiatan_harian.php';
    });
</script>

</body>
</html>
