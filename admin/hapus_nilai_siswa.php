<?php
session_start();
require_once 'partials/db.php';

// Keamanan: Hanya admin dan guru yang bisa menghapus nilai
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// Ambil ID siswa dari URL
$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;

if ($siswa_id > 0) {
    // Siapkan query untuk menghapus semua nilai berdasarkan siswa_id
    $query = "DELETE FROM nilai_siswa WHERE siswa_id = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $siswa_id);

    if ($stmt->execute()) {
        // Jika berhasil, buat pesan sukses
        $_SESSION['pesan_notifikasi'] = [
            'tipe' => 'success',
            'judul' => 'Berhasil!',
            'pesan' => 'Semua data nilai untuk siswa yang dipilih telah berhasil dihapus.'
        ];
    } else {
        // Jika gagal, buat pesan error
        $_SESSION['pesan_notifikasi'] = [
            'tipe' => 'error',
            'judul' => 'Gagal!',
            'pesan' => 'Terjadi kesalahan saat menghapus data nilai.'
        ];
    }
    $stmt->close();
} else {
    // Jika ID tidak valid
    $_SESSION['pesan_notifikasi'] = [
        'tipe' => 'error',
        'judul' => 'Gagal!',
        'pesan' => 'ID Siswa tidak valid untuk penghapusan nilai.'
    ];
}

$koneksi->close();

// Kembalikan pengguna ke halaman laporan
header('Location: laporan_penilaian_siswa.php');
exit();
?>