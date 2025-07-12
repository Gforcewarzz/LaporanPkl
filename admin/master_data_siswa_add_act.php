<?php

session_start();
// Keamanan: Hanya admin yang boleh mengakses ini
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

if (!$is_admin) {
    header('Location: ../login.php');
    exit();
}

include "partials/db.php"; // Pastikan path ini benar!
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tambah Siswa</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php
// Fungsi untuk membersihkan input sebelum digunakan dalam SQL
// TIDAK ADA htmlspecialchars() di sini, karena ini untuk SQL!
function sanitize_for_sql($data) {
    global $koneksi;
    $data = trim($data);
    $data = stripslashes($data); // Hapus backslashes yang mungkin ditambahkan otomatis
    // mysqli_real_escape_string harus dilakukan di sini
    if ($koneksi) {
        $data = mysqli_real_escape_string($koneksi, $data);
    }
    return $data;
}

// Fungsi utama untuk menambahkan siswa
function tambahSiswa($data) {
    global $koneksi;

    // 1. Sanitasi input untuk SQL
    // Gunakan sanitize_for_sql untuk semua data yang akan masuk ke database
    $nama_siswa = sanitize_for_sql($data["nama_siswa"]);
    $no_induk = sanitize_for_sql($data["no_induk"]);
    $nisn = sanitize_for_sql($data["nisn"]);
    $jenis_kelamin = sanitize_for_sql($data["jenis_kelamin"]);
    $kelas = sanitize_for_sql($data["kelas"]);
    $id_jurusan = sanitize_for_sql($data["jurusan"]); // Ini ID Jurusan

    $password = $data["password"];
    $confirm_password = $data["confirm_password"];
    $status_siswa = sanitize_for_sql($data["status_siswa"]);

    // 2. Validasi Password
    if ($password !== $confirm_password) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Password dan Konfirmasi tidak cocok!'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    if (strlen($password) < 4) {
        echo "
        <script>
        Swal.fire({
            icon: 'warning',
            title: 'Password Terlalu Pendek',
            text: 'Minimal 4 karakter.'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    // Hash password yang bersih
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 3. Ambil ID guru dan tempat PKL
    // Data ini perlu DI-SANATIZE DULU sebelum dipakai di query pencarian ID!
    $guru_pendamping_nama = sanitize_for_sql($data["guru_pendamping"]);
    $tempat_pkl_nama = sanitize_for_sql($data["tempat_pkl"]);

    // Query untuk mencari ID guru_pembimbing
    $query_guru = "SELECT id_pembimbing FROM guru_pembimbing WHERE nama_pembimbing = '$guru_pendamping_nama'";
    $result_guru = mysqli_query($koneksi, $query_guru);

    if (!$result_guru) { // Cek jika query guru gagal
        error_log("Query guru gagal: " . mysqli_error($koneksi) . " SQL: " . $query_guru);
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Error Database!',
            text: 'Terjadi masalah saat mencari data guru.'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    $id_guru_pembimbing = $result_guru ? (mysqli_fetch_assoc($result_guru)['id_pembimbing'] ?? null) : null;

    if ($id_guru_pembimbing === null) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Guru Tidak Ditemukan',
            text: 'Nama guru pendamping tidak terdaftar. Pastikan nama guru yang Anda masukkan sudah ada di master data guru.'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    // Query untuk mencari ID tempat_pkl
    $query_tempat = "SELECT id_tempat_pkl FROM tempat_pkl WHERE nama_tempat_pkl = '$tempat_pkl_nama'";
    $result_tempat = mysqli_query($koneksi, $query_tempat);

    if (!$result_tempat) { // Cek jika query tempat gagal
        error_log("Query tempat gagal: " . mysqli_error($koneksi) . " SQL: " . $query_tempat);
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Error Database!',
            text: 'Terjadi masalah saat mencari data tempat PKL.'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    $id_tempat_pkl = $result_tempat ? (mysqli_fetch_assoc($result_tempat)['id_tempat_pkl'] ?? null) : null;

    if ($id_tempat_pkl === null) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Tempat PKL Tidak Ditemukan',
            text: 'Nama tempat PKL tidak terdaftar. Pastikan nama tempat PKL yang Anda masukkan sudah ada di master data tempat PKL.'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    // 4. Cek duplikat no_induk / nisn
    // Pastikan variabel sudah di-sanitize_for_sql
    $check_duplicate_query = "SELECT COUNT(*) as total FROM siswa WHERE no_induk = '$no_induk' OR nisn = '$nisn'";
    $check_duplicate_result = mysqli_query($koneksi, $check_duplicate_query);
    
    if (!$check_duplicate_result) { // Cek jika query duplikat gagal
        error_log("Query duplikat gagal: " . mysqli_error($koneksi) . " SQL: " . $check_duplicate_query);
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Error Database!',
            text: 'Terjadi masalah saat memeriksa duplikasi data.'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    $count = mysqli_fetch_assoc($check_duplicate_result)['total'];

    if ($count > 0) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Data Duplikat',
            text: 'No Induk atau NISN sudah terdaftar. Silakan gunakan yang lain.'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    // 5. Query INSERT Data Siswa
    // Pastikan nama kolom di tabel 'siswa' sesuai dengan database kamu.
    // Contoh: jurusan_id, pembimbing_id, tempat_pkl_id, status
    $query_insert = "INSERT INTO siswa (
                        nama_siswa,
                        no_induk,
                        nisn,
                        password,
                        jenis_kelamin,
                        kelas,
                        jurusan_id,
                        pembimbing_id,
                        tempat_pkl_id,
                        status
                    ) VALUES (
                        '$nama_siswa',
                        '$no_induk',
                        '$nisn',
                        '$hashed_password',
                        '$jenis_kelamin',
                        '$kelas',
                        '$id_jurusan',
                        '$id_guru_pembimbing',
                        '$id_tempat_pkl',
                        '$status_siswa'
                    )";

    $insert_result = mysqli_query($koneksi, $query_insert);

    if (!$insert_result) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Gagal menambahkan data siswa: " . mysqli_error($koneksi) . " SQL: " . $query_insert . "'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    return mysqli_affected_rows($koneksi);
}

// --- Proses jika tombol submit ditekan ---
if (isset($_POST["submit"])) {
    $add_success = tambahSiswa($_POST);

    if ($add_success > 0) {
        echo "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data siswa berhasil ditambahkan.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'master_data_siswa.php';
        });
        </script>";
    }
    // Jika add_success <= 0 atau false, pesan error sudah ditangani di dalam fungsi tambahSiswa
}

// Tutup koneksi database setelah semua proses selesai
if (isset($koneksi)) {
    $koneksi->close();
}
?>
</body>
</html>