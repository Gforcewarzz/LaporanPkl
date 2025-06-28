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

function showAlertAndRedirect($icon, $title, $text, $redirectUrl)
{
    ob_clean();
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Notifikasi Hapus</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{$icon}',
                    title: '{$title}',
                    text: '{$text}',
                    confirmButtonColor: '#696cff',
                    allowOutsideClick: false
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

$id_jurnal_to_delete = $_GET['id'] ?? null;
$redirect_siswa_id_param = $_GET['redirect_siswa_id'] ?? null; // Untuk admin kembali ke view siswa spesifik

// Validasi dasar: ID laporan harus ada
if (empty($id_jurnal_to_delete) || !is_numeric($id_jurnal_to_delete)) {
    showAlertAndRedirect(
        'error',
        'Gagal Hapus',
        'ID laporan tidak valid atau tidak ditemukan.',
        'master_tugas_project.php'
    );
}

$id_jurnal_to_delete = intval($id_jurnal_to_delete);
$upload_dir = 'images/';

// Ambil siswa_id dari laporan yang akan dihapus (PENTING untuk otorisasi dan redirect admin)
$siswa_id_dari_db_laporan = null;
$sql_get_siswa_id = "SELECT siswa_id, gambar FROM jurnal_kegiatan WHERE id_jurnal_kegiatan = ?";
$stmt_get_siswa_id = $koneksi->prepare($sql_get_siswa_id);

if (!$stmt_get_siswa_id) {
    error_log("Failed to prepare statement (get siswa_id for delete): " . $koneksi->error);
    showAlertAndRedirect(
        'error',
        'Gagal Hapus',
        'Terjadi kesalahan internal saat memverifikasi laporan.',
        'master_tugas_project.php'
    );
}
$stmt_get_siswa_id->bind_param("i", $id_jurnal_to_delete);
$stmt_get_siswa_id->execute();
$result_get_siswa_id = $stmt_get_siswa_id->get_result();
$data_laporan = $result_get_siswa_id->fetch_assoc();
$stmt_get_siswa_id->close();

// Jika laporan tidak ditemukan
if (!$data_laporan) {
    showAlertAndRedirect(
        'error',
        'Gagal Hapus',
        'Laporan tidak ditemukan.',
        'master_tugas_project.php'
    );
}

$siswa_id_dari_db_laporan = $data_laporan['siswa_id'];
$gambar_nama_file = $data_laporan['gambar'];

// --- LOGIKA OTORISASI UNTUK PENGHAPUSAN ---
$authorized_to_delete = false;
if ($is_siswa && ($siswa_id_dari_db_laporan == ($_SESSION['id_siswa'] ?? null))) {
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
        'master_tugas_project.php'
    );
}

// Siapkan query DELETE
$sql_delete_record = "DELETE FROM jurnal_kegiatan WHERE id_jurnal_kegiatan = ?";
$types_delete = "i";
$params_delete = [$id_jurnal_to_delete];

if ($is_siswa) {
    // Jika siswa, tambahkan siswa_id ke WHERE clause untuk keamanan ekstra
    $sql_delete_record .= " AND siswa_id = ?";
    $types_delete .= "i";
    $params_delete[] = $_SESSION['id_siswa'] ?? null; // Pastikan siswa_id dari sesi ada
}

$stmt_delete_record = $koneksi->prepare($sql_delete_record);

if ($stmt_delete_record) {
    $stmt_delete_record->bind_param($types_delete, ...$params_delete);

    if ($stmt_delete_record->execute()) {
        if ($stmt_delete_record->affected_rows > 0) {
            // Hapus file gambar dari server jika ada
            if (!empty($gambar_nama_file)) {
                $file_path_to_delete = $upload_dir . $gambar_nama_file;
                if (file_exists($file_path_to_delete)) {
                    if (unlink($file_path_to_delete)) {
                        error_log("Gambar berhasil dihapus dari server: " . $file_path_to_delete);
                    } else {
                        error_log("Gagal menghapus gambar dari server (izin?): " . $file_path_to_delete);
                    }
                } else {
                    error_log("Gambar tidak ditemukan di direktori tetapi tercatat di DB: " . $file_path_to_delete);
                }
            }
            $status_type = 'success';
            $title_swal = 'Berhasil!';
            $message_swal = 'Laporan proyek telah berhasil dihapus.';
        } else {
            $status_type = 'info';
            $title_swal = 'Tidak Ada Perubahan!';
            $message_swal = 'Laporan tidak ditemukan atau sudah dihapus.';
        }
    } else {
        error_log("Error executing delete statement: " . $stmt_delete_record->error);
        $status_type = 'error';
        $title_swal = 'Gagal!';
        $message_swal = 'Terjadi kesalahan pada database saat mencoba menghapus: ' . $stmt_delete_record->error;
    }
    $stmt_delete_record->close();
} else {
    error_log("Failed to prepare delete statement: " . $koneksi->error);
    $status_type = 'error';
    $title_swal = 'Gagal!';
    $message_swal = 'Terjadi kesalahan pada persiapan query database.';
}

// Tentukan URL redirect setelah operasi selesai
$redirect_url_final = 'master_tugas_project.php';
if ($is_admin && !empty($redirect_siswa_id_param)) {
    $redirect_url_final .= '?siswa_id=' . htmlspecialchars($redirect_siswa_id_param);
} elseif ($is_admin && !empty($siswa_id_dari_db_laporan) && empty($redirect_siswa_id_param)) {
    // Fallback: Jika admin tanpa param redirect, tapi laporan punya siswa_id
    $redirect_url_final .= '?siswa_id=' . htmlspecialchars($siswa_id_dari_db_laporan);
}


$koneksi->close();

showAlertAndRedirect(
    $status_type,
    $title_swal,
    $message_swal,
    $redirect_url_final
);