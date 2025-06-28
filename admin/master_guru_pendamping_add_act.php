<?php

session_start();

// LOGIKA KEAMANAN HALAMAN
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

if (!$is_siswa && !$is_admin) {
    if (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        header('Location: ../../halaman_guru.php');
        exit();
    } else {
        header('Location: ../login.php');
        exit();
    }
}

include 'partials/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_pembimbing'] ?? '';
    $nip = $_POST['nip'] ?? '';
    $password_plain = $_POST['password'] ?? '';

    $status = '';
    $message = '';
    $title = '';

    if (empty($nama) || empty($nip) || empty($password_plain)) {
        $status = 'error';
        $title = 'Input Tidak Lengkap!';
        $message = 'Nama guru, NIP, dan password wajib diisi.';
    } else {
        $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

        // Gunakan prepared statement untuk INSERT
        $query = "INSERT INTO guru_pembimbing (nama_pembimbing, nip, password) VALUES (?, ?, ?)";
        $stmt = $koneksi->prepare($query);

        if ($stmt) {
            $stmt->bind_param("sss", $nama, $nip, $password_hash); // 'sss' for three strings

            if ($stmt->execute()) {
                $status = 'success';
                $title = 'Berhasil!';
                $message = 'Data guru berhasil ditambahkan.';
            } else {
                $status = 'error';
                $title = 'Gagal!';
                $message = 'Terjadi kesalahan saat menyimpan data: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $status = 'error';
            $title = 'Gagal!';
            $message = 'Gagal menyiapkan statement database: ' . $koneksi->error;
        }
    }
    $koneksi->close();

    // Tampilkan SweetAlert2 dan kemudian redirect
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Status Aksi</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <script>
    Swal.fire({
        icon: '<?php echo $status; ?>',
        title: '<?php echo $title; ?>',
        text: '<?php echo $message; ?>',
        showConfirmButton: false, // Tidak menampilkan tombol "OK"
        timer: 2500, // Otomatis hilang setelah 2.5 detik
        didClose: () => { // Callback setelah alert tertutup
            window.location.href = 'master_guru_pendamping.php';
        }
    });
    </script>
</body>

</html>
<?php
} else {
    // Jika akses bukan POST request, arahkan kembali ke halaman tambah guru
    header("Location: master_guru_pendamping_add.php");
    exit;
}
?>