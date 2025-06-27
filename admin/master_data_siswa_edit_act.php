<?php

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
        header('Location: ../login.php');
        exit();
    }
}

// 5. Jika lolos semua pemeriksaan di atas, maka dia adalah ADMIN yang sah.
// Tampilkan semua konten halaman ini.
include 'partials/db.php';

$id_siswa       = $_POST['id_siswa'];
$nama_siswa     = htmlspecialchars($_POST['nama_siswa']);
$jenis_kelamin  = $_POST['jenis_kelamin'];
$nisn           = htmlspecialchars($_POST['nisn']);
$no_induk       = htmlspecialchars($_POST['no_induk']);
$kelas          = htmlspecialchars($_POST['kelas']);
$status         = $_POST['status'];

// Ambil nama relasi dari input
$jurusan_nama   = $_POST['jurusan_nama'];
$guru_nama      = $_POST['guru_nama'];
$tempat_nama    = $_POST['tempat_pkl_nama'];

// Cari ID jurusan berdasarkan nama
$jurusan_q = mysqli_query($koneksi, "SELECT id_jurusan FROM jurusan WHERE nama_jurusan = '$jurusan_nama'");
$jurusan_id = mysqli_fetch_assoc($jurusan_q)['id_jurusan'] ?? null;

// Cari ID pembimbing berdasarkan nama
$guru_q = mysqli_query($koneksi, "SELECT id_pembimbing FROM guru_pembimbing WHERE nama_pembimbing = '$guru_nama'");
$pembimbing_id = mysqli_fetch_assoc($guru_q)['id_pembimbing'] ?? null;

// Cari ID tempat PKL berdasarkan nama
$tempat_q = mysqli_query($koneksi, "SELECT id_tempat_pkl FROM tempat_pkl WHERE nama_tempat_pkl = '$tempat_nama'");
$tempat_pkl_id = mysqli_fetch_assoc($tempat_q)['id_tempat_pkl'] ?? null;

// Cek jika password diisi
$password_input = $_POST['password'] ?? null;
$password_query = "";

if (!empty($password_input)) {
    // Hash password baru
    $password_hashed = password_hash($password_input, PASSWORD_BCRYPT);
    $password_query = ", password = '$password_hashed'";
}

// Update data siswa
$query = "UPDATE siswa SET 
            nama_siswa = '$nama_siswa',
            jenis_kelamin = '$jenis_kelamin',
            nisn = '$nisn',
            no_induk = '$no_induk',
            kelas = '$kelas',
            status = '$status',
            jurusan_id = '$jurusan_id',
            pembimbing_id = '$pembimbing_id',
            tempat_pkl_id = '$tempat_pkl_id'
            $password_query
          WHERE id_siswa = '$id_siswa'";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Update Siswa</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php
if (mysqli_query($koneksi, $query)) {
    echo "
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data siswa berhasil diperbarui!',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'master_data_siswa.php';
            }
        });
    </script>
    ";
} else {
    echo "
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Data gagal diperbarui: " . mysqli_error($koneksi) . "',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Coba Lagi'
        }).then(() => {
            window.history.back();
        });
    </script>
    ";
}
?>
</body>
</html>
