<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Atur zona waktu ke Asia/Jakarta

// Sertakan file koneksi database
include 'partials/db.php'; // Pastikan path file db.php sudah benar

// --- Validasi Sesi Siswa ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$siswa_id = $_SESSION['id_siswa'] ?? null;

// Jika bukan siswa atau ID siswa tidak ada, redirect ke login
if (!$is_siswa || empty($siswa_id)) {
    $_SESSION['alert_message'] = 'Anda harus login sebagai siswa untuk mengakses halaman ini.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Akses Ditolak!';
    if ($koneksi) {
        $koneksi->close();
    } // Tutup koneksi sebelum redirect
    header('Location: ../login.php');
    exit();
}

// --- Logika Pembatasan Waktu Absensi (Max 17:30 WIB) ---
$current_time = date('H:i'); // Ambil waktu saat ini (HH:MM)
$cutoff_time = '17:30';     // Batas waktu absensi

if ($current_time > $cutoff_time) {
    // Jika waktu sudah melewati 17:30, gagalkan absensi
    $_SESSION['alert_message'] = 'Absensi gagal! Anda hanya bisa absen hingga pukul 17:30 WIB.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Waktu Absen Habis';
    if ($koneksi) {
        $koneksi->close();
    } // Tutup koneksi sebelum redirect
    header('Location: dashboard_siswa.php'); // Redirect kembali ke dashboard
    exit();
}

// --- Proses Form Absensi Jika Metode POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $statusAbsen = $_POST['statusAbsen'] ?? '';
    // Variabel $keterangan diambil dari POST
    $keterangan = !empty($_POST['keterangan']) ? trim($_POST['keterangan']) : null;
    $tanggal_absen = date('Y-m-d'); // Tanggal absensi hari ini
    $bukti_foto_path = null; // Default null untuk path bukti foto

    $current_timestamp_full = date('Y-m-d H:i:s'); // Timestamp lengkap untuk waktu_input

    // PERUBAHAN KRITIS DI SINI: Inisialisasi jam_datang_auto dan jam_pulang berdasarkan statusAbsen
    $jam_datang_auto = null; // Defaultkan ke NULL
    $jam_pulang = null; // Defaultkan ke NULL

    if ($statusAbsen == 'Hadir') {
        $jam_datang_auto = date('H:i:s'); // Ambil waktu saat ini hanya jika status Hadir
        // jam_pulang tetap NULL, akan diisi terpisah oleh process_absen_pulang.php
    }
    // Untuk status selain Hadir (Sakit, Izin, Libur, Alfa), jam_datang_auto dan jam_pulang tetap NULL


    // Validasi status absensi yang diterima
    if (!in_array($statusAbsen, ['Hadir', 'Sakit', 'Izin', 'Libur', 'Alfa'])) { // Tambahkan 'Alfa' ke validasi
        $_SESSION['alert_message'] = 'Status absensi tidak valid.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Gagal Absen!';
        if ($koneksi) {
            $koneksi->close();
        }
        header('Location: dashboard_siswa.php');
        exit();
    }

    // --- Cek Absensi Ganda ---
    $check_stmt = $koneksi->prepare("SELECT id_absensi FROM absensi_siswa WHERE siswa_id = ? AND tanggal_absen = ?");
    if ($check_stmt) {
        $check_stmt->bind_param("is", $siswa_id, $tanggal_absen);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $_SESSION['alert_message'] = 'Anda sudah absen untuk hari ini.';
            $_SESSION['alert_type'] = 'warning';
            $_SESSION['alert_title'] = 'Absen Ganda!';
            $check_stmt->close();
            if ($koneksi) {
                $koneksi->close();
            }
            header('Location: dashboard_siswa.php');
            exit();
        }
        $check_stmt->close();
    } else {
        error_log("Error preparing check_stmt: " . $koneksi->error);
        $_SESSION['alert_message'] = 'Terjadi kesalahan internal saat memeriksa absensi.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Error Database!';
        if ($koneksi) {
            $koneksi->close();
        }
        header('Location: dashboard_siswa.php');
        exit();
    }

    // --- Proses Upload Bukti Foto dan Validasi Keterangan (untuk Sakit/Izin) ---
    // Pastikan keterangan DAN bukti foto wajib diisi untuk Sakit/Izin
    if ($statusAbsen === 'Sakit' || $statusAbsen === 'Izin') {
        if (empty($keterangan)) {
            $_SESSION['alert_message'] = 'Keterangan wajib diisi untuk status ' . htmlspecialchars($statusAbsen) . '.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Gagal Absen!';
            if ($koneksi) {
                $koneksi->close();
            }
            header('Location: dashboard_siswa.php');
            exit();
        }

        $target_dir = "image_absensi/"; // Direktori penyimpanan bukti foto (pastikan path benar)
        if (!is_dir($target_dir)) { // Buat folder jika belum ada
            mkdir($target_dir, 0775, true);
        }

        if (isset($_FILES['buktiFoto']) && $_FILES['buktiFoto']['error'] === UPLOAD_ERR_OK) {
            $file_name = uniqid('bukti_') . '_' . basename($_FILES["buktiFoto"]["name"]);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $allowed_extensions = ["jpg", "png", "jpeg"];
            $max_file_size = 2 * 1024 * 1024; // Maksimal 2MB

            // Validasi tipe dan ukuran file
            if (!in_array($imageFileType, $allowed_extensions)) {
                $_SESSION['alert_message'] = 'Hanya file JPG, JPEG, & PNG yang diizinkan.';
                $_SESSION['alert_type'] = 'error';
                $_SESSION['alert_title'] = 'Format File Tidak Valid!';
                if ($koneksi) {
                    $koneksi->close();
                }
                header('Location: dashboard_siswa.php');
                exit();
            }
            if ($_FILES["buktiFoto"]["size"] > $max_file_size) {
                $_SESSION['alert_message'] = 'Ukuran file terlalu besar. Maksimal 2MB.';
                $_SESSION['alert_type'] = 'error';
                $_SESSION['alert_title'] = 'Ukuran File Terlalu Besar!';
                if ($koneksi) {
                    $koneksi->close();
                }
                header('Location: dashboard_siswa.php');
                exit();
            }

            // Pindahkan file yang diunggah
            if (move_uploaded_file($_FILES["buktiFoto"]["tmp_name"], $target_file)) {
                $bukti_foto_path = $file_name; // Simpan nama file untuk database
            } else {
                $_SESSION['alert_message'] = 'Terjadi kesalahan saat mengunggah file bukti.';
                $_SESSION['alert_type'] = 'error';
                $_SESSION['alert_title'] = 'Gagal Upload!';
                error_log("Error moving uploaded file: " . $_FILES["buktiFoto"]["error"]);
                if ($koneksi) {
                    $koneksi->close();
                }
                header('Location: dashboard_siswa.php');
                exit();
            }
        } else {
            // Jika status Sakit/Izin tapi tidak ada file diunggah
            $_SESSION['alert_message'] = 'Bukti foto wajib diunggah untuk status ' . htmlspecialchars($statusAbsen) . '.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Bukti Tidak Ada!';
            if ($koneksi) {
                $koneksi->close();
            }
            header('Location: dashboard_siswa.php');
            exit();
        }
    } else {
        // Jika status bukan Sakit/Izin, pastikan keterangan dan bukti foto adalah null
        $keterangan = null; // Set keterangan menjadi null jika tidak relevan
        $bukti_foto_path = null;
    }

    // --- Simpan Data Absensi ke Database ---
    // Kolom 'keterangan' dikembalikan ke INSERT statement
    $insert_stmt = $koneksi->prepare("INSERT INTO absensi_siswa (siswa_id, tanggal_absen, status_absen, keterangan, bukti_foto, waktu_input, jam_datang, jam_pulang) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    if ($insert_stmt) {
        // PERUBAHAN BIND_PARAM: 's' untuk keterangan dikembalikan
        // "isssssss" -> integer, string, string, string, string, string, string, string
        $stmt_bind_params = [];
        $stmt_bind_params[] = $siswa_id;
        $stmt_bind_params[] = $tanggal_absen;
        $stmt_bind_params[] = $statusAbsen;
        $stmt_bind_params[] = $keterangan;
        $stmt_bind_params[] = $bukti_foto_path;
        $stmt_bind_params[] = $current_timestamp_full;
        $stmt_bind_params[] = $jam_datang_auto; // Akan NULL jika status bukan Hadir
        $stmt_bind_params[] = $jam_pulang;     // Akan NULL selalu saat INSERT awal

        $insert_stmt->bind_param("isssssss", ...$stmt_bind_params);

        if ($insert_stmt->execute()) {
            $_SESSION['alert_message'] = 'Absensi ' . htmlspecialchars($statusAbsen) . ' berhasil dicatat!';
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_title'] = 'Absensi Berhasil!';
        } else {
            $_SESSION['alert_message'] = 'Gagal menyimpan absensi ke database: ' . $insert_stmt->error;
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Gagal Absen!';
            error_log("Error inserting attendance: " . $insert_stmt->error);
        }
        $insert_stmt->close();
    } else {
        $_SESSION['alert_message'] = 'Kesalahan persiapan query database.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Error Database!';
        error_log("Error preparing insert_stmt: " . $koneksi->error);
    }

    if ($koneksi) {
        $koneksi->close();
    }
    header('Location: dashboard_siswa.php');
    exit();
} else {
    $_SESSION['alert_message'] = 'Akses tidak sah.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Akses Ditolak!';
    if ($koneksi) {
        $koneksi->close();
    }
    header('Location: dashboard_siswa.php');
    exit();
}
