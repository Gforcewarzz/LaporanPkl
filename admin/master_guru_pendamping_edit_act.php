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
    $id = $_POST['id_pembimbing'] ?? null;
    $nama = $_POST['nama_pembimbing'] ?? '';
    $nip = $_POST['nip'] ?? '';
    $password_plain = $_POST['password'] ?? ''; // Bisa kosong jika password tidak diubah

    $status = '';
    $message = '';
    $title = '';

    // Validasi input wajib
    if (empty($id) || empty($nama) || empty($nip)) {
        $status = 'error';
        $title = 'Input Tidak Lengkap!';
        $message = 'ID guru, nama, dan NIP wajib diisi.';
    } else {
        // Logika UPDATE dengan atau tanpa password
        if (!empty($password_plain)) {
            // Jika password diisi, update password juga
            $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
            $query = "UPDATE guru_pembimbing SET nama_pembimbing = ?, nip = ?, password = ? WHERE id_pembimbing = ?";
            $stmt = $koneksi->prepare($query);
            if ($stmt) {
                $stmt->bind_param("sssi", $nama, $nip, $password_hash, $id); // sssi: 3 string, 1 integer
            }
        } else {
            // Jika password kosong, jangan update password
            $query = "UPDATE guru_pembimbing SET nama_pembimbing = ?, nip = ? WHERE id_pembimbing = ?";
            $stmt = $koneksi->prepare($query);
            if ($stmt) {
                $stmt->bind_param("ssi", $nama, $nip, $id); // ssi: 2 string, 1 integer
            }
        }

        if ($stmt) {
            if ($stmt->execute()) {
                $status = 'success';
                $title = 'Berhasil!';
                $message = 'Data guru berhasil diperbarui.';
            } else {
                $status = 'error';
                $title = 'Gagal!';
                $message = 'Terjadi kesalahan saat memperbarui data: ' . $stmt->error;
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
    // Jika akses bukan POST request, arahkan kembali ke halaman daftar guru
    header('Location: master_guru_pendamping.php');
    exit;
}
?>