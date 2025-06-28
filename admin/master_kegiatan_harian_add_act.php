<?php
session_start();
include 'partials/db.php';

// --- LOGIKA KEAMANAN HALAMAN ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

if (!$is_siswa && !$is_admin) {
    if (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        header('Location: ../halaman_guru.php');
        exit();
    } else {
        header('Location: ../login.php');
        exit();
    }
}

// Ambil data dari form dengan aman
$tanggal   = $_POST['tanggal'] ?? '';
$pekerjaan = htmlspecialchars($_POST['pekerjaan'] ?? '');
$catatan   = htmlspecialchars($_POST['catatan'] ?? '');

// --- PENENTUAN SISWA_ID UNTUK DATABASE ---
$siswa_id_untuk_db = null; // Inisialisasi dengan null

if ($is_siswa) {
    // Jika siswa yang login, ambil ID siswa dari sesi mereka sendiri
    $siswa_id_untuk_db = $_SESSION['id_siswa'] ?? null;
} elseif ($is_admin) {
    // Jika admin yang login, ID siswa harus berasal dari input formulir
    // (Misalnya, dropdown di halaman `master_kegiatan_harian_add.php`
    // dengan `name="selected_siswa_id"`)
    $siswa_id_untuk_db = $_POST['selected_siswa_id'] ?? null;
}

// Validasi data
if (empty($tanggal) || empty($pekerjaan) || empty($siswa_id_untuk_db)) {
    $status = 'error';
    $message = 'Tanggal, deskripsi pekerjaan, dan ID siswa wajib diisi. Mohon lengkapi semua informasi.';
} else {
    // Gunakan prepared statement untuk keamanan
    $query = "INSERT INTO jurnal_harian (tanggal, pekerjaan, catatan, siswa_id) VALUES (?, ?, ?, ?)";

    $stmt = $koneksi->prepare($query);

    if ($stmt) {
        // 'sssi' artinya 3 parameter string dan 1 parameter integer
        $stmt->bind_param("sssi", $tanggal, $pekerjaan, $catatan, $siswa_id_untuk_db);

        if ($stmt->execute()) {
            $status = 'success';
            $message = 'Laporan harian berhasil disimpan!';
        } else {
            $status = 'error';
            $message = 'Gagal menyimpan data: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $status = 'error';
        $message = 'Gagal menyiapkan statement database: ' . $koneksi->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Status Simpan</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <script>
    Swal.fire({
        icon: '<?php echo $status; ?>',
        title: '<?php echo ($status == "success") ? "Berhasil!" : "Gagal!"; ?>',
        text: '<?php echo $message; ?>',
        showConfirmButton: false,
        timer: 2500
    }).then(() => {
        // Alihkan halaman berdasarkan peran yang login
        <?php if ($is_siswa): ?>
        window.location.href = 'master_kegiatan_harian.php';
        <?php elseif ($is_admin): ?>
        // Jika admin, cek apakah mereka sedang menginput untuk siswa spesifik (ada siswa_id di POST)
        // Jika tidak ada siswa_id di POST (berarti admin mungkin mengakses add langsung),
        // alihkan ke halaman daftar semua laporan harian siswa untuk admin.
        // Anda mungkin perlu menyesuaikan URL ini jika halaman daftar admin berbeda.
        var selectedSiswaId = '<?php echo $siswa_id_untuk_db; ?>'; // Ambil ID siswa yang baru saja diinput
        if (selectedSiswaId) {
            window.location.href = 'master_kegiatan_harian.php?siswa_id=' +
            selectedSiswaId; // Kembali ke laporan siswa yang baru diinput
        } else {
            window.location.href =
            'master_kegiatan_harian.php'; // Kembali ke halaman utama laporan harian admin (mungkin menampilkan semua)
        }
        <?php endif; ?>
    });
    </script>

</body>

</html>