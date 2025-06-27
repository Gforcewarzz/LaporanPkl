<?php
// Sertakan file koneksi database Anda
include 'partials/db.php';
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
/**
 * Fungsi untuk menampilkan SweetAlert dan melakukan redirect via JavaScript.
 *
 * @param string $icon    Ikon alert ('success', 'error', 'warning', 'info')
 * @param string $title   Judul alert
 * @param string $text    Teks atau pesan dalam alert
 * @param string $redirectUrl URL tujuan setelah alert ditutup
 */
function showAlertAndRedirect($icon, $title, $text, $redirectUrl) {
    // Hentikan output sebelumnya jika ada
    ob_clean();

    echo <<<HTML
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Menyimpan Laporan...</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            // Menunggu dokumen siap, lalu tampilkan alert
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{$icon}',
                    title: '{$title}',
                    text: '{$text}',
                    confirmButtonColor: '#696cff',
                    allowOutsideClick: false // Mencegah pengguna menutup alert dengan klik di luar
                }).then((result) => {
                    // Jika pengguna menekan tombol konfirmasi, redirect
                    if (result.isConfirmed) {
                        window.location.href = '{$redirectUrl}';
                    }
                });
            });
        </script>
    </body>
    </html>
HTML;
    // Hentikan eksekusi skrip setelah menampilkan alert
    exit();
}

// Pastikan skrip ini diakses melalui metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Ambil data dari form
    $siswa_id = $_POST['siswa_id'] ?? null;
    $nama_pekerjaan = trim($_POST['nama_pekerjaan'] ?? '');
    $perencanaan_kegiatan = trim($_POST['perencanaan_kegiatan'] ?? '');
    $pelaksanaan_kegiatan = trim($_POST['pelaksanaan_kegiatan'] ?? '');
    $catatan_instruktur = trim($_POST['catatan_instruktur'] ?? '');

    // Validasi dasar
    if (empty($siswa_id) || empty($nama_pekerjaan) || empty($perencanaan_kegiatan) || empty($pelaksanaan_kegiatan)) {
        showAlertAndRedirect(
            'error',
            'Gagal',
            'Data wajib (Nama Proyek, Perencanaan, Pelaksanaan) tidak boleh kosong.',
            'master_tugas_project_add.php' // Kembali ke form
        );
    }

    // Siapkan query INSERT menggunakan prepared statement
    $sql = "INSERT INTO jurnal_kegiatan 
                (siswa_id, nama_pekerjaan, perencanaan_kegiatan, pelaksanaan_kegiatan, catatan_instruktur) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $koneksi->prepare($sql);

    if ($stmt) {
        // Bind parameter
        $stmt->bind_param("issss", $siswa_id, $nama_pekerjaan, $perencanaan_kegiatan, $pelaksanaan_kegiatan, $catatan_instruktur);

        // Eksekusi statement
        if ($stmt->execute()) {
            // Jika berhasil
            showAlertAndRedirect(
                'success',
                'Berhasil!',
                'Laporan tugas proyek telah berhasil ditambahkan.',
                'master_tugas_project.php' // Arahkan ke halaman daftar laporan
            );
        } else {
            // Jika gagal eksekusi
            showAlertAndRedirect(
                'error',
                'Gagal Menyimpan',
                'Terjadi kesalahan saat menyimpan data.',
                'master_tugas_project_add.php' // Kembali ke form
            );
        }
        $stmt->close();
    } else {
        // Jika statement gagal dipersiapkan
        showAlertAndRedirect(
            'error',
            'Gagal',
            'Terjadi kesalahan pada persiapan query database.',
            'master_tugas_project_add.php' // Kembali ke form
        );
    }

    $koneksi->close();
    
} else {
    // Jika tidak diakses melalui POST, redirect ke halaman utama
    header("Location: index.php");
    exit();
}
?>