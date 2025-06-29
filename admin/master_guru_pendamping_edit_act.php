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

// Fungsi SweetAlert2
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
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp animate__faster'
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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_pembimbing'] ?? null;
    $nama = $_POST['nama_pembimbing'] ?? '';
    $nip = $_POST['nip'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? ''; // PERUBAHAN: Ambil jenis_kelamin
    $password_plain = $_POST['password'] ?? ''; // Bisa kosong jika password tidak diubah

    $status = '';
    $message = '';
    $title = '';

    // Validasi input wajib
    // PERUBAHAN: Tambahkan jenis_kelamin dalam validasi
    if (empty($id) || empty($nama) || empty($nip) || empty($jenis_kelamin)) {
        $status = 'error';
        $title = 'Input Tidak Lengkap!';
        $message = 'ID guru, nama, NIP, dan jenis kelamin wajib diisi.';
    } else {
        // Logika UPDATE dengan atau tanpa password
        if (!empty($password_plain)) {
            $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
            // PERUBAHAN: Tambahkan jenis_kelamin di UPDATE query dan bind_param
            $query = "UPDATE guru_pembimbing SET nama_pembimbing = ?, nip = ?, jenis_kelamin = ?, password = ? WHERE id_pembimbing = ?";
            $stmt = $koneksi->prepare($query);
            if ($stmt) {
                $stmt->bind_param("ssssi", $nama, $nip, $jenis_kelamin, $password_hash, $id); // ssssi: 4 string, 1 integer
            }
        } else {
            // PERUBAHAN: Tambahkan jenis_kelamin di UPDATE query dan bind_param
            $query = "UPDATE guru_pembimbing SET nama_pembimbing = ?, nip = ?, jenis_kelamin = ? WHERE id_pembimbing = ?";
            $stmt = $koneksi->prepare($query);
            if ($stmt) {
                $stmt->bind_param("sssi", $nama, $nip, $jenis_kelamin, $id); // sssi: 3 string, 1 integer
            }
        }

        if ($stmt) {
            if ($stmt->execute()) {
                $status = 'success';
                $title = 'Berhasil!';
                $message = 'Data guru berhasil diperbarui.';
                // Jika tidak ada baris yang terpengaruh, mungkin tidak ada perubahan data
                if ($stmt->affected_rows === 0) {
                    $status = 'info';
                    $title = 'Tidak Ada Perubahan!';
                    $message = 'Tidak ada perubahan yang terdeteksi pada data guru.';
                }
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

    showAlertAndRedirect(
        $status,
        $title,
        $message,
        'master_guru_pendamping.php'
    );
} else {
    header('Location: master_guru_pendamping.php');
    exit;
}
