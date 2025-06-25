<?php
session_start();
include 'partials/db.php';

if (!isset($_SESSION['id_siswa'])) {
    header("Location: login.php");
    exit;
}

// Ambil data dari form
$id_jurnal_harian = (int)$_POST['id_jurnal_harian'];
$tanggal = $_POST['tanggal'];
$pekerjaan = htmlspecialchars($_POST['pekerjaan']);
$catatan = htmlspecialchars($_POST['catatan']);
$siswa_id = $_SESSION['id_siswa'];

// Validasi input (minimal tanggal dan pekerjaan)
if (empty($tanggal) || empty($pekerjaan)) {
    $status = 'error';
    $message = 'Tanggal dan pekerjaan wajib diisi.';
} else {
    // Cek apakah data milik siswa yang sedang login
    $cek = mysqli_query($koneksi, "SELECT * FROM jurnal_harian WHERE id_jurnal_harian = $id_jurnal_harian AND siswa_id = $siswa_id");
    if (mysqli_num_rows($cek) == 0) {
        $status = 'error';
        $message = 'Data tidak ditemukan atau bukan milik Anda.';
    } else {
        // Update data
        $query = "UPDATE jurnal_harian SET tanggal = '$tanggal', pekerjaan = '$pekerjaan', catatan = '$catatan' 
                  WHERE id_jurnal_harian = $id_jurnal_harian AND siswa_id = $siswa_id";

        if (mysqli_query($koneksi, $query)) {
            $status = 'success';
            $message = 'Laporan berhasil diperbarui!';
        } else {
            $status = 'error';
            $message = 'Gagal memperbarui data: ' . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Status Edit</title>
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
