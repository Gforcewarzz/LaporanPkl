<?php
session_start();

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
    header('Location: master_data_absensi_siswa.php');
    exit();
}

$id_absensi = $_POST['id_absensi'] ?? null;
$siswa_id = $_POST['siswa_id'] ?? null;
$tanggal_absen = $_POST['tanggal_absen'] ?? date('Y-m-d');
$statusAbsen = $_POST['statusAbsen'] ?? '';
$keterangan = !empty($_POST['keterangan']) ? trim($_POST['keterangan']) : null;
$foto_lama = $_POST['foto_lama'] ?? null;
$bukti_foto_path = $foto_lama;

$is_update_mode = !empty($id_absensi);

// --- [PENTING] OTORISASI GURU ---
if ($is_guru) {
    // Tentukan ID siswa yang akan divalidasi
    $siswa_id_for_auth = null;
    if ($is_update_mode) {
        // Jika mode update, kita perlu cari tahu siswa_id dari id_absensi
        $stmt_get_siswa = $koneksi->prepare("SELECT siswa_id FROM absensi_siswa WHERE id_absensi = ?");
        $stmt_get_siswa->bind_param("i", $id_absensi);
        $stmt_get_siswa->execute();
        $result_get_siswa = $stmt_get_siswa->get_result();
        if ($row = $result_get_siswa->fetch_assoc()) {
            $siswa_id_for_auth = $row['siswa_id'];
        }
        $stmt_get_siswa->close();
    } else {
        // Jika mode insert, kita sudah punya siswa_id dari form
        $siswa_id_for_auth = $siswa_id;
    }

    // Jika siswa_id ditemukan, lakukan validasi kepemilikan
    if ($siswa_id_for_auth) {
        $stmt_auth = $koneksi->prepare("SELECT id_siswa FROM siswa WHERE id_siswa = ? AND pembimbing_id = ?");
        $stmt_auth->bind_param("ii", $siswa_id_for_auth, $_SESSION['id_guru_pendamping']);
        $stmt_auth->execute();
        if ($stmt_auth->get_result()->num_rows === 0) {
            // Jika tidak ada hasil, berarti guru ini tidak berhak
            $_SESSION['alert_message'] = 'Anda tidak memiliki izin untuk memproses data absensi siswa ini.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Akses Ditolak!';
            header('Location: master_data_absensi_siswa.php');
            exit();
        }
        $stmt_auth->close();
    } else {
        // Jika siswa_id tidak ditemukan sama sekali (misal id_absensi salah)
        $_SESSION['alert_message'] = 'Data siswa untuk otorisasi tidak ditemukan.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Error!';
        header('Location: master_data_absensi_siswa.php');
        exit();
    }
}


// --- Validasi Parameter Utama ---
// (Kode validasi Anda sebelumnya sudah cukup baik, kita bisa sederhanakan sedikit)
if ($is_update_mode && empty($id_absensi)) {
    // Penanganan error jika mode update tapi ID kosong
    // ...
} elseif (!$is_update_mode && (empty($siswa_id) || empty($tanggal_absen))) {
    // Penanganan error jika mode insert tapi parameter kurang
    // ...
}

// --- Logika Upload File Bukti Foto ---
// (Kode upload file Anda sebelumnya sudah bagus, bisa disalin-tempel di sini)
$target_dir = "../image_absensi/"; // Pastikan path ini benar
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0775, true);
}
if ($statusAbsen === 'Sakit' || $statusAbsen === 'Izin') {
    if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] === UPLOAD_ERR_OK) {
        // Hapus file lama jika ada
        if (!empty($foto_lama) && file_exists($target_dir . $foto_lama)) {
            unlink($target_dir . $foto_lama);
        }
        // Proses upload file baru
        $file_name = uniqid('bukti_') . '_' . basename($_FILES["bukti_foto"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["bukti_foto"]["tmp_name"], $target_file)) {
            $bukti_foto_path = $file_name;
        }
    }
} else {
    // Jika status bukan Sakit/Izin, hapus foto dan keterangan
    if (!empty($foto_lama) && file_exists($target_dir . $foto_lama)) {
        unlink($target_dir . $foto_lama);
    }
    $keterangan = null;
    $bukti_foto_path = null;
}


// --- Proses INSERT atau UPDATE ke Database ---
$success = false;
if ($is_update_mode) {
    // Mode UPDATE
    $stmt = $koneksi->prepare("UPDATE absensi_siswa SET status_absen = ?, keterangan = ?, bukti_foto = ? WHERE id_absensi = ?");
    $stmt->bind_param("sssi", $statusAbsen, $keterangan, $bukti_foto_path, $id_absensi);
    $success = $stmt->execute();
    $stmt->close();
} else {
    // Mode INSERT
    $stmt = $koneksi->prepare("INSERT INTO absensi_siswa (siswa_id, tanggal_absen, status_absen, keterangan, bukti_foto, waktu_input) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $siswa_id, $tanggal_absen, $statusAbsen, $keterangan, $bukti_foto_path);
    $success = $stmt->execute();
    $stmt->close();
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
header('Location: master_data_absensi_siswa.php?tanggal=' . urlencode($tanggal_absen));
exit();
?>