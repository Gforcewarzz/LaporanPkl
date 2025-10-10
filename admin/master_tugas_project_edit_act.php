<?php
session_start();
ob_start(); // Mulai output buffering untuk mencegah error "headers already sent"
include 'partials/db.php';

// --- LOGIKA KEAMANAN & HAK AKSES ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Jika tidak login sebagai salah satu dari peran yang diizinkan, redirect
if (!$is_siswa && !$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// Fungsi SweetAlert2 untuk notifikasi dan redirect
function showAlertAndRedirect($icon, $title, $text, $redirectUrl)
{
    // Hapus semua output yang mungkin sudah ada
    ob_clean();
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Notifikasi</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{$icon}',
                    title: '{$title}',
                    html: '{$text}', // Menggunakan 'html' agar bisa menampilkan baris baru
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id_jurnal_kegiatan = $_POST['id_jurnal_kegiatan'] ?? null;
    $nama_pekerjaan = trim($_POST['nama_pekerjaan'] ?? '');
    $perencanaan_kegiatan = trim($_POST['perencanaan_kegiatan'] ?? '');
    $pelaksanaan_kegiatan = trim($_POST['pelaksanaan_kegiatan'] ?? '');
    $catatan_instruktur = trim($_POST['catatan_instruktur'] ?? ''); // Boleh kosong
    $gambar_lama = $_POST['gambar_lama'] ?? null;
    $siswa_id_original = $_POST['siswa_id_original'] ?? null;
    $redirect_siswa_id = $_POST['redirect_siswa_id'] ?? null;

    // --- VALIDASI INPUT WAJIB YANG SPESIFIK ---
    $errors = [];
    if (empty($nama_pekerjaan)) {
        $errors[] = '&#8226; Nama Tugas / Aktivitas Utama wajib diisi.';
    }
    if (empty($perencanaan_kegiatan)) {
        $errors[] = '&#8226; Perencanaan Kegiatan wajib diisi.';
    }
    if (empty($pelaksanaan_kegiatan)) {
        $errors[] = '&#8226; Pelaksanaan Kegiatan wajib diisi.';
    }

    // Jika ditemukan error pada input
    if (!empty($errors)) {
        $error_message = "Mohon perbaiki kesalahan berikut:<br><br>" . implode("<br>", $errors);
        showAlertAndRedirect('error', 'Gagal', $error_message, 'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan));
    }

    // Validasi kritis untuk data tersembunyi dengan pesan detail
    if (empty($id_jurnal_kegiatan) || empty($siswa_id_original)) {
        $debug_message = "Terjadi kesalahan teknis:<br>";
        if (empty($id_jurnal_kegiatan)) {
            $debug_message .= "&#8226; ID Jurnal tidak terkirim dari form.<br>";
        }
        if (empty($siswa_id_original)) {
            $debug_message .= "&#8226; ID Siswa asli tidak terkirim dari form.<br>";
        }
        $debug_message .= "Mohon kembali dan coba lagi.";

        showAlertAndRedirect('error', 'Data Tidak Lengkap', $debug_message, 'master_tugas_project.php');
    }

    // --- LOGIKA OTORISASI SEBELUM UPDATE ---
    $is_authorized = false;
    if ($is_admin) {
        $is_authorized = true;
    } elseif ($is_siswa) {
        if ($siswa_id_original == ($_SESSION['id_siswa'] ?? null)) {
            $is_authorized = true;
        }
    } elseif ($is_guru) {
        $guru_id_bimbingan = $_SESSION['id_guru_pendamping'] ?? null;
        if ($guru_id_bimbingan !== null) {
            $query_cek = "SELECT id_siswa FROM siswa WHERE id_siswa = ? AND pembimbing_id = ?";
            $stmt_cek = $koneksi->prepare($query_cek);
            if ($stmt_cek) {
                $stmt_cek->bind_param("ii", $siswa_id_original, $guru_id_bimbingan);
                $stmt_cek->execute();
                $stmt_cek->store_result();
                if ($stmt_cek->num_rows > 0) {
                    $is_authorized = true;
                }
                $stmt_cek->close();
            }
        }
    }

    if (!$is_authorized) {
        showAlertAndRedirect('error', 'Akses Ditolak', 'Anda tidak memiliki izin untuk mengubah laporan ini.', 'master_tugas_project.php');
    }

    // --- Penanganan Upload Gambar Baru ---
    $gambar_nama_file = $gambar_lama;
    $upload_dir = 'images/';
    if (isset($_FILES['gambar_proyek']) && $_FILES['gambar_proyek']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar_proyek']['tmp_name'];
        $file_size = $_FILES['gambar_proyek']['size'];
        $file_ext = strtolower(pathinfo($_FILES['gambar_proyek']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 2 * 1024 * 1024; // 2 MB

        if (!in_array($file_ext, $allowed_extensions) || $file_size > $max_file_size) {
            showAlertAndRedirect('error', 'Gagal Upload', 'Format gambar tidak valid atau ukuran melebihi 2MB.', 'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan));
        }

        if (!empty($gambar_lama) && file_exists($upload_dir . $gambar_lama)) {
            unlink($upload_dir . $gambar_lama);
        }

        $new_file_name = uniqid('proyek_edit_', true) . '.' . $file_ext;
        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            $gambar_nama_file = $new_file_name;
        } else {
            showAlertAndRedirect('error', 'Gagal Upload', 'Gagal memindahkan file gambar.', 'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan));
        }
    }

    // --- PERSIAPAN QUERY UPDATE ---
    $sql = "UPDATE jurnal_kegiatan SET 
                nama_pekerjaan = ?, 
                perencanaan_kegiatan = ?, 
                pelaksanaan_kegiatan = ?, 
                catatan_instruktur = ?, 
                gambar = ?
            WHERE id_jurnal_kegiatan = ? AND siswa_id = ?";

    $stmt = $koneksi->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(
            "sssssii",
            $nama_pekerjaan,
            $perencanaan_kegiatan,
            $pelaksanaan_kegiatan,
            $catatan_instruktur,
            $gambar_nama_file,
            $id_jurnal_kegiatan,
            $siswa_id_original
        );

        if ($stmt->execute()) {
            $status_type = 'success';
            $title_swal = 'Berhasil!';
            $message_to_display = 'Laporan tugas proyek berhasil diperbarui!';
            if ($stmt->affected_rows === 0) {
                $status_type = 'info';
                $title_swal = 'Informasi';
                $message_to_display = 'Tidak ada perubahan data yang disimpan.';
            }

            // --- PERUBAHAN DI SINI ---
            // URL redirect sekarang tidak lagi menyertakan ?siswa_id=...
            $redirect_url = 'master_tugas_project.php';
            showAlertAndRedirect($status_type, $title_swal, $message_to_display, $redirect_url);
        } else {
            showAlertAndRedirect('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan data: ' . $stmt->error, 'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan));
        }
        $stmt->close();
    } else {
        showAlertAndRedirect('error', 'Gagal', 'Terjadi kesalahan pada persiapan query: ' . $koneksi->error, 'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan));
    }
    $koneksi->close();
} else {
    header("Location: master_tugas_project.php");
    exit();
}
ob_end_flush(); // Kirim output buffer ke browser