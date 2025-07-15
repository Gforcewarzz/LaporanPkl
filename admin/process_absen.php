<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // <-- Tambahkan baris ini

// Sertakan file koneksi database
include 'partials/db.php'; // Pastikan path ini benar

// Pastikan hanya siswa yang login yang boleh mengakses halaman ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$siswa_id = $_SESSION['id_siswa'] ?? null;

if (!$is_siswa || empty($siswa_id)) {
    $_SESSION['alert_message'] = 'Anda harus login sebagai siswa untuk mengakses halaman ini.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Akses Ditolak!';
    header('Location: ../login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $statusAbsen = $_POST['statusAbsen'] ?? '';
    // Keterangan dan bukti foto hanya relevan untuk Sakit/Izin
    // Untuk Hadir dan Libur, ini bisa null atau kosong
    $keterangan = !empty($_POST['keterangan']) ? trim($_POST['keterangan']) : null;
    $tanggal_absen = date('Y-m-d'); // Ini akan menggunakan zona waktu 'Asia/Jakarta'
    $bukti_foto_path = null; // Default null, akan diisi jika ada upload

    // Tambahkan 'Libur' ke daftar status yang valid
    if (!in_array($statusAbsen, ['Hadir', 'Sakit', 'Izin', 'Libur'])) {
        $_SESSION['alert_message'] = 'Status absensi tidak valid.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Gagal Absen!';
        header('Location: dashboard_siswa.php');
        exit();
    }

    // --- Cek apakah siswa sudah absen hari ini ---
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
            $koneksi->close();
            header('Location: dashboard_siswa.php');
            exit();
        }
        $check_stmt->close();
    } else {
        error_log("Error preparing check_stmt: " . $koneksi->error);
        $_SESSION['alert_message'] = 'Terjadi kesalahan internal saat memeriksa absensi.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Error Database!';
        $koneksi->close();
        header('Location: dashboard_siswa.php');
        exit();
    }

    // --- Logika Upload File Bukti Foto dan Validasi Keterangan ---
    // Hanya berlaku jika status Sakit atau Izin
    if ($statusAbsen === 'Sakit' || $statusAbsen === 'Izin') {
        if (empty($keterangan)) {
            $_SESSION['alert_message'] = 'Keterangan wajib diisi untuk status ' . htmlspecialchars($statusAbsen) . '.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Gagal Absen!';
            header('Location: dashboard_siswa.php');
            exit();
        }

        $target_dir = "image_absensi/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0775, true); // Buat folder jika belum ada
        }

        if (isset($_FILES['buktiFoto']) && $_FILES['buktiFoto']['error'] === UPLOAD_ERR_OK) {
            $file_name = uniqid('bukti_') . '_' . basename($_FILES["buktiFoto"]["name"]);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Validasi file
            $allowed_extensions = ["jpg", "png", "jpeg"];
            $max_file_size = 2 * 1024 * 1024; // 2MB

            if (!in_array($imageFileType, $allowed_extensions)) {
                $_SESSION['alert_message'] = 'Maaf, hanya file JPG, JPEG, & PNG yang diizinkan.';
                $_SESSION['alert_type'] = 'error';
                $_SESSION['alert_title'] = 'Gagal Absen!';
                header('Location: dashboard_siswa.php');
                exit();
            }

            if ($_FILES["buktiFoto"]["size"] > $max_file_size) {
                $_SESSION['alert_message'] = 'Maaf, ukuran file Anda terlalu besar. Maksimal 2MB.';
                $_SESSION['alert_type'] = 'error';
                $_SESSION['alert_title'] = 'Gagal Absen!';
                header('Location: dashboard_siswa.php');
                exit();
            }

            if (move_uploaded_file($_FILES["buktiFoto"]["tmp_name"], $target_file)) {
                $bukti_foto_path = $file_name; // Simpan hanya nama file untuk database
            } else {
                $_SESSION['alert_message'] = 'Terjadi kesalahan saat mengunggah file bukti.';
                $_SESSION['alert_type'] = 'error';
                $_SESSION['alert_title'] = 'Gagal Upload!';
                error_log("Error moving uploaded file: " . $_FILES["buktiFoto"]["error"]);
                header('Location: dashboard_siswa.php');
                exit();
            }
        } else {
            // Jika status Sakit/Izin tapi tidak ada file diunggah
            $_SESSION['alert_message'] = 'Bukti foto wajib diunggah untuk status ' . htmlspecialchars($statusAbsen) . '.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Gagal Absen!';
            header('Location: dashboard_siswa.php');
            exit();
        }
    }
    // Jika statusnya 'Hadir' atau 'Libur', bagian ini akan dilewati,
    // dan $keterangan, $bukti_foto_path akan tetap null (atau nilai default dari form)
    // yang memang tidak dibutuhkan untuk status tersebut.

    // --- Insert data ke database ---
    // Pastikan kolom 'waktu_input' juga diisi dengan waktu WIB yang sudah diformat PHP
    $current_timestamp_wib = date('Y-m-d H:i:s'); 
    $insert_stmt = $koneksi->prepare("INSERT INTO absensi_siswa (siswa_id, tanggal_absen, status_absen, keterangan, bukti_foto, waktu_input) VALUES (?, ?, ?, ?, ?, ?)");

    if ($insert_stmt) {
        $insert_stmt->bind_param("isssss", $siswa_id, $tanggal_absen, $statusAbsen, $keterangan, $bukti_foto_path, $current_timestamp_wib);

        if ($insert_stmt->execute()) {
            $_SESSION['alert_message'] = 'Absensi ' . htmlspecialchars($statusAbsen) . ' berhasil dikirim!';
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_title'] = 'Absensi Berhasil!';

            // Variabel $_SESSION['simulasi_absen'] ini tidak terlalu relevan karena dashboard
            // Anda sudah mengecek langsung dari database, tapi tetap bisa dipertahankan
            // jika ada bagian lain yang menggunakannya.
            $_SESSION['simulasi_absen'] = [
                'sudah_absen' => true,
                'status' => $statusAbsen,
                'lengkap' => ($statusAbsen !== 'Sakit' && $statusAbsen !== 'Izin') || (!empty($keterangan) && !empty($bukti_foto_path)), 
                'tanggal' => $tanggal_absen
            ];
        } else {
            $_SESSION['alert_message'] = 'Terjadi kesalahan saat menyimpan absensi ke database: ' . $insert_stmt->error;
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Gagal Absen!';
            error_log("Error inserting attendance: " . $insert_stmt->error);
        }
        $insert_stmt->close();
    } else {
        $_SESSION['alert_message'] = 'Terjadi kesalahan internal saat menyiapkan query absensi.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Error Database!';
        error_log("Error preparing insert_stmt: " . $koneksi->error);
    }

    $koneksi->close(); 
    header('Location: dashboard_siswa.php'); 
    exit();
} else {
    $_SESSION['alert_message'] = 'Akses tidak sah.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Akses Ditolak!';
    header('Location: dashboard_siswa.php');
    exit();
}