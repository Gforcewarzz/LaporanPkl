<?php
session_start();
include 'partials/db.php';

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

if (!$is_siswa && !$is_admin) {
    if (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        header('Location: ../halaman_guru.php');
        exit();
    } else {
        header('Location: ../login.php');
        exit();
    }
}

$id_jurnal_harian = $_POST['id_jurnal_harian'] ?? null;
$tanggal          = $_POST['tanggal'] ?? '';
$pekerjaan        = htmlspecialchars($_POST['pekerjaan'] ?? '');
$catatan          = htmlspecialchars($_POST['catatan'] ?? '');
$siswa_id_original = $_POST['siswa_id_original'] ?? null;
$redirect_siswa_id = $_POST['redirect_siswa_id'] ?? null;

if (empty($id_jurnal_harian) || empty($tanggal) || empty($pekerjaan) || empty($siswa_id_original)) {
    $status = 'error';
    $message = 'ID laporan, tanggal, pekerjaan, dan ID siswa asli wajib diisi.';
} else {
    if ($is_siswa && $siswa_id_original != ($_SESSION['id_siswa'] ?? null)) {
        $status = 'error';
        $message = 'Anda tidak diizinkan mengedit laporan siswa lain.';
    } else {
        $query = "UPDATE jurnal_harian SET tanggal = ?, pekerjaan = ?, catatan = ? WHERE id_jurnal_harian = ? AND siswa_id = ?";
        $stmt = $koneksi->prepare($query);

        if ($stmt) {
            // CORRECTED LINE:
            // The query has 5 placeholders (tanggal, pekerjaan, catatan, id_jurnal_harian, siswa_id)
            // So, bind_param needs 5 types (sssii) and 5 variables.
            $stmt->bind_param("sssii", $tanggal, $pekerjaan, $catatan, $id_jurnal_harian, $siswa_id_original);

            if ($stmt->execute()) {
                $status = 'success';
                $message = 'Laporan harian berhasil diperbarui!';
            } else {
                $status = 'error';
                $message = 'Gagal memperbarui data: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $status = 'error';
            $message = 'Gagal menyiapkan statement database: ' . $koneksi->error;
        }
    }
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Status Update</title>
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
        <?php if ($is_siswa): ?>
        window.location.href = 'master_kegiatan_harian.php';
        <?php elseif ($is_admin): ?>
        var redirectSiswaId = '<?php echo $redirect_siswa_id; ?>';
        if (redirectSiswaId) {
            window.location.href = 'master_kegiatan_harian.php?siswa_id=' + redirectSiswaId;
        } else {
            window.location.href = 'master_kegiatan_harian.php';
        }
        <?php endif; ?>
    });
    </script>

</body>

</html>