<?php
session_start();
include 'partials/db.php'; // Ganti sesuai file koneksimu

if (!isset($_SESSION['id_siswa'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil data dari form
$tanggal    = $_POST['tanggal'];
$pekerjaan  = htmlspecialchars($_POST['pekerjaan']);
$catatan    = htmlspecialchars($_POST['catatan']);
$siswa_id   = $_SESSION['id_siswa'];

// Validasi data
if (empty($tanggal) || empty($pekerjaan)) {
    $status = 'error';
    $message = 'Tanggal dan pekerjaan wajib diisi.';
} else {
    // Query simpan
    $query = "INSERT INTO jurnal_harian (tanggal, pekerjaan, catatan, siswa_id) 
              VALUES ('$tanggal', '$pekerjaan', '$catatan', '$siswa_id')";
    
    if (mysqli_query($koneksi, $query)) {
        $status = 'success';
        $message = 'Laporan harian berhasil disimpan!';
    } else {
        $status = 'error';
        $message = 'Gagal menyimpan data: ' . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Status Simpan</title>
    <!-- SweetAlert CDN -->
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
