<?php
session_start();
include 'partials/db.php';

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru  = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// hanya siswa & admin yang boleh masuk
if (!$is_siswa && !$is_admin) {
    if ($is_guru) {
        header('Location: ../halaman_guru.php');
        exit();
    }
    header('Location: ../login.php');
    exit();
}

// fungsi notifikasi sweetalert
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
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{$icon}',
                    title: '{$title}',
                    text: '{$text}',
                    confirmButtonColor: '#696cff',
                    allowOutsideClick: false,
                    showClass: { popup: 'animate__animated animate__fadeInDown animate__faster' },
                    hideClass: { popup: 'animate__animated animate__fadeOutUp animate__faster' }
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

// ambil data form
$tanggal   = $_POST['tanggal']   ?? '';
$pekerjaan = trim($_POST['pekerjaan'] ?? '');
$catatan   = trim($_POST['catatan'] ?? ''); // opsional

// tentukan siswa_id
$siswa_id_untuk_db = null;
if ($is_siswa) {
    $siswa_id_untuk_db = $_SESSION['id_siswa'] ?? null;
} elseif ($is_admin) {
    $siswa_id_untuk_db = $_POST['selected_siswa_id'] ?? null;
}

// validasi data wajib
if (empty($tanggal) || empty($pekerjaan) || empty($siswa_id_untuk_db)) {
    $redirect_url = 'master_kegiatan_harian_add.php';
    if ($is_admin && !empty($siswa_id_untuk_db)) {
        $redirect_url .= '?siswa_id=' . urlencode($siswa_id_untuk_db);
    }
    showAlertAndRedirect(
        'error',
        'Input Tidak Lengkap!',
        'Tanggal, deskripsi pekerjaan, dan siswa wajib diisi.',
        $redirect_url
    );
}

// query insert (catatan opsional → NULL kalau kosong)
$query = "INSERT INTO jurnal_harian (tanggal, pekerjaan, catatan, siswa_id) VALUES (?, ?, ?, ?)";
$stmt  = $koneksi->prepare($query);

if ($stmt) {
    // kalau kosong, set null biar rapih
    $catatan_db = !empty($catatan) ? $catatan : null;
    $stmt->bind_param("sssi", $tanggal, $pekerjaan, $catatan_db, $siswa_id_untuk_db);

    if ($stmt->execute()) {
        $redirect_url = 'master_kegiatan_harian.php';
        if ($is_admin && !empty($siswa_id_untuk_db)) {
            $redirect_url .= '?siswa_id=' . urlencode($siswa_id_untuk_db);
        }
        showAlertAndRedirect(
            'success',
            'Berhasil!',
            'Laporan harian berhasil disimpan.',
            $redirect_url
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
        'Query database error: ' . $koneksi->error,
        'master_kegiatan_harian_add.php'
    );
}

$koneksi->close();
