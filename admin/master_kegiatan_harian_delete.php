<?php
session_start();
include 'partials/db.php';

// LOGIKA KEAMANAN HALAMAN
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

// Fungsi SweetAlert2 untuk notifikasi dan redirect
function showAlertAndRedirect($icon, $title, $text, $redirectUrl)
{
    ob_clean(); // Membersihkan output buffer jika ada
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Notifikasi Hapus</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{$icon}',
                    title: '{$title}',
                    text: '{$text}',
                    confirmButtonColor: '#696cff',
                    allowOutsideClick: false,
                    showClass: { // Menambahkan kelas animasi saat muncul
                        popup: 'animate__animated animate__fadeInDown animate__faster' // Contoh: fadeIn dari atas
                    },
                    hideClass: { // Menambahkan kelas animasi saat sembunyi
                        popup: 'animate__animated animate__fadeOutUp animate__faster' // Contoh: fadeOut ke atas
                    }
                }).then(() => {
                    window.location.href = '{$redirectUrl}';
                });
            });
        </script>
    </body>
    </html>
HTML;
    exit();
}

$id_jurnal_harian = $_GET['id'] ?? null;
$redirect_to_siswa_id = $_GET['redirect_siswa_id'] ?? null; // Tambahan untuk admin redirect

// Validasi dasar: ID laporan harus ada
if (empty($id_jurnal_harian) || !is_numeric($id_jurnal_harian)) {
    showAlertAndRedirect(
        'error',
        'Gagal Hapus',
        'ID laporan tidak valid atau tidak ditemukan.',
        'master_kegiatan_harian.php'
    );
}

$id_jurnal_harian = intval($id_jurnal_harian);

// Ambil siswa_id dari laporan yang akan dihapus untuk verifikasi (PENTING)
$siswa_id_dari_db = null;
$query_get_siswa_id = "SELECT siswa_id FROM jurnal_harian WHERE id_jurnal_harian = ?";
$stmt_get_siswa_id = $koneksi->prepare($query_get_siswa_id);

if (!$stmt_get_siswa_id) {
    error_log("Failed to prepare statement (get siswa_id for delete): " . $koneksi->error);
    showAlertAndRedirect(
        'error',
        'Gagal Hapus',
        'Terjadi kesalahan internal saat memverifikasi laporan.',
        'master_kegiatan_harian.php'
    );
}
$stmt_get_siswa_id->bind_param("i", $id_jurnal_harian);
$stmt_get_siswa_id->execute();
$result_get_siswa_id = $stmt_get_siswa_id->get_result();
$data_laporan_dihapus = $result_get_siswa_id->fetch_assoc();
$stmt_get_siswa_id->close();

if (!$data_laporan_dihapus) {
    showAlertAndRedirect(
        'error',
        'Gagal Hapus',
        'Laporan tidak ditemukan.',
        'master_kegiatan_harian.php'
    );
}

$siswa_id_dari_db = $data_laporan_dihapus['siswa_id'];

// LOGIKA OTORISASI UNTUK PENGHAPUSAN
$authorized_to_delete = false;
if ($is_siswa && $siswa_id_dari_db == ($_SESSION['id_siswa'] ?? null)) {
    // Siswa hanya bisa menghapus laporan miliknya sendiri
    $authorized_to_delete = true;
} elseif ($is_admin) {
    // Admin bisa menghapus laporan siapa saja
    $authorized_to_delete = true;
}

if (!$authorized_to_delete) {
    showAlertAndRedirect(
        'error',
        'Akses Ditolak',
        'Anda tidak diizinkan menghapus laporan ini.',
        'master_kegiatan_harian.php'
    );
}

// Lanjutkan proses penghapusan
$query_delete = "DELETE FROM jurnal_harian WHERE id_jurnal_harian = ?";
$types_delete = "i";
$params_delete = [$id_jurnal_harian];

if ($is_siswa) {
    // Jika siswa, tambahkan siswa_id ke WHERE clause untuk keamanan ekstra
    $query_delete .= " AND siswa_id = ?";
    $types_delete .= "i";
    $params_delete[] = $_SESSION['id_siswa'] ?? null;
}

$stmt_delete = $koneksi->prepare($query_delete);

if ($stmt_delete) {
    $stmt_delete->bind_param($types_delete, ...$params_delete);

    if ($stmt_delete->execute()) {
        if ($stmt_delete->affected_rows > 0) {
            $status_type = 'success';
            $title_swal = 'Berhasil!';
            $message_swal = 'Laporan kegiatan harian telah berhasil dihapus.';
        } else {
            $status_type = 'info';
            $title_swal = 'Tidak Ada Perubahan!';
            $message_swal = 'Laporan tidak ditemukan atau sudah dihapus.';
        }
    } else {
        error_log("Error executing delete statement: " . $stmt_delete->error);
        $status_type = 'error';
        $title_swal = 'Gagal!';
        $message_swal = 'Terjadi kesalahan pada database saat mencoba menghapus: ' . $stmt_delete->error;
    }
    $stmt_delete->close();
} else {
    error_log("Failed to prepare delete statement: " . $koneksi->error);
    $status_type = 'error';
    $title_swal = 'Gagal!';
    $message_swal = 'Terjadi kesalahan pada persiapan query database.';
}

// Tentukan URL redirect setelah operasi selesai
$redirect_url_final = 'master_kegiatan_harian.php';
// Prioritaskan redirect_siswa_id dari GET jika admin memintanya
if ($is_admin && !empty($redirect_to_siswa_id)) {
    $redirect_url_final .= '?siswa_id=' . htmlspecialchars($redirect_to_siswa_id);
}
// Jika admin menghapus dari tampilan semua laporan, siswa_id_dari_db bisa membantu redirect ke laporan siswa yang benar
// Namun, jika redirect_siswa_id_param ada di URL, itu yang kita gunakan.


$koneksi->close();

showAlertAndRedirect(
    $status_type,
    $title_swal,
    $message_swal,
    $redirect_url_final
);
