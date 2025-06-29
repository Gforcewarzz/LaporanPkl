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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $siswa_id_untuk_db = null;
    if ($is_siswa && isset($_SESSION['id_siswa'])) {
        $siswa_id_untuk_db = $_SESSION['id_siswa'];
    } elseif ($is_admin) {
        $siswa_id_untuk_db = $_POST['siswa_id'] ?? null; // Admin akan mengirim siswa_id via POST
    }

    $nama_pekerjaan = trim($_POST['nama_pekerjaan'] ?? '');
    $perencanaan_kegiatan = trim($_POST['perencanaan_kegiatan'] ?? '');
    $pelaksanaan_kegiatan = trim($_POST['pelaksanaan_kegiatan'] ?? '');
    $catatan_instruktur = trim($_POST['catatan_instruktur'] ?? '');

    $gambar_nama_file = null;
    $upload_dir = 'images/';

    // Validasi Input Teks (Wajib) dan siswa_id
    if (empty($siswa_id_untuk_db) || empty($nama_pekerjaan) || empty($perencanaan_kegiatan) || empty($pelaksanaan_kegiatan)) {
        $redirect_url_on_fail = 'master_tugas_project_add.php';
        if ($is_admin && !empty($siswa_id_untuk_db)) { // Jika admin input untuk siswa spesifik
            $redirect_url_on_fail .= '?siswa_id=' . htmlspecialchars($siswa_id_untuk_db);
        }
        showAlertAndRedirect(
            'error',
            'Gagal Menyimpan',
            'Semua kolom teks (Nama Kegiatan, Perencanaan, Pelaksanaan) dan ID Siswa wajib diisi.',
            $redirect_url_on_fail
        );
    }

    // Penanganan dan Validasi Upload Gambar (Wajib)
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            showAlertAndRedirect(
                'error',
                'Gagal Upload Gambar',
                'Tidak dapat membuat direktori upload. Periksa izin folder server.',
                'master_tugas_project_add.php'
            );
        }
    }

    if (!isset($_FILES['gambar_proyek']) || $_FILES['gambar_proyek']['error'] == UPLOAD_ERR_NO_FILE) {
        showAlertAndRedirect(
            'error',
            'Gagal Upload Gambar',
            'Bukti kegiatan (foto/screenshot) wajib diunggah.',
            'master_tugas_project_add.php'
        );
    } elseif ($_FILES['gambar_proyek']['error'] != UPLOAD_ERR_OK) {
        showAlertAndRedirect(
            'error',
            'Gagal Upload Gambar',
            'Terjadi kesalahan saat mengunggah bukti kegiatan. Kode error: ' . $_FILES['gambar_proyek']['error'],
            'master_tugas_project_add.php'
        );
    } else {
        $file_tmp = $_FILES['gambar_proyek']['tmp_name'];
        $file_name = $_FILES['gambar_proyek']['name'];
        $file_size = $_FILES['gambar_proyek']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        $max_file_size = 2 * 1024 * 1024; // 2 MB

        if (!in_array($file_ext, $allowed_extensions)) {
            showAlertAndRedirect(
                'error',
                'Gagal Upload Gambar',
                'Ekstensi bukti kegiatan tidak diizinkan. Hanya JPG, JPEG, PNG, GIF.',
                'master_tugas_project_add.php'
            );
        } elseif ($file_size > $max_file_size) {
            showAlertAndRedirect(
                'error',
                'Gagal Upload Gambar',
                'Ukuran bukti kegiatan terlalu besar. Maksimal 2MB.',
                'master_tugas_project_add.php'
            );
        } else {
            $new_file_name = uniqid('proyek_', true) . '.' . $file_ext;
            $destination_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination_path)) {
                $gambar_nama_file = $new_file_name;
            } else {
                showAlertAndRedirect(
                    'error',
                    'Gagal Upload Gambar',
                    'Gagal memindahkan file gambar ke direktori penyimpanan.',
                    'master_tugas_project_add.php'
                );
            }
        }
    }

    // Siapkan query INSERT menggunakan prepared statement
    $sql = "INSERT INTO jurnal_kegiatan
                (siswa_id, nama_pekerjaan, perencanaan_kegiatan, pelaksanaan_kegiatan, catatan_instruktur, gambar, tanggal_laporan)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $koneksi->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(
            "isssss", // i (integer) untuk siswa_id, s (string) untuk 5 kolom berikutnya
            $siswa_id_untuk_db,
            $nama_pekerjaan,
            $perencanaan_kegiatan,
            $pelaksanaan_kegiatan,
            $catatan_instruktur,
            $gambar_nama_file
        );

        if ($stmt->execute()) {
            // Tentukan URL redirect setelah berhasil
            $redirect_url_on_success = 'master_tugas_project.php';
            if ($is_admin && !empty($siswa_id_untuk_db)) { // Jika admin input untuk siswa spesifik
                $redirect_url_on_success .= '?siswa_id=' . htmlspecialchars($siswa_id_untuk_db);
            }
            showAlertAndRedirect(
                'success',
                'Berhasil!',
                'Laporan tugas proyek telah berhasil ditambahkan.',
                $redirect_url_on_success
            );
        } else {
            // Jika ada error database, coba hapus file gambar yang sudah diupload
            if ($gambar_nama_file && file_exists($upload_dir . $gambar_nama_file)) {
                unlink($upload_dir . $gambar_nama_file);
            }
            showAlertAndRedirect(
                'error',
                'Gagal Menyimpan',
                'Terjadi kesalahan saat menyimpan data ke database: ' . $stmt->error,
                'master_tugas_project_add.php'
            );
        }
        $stmt->close();
    } else {
        // Jika ada error prepare statement, coba hapus file gambar yang sudah diupload
        if ($gambar_nama_file && file_exists($upload_dir . $gambar_nama_file)) {
            unlink($upload_dir . $gambar_nama_file);
        }
        showAlertAndRedirect(
            'error',
            'Gagal',
            'Terjadi kesalahan pada persiapan query database: ' . $koneksi->error,
            'master_tugas_project_add.php'
        );
    }

    $koneksi->close();
} else {
    // Jika tidak diakses melalui POST, redirect ke halaman utama
    header("Location: master_tugas_project_add.php");
    exit();
}