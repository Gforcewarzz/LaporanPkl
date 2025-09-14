<?php
// Mulai session untuk membawa pesan notifikasi
session_start();

require_once 'partials/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form_penilaian.php');
    exit();
}

$id_siswa = isset($_POST['id_siswa']) ? (int)$_POST['id_siswa'] : 0;
// Ambil nilai yang tidak kosong saja untuk diproses
$nilai_arr = isset($_POST['nilai']) ? array_filter($_POST['nilai'], function ($val) {
    return $val !== '';
}) : [];


if ($id_siswa === 0 || empty($nilai_arr)) {
    $_SESSION['pesan_notifikasi'] = ['tipe' => 'error', 'judul' => 'Gagal!', 'pesan' => 'Data tidak lengkap. Harap pilih siswa dan isi setidaknya satu nilai.'];
    header('Location: form_penilaian_detail.php?id_siswa=' . $id_siswa); // Kembali ke form detail
    exit();
}

$koneksi->begin_transaction();
try {
    // Siapkan DUA query: satu untuk TP, satu untuk Jurnal
    $stmt_tp = $koneksi->prepare(
        "INSERT INTO nilai_siswa (siswa_id, id_tp, nilai, tanggal_penilaian) VALUES (?, ?, ?, CURDATE())
         ON DUPLICATE KEY UPDATE nilai = VALUES(nilai), tanggal_penilaian = VALUES(tanggal_penilaian)"
    );

    $stmt_jurnal = $koneksi->prepare(
        "INSERT INTO nilai_siswa (siswa_id, jurnal_kegiatan_id, nilai, tanggal_penilaian) VALUES (?, ?, ?, CURDATE())
         ON DUPLICATE KEY UPDATE nilai = VALUES(nilai), tanggal_penilaian = VALUES(tanggal_penilaian)"
    );

    foreach ($nilai_arr as $id => $nilai) {
        // Jika nilai kosong atau tidak valid, lewati (sudah ditangani array_filter, tapi sebagai pengaman tambahan)
        if (trim($nilai) === '' || !is_numeric($nilai)) {
            continue;
        }
        $nilai_float = (float)$nilai;

        // --- PERBAIKAN DI SINI: Tambahkan (string) untuk memastikan $id adalah string ---
        // Cek apakah ID berasal dari jurnal (berawalan 'jurnal_')
        if (strpos((string)$id, 'jurnal_') === 0) {
            // Ini adalah nilai untuk Jurnal
            $id_jurnal = (int)str_replace('jurnal_', '', $id);
            if ($id_jurnal > 0) {
                $stmt_jurnal->bind_param("iid", $id_siswa, $id_jurnal, $nilai_float);
                $stmt_jurnal->execute();
            }
        } else {
            // Ini adalah nilai untuk TP biasa
            $id_tp_int = (int)$id;
            if ($id_tp_int > 0) {
                $stmt_tp->bind_param("iid", $id_siswa, $id_tp_int, $nilai_float);
                $stmt_tp->execute();
            }
        }
    }

    $stmt_tp->close();
    $stmt_jurnal->close();
    $koneksi->commit();

    $_SESSION['pesan_notifikasi'] = ['tipe' => 'success', 'judul' => 'Berhasil!', 'pesan' => 'Nilai siswa telah berhasil disimpan.'];
} catch (mysqli_sql_exception $e) {
    $koneksi->rollback();
    $_SESSION['pesan_notifikasi'] = ['tipe' => 'error', 'judul' => 'Gagal!', 'pesan' => 'Terjadi kesalahan saat menyimpan data. Error: ' . $e->getMessage()];
    error_log("Gagal menyimpan nilai: " . $e->getMessage());
}

$koneksi->close();

// Alihkan kembali ke halaman form detail siswa tersebut, bukan ke laporan umum
header('laporan_penilaian_siswa.php');
exit();