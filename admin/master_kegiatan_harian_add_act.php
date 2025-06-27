<?php
session_start();
include 'partials/db.php'; // Ganti sesuai file koneksimu

// --- LOGIKA KEAMANAN HALAMAN SISWA ---

// 1. Definisikan dulu role yang sedang login untuk mempermudah pembacaan kode.
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

// 2. Aturan utama: Cek jika pengguna BUKAN Siswa DAN BUKAN Admin.
// Jika salah satu dari mereka (siswa atau admin) login, kondisi ini akan false dan halaman akan lanjut dimuat.
if (!$is_siswa && !$is_admin) {
    
    // 3. Jika tidak diizinkan, baru kita cek siapa pengguna ini.
    // Apakah dia seorang Guru yang mencoba masuk?
    if (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        // Jika benar guru, kembalikan ke halaman dasbor guru.
        header('Location: ../halaman_guru.php'); // Sesuaikan path jika perlu
        exit();
    }
    // 4. Jika bukan siapa-siapa dari role di atas, artinya pengguna belum login.
    else {
        // Arahkan paksa ke halaman login.
        header('Location: ../login.php'); // Sesuaikan path jika perlu
        exit();
    }
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
