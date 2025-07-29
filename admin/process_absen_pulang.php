<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Atur zona waktu ke Asia/Jakarta

include 'partials/db.php'; // Sertakan file koneksi database

// --- Logika Keamanan Halaman ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$siswa_id = $_SESSION['id_siswa'] ?? null;

// Jika bukan siswa atau ID siswa tidak ada, redirect ke login
if (!$is_siswa || empty($siswa_id)) {
    $_SESSION['alert_message'] = 'Anda harus login sebagai siswa untuk mengakses fitur ini.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Akses Ditolak!';
    // Tutup koneksi sebelum redirect
    if ($koneksi) {
        $koneksi->close();
    }
    header('Location: ../login.php');
    exit();
}

// Inisialisasi variabel untuk pesan notifikasi
$message = '';
$message_type = '';
$message_title = '';

$current_date = date('Y-m-d'); // Tanggal hari ini
$current_time_full = date('H:i:s'); // Waktu saat ini (HH:MM:SS)

// --- MODIFIKASI DIMULAI DI SINI ---
// Tentukan tanggal yang mungkin untuk absen masuk
$possible_checkin_dates = [$current_date]; // Default: hanya tanggal hari ini

// Jika waktu saat ini masih dini hari (misalnya sebelum jam 06:00 pagi),
// maka mungkin absen masuknya dari hari sebelumnya.
// Anda bisa menyesuaikan jam '06:00:00' sesuai kebutuhan shift malam Anda.
if (strtotime($current_time_full) < strtotime('06:00:00')) {
    $yesterday_date = date('Y-m-d', strtotime('-1 day', strtotime($current_date)));
    array_unshift($possible_checkin_dates, $yesterday_date); // Tambahkan tanggal kemarin di awal
}

// --- B. Cek Status Absensi Datang Hari Ini atau Kemarin (jika dini hari) ---
// PERBAHAN: Gunakan IN clause untuk tanggal
$placeholders = implode(',', array_fill(0, count($possible_checkin_dates), '?'));
$query_check_absen = "SELECT status_absen, jam_pulang, tanggal_absen FROM absensi_siswa WHERE siswa_id = ? AND tanggal_absen IN ($placeholders) ORDER BY tanggal_absen DESC LIMIT 1";
$stmt_check_absen = $koneksi->prepare($query_check_absen);

if ($stmt_check_absen) {
    // Bangun string tipe untuk bind_param
    $types = 'i' . str_repeat('s', count($possible_checkin_dates));
    // Gabungkan siswa_id dengan array tanggal
    $bind_params = array_merge([$siswa_id], $possible_checkin_dates);

    // Gunakan call_user_func_array untuk bind_param
    $stmt_check_absen->bind_param($types, ...$bind_params);
    $stmt_check_absen->execute();
    $result_check_absen = $stmt_check_absen->get_result();

    if ($result_check_absen->num_rows > 0) {
        $data_absen = $result_check_absen->fetch_assoc();
        $status_absen_hari_ini = $data_absen['status_absen'];
        $jam_pulang_sudah_ada = !empty($data_absen['jam_pulang']);
        $tanggal_absen_ditemukan = $data_absen['tanggal_absen']; // Tanggal absen masuk yang ditemukan

        if ($status_absen_hari_ini !== 'Hadir') {
            $message = 'Anda tidak absen masuk sebagai Hadir untuk shift ini. Tidak bisa absen pulang.';
            $message_type = 'error';
            $message_title = 'Status Absen Tidak Sesuai!';
        } elseif ($jam_pulang_sudah_ada && $tanggal_absen_ditemukan == $current_date) {
            // Jika sudah absen pulang untuk TANGGAL HARI INI
            $message = 'Anda sudah absen pulang untuk hari ini.';
            $message_type = 'warning';
            $message_title = 'Sudah Absen Pulang!';
        } elseif ($jam_pulang_sudah_ada && $tanggal_absen_ditemukan == $yesterday_date && strtotime($current_time_full) < strtotime('06:00:00')) {
            // Jika sudah absen pulang untuk TANGGAL KEMARIN dan masih dini hari (mencegah double pulang jika sudah absen masuk lagi hari ini)
            $message = 'Anda sudah absen pulang untuk shift kemarin.';
            $message_type = 'warning';
            $message_title = 'Sudah Absen Pulang!';
        } else {
            // --- C. Proses Update Jam Pulang ---
            $jam_pulang_sekarang = date('H:i:s'); // Ambil waktu pulang saat ini
            // UPDATE berdasarkan tanggal absen masuk yang ditemukan, BUKAN selalu current_date
            $query_update_pulang = "UPDATE absensi_siswa SET jam_pulang = ? WHERE siswa_id = ? AND tanggal_absen = ?";
            $stmt_update_pulang = $koneksi->prepare($query_update_pulang);

            if ($stmt_update_pulang) {
                $stmt_update_pulang->bind_param("sis", $jam_pulang_sekarang, $siswa_id, $tanggal_absen_ditemukan);
                if ($stmt_update_pulang->execute()) {
                    $message = 'Absen pulang berhasil dicatat pada pukul ' . date('H:i') . ' WIB.';
                    $message_type = 'success';
                    $message_title = 'Absen Pulang Berhasil!';
                } else {
                    $message = 'Gagal mencatat absen pulang: ' . $stmt_update_pulang->error;
                    $message_type = 'error';
                    $message_title = 'Gagal Update!';
                }
                $stmt_update_pulang->close();
            } else {
                $message = 'Kesalahan persiapan query update absen pulang: ' . $koneksi->error;
                $message_type = 'error';
                $message_title = 'Error Database!';
            }
        }
    } else {
        $message = 'Anda belum absen masuk untuk shift yang aktif. Silakan absen masuk terlebih dahulu.';
        $message_type = 'info';
        $message_title = 'Absen Masuk Belum Dicatat!';
    }
    $stmt_check_absen->close();
} else {
    $message = 'Kesalahan persiapan query cek absensi: ' . $koneksi->error;
    $message_type = 'error';
    $message_title = 'Error Database!';
}

// --- Simpan pesan notifikasi ke sesi dan redirect ---
$_SESSION['alert_message'] = $message;
$_SESSION['alert_type'] = $message_type;
$_SESSION['alert_title'] = $message_title;

if ($koneksi) {
    $koneksi->close();
} // Tutup koneksi database
header('Location: dashboard_siswa.php'); // Redirect kembali ke dashboard siswa
exit();
