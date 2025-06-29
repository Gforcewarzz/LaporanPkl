<?php

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
include 'partials/db.php';

$id = $_GET['id'] ?? null;

$status = '';
$message = '';
$title = '';

if (empty($id)) {
    $status = 'error';
    $title = 'Gagal!';
    $message = 'ID guru tidak ditemukan.';
} else {
    // Cek apakah data ada menggunakan prepared statement
    $query_check = "SELECT COUNT(*) FROM guru_pembimbing WHERE id_pembimbing = ?";
    $stmt_check = $koneksi->prepare($query_check);

    if ($stmt_check) {
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_row();
        $count = $row_check[0];
        $stmt_check->close();

        if ($count === 0) {
            $status = 'error';
            $title = 'Tidak Ditemukan!';
            $message = 'Data guru tidak ditemukan.';
        } else {
            // Lanjutkan delete menggunakan prepared statement
            $query_delete = "DELETE FROM guru_pembimbing WHERE id_pembimbing = ?";
            $stmt_delete = $koneksi->prepare($query_delete);

            if ($stmt_delete) {
                $stmt_delete->bind_param("i", $id);

                if ($stmt_delete->execute()) {
                    if ($stmt_delete->affected_rows > 0) {
                        $status = 'success';
                        $title = 'Berhasil!';
                        $message = 'Data guru berhasil dihapus.';
                    } else {
                        $status = 'info';
                        $title = 'Tidak Ada Perubahan!';
                        $message = 'Data guru tidak ditemukan atau sudah terhapus.';
                    }
                } else {
                    $status = 'error';
                    $title = 'Gagal!';
                    $message = 'Gagal menghapus data guru: ' . $stmt_delete->error;
                }
                $stmt_delete->close();
            } else {
                $status = 'error';
                $title = 'Gagal!';
                $message = 'Gagal menyiapkan statement penghapusan: ' . $koneksi->error;
            }
        }
    } else {
        $status = 'error';
        $title = 'Gagal!';
        $message = 'Gagal menyiapkan statement cek data: ' . $koneksi->error;
    }
}
$koneksi->close();
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
        title: '<?php echo $title; ?>',
        text: '<?php echo $message; ?>',
        showConfirmButton: false,
        timer: 2500,
        didClose: () => {
            window.location.href = 'master_guru_pendamping.php';
        }
    });
    </script>

</body>

</html>