<?php
session_start();

// Standarisasi pengecekan peran
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

// Keamanan: Hanya admin atau guru yang boleh mengakses fungsi ini
if (!$is_admin && !$is_guru) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php');
    } else {
        header('Location: ../login.php');
    }
    exit();
}

// Hanya proses jika ada request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: master_data_siswa_add.php');
    exit();
}

require "partials/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Proses Tambah Siswa</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php

function tambahSiswa($data, $koneksi) {
    // 1. Ambil data dari $_POST menggunakan kunci yang benar (ID, bukan nama)
    $nama_siswa = trim($data["nama_siswa"]);
    $no_induk = trim($data["no_induk"]);
    $nisn = trim($data["nisn"]);
    $password = $data["password"];
    $confirm_password = $data["confirm_password"];
    $jenis_kelamin = $data["jenis_kelamin"];
    $kelas = trim($data["kelas"]);
    $status_siswa = $data["status_siswa"];

    // [DIUBAH] Ambil ID langsung dari form
    $id_jurusan = $data["jurusan_id"];
    $id_pembimbing = $data["pembimbing_id"];
    $id_tempat_pkl = $data["tempat_pkl_id"];

    // 2. Validasi dasar
    if (empty($nama_siswa) || empty($no_induk) || empty($nisn) || empty($password) || empty($kelas) || empty($id_jurusan) || empty($id_pembimbing) || empty($id_tempat_pkl)) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Semua kolom wajib diisi.'];
        header('Location: master_data_siswa_add.php');
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Password dan Konfirmasi Password tidak cocok.'];
        header('Location: master_data_siswa_add.php');
        exit();
    }

    if (strlen($password) < 4) {
        $_SESSION['alert'] = ['type' => 'warning', 'title' => 'Password Lemah', 'text' => 'Password minimal harus 4 karakter.'];
        header('Location: master_data_siswa_add.php');
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 3. Cek Duplikat No Induk atau NISN menggunakan Prepared Statement (AMAN)
    $stmt_check = $koneksi->prepare("SELECT id_siswa FROM siswa WHERE no_induk = ? OR nisn = ?");
    $stmt_check->bind_param("ss", $no_induk, $nisn);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Data Duplikat', 'text' => 'No Induk atau NISN sudah terdaftar. Silakan gunakan yang lain.'];
        header('Location: master_data_siswa_add.php');
        exit();
    }
    $stmt_check->close();

    // 4. Query INSERT menggunakan Prepared Statement (AMAN dari SQL Injection)
    $sql = "INSERT INTO siswa 
                (nama_siswa, no_induk, nisn, password, jenis_kelamin, kelas, jurusan_id, pembimbing_id, tempat_pkl_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $koneksi->prepare($sql);
    if ($stmt === false) {
        // Gagal mempersiapkan statement
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Error Sistem', 'text' => 'Gagal mempersiapkan query database.'];
        header('Location: master_data_siswa_add.php');
        exit();
    }

    // Bind parameter ke query
    // Tipe data: s=string, i=integer
    $stmt->bind_param("ssssssiiis",
        $nama_siswa,
        $no_induk,
        $nisn,
        $hashed_password,
        $jenis_kelamin,
        $kelas,
        $id_jurusan,
        $id_pembimbing,
        $id_tempat_pkl,
        $status_siswa
    );

    // Eksekusi query
    if ($stmt->execute()) {
        // Jika berhasil, kembalikan jumlah baris yang terpengaruh
        return $stmt->affected_rows;
    } else {
        // Jika eksekusi gagal
        $_SESSION['alert'] = ['type' => 'error', 'title' => 'Gagal', 'text' => 'Gagal menyimpan data ke database. Error: ' . $stmt->error];
        header('Location: master_data_siswa_add.php');
        exit();
    }
}

// --- Proses jika tombol submit ditekan ---
if (isset($_POST["submit"])) {
    $affected_rows = tambahSiswa($_POST, $koneksi);

    if ($affected_rows > 0) {
        // Set session untuk notifikasi SweetAlert di halaman tujuan
        $_SESSION['excel_message_type'] = 'success';
        $_SESSION['excel_message_title'] = 'Berhasil!';
        $_SESSION['excel_message'] = 'Data siswa baru berhasil ditambahkan.';
        header('Location: master_data_siswa.php');
        exit();
    }
    // Jika gagal, pesan error sudah di-handle di dalam fungsi dan user di-redirect kembali.
} else {
    // Jika akses langsung tanpa submit, redirect
    header('Location: master_data_siswa_add.php');
    exit();
}

// Tutup koneksi (sebenarnya tidak perlu jika skrip langsung exit, tapi ini praktik yang baik)
$koneksi->close();
?>
</body>
</html>