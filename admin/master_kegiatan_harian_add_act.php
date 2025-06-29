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

function showAlertAndRedirect($icon, $title, $text, $redirectUrl)
{
    ob_clean();
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

// Ambil data dari form dengan aman
$tanggal   = $_POST['tanggal'] ?? '';
$pekerjaan = htmlspecialchars($_POST['pekerjaan'] ?? '');
$catatan   = htmlspecialchars($_POST['catatan'] ?? '');

// PENENTUAN SISWA_ID UNTUK DATABASE
$siswa_id_untuk_db = null;

if ($is_siswa) {
    $siswa_id_untuk_db = $_SESSION['id_siswa'] ?? null;
} elseif ($is_admin) {
    $siswa_id_untuk_db = $_POST['selected_siswa_id'] ?? null;
}

// Validasi data
if (empty($tanggal) || empty($pekerjaan) || empty($catatan) || empty($siswa_id_untuk_db)) { // Catatan juga wajib
    $redirect_url_on_fail = 'master_kegiatan_harian_add.php';
    if ($is_admin && !empty($siswa_id_untuk_db)) {
        $redirect_url_on_fail .= '?siswa_id=' . htmlspecialchars($siswa_id_untuk_db);
    }
    showAlertAndRedirect(
        'error',
        'Input Tidak Lengkap!',
        'Tanggal, deskripsi pekerjaan, catatan, dan ID siswa wajib diisi. Mohon lengkapi semua informasi.',
        $redirect_url_on_fail
    );
} else {
    // Gunakan prepared statement untuk keamanan
    $query = "INSERT INTO jurnal_harian (tanggal, pekerjaan, catatan, siswa_id) VALUES (?, ?, ?, ?)";

    $stmt = $koneksi->prepare($query);

    if ($stmt) {
        $stmt->bind_param("sssi", $tanggal, $pekerjaan, $catatan, $siswa_id_untuk_db);

        if ($stmt->execute()) {
            $redirect_url_on_success = 'master_kegiatan_harian.php';
            if ($is_admin && !empty($siswa_id_untuk_db)) {
                $redirect_url_on_success .= '?siswa_id=' . htmlspecialchars($siswa_id_untuk_db);
            }
            showAlertAndRedirect(
                'success',
                'Berhasil!',
                'Laporan harian berhasil disimpan!',
                $redirect_url_on_success
            );
        } else {
            showAlertAndRedirect(
                'error',
                'Gagal Menyimpan',
                'Terjadi kesalahan saat menyimpan data: ' . $stmt->error,
                'master_kegiatan_harian_add.php'
            );
        }
        $stmt->close();
    } else {
        showAlertAndRedirect(
            'error',
            'Gagal',
            'Terjadi kesalahan pada persiapan query database: ' . $koneksi->error,
            'master_kegiatan_harian_add.php'
        );
    }
}
$koneksi->close(); // Tutup koneksi database