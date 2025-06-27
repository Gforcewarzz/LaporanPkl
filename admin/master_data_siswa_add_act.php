<?php

session_start();

// 1. Aturan utama: Cek apakah pengguna yang mengakses BUKAN seorang ADMIN.
if (!isset($_SESSION['admin_status_login']) || $_SESSION['admin_status_login'] !== 'logged_in') {

    // 2. Jika bukan admin, cek apakah dia adalah SISWA.
    if (isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in') {
        // Jika benar siswa, kembalikan ke halaman siswa.
        header('Location: master_kegiatan_harian.php');
        exit();
    }
    // 3. TAMBAHAN: Jika bukan siswa, cek apakah dia adalah GURU.
    elseif (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        // Jika benar guru, kembalikan ke halaman guru.
        header('Location: ../../halaman_guru.php'); //belum di atur
        exit();
    }
    // 4. Jika bukan salah satu dari role di atas (admin, siswa, guru),
    // artinya pengguna belum login sama sekali. Arahkan ke halaman login.
    else {
        header('Location: ../login.php');
        exit();
    }
}

// 5. Jika lolos semua pemeriksaan di atas, maka dia adalah ADMIN yang sah.
// Tampilkan semua konten halaman ini.
include "partials/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tambah Siswa</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php
// Fungsi dan proses tambah siswa
function sanitize_input($data) {
    global $koneksi;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if ($koneksi) {
        $data = mysqli_real_escape_string($koneksi, $data);
    }
    return $data;
}

function tambahSiswa($data) {
    global $koneksi;

    $nama_siswa = sanitize_input($data["nama_siswa"]);
    $no_induk = sanitize_input($data["no_induk"]);
    $nisn = sanitize_input($data["nisn"]);
    $jenis_kelamin = sanitize_input($data["jenis_kelamin"]);
    $kelas = sanitize_input($data["kelas"]);
    $id_jurusan = sanitize_input($data["jurusan"]);

    $password = $data["password"];
    $confirm_password = $data["confirm_password"];

    // Validasi
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

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Ambil ID guru dan tempat PKL
    $guru_pendamping_nama = sanitize_input($data["guru_pendamping"]);
    $query_guru = "SELECT id_pembimbing FROM guru_pembimbing WHERE nama_pembimbing = '$guru_pendamping_nama'";
    $result_guru = mysqli_query($koneksi, $query_guru);
    $id_guru_pembimbing = $result_guru ? mysqli_fetch_assoc($result_guru)['id_pembimbing'] ?? null : null;

    $tempat_pkl_nama = sanitize_input($data["tempat_pkl"]);
    $query_tempat = "SELECT id_tempat_pkl FROM tempat_pkl WHERE nama_tempat_pkl = '$tempat_pkl_nama'";
    $result_tempat = mysqli_query($koneksi, $query_tempat);
    $id_tempat_pkl = $result_tempat ? mysqli_fetch_assoc($result_tempat)['id_tempat_pkl'] ?? null : null;

    if ($id_guru_pembimbing === null) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Guru Tidak Ditemukan',
            text: 'Nama guru pendamping tidak terdaftar.'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    if ($id_tempat_pkl === null) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Tempat PKL Tidak Ditemukan',
            text: 'Nama tempat PKL tidak terdaftar.'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    // Cek duplikat no_induk / nisn
    $check_duplicate = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa WHERE no_induk = '$no_induk' OR nisn = '$nisn'");
    $count = mysqli_fetch_assoc($check_duplicate)['total'];

    if ($count > 0) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Data Duplikat',
            text: 'No Induk atau NISN sudah terdaftar.'
        }).then(() => {
            window.history.back();
        });
        </script>";
        return false;
    }

    $status_siswa = sanitize_input($data["status_siswa"]);

    $query = "INSERT INTO siswa (nama_siswa, no_induk, nisn, password, jenis_kelamin, kelas, jurusan_id, pembimbing_id, tempat_pkl_id, status)
              VALUES ('$nama_siswa', '$no_induk', '$nisn', '$hashed_password', '$jenis_kelamin', '$kelas', '$id_jurusan', '$id_guru_pembimbing', '$id_tempat_pkl', '$status_siswa')";
    $insert_result = mysqli_query($koneksi, $query);

    if (!$insert_result) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Gagal menambahkan data: " . mysqli_error($koneksi) . "'
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
}
?>
</body>
</html>
