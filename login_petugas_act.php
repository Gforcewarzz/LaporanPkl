<?php
session_start();
include 'admin/partials/db.php'; // Pastikan path ini benar ke file koneksi database Anda

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_identifier = mysqli_real_escape_string($koneksi, $_POST['username']); // Ini bisa username admin atau NIP guru
    $password_input   = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role_selected    = mysqli_real_escape_string($koneksi, $_POST['role']);

    $table_name = '';           // Nama tabel database
    $id_column = '';            // Nama kolom ID di tabel
    $identifier_column = '';    // Nama kolom yang digunakan sebagai username/NIP
    $name_column = '';          // Nama kolom untuk nama lengkap
    $redirect_path = '';        // Path setelah login berhasil

    // Tentukan tabel, kolom, dan path redirect berdasarkan role yang dipilih
    switch ($role_selected) {
        case 'admin':
            $table_name = 'admin';
            $id_column = 'id_admin';
            $identifier_column = 'username'; // Admin login pakai username
            $name_column = 'nama_admin';
            $redirect_path = 'admin/index.php'; // Ganti jika path dashboard admin beda
            // Set session spesifik admin untuk kompatibilitas mundur jika ada
            $_SESSION['admin_status_login'] = 'logged_in';
            $_SESSION['admin'] = 'login';
            break;
        case 'guru_pendamping':
            $table_name = 'guru_pembimbing';
            $id_column = 'id_pembimbing';
            $identifier_column = 'nip'; // Guru login pakai NIP
            $name_column = 'nama_pembimbing';
            $redirect_path = 'guru/dashboard_guru.php'; // Ganti jika path dashboard guru beda
            // Set session spesifik guru untuk kompatibilitas mundur jika ada
            $_SESSION['guru_pendamping_status_login'] = 'logged_in';
            $_SESSION['guru_pendamping'] = 'login';
            break;
        default:
            // Role tidak valid atau tidak dipilih
            echo "<script>alert('Peran login tidak valid. Silakan pilih peran yang benar!');window.location='login.php';</script>";
            exit;
    }

    // Menggunakan Prepared Statement untuk mencari pengguna berdasarkan username/NIP
    $stmt = mysqli_prepare($koneksi, "SELECT $id_column, $name_column, password FROM $table_name WHERE $identifier_column = ?");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $input_identifier); // 's' karena identifier adalah string
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt); // Tutup statement setelah digunakan

        if ($user_data) {
            $hashed_password_from_db = $user_data['password'];

            // Verifikasi password yang dimasukkan dengan hash di database
            if (password_verify($password_input, $hashed_password_from_db)) {
                // Login berhasil! Atur sesi universal yang konsisten
                $_SESSION['user_id'] = $user_data[$id_column];
                $_SESSION['user_role'] = $role_selected; // Simpan role yang dipilih (admin/guru_pendamping)
                $_SESSION['user_name'] = $user_data[$name_column]; // Simpan nama pengguna

                mysqli_close($koneksi); // Tutup koneksi database
                header("Location: $redirect_path"); // Redirect ke dashboard yang sesuai
                exit;
            } else {
                // Password salah
                mysqli_close($koneksi);
                echo "<script>alert('Password salah!');window.location='login.php';</script>";
                exit;
            }
        } else {
            // Username/NIP tidak ditemukan di tabel yang dipilih
            mysqli_close($koneksi);
            echo "<script>alert('Identifikasi pengguna tidak ditemukan untuk peran yang dipilih!');window.location='login.php';</script>";
            exit;
        }
    } else {
        // Error saat menyiapkan statement SQL
        mysqli_close($koneksi);
        echo "<script>alert('Terjadi kesalahan sistem saat memproses login. Silakan coba lagi nanti.');window.location='login.php';</script>";
        exit;
    }
} else {
    // Jika bukan request POST (misalnya diakses langsung via URL), redirect ke halaman login
    header("Location: login.php");
    exit;
}