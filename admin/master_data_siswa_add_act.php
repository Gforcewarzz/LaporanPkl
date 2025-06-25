<?php
// Pastikan file koneksi database sudah terhubung dengan benar.
// PENTING: Pastikan partials/db.php MENGINISIALISASI variabel $koneksi
// Contoh di partials/db.php:
// $koneksi = mysqli_connect("localhost", "username", "password", "nama_database");
// if (!$koneksi) { die("Koneksi database gagal: " . mysqli_connect_error()); }
require "partials/db.php";

// Cek apakah $koneksi sudah terdefinisi dan bukan NULL setelah require db.php
if (!isset($koneksi) || $koneksi === null) {
    die("Error: Koneksi database (\$koneksi) tidak tersedia setelah menyertakan partials/db.php. Pastikan partials/db.php menginisialisasi \$koneksi dengan benar.");
}

/**
 * Fungsi untuk membersihkan dan mengamankan input dari pengguna.
 * @param string $data Data input yang akan dibersihkan.
 * @return string Data yang sudah dibersihkan dan aman.
 */
function sanitize_input($data)
{
    // Menggunakan koneksi global. Pastikan $koneksi sudah terinisialisasi.
    global $koneksi;

    // Hapus spasi di awal dan akhir string
    $data = trim($data);
    // Hapus backslashes (meskipun modern PHP jarang butuh ini, tidak ada salahnya)
    $data = stripslashes($data);
    // Konversi karakter khusus HTML ke entitas HTML untuk mencegah XSS
    $data = htmlspecialchars($data);
    // Escape string untuk database untuk mencegah SQL Injection
    // Penting: Pastikan $koneksi bukan NULL di sini
    if ($koneksi) { // Cek lagi untuk berjaga-jaga
        $data = mysqli_real_escape_string($koneksi, $data);
    }
    return $data;
}

/**
 * Fungsi untuk menambahkan data siswa ke database.
 * @param array $data Data dari form POST.
 * @return int Jumlah baris yang terpengaruh (1 jika berhasil, 0 jika gagal), atau false jika ada validasi error.
 */
function tambahSiswa($data)
{
    global $koneksi; // Pastikan $koneksi tersedia dari scope global

    // Sanitize dan ambil data dari form
    $nama_siswa = sanitize_input($data["nama_siswa"]);
    $no_induk = sanitize_input($data["no_induk"]);
    $nisn = sanitize_input($data["nisn"]);
    $jenis_kelamin = sanitize_input($data["jenis_kelamin"]);
    $kelas = sanitize_input($data["kelas"]);
    $id_jurusan = sanitize_input($data["jurusan"]); // Ini sudah id_jurusan dari form

    // Ambil password dan konfirmasi password tanpa sanitasi awal karena akan di-hash
    $password = $data["password"];
    $confirm_password = $data["confirm_password"];

    // --- Validasi Sisi Server untuk Password ---
    if ($password !== $confirm_password) {
        echo "<script>alert('Password dan Konfirmasi Password tidak cocok!');</script>";
        return false; // Mengembalikan false agar tidak melanjutkan proses tambah data
    }

    if (strlen($password) < 4) {
        echo "<script>alert('Password minimal harus 4 karakter!');</script>";
        return false;
    }
    // --- Akhir Validasi Password ---

    // Hash password sebelum disimpan ke database (SANGAT PENTING!)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // --- Ambil ID Guru Pembimbing dan Tempat PKL dari Nama ---
    // Pastikan Anda mendapatkan ID yang benar dari nama yang diinput
    // Perhatikan penanganan jika nama tidak ditemukan (kembalikan NULL)

    // Cari id_pembimbing dari nama_pembimbing
    $guru_pendamping_nama = sanitize_input($data["guru_pendamping"]);
    $query_guru = "SELECT id_pembimbing FROM guru_pembimbing WHERE nama_pembimbing = '$guru_pendamping_nama'";
    $result_guru = mysqli_query($koneksi, $query_guru);
    if (!$result_guru) {
        error_log("Error query guru: " . mysqli_error($koneksi)); // Log error
        echo "<script>alert('Terjadi kesalahan saat mencari guru pendamping.');</script>";
        return false;
    }
    $row_guru = mysqli_fetch_assoc($result_guru);
    $id_guru_pembimbing = $row_guru ? $row_guru['id_pembimbing'] : NULL;

    // Cari id_tempat_pkl dari nama_tempat_pkl
    $tempat_pkl_nama = sanitize_input($data["tempat_pkl"]);
    $query_tempat = "SELECT id_tempat_pkl FROM tempat_pkl WHERE nama_tempat_pkl = '$tempat_pkl_nama'";
    $result_tempat = mysqli_query($koneksi, $query_tempat);
    if (!$result_tempat) {
        error_log("Error query tempat PKL: " . mysqli_error($koneksi)); // Log error
        echo "<script>alert('Terjadi kesalahan saat mencari tempat PKL.');</script>";
        return false;
    }
    $row_tempat = mysqli_fetch_assoc($result_tempat);
    $id_tempat_pkl = $row_tempat ? $row_tempat['id_tempat_pkl'] : NULL;

    // Tambahkan validasi jika ID tidak ditemukan (opsional, tergantung kebutuhan)
    if ($id_guru_pembimbing === NULL) {
        echo "<script>alert('Guru Pendamping tidak ditemukan. Pastikan nama yang Anda masukkan sudah terdaftar.');</script>";
        return false;
    }
    if ($id_tempat_pkl === NULL) {
        echo "<script>alert('Tempat PKL tidak ditemukan. Pastikan nama yang Anda masukkan sudah terdaftar.');</script>";
        return false;
    }

    // --- Cek apakah ada data duplikat untuk no_induk atau nisn ---
    $check_duplicate_query = "SELECT COUNT(*) FROM siswa WHERE no_induk = '$no_induk' OR nisn = '$nisn'";
    $duplicate_result = mysqli_query($koneksi, $check_duplicate_query);
    if (!$duplicate_result) {
        error_log("Error check duplicate: " . mysqli_error($koneksi)); // Log error
        echo "<script>alert('Terjadi kesalahan saat memeriksa duplikasi data.');</script>";
        return false;
    }
    $count = mysqli_fetch_array($duplicate_result)[0];

    if ($count > 0) {
        echo "<script>alert('No Induk atau NISN sudah terdaftar. Mohon gunakan data yang berbeda.');</script>";
        return false;
    }
    // --- Akhir Cek Duplikat ---

    // Sanitize status_siswa
    $status_siswa = sanitize_input($data["status_siswa"]);

    // Query INSERT data siswa ke database
    // Sesuaikan nama kolom tabel Anda jika berbeda:
    // nama_siswa, no_induk, nisn, password (hashed), jenis_kelamin, kelas, id_jurusan, id_guru_pembimbing, id_tempat_pkl, status_siswa
    $query = "INSERT INTO siswa (nama_siswa, no_induk, nisn, password, jenis_kelamin, kelas, jurusan_id, pembimbing_id, tempat_pkl_id, status)
              VALUES ('$nama_siswa', '$no_induk', '$nisn', '$hashed_password', '$jenis_kelamin', '$kelas', '$id_jurusan', '$id_guru_pembimbing', '$id_tempat_pkl', '$status_siswa')";
    $insert_result = mysqli_query($koneksi, $query);

    if (!$insert_result) {
        error_log("Error insert data siswa: " . mysqli_error($koneksi)); // Log error
        echo "<script>alert('Terjadi kesalahan saat menyimpan data siswa ke database.');</script>";
        return false;
    }

    return mysqli_affected_rows($koneksi);
}

// --- Main Logic ---
// Cek apakah tombol submit sudah ditekan
if (isset($_POST["submit"])) {
    // Panggil fungsi tambahSiswa
    $add_success = tambahSiswa($_POST);

    if ($add_success > 0) {
        echo "
        <script>
            alert('Data siswa berhasil ditambahkan!');
            document.location.href = 'master_data_siswa.php';
        </script>
        ";
    } else {
        // Pesan error sudah ditampilkan di dalam fungsi tambahSiswa()
        // Ini adalah fallback jika ada error yang tidak tertangani di fungsi,
        // atau jika fungsi tambahSiswa() mengembalikan false.
        echo "
        <script>
            // alert('Data siswa gagal ditambahkan! Pastikan semua data benar dan tidak ada duplikasi.');
            document.location.href = 'master_data_siswa.php'; // Redirect saja, alert sudah ada dari fungsi
        </script>";
    }
}
