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
$current_time = date('H:i');   // Waktu saat ini (HH:MM)

// --- A. Pembatasan Waktu Absen Pulang (Server-side) ---
$cutoff_time_pulang = '10:00'; // Batas waktu minimum untuk absen pulang

if ($current_time < $cutoff_time_pulang) {
    $message = 'Absen pulang hanya bisa dilakukan setelah pukul 10:00 WIB.';
    $message_type = 'warning';
    $message_title = 'Waktu Belum Memenuhi!';
} else {
    // --- B. Cek Status Absensi Datang Hari Ini ---
    $query_check_absen = "SELECT status_absen, jam_pulang FROM absensi_siswa WHERE siswa_id = ? AND tanggal_absen = ?";
    $stmt_check_absen = $koneksi->prepare($query_check_absen);

    if ($stmt_check_absen) {
        $stmt_check_absen->bind_param("is", $siswa_id, $current_date);
        $stmt_check_absen->execute();
        $result_check_absen = $stmt_check_absen->get_result();

        if ($result_check_absen->num_rows > 0) {
            $data_absen = $result_check_absen->fetch_assoc();
            $status_absen_hari_ini = $data_absen['status_absen'];
            $jam_pulang_sudah_ada = !empty($data_absen['jam_pulang']);

            if ($status_absen_hari_ini !== 'Hadir') {
                $message = 'Anda tidak absen masuk sebagai Hadir hari ini. Tidak bisa absen pulang.';
                $message_type = 'error';
                $message_title = 'Status Absen Tidak Sesuai!';
            } elseif ($jam_pulang_sudah_ada) {
                $message = 'Anda sudah absen pulang untuk hari ini.';
                $message_type = 'warning';
                $message_title = 'Sudah Absen Pulang!';
            } else {
                // --- C. Proses Update Jam Pulang ---
                $jam_pulang_sekarang = date('H:i:s'); // Ambil waktu pulang saat ini
                $query_update_pulang = "UPDATE absensi_siswa SET jam_pulang = ? WHERE siswa_id = ? AND tanggal_absen = ?";
                $stmt_update_pulang = $koneksi->prepare($query_update_pulang);

                if ($stmt_update_pulang) {
                    $stmt_update_pulang->bind_param("sis", $jam_pulang_sekarang, $siswa_id, $current_date);
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
            $message = 'Anda belum absen masuk hari ini. Silakan absen masuk terlebih dahulu.';
            $message_type = 'info';
            $message_title = 'Absen Masuk Belum Dicatat!';
        }
        $stmt_check_absen->close();
    } else {
        $message = 'Kesalahan persiapan query cek absensi: ' . $koneksi->error;
        $message_type = 'error';
        $message_title = 'Error Database!';
    }
}

// --- Simpan pesan notifikasi ke sesi dan redirect ---
$_SESSION['alert_message'] = $message;
$_SESSION['alert_type'] = $message_type;
$_SESSION['alert_title'] = $message_title;

$koneksi->close(); // Tutup koneksi database
header('Location: dashboard_siswa.php'); // Redirect kembali ke dashboard siswa
exit();
