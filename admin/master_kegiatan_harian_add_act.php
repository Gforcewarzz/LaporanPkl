<?php
session_start();
ob_start(); // Mulai output buffering untuk mencegah error "headers already sent"
date_default_timezone_set('Asia/Jakarta');

include 'partials/db.php';

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

// Keamanan: Hanya siswa dan admin yang boleh memproses
if (!$is_siswa && !$is_admin) {
    header('Location: ../login.php');
    exit();
}

// Fungsi notifikasi SweetAlert yang rapi
function showAlertAndRedirect($icon, $title, $text, $redirectUrl)
{
    $_SESSION['alert_message'] = $text;
    $_SESSION['alert_type'] = $icon;
    $_SESSION['alert_title'] = $title;
    header("Location: " . $redirectUrl);
    exit();
}

// 1. Ambil data dari form
$tanggal   = $_POST['tanggal']   ?? '';
$pekerjaan = trim($_POST['pekerjaan'] ?? '');
$catatan   = trim($_POST['catatan'] ?? '');

// 2. Tentukan ID siswa yang akan diinput
$siswa_id_untuk_db = null;
if ($is_siswa) {
    $siswa_id_untuk_db = $_SESSION['id_siswa'] ?? null;
} elseif ($is_admin) {
    $siswa_id_untuk_db = $_POST['selected_siswa_id'] ?? null;
}

// Buat URL redirect jika terjadi error, agar filter siswa (untuk admin) tidak hilang
$redirect_url_on_error = 'master_kegiatan_harian_add.php';
if ($is_admin && !empty($siswa_id_untuk_db)) {
    $redirect_url_on_error .= '?siswa_id=' . urlencode($siswa_id_untuk_db);
}

// 3. Validasi dasar: pastikan data penting tidak kosong
if (empty($tanggal) || empty($pekerjaan) || empty($siswa_id_untuk_db)) {
    showAlertAndRedirect('error', 'Input Tidak Lengkap!', 'Pastikan tanggal, deskripsi pekerjaan, dan siswa sudah terisi.', $redirect_url_on_error);
}

// 4. [MODIFIKASI] Cek apakah sudah ada laporan di tanggal yang sama untuk siswa ini
$check_query = "SELECT id_jurnal_harian FROM jurnal_harian WHERE siswa_id = ? AND tanggal = ?";
$stmt_check = $koneksi->prepare($check_query);
$stmt_check->bind_param("is", $siswa_id_untuk_db, $tanggal);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    // Jika sudah ada, tampilkan pesan error
    showAlertAndRedirect('warning', 'Gagal!', 'Laporan untuk tanggal ' . date('d F Y', strtotime($tanggal)) . ' sudah ada.', $redirect_url_on_error);
}
$stmt_check->close();

// 5. Jika semua validasi lolos, lanjutkan proses simpan data
$insert_query = "INSERT INTO jurnal_harian (tanggal, pekerjaan, catatan, siswa_id) VALUES (?, ?, ?, ?)";
$stmt_insert = $koneksi->prepare($insert_query);

if ($stmt_insert) {
    $catatan_db = !empty($catatan) ? $catatan : NULL; // Simpan NULL jika catatan kosong
    $stmt_insert->bind_param("sssi", $tanggal, $pekerjaan, $catatan_db, $siswa_id_untuk_db);

    if ($stmt_insert->execute()) {
        $redirect_url_success = 'master_kegiatan_harian.php';
        // Arahkan admin kembali ke halaman detail siswa tersebut jika ada
        if ($is_admin && !empty($siswa_id_untuk_db)) {
            $redirect_url_success .= '?siswa_id=' . urlencode($siswa_id_untuk_db);
        }
        showAlertAndRedirect('success', 'Berhasil!', 'Laporan harian berhasil disimpan.', $redirect_url_success);
    } else {
        showAlertAndRedirect('error', 'Gagal Menyimpan', 'Terjadi kesalahan pada database: ' . $stmt_insert->error, $redirect_url_on_error);
    }
    $stmt_insert->close();
} else {
    showAlertAndRedirect('error', 'Gagal', 'Query database error: ' . $koneksi->error, $redirect_url_on_error);
}

$koneksi->close();
ob_end_flush(); // Kirim output buffer