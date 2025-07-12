<?php
session_start();

// Standarisasi pengecekan peran
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

// Keamanan: Hanya admin atau guru yang boleh mengakses fungsi ini
if (!$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// Hanya proses jika ada request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit'])) {
    header('Location: master_data_siswa.php');
    exit();
}

require "partials/db.php";

// 1. Ambil semua data dari POST
$id_siswa = $_POST['id_siswa'] ?? 0;
$nama_siswa = trim($_POST['nama_siswa'] ?? '');
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$nisn = trim($_POST['nisn'] ?? '');
$no_induk = trim($_POST['no_induk'] ?? '');
$kelas = trim($_POST['kelas'] ?? '');
$status = $_POST['status'] ?? '';
$password_baru = $_POST['password'] ?? '';

// [DIUBAH] Ambil ID langsung dari form
$id_jurusan = $_POST['jurusan_id'] ?? 0;
$id_pembimbing = $_POST['pembimbing_id'] ?? 0;
$id_tempat_pkl = $_POST['tempat_pkl_id'] ?? 0;

// Validasi dasar
if (empty($id_siswa) || empty($nama_siswa) || empty($no_induk) || empty($nisn)) {
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Data wajib tidak boleh kosong.'];
    header("Location: master_data_siswa_edit.php?id=$id_siswa");
    exit();
}

// [PENTING] Langkah Otorisasi untuk Guru
if ($is_guru) {
    $auth_stmt = $koneksi->prepare("SELECT id_siswa FROM siswa WHERE id_siswa = ? AND pembimbing_id = ?");
    $auth_stmt->bind_param("ii", $id_siswa, $_SESSION['id_guru_pendamping']);
    $auth_stmt->execute();
    $auth_result = $auth_stmt->get_result();
    if ($auth_result->num_rows === 0) {
        // Jika tidak ada hasil, berarti guru ini tidak berhak mengedit siswa ini
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Akses Ditolak!', 'text' => 'Anda tidak memiliki izin untuk mengubah data siswa ini.'];
        header('Location: master_data_siswa.php');
        $auth_stmt->close();
        $koneksi->close();
        exit();
    }
    $auth_stmt->close();
}

// 2. Bangun query UPDATE secara dinamis dan aman
$sql_parts = [];
$params = [];
$types = '';

// Tambahkan field-field yang pasti diupdate
array_push($sql_parts, "nama_siswa = ?", "jenis_kelamin = ?", "nisn = ?", "no_induk = ?", "kelas = ?", "status = ?", "jurusan_id = ?", "pembimbing_id = ?", "tempat_pkl_id = ?");
array_push($params, $nama_siswa, $jenis_kelamin, $nisn, $no_induk, $kelas, $status, $id_jurusan, $id_pembimbing, $id_tempat_pkl);
$types .= 'ssssssiii';

// 3. Cek jika password diisi, maka tambahkan ke query
if (!empty($password_baru)) {
    if (strlen($password_baru) < 4) {
        $_SESSION['alert'] = ['type' => 'warning', 'title' => 'Password Lemah', 'text' => 'Password baru minimal harus 4 karakter. Perubahan lain disimpan.'];
        // Tetap lanjutkan update data lain, tapi beri peringatan soal password
    } else {
        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
        $sql_parts[] = "password = ?";
        $params[] = $hashed_password;
        $types .= 's';
    }
}

// Tambahkan ID siswa ke akhir parameter untuk WHERE clause
$params[] = $id_siswa;
$types .= 'i';

// 4. Gabungkan dan jalankan query UPDATE
$sql = "UPDATE siswa SET " . implode(", ", $sql_parts) . " WHERE id_siswa = ?";
$stmt = $koneksi->prepare($sql);

if ($stmt === false) {
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'Error Sistem', 'text' => 'Gagal mempersiapkan query update.'];
    header('Location: master_data_siswa.php');
    exit();
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // Berhasil
    $_SESSION['excel_message_type'] = 'success';
    $_SESSION['excel_message_title'] = 'Berhasil!';
    $_SESSION['excel_message'] = 'Data siswa berhasil diperbarui.';
} else {
    // Gagal
    $_SESSION['excel_message_type'] = 'error';
    $_SESSION['excel_message_title'] = 'Gagal!';
    $_SESSION['excel_message'] = 'Gagal memperbarui data siswa. Error: ' . $stmt->error;
}

$stmt->close();
$koneksi->close();

header('Location: master_data_siswa.php');
exit();

?>