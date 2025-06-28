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
    $id_jurnal_kegiatan = $_POST['id_jurnal_kegiatan'] ?? null;
    $nama_pekerjaan = trim($_POST['nama_pekerjaan'] ?? '');
    $perencanaan_kegiatan = trim($_POST['perencanaan_kegiatan'] ?? '');
    $pelaksanaan_kegiatan = trim($_POST['pelaksanaan_kegiatan'] ?? '');
    $catatan_instruktur = trim($_POST['catatan_instruktur'] ?? '');
    $gambar_lama = $_POST['gambar_lama'] ?? null; // Nama gambar lama
    $siswa_id_original = $_POST['siswa_id_original'] ?? null; // ID siswa pemilik laporan asli
    $redirect_siswa_id = $_POST['redirect_siswa_id'] ?? null; // ID siswa untuk redirect admin

    $gambar_nama_file = $gambar_lama; // Default, gunakan gambar lama
    $upload_dir = 'images/';

    // Validasi ID laporan dan input wajib
    if (empty($id_jurnal_kegiatan) || empty($nama_pekerjaan) || empty($perencanaan_kegiatan) || empty($pelaksanaan_kegiatan) || empty($siswa_id_original)) {
        showAlertAndRedirect(
            'error',
            'Gagal Memperbarui',
            'ID laporan, nama pekerjaan, perencanaan, pelaksanaan, dan ID siswa asli wajib diisi.',
            'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan)
        );
    }

    // LOGIKA OTORISASI SEBELUM UPDATE
    // Siswa hanya bisa mengedit laporan miliknya sendiri
    if ($is_siswa && $siswa_id_original != ($_SESSION['id_siswa'] ?? null)) {
        showAlertAndRedirect(
            'error',
            'Akses Ditolak',
            'Anda tidak diizinkan mengedit laporan siswa lain.',
            'master_tugas_project.php' // Kembali ke daftar laporan siswa
        );
    }

    // Penanganan Upload Gambar Baru
    if (isset($_FILES['gambar_proyek']) && $_FILES['gambar_proyek']['error'] == UPLOAD_ERR_OK) {
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
                'Ekstensi gambar tidak diizinkan. Hanya JPG, JPEG, PNG, GIF.',
                'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan)
            );
        } elseif ($file_size > $max_file_size) {
            showAlertAndRedirect(
                'error',
                'Gagal Upload Gambar',
                'Ukuran gambar terlalu besar. Maksimal 2MB.',
                'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan)
            );
        } else {
            // Hapus gambar lama jika ada dan berhasil diupload gambar baru
            if (!empty($gambar_lama) && file_exists($upload_dir . $gambar_lama)) {
                unlink($upload_dir . $gambar_lama);
            }
            $new_file_name = uniqid('proyek_edit_', true) . '.' . $file_ext;
            $destination_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination_path)) {
                $gambar_nama_file = $new_file_name;
            } else {
                showAlertAndRedirect(
                    'error',
                    'Gagal Upload Gambar',
                    'Gagal memindahkan file gambar baru ke direktori penyimpanan.',
                    'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan)
                );
            }
        }
    }

    // Siapkan query UPDATE menggunakan prepared statement
    $sql = "UPDATE jurnal_kegiatan SET 
                nama_pekerjaan = ?, 
                perencanaan_kegiatan = ?, 
                pelaksanaan_kegiatan = ?, 
                catatan_instruktur = ?, 
                gambar = ?
            WHERE id_jurnal_kegiatan = ? AND siswa_id = ?";

    $stmt = $koneksi->prepare($sql);

    if ($stmt) {
        // Bind parameter: sssssii (5 string, 2 integer)
        $stmt->bind_param(
            "sssssii",
            $nama_pekerjaan,
            $perencanaan_kegiatan,
            $pelaksanaan_kegiatan,
            $catatan_instruktur,
            $gambar_nama_file,
            $id_jurnal_kegiatan,
            $siswa_id_original // Pastikan ini cocok dengan siswa_id di database untuk laporan ini
        );

        if ($stmt->execute()) {
            $message_to_display = 'Laporan tugas proyek berhasil diperbarui!';
            if ($stmt->affected_rows === 0) { // Jika tidak ada baris yang terpengaruh, mungkin tidak ada perubahan data
                $status_type = 'info';
                $title_swal = 'Tidak Ada Perubahan!';
                $message_to_display = 'Tidak ada perubahan yang terdeteksi pada laporan.';
            } else {
                $status_type = 'success';
                $title_swal = 'Berhasil!';
            }

            // Tentukan URL redirect setelah berhasil/info
            $redirect_url_on_finish = 'master_tugas_project.php';
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
                'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan)
            );
        }
        $stmt->close();
    } else {
        showAlertAndRedirect(
            'error',
            'Gagal',
            'Terjadi kesalahan pada persiapan query database: ' . $koneksi->error,
            'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan)
        );
    }

    $koneksi->close();
} else {
    header("Location: master_tugas_project.php"); // Jika tidak diakses melalui POST
    exit();
}