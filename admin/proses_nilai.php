<?php
// Mulai session untuk membawa pesan notifikasi
session_start();

require_once 'partials/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika akses bukan POST, kembalikan ke form
    header('Location: form_penilaian.php');
    exit();
}

$id_siswa = isset($_POST['id_siswa']) ? (int)$_POST['id_siswa'] : 0;
$nilai_arr = isset($_POST['nilai']) ? $_POST['nilai'] : [];

if ($id_siswa === 0 || empty($nilai_arr)) {
    // Jika data tidak lengkap, simpan pesan error dan kembalikan ke form
    $_SESSION['pesan_notifikasi'] = [
        'tipe' => 'error',
        'judul' => 'Gagal!',
        'pesan' => 'Data tidak lengkap. Harap pilih siswa dan isi semua nilai.'
    ];
    header('Location: form_penilaian.php');
    exit();
}

$koneksi->begin_transaction();
try {
    // Siapkan query sekali di luar loop untuk efisiensi
    $stmt = $koneksi->prepare(
        "INSERT INTO nilai_siswa (siswa_id, id_tp, nilai, tanggal_penilaian) VALUES (?, ?, ?, CURDATE())
         ON DUPLICATE KEY UPDATE nilai = VALUES(nilai), tanggal_penilaian = VALUES(tanggal_penilaian)"
    );

    foreach ($nilai_arr as $id_tp => $nilai) {
        $id_tp_int = (int)$id_tp;
        $nilai_float = (float)$nilai;
        
        $stmt->bind_param("iid", $id_siswa, $id_tp_int, $nilai_float);
        $stmt->execute();
    }
    
    $stmt->close();
    $koneksi->commit();
    
    // Buat pesan sukses di session
    $_SESSION['pesan_notifikasi'] = [
        'tipe' => 'success',
        'judul' => 'Berhasil!',
        'pesan' => 'Nilai siswa telah berhasil disimpan.'
    ];

} catch (mysqli_sql_exception $e) {
    $koneksi->rollback();
    // Buat pesan error di session
    $_SESSION['pesan_notifikasi'] = [
        'tipe' => 'error',
        'judul' => 'Gagal!',
        'pesan' => 'Terjadi kesalahan saat menyimpan data ke database.'
    ];
    // Log error asli untuk developer (opsional)
    error_log("Gagal menyimpan nilai: " . $e->getMessage());
}

$koneksi->close();

// Alihkan ke halaman laporan nilai
header('Location: laporan_penilaian_siswa.php');
exit();
?>