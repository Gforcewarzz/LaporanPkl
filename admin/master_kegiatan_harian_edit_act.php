<?php
session_start();
ob_start(); // Memulai output buffering untuk mengatasi masalah header
date_default_timezone_set('Asia/Jakarta');

include 'partials/db.php';

// --- FUNGSI & KEAMANAN ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

// Akses hanya untuk siswa & admin
if (!$is_siswa && !$is_admin) {
    header('Location: ../login.php');
    exit();
}

// Fungsi notifikasi menggunakan Session yang lebih andal
function setAlertAndRedirect($icon, $title, $text, $redirectUrl)
{
    // Simpan pesan ke session
    $_SESSION['alert_message'] = $text;
    $_SESSION['alert_type'] = $icon;
    $_SESSION['alert_title'] = $title;
    // Lakukan redirect
    header("Location: " . $redirectUrl);
    exit();
}

// 1. Ambil semua data dari form
$id_jurnal_harian = $_POST['id_jurnal_harian'] ?? null;
$tanggal          = $_POST['tanggal'] ?? '';
$pekerjaan        = trim($_POST['pekerjaan'] ?? '');
$catatan          = trim($_POST['catatan'] ?? ''); // Catatan bersifat opsional
$siswa_id_original = $_POST['siswa_id_original'] ?? null;

// URL untuk redirect jika terjadi error, agar tidak kehilangan ID
$redirect_url_on_error = 'master_kegiatan_harian_edit.php?id=' . urlencode($id_jurnal_harian);

// 2. [PERBAIKAN] Validasi hanya untuk data wajib. 'catatan' tidak ikut divalidasi.
if (empty($id_jurnal_harian) || empty($tanggal) || empty($pekerjaan) || empty($siswa_id_original)) {
    setAlertAndRedirect('error', 'Gagal!', 'Terjadi kesalahan. Data wajib tidak boleh kosong.', $redirect_url_on_error);
}

// 3. Otorisasi: Siswa hanya boleh mengedit laporannya sendiri
if ($is_siswa && $siswa_id_original != ($_SESSION['id_siswa'] ?? null)) {
    setAlertAndRedirect('error', 'Akses Ditolak', 'Anda tidak diizinkan mengedit laporan ini.', 'master_kegiatan_harian.php');
}

// 4. Cek duplikasi jika tanggal diubah
// Query ini akan mencari apakah ada laporan LAIN (id_jurnal_harian != ?) 
// yang dimiliki siswa yang sama pada tanggal yang baru.
$stmt_check = $koneksi->prepare("SELECT id_jurnal_harian FROM jurnal_harian WHERE siswa_id = ? AND tanggal = ? AND id_jurnal_harian != ?");
$stmt_check->bind_param("isi", $siswa_id_original, $tanggal, $id_jurnal_harian);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $stmt_check->close();
    setAlertAndRedirect('warning', 'Gagal Update!', 'Sudah ada laporan lain di tanggal yang Anda pilih.', $redirect_url_on_error);
}
$stmt_check->close();

// 5. Jika semua validasi lolos, lanjutkan proses UPDATE ke database
$stmt_update = $koneksi->prepare("UPDATE jurnal_harian SET tanggal = ?, pekerjaan = ?, catatan = ? WHERE id_jurnal_harian = ? AND siswa_id = ?");

if ($stmt_update) {
    // Jika catatan kosong, simpan sebagai NULL di database
    $catatan_db = !empty($catatan) ? $catatan : NULL;

    $stmt_update->bind_param("sssii", $tanggal, $pekerjaan, $catatan_db, $id_jurnal_harian, $siswa_id_original);

    if ($stmt_update->execute()) {
        $redirect_url_success = 'master_kegiatan_harian.php';
        if ($is_admin) {
            // Arahkan admin kembali ke daftar jurnal siswa yang bersangkutan
            $redirect_url_success .= '?siswa_id=' . urlencode($siswa_id_original);
        }

        // Cek apakah ada baris yang benar-benar berubah
        if ($stmt_update->affected_rows > 0) {
            setAlertAndRedirect('success', 'Berhasil!', 'Laporan harian berhasil diperbarui.', $redirect_url_success);
        } else {
            setAlertAndRedirect('info', 'Tidak Ada Perubahan', 'Tidak ada data yang diubah dari sebelumnya.', $redirect_url_success);
        }
    } else {
        setAlertAndRedirect('error', 'Gagal Menyimpan', 'Terjadi kesalahan pada database saat update.', $redirect_url_on_error);
    }
    $stmt_update->close();
} else {
    setAlertAndRedirect('error', 'Server Error', 'Gagal mempersiapkan query database.', $redirect_url_on_error);
}

$koneksi->close();
ob_end_flush(); // Mengakhiri dan mengirim output buffer