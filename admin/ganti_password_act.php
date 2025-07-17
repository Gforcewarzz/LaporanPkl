<?php
session_start(); // Pastikan session dimulai paling awal di setiap skrip yang menggunakan sesi

// Pastikan path ke db.php benar relatif dari lokasi file ini
include 'partials/db.php'; // File ini diharapkan menginisialisasi $koneksi

// --- DEBUGGING MODE KONFIGURASI ---
// Ubah ke TRUE untuk melihat output debugging di browser.
// Ubah ke FALSE saat kode sudah siap untuk produksi.
const DEBUG_MODE = false; // <--- SUDAH DIUBAH MENJADI FALSE

if (DEBUG_MODE) {
    error_reporting(E_ALL); // Laporkan semua error PHP
    ini_set('display_errors', 1); // Tampilkan error di browser
    echo "<h3>[DEBUG] ganti_password_act.php - Mulai Eksekusi</h3>";
    echo "Metode Request: " . htmlspecialchars($_SERVER['REQUEST_METHOD']) . "<br>";
    echo "POST Data Diterima: <pre>" . htmlspecialchars(print_r($_POST, true)) . "</pre><br>";
    echo "Sesi Global (\$_SESSION) Saat Ini: <pre>" . htmlspecialchars(print_r($_SESSION, true)) . "</pre><br>";
    echo "<hr>";
}
// --- AKHIR DEBUGGING MODE KONFIGURASI ---


// Ambil data sesi universal dari pengguna yang sedang login
// Variabel-variabel ini harus diatur secara konsisten di semua alur login Anda (admin, guru, siswa).
$loggedInUserId = $_SESSION['user_id'] ?? null;
$loggedInUserRole = $_SESSION['user_role'] ?? null;

// Ambil data dari form POST (data dari hidden input dan field password)
$form_user_id = $_POST['user_id'] ?? '';
$form_user_role = $_POST['user_role'] ?? '';
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

// Inisialisasi variabel untuk pesan notifikasi SweetAlert
$message = '';
$message_type = '';
$message_title = '';

if (DEBUG_MODE) {
    echo "Variabel Sesi Diambil:<br>";
    echo "  \$loggedInUserId: " . htmlspecialchars(var_export($loggedInUserId, true)) . " (Tipe: " . gettype($loggedInUserId) . ")<br>";
    echo "  \$loggedInUserRole: " . htmlspecialchars(var_export($loggedInUserRole, true)) . " (Tipe: " . gettype($loggedInUserRole) . ")<br>";
    echo "Variabel Form Diambil:<br>";
    echo "  \$form_user_id: " . htmlspecialchars(var_export($form_user_id, true)) . " (Tipe: " . gettype($form_user_id) . ")<br>";
    echo "  \$form_user_role: " . htmlspecialchars(var_export($form_user_role, true)) . " (Tipe: " . gettype($form_user_role) . ")<br>";
    echo "<hr>";
}

// --- A. VALIDASI AKSES AWAL & SESI UTAMA ---
// Kondisi 1: Tidak ada user yang login di sesi, atau sesi tidak valid.
// Kondisi 2: Request bukan dari form POST.
if (!$loggedInUserId || !$loggedInUserRole || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $message = 'Akses tidak sah atau sesi tidak valid. Silakan login kembali untuk melanjutkan.';
    $message_type = 'error';
    $message_title = 'Akses Ditolak!';

    // Simpan pesan ke sesi untuk ditampilkan di halaman login
    $_SESSION['ganti_password_message'] = $message;
    $_SESSION['ganti_password_message_type'] = $message_type;
    $_SESSION['ganti_password_message_title'] = $message_title;

    mysqli_close($koneksi); // Tutup koneksi sebelum redirect
    header('Location: login.php'); // Arahkan ke halaman login
    exit();
}

// --- B. VALIDASI DATA FORM DAN KEAMANAN KONSISTENSI SESI DENGAN FORM ---
// 1. Keamanan Kritis: Verifikasi bahwa ID dan peran yang disubmit dari form
//    harus sama persis dengan yang ada di sesi pengguna yang sedang login.
//    Penting: Lakukan type casting ke (string) untuk perbandingan yang aman
//    karena nilai dari $_POST seringkali string, sementara dari sesi bisa integer.
if ((string)$form_user_id !== (string)$loggedInUserId || (string)$form_user_role !== (string)$loggedInUserRole) {
    $message = 'Terdeteksi ketidaksesuaian data. Mohon coba login ulang untuk alasan keamanan.';
    $message_type = 'error';
    $message_title = 'Peringatan Keamanan!';
    if (DEBUG_MODE) {
        echo "[DEBUG] Validasi keamanan (ID/Role mismatch) GAGAL. Sesi ID: '$loggedInUserId' (Tipe: " . gettype($loggedInUserId) . ") Role: '$loggedInUserRole' (Tipe: " . gettype($loggedInUserRole) . ") vs Form ID: '$form_user_id' (Tipe: " . gettype($form_user_id) . ") Role: '$form_user_role' (Tipe: " . gettype($form_user_role) . ")<br>";
    }
}
// 2. Pastikan semua kolom password diisi
elseif (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
    $message = 'Semua kolom password harus diisi.';
    $message_type = 'error';
    $message_title = 'Input Tidak Lengkap!';
    if (DEBUG_MODE) {
        echo "[DEBUG] Validasi input (empty fields) GAGAL.<br>";
    }
}
// 3. Pastikan password baru dan konfirmasi password cocok
elseif ($new_password !== $confirm_new_password) {
    $message = 'Password baru dan konfirmasi password tidak cocok.';
    $message_type = 'error';
    $message_title = 'Password Tidak Cocok!';
    if (DEBUG_MODE) {
        echo "[DEBUG] Validasi password (new vs confirm) GAGAL.<br>";
    }
}
// 4. Validasi panjang password baru (minimal 6 karakter)
elseif (strlen($new_password) < 6) {
    $message = 'Password baru minimal 6 karakter.';
    $message_type = 'error';
    $message_title = 'Password Lemah!';
    if (DEBUG_MODE) {
        echo "[DEBUG] Validasi password (length) GAGAL.<br>";
    }
}
// --- Jika semua validasi di atas lolos, lanjutkan ke proses database ---
else {
    // Tentukan nama tabel database dan kolom ID yang sesuai
    // Berdasarkan peran pengguna yang sedang login (dari sesi).
    $table_name = '';
    $id_column = '';
    $password_column = 'password'; // Asumsi nama kolom password adalah 'password' di semua tabel

    switch ($loggedInUserRole) {
        case 'siswa':
            $table_name = 'siswa';
            $id_column = 'id_siswa';
            break;
        case 'guru_pendamping':
            $table_name = 'guru_pembimbing';
            $id_column = 'id_pembimbing';
            break;
        case 'admin':
            $table_name = 'admin';
            $id_column = 'id_admin';
            break;
        default:
            // Ini seharusnya tidak terjadi jika $_SESSION['user_role'] diatur dengan benar saat login.
            $message = 'Peran pengguna tidak dikenali. Mohon hubungi administrator.';
            $message_type = 'error';
            $message_title = 'Peran Tidak Valid!';
            if (DEBUG_MODE) {
                echo "[DEBUG] Validasi peran GAGAL: Peran '$loggedInUserRole' tidak dikenal.<br>";
            }
            break;
    }

    // Hanya lanjutkan proses database jika $table_name berhasil ditentukan
    if (!empty($table_name)) {
        // 1. Ambil hash password lama dari database menggunakan prepared statement
        $sql_get_password = "SELECT $password_column FROM $table_name WHERE $id_column = ?";
        $stmt_get_password = mysqli_prepare($koneksi, $sql_get_password);

        if ($stmt_get_password) {
            mysqli_stmt_bind_param($stmt_get_password, "i", $loggedInUserId); // Bind ID pengguna dari sesi (integer)
            mysqli_stmt_execute($stmt_get_password);
            $result_get_password = mysqli_stmt_get_result($stmt_get_password);
            $user_data = mysqli_fetch_assoc($result_get_password);
            mysqli_stmt_close($stmt_get_password);

            if (!$user_data) {
                $message = 'Data pengguna tidak ditemukan di database. Mohon coba login ulang.';
                $message_type = 'error';
                $message_title = 'Pengguna Tidak Ditemukan!';
                if (DEBUG_MODE) {
                    echo "[DEBUG] Pengguna ID: '$loggedInUserId' tidak ditemukan di tabel '$table_name'.<br>";
                }
            } else {
                $hashed_password_db = $user_data[$password_column];

                // 2. Verifikasi password saat ini yang dimasukkan pengguna
                if (password_verify($current_password, $hashed_password_db)) {
                    // 3. Hash password baru sebelum menyimpannya (selalu hash password!)
                    $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);

                    // 4. Update password di database menggunakan prepared statement
                    $sql_update_password = "UPDATE $table_name SET $password_column = ? WHERE $id_column = ?";
                    $stmt_update_password = mysqli_prepare($koneksi, $sql_update_password);

                    if ($stmt_update_password) {
                        mysqli_stmt_bind_param($stmt_update_password, "si", $new_password_hashed, $loggedInUserId);
                        if (mysqli_stmt_execute($stmt_update_password)) {
                            $message = 'Password berhasil diganti!';
                            $message_type = 'success';
                            $message_title = 'Berhasil!';
                            if (DEBUG_MODE) {
                                echo "[DEBUG] Password berhasil diupdate di tabel '$table_name' untuk ID: '$loggedInUserId'.<br>";
                            }
                        } else {
                            $message = 'Gagal mengganti password: ' . mysqli_error($koneksi);
                            $message_type = 'error';
                            $message_title = 'Gagal Update!';
                            if (DEBUG_MODE) {
                                echo "[DEBUG] Gagal update password. Error: " . mysqli_error($koneksi) . "<br>";
                            }
                        }
                        mysqli_stmt_close($stmt_update_password);
                    } else {
                        $message = 'Terjadi kesalahan saat menyiapkan query update: ' . mysqli_error($koneksi);
                        $message_type = 'error';
                        $message_title = 'Error Database!';
                        if (DEBUG_MODE) {
                            echo "[DEBUG] Gagal prepare update query. Error: " . mysqli_error($koneksi) . "<br>";
                        }
                    }
                } else {
                    $message = 'Password saat ini salah.';
                    $message_type = 'error';
                    $message_title = 'Password Salah!';
                    if (DEBUG_MODE) {
                        echo "[DEBUG] Password saat ini tidak cocok.<br>";
                    }
                }
            }
        } else {
            $message = 'Terjadi kesalahan saat menyiapkan query pengambilan password: ' . mysqli_error($koneksi);
            $message_type = 'error';
            $message_title = 'Error Database!';
            if (DEBUG_MODE) {
                echo "[DEBUG] Gagal prepare get password query. Error: " . mysqli_error($koneksi) . "<br>";
            }
        }
    }
}

// --- C. SIMPAN PESAN KE SESI & REDIRECT KEMBALI KE ganti_password.php ---
$_SESSION['ganti_password_message'] = $message;
$_SESSION['ganti_password_message_type'] = $message_type;
$_SESSION['ganti_password_message_title'] = $message_title;

mysqli_close($koneksi); // Tutup koneksi database

// Jika DEBUG_MODE aktif, skrip akan berhenti di sini dan tidak melakukan redirect
if (DEBUG_MODE) {
    echo "<hr>[DEBUG] Pesan Disimpan ke Sesi: <pre>" . htmlspecialchars(print_r($_SESSION, true)) . "</pre>";
    echo "Redirecting to ganti_password.php... (Skrip berhenti di sini jika DEBUG_MODE aktif)";
    exit();
}

header('Location: ganti_password.php'); // REDIRECT UTAMA KEMBALI KE HALAMAN FORM GANTI PASSWORD
exit();
