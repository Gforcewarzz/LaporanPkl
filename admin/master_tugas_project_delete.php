<?php
// 1. Memulai sesi untuk memverifikasi ID siswa yang login
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
        <title>Menghapus Laporan...</title>
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

    // 5. Siapkan query DELETE dengan DUA kondisi WHERE untuk keamanan
    //    Hanya hapus jika 'id_jurnal_kegiatan' DAN 'siswa_id' cocok.
    $sql = "DELETE FROM jurnal_kegiatan WHERE id_jurnal_kegiatan = ? AND siswa_id = ?";

    $stmt = $koneksi->prepare($sql);

    if ($stmt) {
        // Bind parameter ke statement ('ii' berarti dua-duanya integer)
        $stmt->bind_param("ii", $id_jurnal_to_delete, $id_siswa_login);

        // Eksekusi statement
        if ($stmt->execute()) {
            // Periksa apakah ada baris yang benar-benar terhapus
            if ($stmt->affected_rows > 0) {
                // Jika berhasil, tampilkan notifikasi sukses
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
            // Jika terjadi error saat eksekusi
            showAlertAndRedirect(
                'error',
                'Gagal',
                'Terjadi kesalahan pada database saat mencoba menghapus.',
                'master_tugas_project.php'
            );
        }
        $stmt->close();
    } else {
        // Jika statement gagal dipersiapkan
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
?>