<?php
session_start();
include 'admin/partials/db.php'; // Pastikan path ini benar ke file koneksi database Anda

// --- DEBUGGING START ---
error_reporting(E_ALL); // Aktifkan pelaporan semua error
ini_set('display_errors', 1); // Tampilkan error di browser

// Tampilkan data POST yang diterima
echo "<h3>Debugging Data POST:</h3>";
echo "Username/NIP dari form: <strong>" . htmlspecialchars($_POST['username'] ?? 'Tidak ada') . "</strong><br>";
echo "Password dari form: <strong>" . htmlspecialchars($_POST['password'] ?? 'Tidak ada') . "</strong><br>";
echo "Role yang dipilih: <strong>" . htmlspecialchars($_POST['role'] ?? 'Tidak ada') . "</strong><br>";
echo "<hr>";
// --- DEBUGGING END ---

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_identifier = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_input   = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role_selected    = mysqli_real_escape_string($koneksi, $_POST['role']);

    $table_name = '';
    $id_column = '';
    $identifier_column = '';
    $name_column = '';
    $redirect_path = '';

    switch ($role_selected) {
        case 'admin':
            $table_name = 'admin';
            $id_column = 'id_admin';
            $identifier_column = 'username';
            $name_column = 'nama_admin';
            $redirect_path = 'admin/index.php';
            $_SESSION['admin_status_login'] = 'logged_in';
            $_SESSION['admin'] = 'login';
            break;
        case 'guru_pendamping':
            $table_name = 'guru_pembimbing';
            $id_column = 'id_pembimbing';
            $identifier_column = 'nip'; // <--- PASTIKAN NAMA KOLOM INI SAMA DI DB
            $name_column = 'nama_pembimbing';
            $redirect_path = 'admin/index.php'; // Path dashboard sama dengan admin
            $_SESSION['guru_pendamping_status_login'] = 'logged_in';
            $_SESSION['guru_pendamping'] = 'login';
            break;
        default:
            echo "<script>alert('Peran login tidak valid. Silakan pilih peran yang benar!');window.location='login_petugas.php';</script>";
            exit;
    }

    $stmt = mysqli_prepare($koneksi, "SELECT $id_column, $name_column, password FROM $table_name WHERE $identifier_column = ?");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $input_identifier);
        mysqli_stmt_execute($stmt);

        // --- DEBUGGING START ---
        // Cek jika eksekusi query gagal
        if (mysqli_stmt_error($stmt)) {
            echo "Error Eksekusi Query: " . mysqli_stmt_error($stmt) . "<br>";
            mysqli_close($koneksi);
            exit; // Hentikan eksekusi untuk melihat error
        }
        // --- DEBUGGING END ---

        $result = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // --- DEBUGGING START ---
        echo "Data user dari database (jika ditemukan): ";
        print_r($user_data);
        echo "<br>";
        // --- DEBUGGING END ---

        if ($user_data) {
            $hashed_password_from_db = $user_data['password'];

            // --- DEBUGGING START ---
            echo "Password dari DB (hash): " . htmlspecialchars($hashed_password_from_db) . "<br>";
            echo "Verifikasi Password: " . (password_verify($password_input, $hashed_password_from_db) ? 'SUKSES' : 'GAGAL') . "<br>";
            // --- DEBUGGING END ---

            if (password_verify($password_input, $hashed_password_from_db)) {
                $_SESSION['user_id'] = $user_data[$id_column];
                $_SESSION['user_role'] = $role_selected;
                $_SESSION['user_name'] = $user_data[$name_column];

                if ($role_selected === 'admin') {
                    $_SESSION['admin_status_login'] = 'logged_in';
                    $_SESSION['admin'] = 'login';
                } elseif ($role_selected === 'guru_pendamping') {
                    $_SESSION['guru_pendamping_status_login'] = 'logged_in';
                    $_SESSION['guru_pendamping'] = 'login';
                }

                mysqli_close($koneksi);
                header("Location: $redirect_path");
                exit;
            } else {
                mysqli_close($koneksi);
                echo "<script>alert('Password salah!');window.location='login_petugas.php';</script>";
            }
        } else {
            mysqli_close($koneksi);
            echo "<script>alert('Identifikasi pengguna tidak ditemukan untuk peran yang dipilih!');window.location='login_petugas.php';</script>";
            exit;
        }
    } else {
        mysqli_close($koneksi);
        echo "<script>alert('Terjadi kesalahan sistem saat memproses login (Prepared Statement Error)! Silakan coba lagi nanti.');window.location='login_petugas.php';</script>";
        exit;
    }
} else {
    header("Location: login_petugas.php");
    exit;
}