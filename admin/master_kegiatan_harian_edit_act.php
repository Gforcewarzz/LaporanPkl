<?php
session_start();
include 'partials/db.php';

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_siswa && !$is_admin) {

    if ($is_guru) {
        header('Location: ../halaman_guru.php'); // Redirect guru ke halaman guru
        exit();
    } else {
        header('Location: ../login.php'); // Jika tidak login sama sekali, redirect ke halaman login
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
        <title>Notifikasi</title>
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
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{$redirectUrl}';
                    }
                });
            });
        </script>
    </body>
    </html>
HTML;
    exit();
}

$id_jurnal_harian = $_POST['id_jurnal_harian'] ?? null;
$tanggal          = $_POST['tanggal'] ?? '';
$pekerjaan        = htmlspecialchars($_POST['pekerjaan'] ?? '');
$catatan          = htmlspecialchars($_POST['catatan'] ?? '');
$siswa_id_original = $_POST['siswa_id_original'] ?? null; // ID siswa pemilik laporan asli
$redirect_siswa_id = $_POST['redirect_siswa_id'] ?? null; // ID siswa untuk redirect admin

// Validasi ID laporan dan data wajib
if (empty($id_jurnal_harian) || empty($tanggal) || empty($pekerjaan) || empty($catatan) || empty($siswa_id_original)) { // Catatan juga wajib di sini
    $redirect_url_on_fail = 'master_kegiatan_harian_edit.php?id=' . htmlspecialchars($id_jurnal_harian);
    showAlertAndRedirect(
        'error',
        'Input Tidak Lengkap!',
        'ID laporan, tanggal, pekerjaan, catatan, dan ID siswa asli wajib diisi.',
        $redirect_url_on_fail
    );
} else {
    // LOGIKA OTORISASI SEBELUM UPDATE
    // Siswa hanya bisa mengedit laporan miliknya sendiri
    if ($is_siswa && $siswa_id_original != ($_SESSION['id_siswa'] ?? null)) {
        showAlertAndRedirect(
            'error',
            'Akses Ditolak',
            'Anda tidak diizinkan mengedit laporan siswa lain.',
            'master_kegiatan_harian.php' // Kembali ke daftar laporan siswa
        );
    } else {
        $query = "UPDATE jurnal_harian SET tanggal = ?, pekerjaan = ?, catatan = ? WHERE id_jurnal_harian = ? AND siswa_id = ?";
        $stmt = $koneksi->prepare($query);

        if ($stmt) {
            // 'sssii' artinya 3 string (tanggal, pekerjaan, catatan) dan 2 integer (id_jurnal_harian, siswa_id_original)
            $stmt->bind_param("sssii", $tanggal, $pekerjaan, $catatan, $id_jurnal_harian, $siswa_id_original);

            if ($stmt->execute()) {
                $message_to_display = 'Laporan harian berhasil diperbarui!';
                if ($stmt->affected_rows === 0) { // Jika tidak ada baris yang terpengaruh, mungkin tidak ada perubahan data
                    $status_type = 'info';
                    $title_swal = 'Tidak Ada Perubahan!';
                    $message_to_display = 'Tidak ada perubahan yang terdeteksi pada laporan.';
                } else {
                    $status_type = 'success';
                    $title_swal = 'Berhasil!';
                }

                // Tentukan URL redirect setelah berhasil/info
                $redirect_url_on_finish = 'master_kegiatan_harian.php';
                if ($is_admin && !empty($redirect_siswa_id)) { // Jika admin input untuk siswa spesifik
                    $redirect_url_on_finish .= '?siswa_id=' . htmlspecialchars($redirect_siswa_id);
                }

                showAlertAndRedirect(
                    $status_type,
                    $title_swal,
                    $message_to_display,
                    $redirect_url_on_finish
                );
            } else {
                showAlertAndRedirect(
                    'error',
                    'Gagal Menyimpan',
                    'Terjadi kesalahan saat menyimpan data ke database: ' . $stmt->error,
                    'master_kegiatan_harian_edit.php?id=' . htmlspecialchars($id_jurnal_harian)
                );
            }
            $stmt->close();
        } else {
            showAlertAndRedirect(
                'error',
                'Gagal',
                'Terjadi kesalahan pada persiapan query database: ' . $koneksi->error,
                'master_kegiatan_harian_edit.php?id=' . htmlspecialchars($id_jurnal_harian)
            );
        }
    }
}
$koneksi->close();
