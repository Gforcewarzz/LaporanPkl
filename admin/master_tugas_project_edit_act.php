<?php
// 1. Mulai sesi untuk memverifikasi ID siswa yang login
session_start();

// 2. Sertakan file koneksi database
include 'partials/db.php';

/**
 * Fungsi untuk menampilkan SweetAlert dan melakukan redirect via JavaScript.
 *
 * @param string $icon    Ikon alert ('success', 'error', 'warning', 'info')
 * @param string $title   Judul alert
 * @param string $text    Teks atau pesan dalam alert
 * @param string $redirectUrl URL tujuan setelah alert ditutup
 */
function showAlertAndRedirect($icon, $title, $text, $redirectUrl) {
    ob_clean(); // Hapus output buffer sebelumnya
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Memperbarui Laporan...</title>
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

// Pastikan skrip ini diakses melalui metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Ambil semua data dari form
    $id_jurnal_kegiatan = $_POST['id_jurnal_kegiatan'] ?? 0;
    $nama_pekerjaan = trim($_POST['nama_pekerjaan'] ?? '');
    $perencanaan_kegiatan = trim($_POST['perencanaan_kegiatan'] ?? '');
    $pelaksanaan_kegiatan = trim($_POST['pelaksanaan_kegiatan'] ?? '');
    $catatan_instruktur = trim($_POST['catatan_instruktur'] ?? '');
    
    // Ambil ID siswa yang sedang login dari sesi untuk verifikasi
    $id_siswa_login = $_SESSION['id_siswa'];

    // Validasi dasar: Pastikan field yang wajib diisi tidak kosong
    if (empty($id_jurnal_kegiatan) || empty($nama_pekerjaan) || empty($perencanaan_kegiatan) || empty($pelaksanaan_kegiatan)) {
        showAlertAndRedirect(
            'error',
            'Gagal',
            'Semua kolom wajib diisi. Silakan periksa kembali.',
            // Kembali ke halaman edit dengan ID yang benar
            'master_tugas_project_edit.php?id=' . $id_jurnal_kegiatan
        );
    }

    // 5. Siapkan query UPDATE dengan DUA kondisi WHERE untuk keamanan
    //    Hanya update jika 'id_jurnal_kegiatan' DAN 'siswa_id' cocok.
    $sql = "UPDATE jurnal_kegiatan SET 
                nama_pekerjaan = ?, 
                perencanaan_kegiatan = ?, 
                pelaksanaan_kegiatan = ?, 
                catatan_instruktur = ? 
            WHERE 
                id_jurnal_kegiatan = ? AND siswa_id = ?";

    $stmt = $koneksi->prepare($sql);

    if ($stmt) {
        // Bind parameter ke statement ('ssssii' -> 4 string, 2 integer)
        $stmt->bind_param("ssssii", 
            $nama_pekerjaan, 
            $perencanaan_kegiatan, 
            $pelaksanaan_kegiatan, 
            $catatan_instruktur,
            $id_jurnal_kegiatan,
            $id_siswa_login
        );

        // Eksekusi statement
        if ($stmt->execute()) {
            // Jika berhasil, tampilkan notifikasi sukses
             showAlertAndRedirect(
                'success',
                'Berhasil!',
                'Laporan tugas proyek telah berhasil diperbarui.',
                'master_tugas_project.php' // Arahkan kembali ke daftar laporan
            );
        } else {
            // Jika terjadi error saat eksekusi
            showAlertAndRedirect(
                'error',
                'Gagal',
                'Terjadi kesalahan pada database saat mencoba memperbarui data.',
                'master_tugas_project_edit.php?id=' . $id_jurnal_kegiatan
            );
        }
        $stmt->close();
    } else {
        // Jika statement gagal dipersiapkan
        showAlertAndRedirect(
            'error',
            'Gagal',
            'Terjadi kesalahan pada persiapan query database.',
            'master_tugas_project_edit.php?id=' . $id_jurnal_kegiatan
        );
    }
    
    $koneksi->close();

} else {
    // Jika tidak diakses melalui POST, redirect ke halaman utama
    header("Location: index.php");
    exit();
}
?>