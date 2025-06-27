<?php
// 1. Memulai sesi untuk memverifikasi ID siswa yang login
session_start();
// --- LOGIKA KEAMANAN HALAMAN SISWA ---

// 1. Definisikan dulu role yang sedang login untuk mempermudah pembacaan kode.
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

// 2. Aturan utama: Cek jika pengguna BUKAN Siswa DAN BUKAN Admin.
// Jika salah satu dari mereka (siswa atau admin) login, kondisi ini akan false dan halaman akan lanjut dimuat.
if (!$is_siswa && !$is_admin) {

    // 3. Jika tidak diizinkan, baru kita cek siapa pengguna ini.
    // Apakah dia seorang Guru yang mencoba masuk?
    if (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        // Jika benar guru, kembalikan ke halaman dasbor guru.
        header('Location: ../halaman_guru.php'); // Sesuaikan path jika perlu
        exit();
    }
    // 4. Jika bukan siapa-siapa dari role di atas, artinya pengguna belum login.
    else {
        // Arahkan paksa ke halaman login.
        header('Location: ../login.php'); // Sesuaikan path jika perlu
        exit();
    }
}

// 2. Sertakan file koneksi database
include 'partials/db.php'; // Pastikan path ini benar

/**
 * Fungsi untuk menampilkan SweetAlert dan melakukan redirect via JavaScript.
 *
 * @param string $icon        Ikon alert ('success', 'error', 'warning', 'info')
 * @param string $title       Judul alert
 * @param string $text        Teks atau pesan dalam alert
 * @param string $redirectUrl URL tujuan setelah alert ditutup
 */
function showAlertAndRedirect($icon, $title, $text, $redirectUrl)
{
    ob_clean(); // Hapus output buffer sebelumnya
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

// 3. Keamanan: Verifikasi apakah pengguna sudah login
if (!isset($_SESSION['id_siswa'])) {
    showAlertAndRedirect(
        'error',
        'Akses Ditolak!',
        'Anda harus login terlebih dahulu untuk melakukan tindakan ini.',
        'login.php' // Arahkan ke halaman login
    );
}

// 4. Keamanan: Pastikan ID laporan dikirim melalui GET dan tidak kosong
if (isset($_GET['id']) && !empty($_GET['id'])) {

    // Ambil ID dari URL dan pastikan itu adalah integer
    $id_jurnal_to_delete = intval($_GET['id']);

    // Ambil ID siswa yang sedang login dari sesi
    $id_siswa_login = $_SESSION['id_siswa'];

    // Direktori tempat gambar disimpan (relatif dari file ini di folder admin/)
    // Jika master_tugas_project_delete.php ada di admin/, dan images/ ada di root, maka pathnya images/
    $upload_dir = 'images/';

    // --- LANGKAH BARU: Ambil nama file gambar sebelum menghapus data laporan ---
    $gambar_nama_file = null;
    $sql_get_gambar = "SELECT gambar FROM jurnal_kegiatan WHERE id_jurnal_kegiatan = ? AND siswa_id = ?";
    $stmt_get_gambar = $koneksi->prepare($sql_get_gambar);
    if ($stmt_get_gambar) {
        $stmt_get_gambar->bind_param("ii", $id_jurnal_to_delete, $id_siswa_login);
        $stmt_get_gambar->execute();
        $result_get_gambar = $stmt_get_gambar->get_result();
        if ($result_get_gambar->num_rows > 0) {
            $row_gambar = $result_get_gambar->fetch_assoc();
            $gambar_nama_file = $row_gambar['gambar'];
        }
        $stmt_get_gambar->close();
    } else {
        // Log error jika prepared statement gagal
        error_log("Failed to prepare get_gambar statement: " . $koneksi->error);
        // Lanjutkan tanpa menghapus file jika tidak dapat mengambil namanya
    }

    // 5. Siapkan query DELETE dengan DUA kondisi WHERE untuk keamanan
    //    Hanya hapus jika 'id_jurnal_kegiatan' DAN 'siswa_id' cocok.
    $sql_delete_record = "DELETE FROM jurnal_kegiatan WHERE id_jurnal_kegiatan = ? AND siswa_id = ?";
    $stmt_delete_record = $koneksi->prepare($sql_delete_record);

    if ($stmt_delete_record) {
        // Bind parameter ke statement ('ii' berarti dua-duanya integer)
        $stmt_delete_record->bind_param("ii", $id_jurnal_to_delete, $id_siswa_login);

        // Eksekusi statement DELETE
        if ($stmt_delete_record->execute()) {
            // Periksa apakah ada baris yang benar-benar terhapus
            if ($stmt_delete_record->affected_rows > 0) {
                // --- LANGKAH BARU: Hapus file gambar dari server jika ada ---
                if (!empty($gambar_nama_file)) {
                    $file_path_to_delete = $upload_dir . $gambar_nama_file;
                    if (file_exists($file_path_to_delete)) {
                        if (unlink($file_path_to_delete)) {
                            // File berhasil dihapus
                            error_log("Gambar berhasil dihapus dari server: " . $file_path_to_delete);
                        } else {
                            // Gagal menghapus file (mungkin karena izin)
                            error_log("Gagal menghapus gambar dari server: " . $file_path_to_delete . ". Periksa izin.");
                        }
                    } else {
                        // File tidak ditemukan di direktori, tetapi tercatat di DB
                        error_log("Gambar tidak ditemukan di direktori tetapi ada di DB: " . $file_path_to_delete);
                    }
                }

                showAlertAndRedirect(
                    'success',
                    'Berhasil!',
                    'Laporan proyek telah berhasil dihapus.',
                    'master_tugas_project.php' // Arahkan kembali ke daftar laporan
                );
            } else {
                // Jika tidak ada baris yang terhapus (ID tidak cocok atau bukan milik siswa tsb)
                showAlertAndRedirect(
                    'error',
                    'Gagal Hapus',
                    'Laporan tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.',
                    'master_tugas_project.php'
                );
            }
        } else {
            // Jika terjadi error saat eksekusi DELETE
            error_log("Error executing delete statement: " . $stmt_delete_record->error);
            showAlertAndRedirect(
                'error',
                'Gagal',
                'Terjadi kesalahan pada database saat mencoba menghapus.',
                'master_tugas_project.php'
            );
        }
        $stmt_delete_record->close(); // Tutup statement DELETE
    } else {
        // Jika statement DELETE gagal dipersiapkan
        error_log("Failed to prepare delete statement: " . $koneksi->error);
        showAlertAndRedirect(
            'error',
            'Gagal',
            'Terjadi kesalahan pada persiapan query database.',
            'master_tugas_project.php'
        );
    }

    $koneksi->close();
} else {
    // Jika tidak ada ID yang dikirim di URL
    showAlertAndRedirect(
        'warning',
        'Akses Tidak Valid',
        'Tidak ada ID laporan yang dipilih untuk dihapus.',
        'master_tugas_project.php'
    );
}