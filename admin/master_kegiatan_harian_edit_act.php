<?php
session_start();
include 'partials/db.php';

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru  = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// akses hanya siswa & admin
if (!$is_siswa && !$is_admin) {
    if ($is_guru) {
        header('Location: ../halaman_guru.php');
    } else {
        header('Location: ../login.php');
    }
    exit();
}

// fungsi helper swal2
function showAlertAndRedirect($icon, $title, $text, $redirectUrl)
{
    ob_clean();
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Notifikasi</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    </head>
    <body>
        <script>
        Swal.fire({
            icon: '{$icon}',
            title: '{$title}',
            text: '{$text}',
            confirmButtonColor: '#696cff',
            allowOutsideClick: false,
            showClass: { popup: 'animate__animated animate__fadeInDown animate__faster' },
            hideClass: { popup: 'animate__animated animate__fadeOutUp animate__faster' }
        }).then(() => { window.location.href = '{$redirectUrl}'; });
        </script>
    </body>
    </html>
HTML;
    exit();
}

// ambil data dari form
$id_jurnal_harian   = $_POST['id_jurnal_harian'] ?? null;
$tanggal            = $_POST['tanggal'] ?? '';
$pekerjaan          = trim($_POST['pekerjaan'] ?? '');
$catatan            = trim($_POST['catatan'] ?? ''); // opsional
$siswa_id_original  = $_POST['siswa_id_original'] ?? null;
$redirect_siswa_id  = $_POST['redirect_siswa_id'] ?? null;

// validasi data wajib
if (empty($id_jurnal_harian) || empty($tanggal) || empty($pekerjaan) || empty($siswa_id_original)) {
    $redirect_url = 'master_kegiatan_harian_edit.php?id=' . urlencode($id_jurnal_harian);
    showAlertAndRedirect('error', 'Input Tidak Lengkap!', 'ID laporan, tanggal, pekerjaan, dan ID siswa wajib diisi.', $redirect_url);
}

// otorisasi
if ($is_siswa && $siswa_id_original != ($_SESSION['id_siswa'] ?? null)) {
    showAlertAndRedirect('error', 'Akses Ditolak', 'Anda tidak diizinkan mengedit laporan siswa lain.', 'master_kegiatan_harian.php');
}

// jika catatan kosong → set NULL
$catatan_for_db = ($catatan === '') ? null : htmlspecialchars($catatan);

// update query
$query = "UPDATE jurnal_harian 
          SET tanggal = ?, pekerjaan = ?, catatan = ? 
          WHERE id_jurnal_harian = ? AND siswa_id = ?";
$stmt  = $koneksi->prepare($query);

if ($stmt) {
    $stmt->bind_param("sssii", $tanggal, $pekerjaan, $catatan_for_db, $id_jurnal_harian, $siswa_id_original);

    if ($stmt->execute()) {
        if ($stmt->affected_rows === 0) {
            $status  = 'info';
            $title   = 'Tidak Ada Perubahan!';
            $message = 'Data sama, tidak ada perubahan yang tersimpan.';
        } else {
            $status  = 'success';
            $title   = 'Berhasil!';
            $message = 'Laporan harian berhasil diperbarui!';
        }

        $redirect_url = 'master_kegiatan_harian.php';
        if ($is_admin && !empty($redirect_siswa_id)) {
            $redirect_url .= '?siswa_id=' . urlencode($redirect_siswa_id);
        }

        showAlertAndRedirect($status, $title, $message, $redirect_url);
    } else {
        $redirect_url = 'master_kegiatan_harian_edit.php?id=' . urlencode($id_jurnal_harian);
        showAlertAndRedirect('error', 'Gagal Menyimpan', 'Kesalahan saat update: ' . $stmt->error, $redirect_url);
    }
    $stmt->close();
} else {
    $redirect_url = 'master_kegiatan_harian_edit.php?id=' . urlencode($id_jurnal_harian);
    showAlertAndRedirect('error', 'Gagal', 'Kesalahan query: ' . $koneksi->error, $redirect_url);
}

$koneksi->close();
