<?php

session_start();

// Keamanan: Hanya admin yang boleh mengakses dashboard ini
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php'); // Redirect siswa ke dashboard siswa
        exit();
    } elseif ($is_guru) {
        header('Location: ../halaman_guru.php'); // Redirect guru ke halaman guru
        exit();
    } else {
        header('Location: ../login.php'); // Jika tidak login sama sekali, redirect ke halaman login
        exit();
    }
}

// Sertakan koneksi database
include 'partials/db.php'; // Pastikan path ini benar!

// --- Fungsi untuk sanitasi input agar aman di SQL ---
// Fungsi ini HANYA untuk SQL, jangan ada htmlspecialchars di sini!
function sanitize_for_sql($data) {
    global $koneksi;
    if ($koneksi) {
        $data = trim($data); // Hapus spasi di awal/akhir
        $data = stripslashes($data); // Hapus backslashes
        $data = mysqli_real_escape_string($koneksi, $data); // Escaping untuk SQL
    }
    return $data;
}

// --- Ambil dan sanitasi semua input POST ---
// TERAPKAN sanitize_for_sql() ke SEMUA input dari $_POST yang akan masuk ke query SQL
$id_siswa        = sanitize_for_sql($_POST['id_siswa'] ?? ''); // Pastikan id_siswa selalu ada
$nama_siswa      = sanitize_for_sql($_POST['nama_siswa'] ?? '');
$jenis_kelamin   = sanitize_for_sql($_POST['jenis_kelamin'] ?? '');
$nisn            = sanitize_for_sql($_POST['nisn'] ?? '');
$no_induk        = sanitize_for_sql($_POST['no_induk'] ?? '');
$kelas           = sanitize_for_sql($_POST['kelas'] ?? '');
$status          = sanitize_for_sql($_POST['status'] ?? '');

// Ambil nama relasi dari input (nama_jurusan, nama_pembimbing, nama_tempat_pkl)
// Mereka juga perlu di-sanitize untuk query pencarian ID!
$jurusan_nama    = sanitize_for_sql($_POST['jurusan_nama'] ?? '');
$guru_nama       = sanitize_for_sql($_POST['guru_nama'] ?? '');
$tempat_nama     = sanitize_for_sql($_POST['tempat_pkl_nama'] ?? '');

// --- Validasi dasar sebelum melanjutkan (opsional tapi disarankan) ---
$errors = [];
if (empty($id_siswa)) $errors[] = "ID Siswa tidak ditemukan.";
if (empty($nama_siswa)) $errors[] = "Nama Siswa tidak boleh kosong.";
// Tambahkan validasi lain sesuai kebutuhan (misal: NISN harus 10 digit angka, dll.)

if (!empty($errors)) {
    $_SESSION['pesan_error'] = implode("<br>", $errors);
    header("Location: master_data_siswa.php"); // Redirect ke halaman daftar siswa atau halaman edit
    exit();
}

// --- Cari ID jurusan berdasarkan nama ---
$jurusan_id = null;
if (!empty($jurusan_nama)) {
    $jurusan_q = mysqli_query($koneksi, "SELECT id_jurusan FROM jurusan WHERE nama_jurusan = '$jurusan_nama'");
    if ($jurusan_q && mysqli_num_rows($jurusan_q) > 0) {
        $jurusan_id = mysqli_fetch_assoc($jurusan_q)['id_jurusan'];
    } else {
        $errors[] = "Jurusan '" . htmlspecialchars($jurusan_nama) . "' tidak ditemukan. Data tidak diperbarui.";
    }
} else {
    // Jika jurusan_nama kosong, mungkin atur jurusan_id menjadi NULL di database
    // Tergantung pada desain tabel kamu
    $jurusan_id = 'NULL'; // Gunakan string 'NULL' jika kolom bisa NULL
}


// --- Cari ID pembimbing berdasarkan nama ---
$pembimbing_id = null;
if (!empty($guru_nama)) {
    $guru_q = mysqli_query($koneksi, "SELECT id_pembimbing FROM guru_pembimbing WHERE nama_pembimbing = '$guru_nama'");
    if ($guru_q && mysqli_num_rows($guru_q) > 0) {
        $pembimbing_id = mysqli_fetch_assoc($guru_q)['id_pembimbing'];
    } else {
        $errors[] = "Guru Pembimbing '" . htmlspecialchars($guru_nama) . "' tidak ditemukan. Data tidak diperbarui.";
    }
} else {
    $pembimbing_id = 'NULL';
}


// --- Cari ID tempat PKL berdasarkan nama ---
$tempat_pkl_id = null;
if (!empty($tempat_nama)) {
    $tempat_q = mysqli_query($koneksi, "SELECT id_tempat_pkl FROM tempat_pkl WHERE nama_tempat_pkl = '$tempat_nama'");
    if ($tempat_q && mysqli_num_rows($tempat_q) > 0) {
        $tempat_pkl_id = mysqli_fetch_assoc($tempat_q)['id_tempat_pkl'];
    } else {
        $errors[] = "Tempat PKL '" . htmlspecialchars($tempat_nama) . "' tidak ditemukan. Data tidak diperbarui.";
    }
} else {
    $tempat_pkl_id = 'NULL';
}

// Jika ada error dari pencarian ID, tampilkan dan keluar
if (!empty($errors)) {
    $_SESSION['pesan_error'] = implode("<br>", $errors);
    header("Location: master_data_siswa.php"); // Atau kembali ke halaman edit
    exit();
}

// Cek jika password diisi
$password_input = $_POST['password'] ?? ''; // Ambil langsung dari POST, lalu cek empty
$password_query = "";

if (!empty($password_input)) {
    // Hash password baru (tidak perlu sanitize_for_sql untuk $password_input sebelum hash,
    // karena password_hash sudah aman, tapi kalau $_POST['password'] mau dipakai
    // di tempat lain di query (sangat jarang), baru di sanitize)
    $password_hashed = password_hash($password_input, PASSWORD_BCRYPT);
    $password_query = ", password = '$password_hashed'";
}

// --- Update data siswa ---
// Pastikan ID relasi yang ditemukan (atau NULL string) digunakan dengan benar
$query = "UPDATE siswa SET
            nama_siswa = '$nama_siswa',
            jenis_kelamin = '$jenis_kelamin',
            nisn = '$nisn',
            no_induk = '$no_induk',
            kelas = '$kelas',
            status = '$status',
            jurusan_id = " . ($jurusan_id === 'NULL' ? 'NULL' : "'$jurusan_id'") . ",
            pembimbing_id = " . ($pembimbing_id === 'NULL' ? 'NULL' : "'$pembimbing_id'") . ",
            tempat_pkl_id = " . ($tempat_pkl_id === 'NULL' ? 'NULL' : "'$tempat_pkl_id'") . "
            $password_query
          WHERE id_siswa = '$id_siswa'";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Update Siswa</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php
if (mysqli_query($koneksi, $query)) {
    echo "
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data siswa berhasil diperbarui!',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'master_data_siswa.php';
            }
        });
    </script>
    ";
} else {
    echo "
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Data gagal diperbarui: " . mysqli_error($koneksi) . ". Query: " . htmlspecialchars($query) . "', // Tampilkan query untuk debugging
            confirmButtonColor: '#d33',
            confirmButtonText: 'Coba Lagi'
        }).then(() => {
            window.history.back();
        });
    </script>
    ";
}

// Tutup koneksi
if (isset($koneksi)) {
    $koneksi->close();
}
?>
</body>
</html>