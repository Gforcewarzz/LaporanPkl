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

// Ambil ID laporan yang akan dihapus dari URL
$id_jurnal_harian = $_GET['id'] ?? null;

// Inisialisasi variabel untuk ID siswa yang akan digunakan dalam query
$siswa_id_filter = null;
$redirect_to_siswa_id = null; // Untuk mengarahkan admin kembali ke laporan siswa tertentu

// Validasi dasar: ID laporan harus ada
if (empty($id_jurnal_harian)) {
    $status = 'error';
    $message = 'ID laporan tidak valid.';
} else {
    // --- Ambil siswa_id dari laporan yang akan dihapus untuk verifikasi ---
    $query_get_siswa_id = "SELECT siswa_id FROM jurnal_harian WHERE id_jurnal_harian = ?";
    $stmt_get_siswa_id = $koneksi->prepare($query_get_siswa_id);

    if (!$stmt_get_siswa_id) {
        $status = 'error';
        $message = 'Gagal menyiapkan statement untuk verifikasi data: ' . $koneksi->error;
    } else {
        $stmt_get_siswa_id->bind_param("i", $id_jurnal_harian);
        $stmt_get_siswa_id->execute();
        $result_get_siswa_id = $stmt_get_siswa_id->get_result();
        $data_laporan_dihapus = $result_get_siswa_id->fetch_assoc();
        $stmt_get_siswa_id->close();

        if (!$data_laporan_dihapus) {
            $status = 'error';
            $message = 'Laporan tidak ditemukan.';
        } else {
            $siswa_id_dari_db = $data_laporan_dihapus['siswa_id'];
            $redirect_to_siswa_id = $siswa_id_dari_db; // Simpan untuk redirect admin

            // --- LOGIKA OTORISASI UNTUK PENGHAPUSAN ---
            $authorized_to_delete = false;
            if ($is_siswa && $siswa_id_dari_db == ($_SESSION['id_siswa'] ?? null)) {
                // Siswa hanya bisa menghapus laporan miliknya sendiri
                $authorized_to_delete = true;
            } elseif ($is_admin) {
                // Admin bisa menghapus laporan siapa saja
                $authorized_to_delete = true;
            }

            if (!$authorized_to_delete) {
                $status = 'error';
                $message = 'Anda tidak diizinkan menghapus laporan ini.';
            } else {
                // Lanjutkan proses penghapusan
                $query_delete = "DELETE FROM jurnal_harian WHERE id_jurnal_harian = ?";
                // Tambahkan siswa_id ke WHERE clause untuk keamanan ekstra, terutama untuk siswa
                if ($is_siswa) {
                    $query_delete .= " AND siswa_id = ?";
                }

                $stmt_delete = $koneksi->prepare($query_delete);

                if ($stmt_delete) {
                    if ($is_siswa) {
                        $stmt_delete->bind_param("ii", $id_jurnal_harian, $_SESSION['id_siswa']);
                    } elseif ($is_admin) {
                        // Admin menghapus hanya berdasarkan id_jurnal_harian, karena sudah diotorisasi di atas
                        $stmt_delete->bind_param("i", $id_jurnal_harian);
                    }

                    if ($stmt_delete->execute()) {
                        if ($stmt_delete->affected_rows > 0) {
                            $status = 'success';
                            $message = 'Laporan berhasil dihapus.';
                        } else {
                            // Ini bisa terjadi jika data tidak ditemukan (sudah dihapus oleh orang lain)
                            // atau siswa_id tidak cocok (untuk siswa)
                            $status = 'info';
                            $message = 'Laporan tidak ditemukan atau sudah dihapus.';
                        }
                    } else {
                        $status = 'error';
                        $message = 'Gagal menghapus data: ' . $stmt_delete->error;
                    }
                    $stmt_delete->close();
                } else {
                    $status = 'error';
                    $message = 'Gagal menyiapkan statement penghapusan: ' . $koneksi->error;
                }
            }
        }
    }
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Status Hapus</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <script>
    Swal.fire({
        icon: '<?php echo $status; ?>',
        title: '<?php echo ($status == "success" || $status == "info") ? "Berhasil!" : "Gagal!"; ?>',
        text: '<?php echo $message; ?>',
        showConfirmButton: false,
        timer: 2500
    }).then(() => {
        // Alihkan halaman berdasarkan peran yang login
        <?php if ($is_siswa): ?>
        window.location.href = 'master_kegiatan_harian.php';
        <?php elseif ($is_admin): ?>
        var redirectSiswaId = '<?php echo $redirect_to_siswa_id; ?>';
        if (redirectSiswaId) {
            // Jika admin menghapus laporan siswa spesifik, kembali ke daftar laporan siswa tersebut
            window.location.href = 'master_kegiatan_harian.php?siswa_id=' + redirectSiswaId;
        } else {
            // Jika tidak ada siswa_id yang spesifik (misal admin melihat semua laporan), kembali ke halaman daftar laporan umum admin
            window.location.href = 'master_kegiatan_harian.php';
        }
        <?php endif; ?>
    });
    </script>

</body>

</html>