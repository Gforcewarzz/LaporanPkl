<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Standarisasi pengecekan peran
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Keamanan: Hanya admin atau guru yang boleh mengakses halaman ini
if (!$is_admin && !$is_guru) {
    $_SESSION['alert_message'] = 'Anda tidak memiliki izin untuk melakukan aksi ini.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Akses Ditolak!';
    header('Location: ../login.php');
    exit();
}

include 'partials/db.php';

// Hanya proses jika request method adalah POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['alert_message'] = 'Akses tidak sah.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Akses Ditolak!';
    header('Location: master_data_absensi_siswa.php');
    exit();
}

$id_absensi = $_POST['id_absensi'] ?? null;
$siswa_id = $_POST['siswa_id'] ?? null; // Digunakan untuk mode INSERT
$tanggal_absen = $_POST['tanggal_absen'] ?? date('Y-m-d');
$statusAbsen = $_POST['statusAbsen'] ?? '';
$foto_lama = $_POST['foto_lama'] ?? null;
$bukti_foto_path = $foto_lama; // Default path foto bukti adalah foto lama


$is_update_mode = !empty($id_absensi);

// --- [PENTING] OTORISASI GURU ---
// Guru hanya boleh mengedit/menambah absensi siswa yang menjadi bimbingannya
if ($is_guru) {
    $siswa_id_for_auth = null;
    if ($is_update_mode) {
        // Jika mode update, kita perlu cari tahu siswa_id dari id_absensi yang akan diupdate
        $stmt_get_siswa = $koneksi->prepare("SELECT siswa_id FROM absensi_siswa WHERE id_absensi = ?");
        if (!$stmt_get_siswa) {
            $_SESSION['alert_message'] = 'Error persiapan query otorisasi siswa (UPDATE): ' . $koneksi->error;
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Gagal!';
            header('Location: master_data_absensi_siswa.php');
            exit();
        }
        $stmt_get_siswa->bind_param("i", $id_absensi);
        $stmt_get_siswa->execute();
        if ($row = $stmt_get_siswa->get_result()->fetch_assoc()) {
            $siswa_id_for_auth = $row['siswa_id'];
        }
        $stmt_get_siswa->close();
    } else {
        // Jika mode insert, siswa_id sudah tersedia dari form
        $siswa_id_for_auth = $siswa_id;
    }

    // Jika siswa_id ditemukan, lakukan validasi kepemilikan guru
    if ($siswa_id_for_auth) {
        $stmt_auth = $koneksi->prepare("SELECT id_siswa FROM siswa WHERE id_siswa = ? AND pembimbing_id = ?");
        if (!$stmt_auth) {
            $_SESSION['alert_message'] = 'Error persiapan query otorisasi guru: ' . $koneksi->error;
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Gagal!';
            header('Location: master_data_absensi_siswa.php');
            exit();
        }
        $stmt_auth->bind_param("ii", $siswa_id_for_auth, $_SESSION['id_guru_pendamping']);
        $stmt_auth->execute();
        if ($stmt_auth->get_result()->num_rows === 0) {
            // Jika tidak ada hasil, berarti guru ini tidak berhak atas siswa ini
            $_SESSION['alert_message'] = 'Anda tidak memiliki izin untuk memproses data absensi siswa ini.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Akses DitolAK!';
            header('Location: master_data_absensi_siswa.php');
            exit();
        }
        $stmt_auth->close();
    } else {
        // Jika siswa_id tidak ditemukan sama sekali (misal id_absensi di URL salah atau siswa_id form kosong)
        $_SESSION['alert_message'] = 'Data siswa untuk otorisasi tidak ditemukan.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Error!';
        header('Location: master_data_absensi_siswa.php');
        exit();
    }
}


// --- Validasi Parameter Umum ---
if ($is_update_mode && empty($id_absensi)) {
    $_SESSION['alert_message'] = 'ID Absensi tidak ditemukan untuk pembaruan.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Gagal!';
    header('Location: master_data_absensi_siswa.php');
    exit();
} elseif (!$is_update_mode && (empty($siswa_id) || empty($tanggal_absen))) {
    $_SESSION['alert_message'] = 'Parameter Siswa ID atau Tanggal Absen tidak lengkap untuk penambahan.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Gagal!';
    header('Location: master_data_absensi_siswa.php');
    exit();
}
if (!in_array($statusAbsen, ['Hadir', 'Sakit', 'Izin', 'Libur', 'Alfa'])) {
    $_SESSION['alert_message'] = 'Status absensi tidak valid.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Gagal!';
    header('Location: master_data_absensi_siswa.php');
    exit();
}

// --- Logika Upload File Bukti Foto ---
$target_dir = "image_absensi/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0775, true);
}

// Hanya butuh upload foto jika statusnya Sakit atau Izin
if ($statusAbsen === 'Sakit' || $statusAbsen === 'Izin') {
    if (isset($_FILES['buktiFoto']) && $_FILES['buktiFoto']['error'] === UPLOAD_ERR_OK) {
        if (!empty($foto_lama) && file_exists($target_dir . $foto_lama)) {
            unlink($target_dir . $foto_lama);
        }
        $file_name = uniqid('bukti_') . '_' . basename($_FILES["buktiFoto"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_extensions = ["jpg", "png", "jpeg"];
        $max_file_size = 2 * 1024 * 1024;

        if (!in_array($imageFileType, $allowed_extensions)) {
            $_SESSION['alert_message'] = 'Hanya file JPG, JPEG, & PNG yang diizinkan untuk bukti foto.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Format File Tidak Valid!';
            header('Location: master_data_absensi_siswa.php');
            exit();
        }
        if ($_FILES["buktiFoto"]["size"] > $max_file_size) {
            $_SESSION['alert_message'] = 'Ukuran file bukti terlalu besar. Maksimal 2MB.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Ukuran File Terlalu Besar!';
            header('Location: master_data_absensi_siswa.php');
            exit();
        }

        if (move_uploaded_file($_FILES["buktiFoto"]["tmp_name"], $target_file)) {
            $bukti_foto_path = $file_name;
        } else {
            $_SESSION['alert_message'] = 'Terjadi kesalahan saat mengunggah file bukti.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Gagal Upload!';
            error_log("Error moving uploaded file: " . $_FILES["buktiFoto"]["error"]);
            header('Location: master_data_absensi_siswa.php');
            exit();
        }
    } elseif (empty($foto_lama)) {
        $_SESSION['alert_message'] = 'Bukti foto wajib diunggah untuk status ' . htmlspecialchars($statusAbsen) . '.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Bukti Tidak Ada!';
        header('Location: master_data_absensi_siswa.php');
        exit();
    }
} else {
    if (!empty($foto_lama) && file_exists($target_dir . $foto_lama)) {
        unlink($target_dir . $foto_lama);
    }
    $bukti_foto_path = null;
}


// --- Proses INSERT atau UPDATE ke Database ---
$success = false;
$current_timestamp_db = date('Y-m-d H:i:s');
$jam_datang_db = date('H:i:s'); // Jam datang akan sama dengan waktu input jika absen baru
$jam_pulang_db_initial = null; // Inisialisasi eksplisit untuk INSERT

if ($is_update_mode) {
    // Mode UPDATE: Update status_absen dan bukti_foto. jam_datang/pulang tidak diubah di sini.
    // Query tidak akan mencoba mengupdate keterangan karena sudah di-drop
    $stmt = $koneksi->prepare("UPDATE absensi_siswa SET status_absen = ?, bukti_foto = ? WHERE id_absensi = ?");
    if ($stmt) {
        $stmt->bind_param("ssi", $statusAbsen, $bukti_foto_path, $id_absensi);
        $success = $stmt->execute();
        $stmt->close();
    }
} else {
    // Mode INSERT: Tambah absensi baru. jam_datang akan sama dengan waktu_input.
    // Query tidak akan mencoba insert keterangan karena sudah di-drop
    $stmt = $koneksi->prepare("INSERT INTO absensi_siswa (siswa_id, tanggal_absen, status_absen, bukti_foto, waktu_input, jam_datang, jam_pulang) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issssss", $siswa_id, $tanggal_absen, $statusAbsen, $bukti_foto_path, $current_timestamp_db, $jam_datang_db, $jam_pulang_db_initial);
        $success = $stmt->execute();
        $stmt->close();
    }
}

// --- Tanggapan & Redirect ---
if ($success) {
    $_SESSION['alert_message'] = $is_update_mode ? 'Absensi berhasil diperbarui!' : 'Absensi berhasil ditambahkan!';
    $_SESSION['alert_type'] = 'success';
    $_SESSION['alert_title'] = 'Berhasil!';
} else {
    $_SESSION['alert_message'] = 'Operasi database gagal: ' . $koneksi->error;
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Gagal!';
}

$koneksi->close();

// Redirect kembali ke halaman master data absensi siswa, mempertahankan tanggal filter
header('Location: master_data_absensi_siswa.php?tanggal_mulai=' . urlencode($tanggal_absen) . '&tanggal_akhir=' . urlencode($tanggal_absen));
exit();
