<?php
// Sertakan file koneksi database Anda
include 'partials/db.php'; // Sesuaikan path ini
session_start();

// --- LOGIKA KEAMANAN HALAMAN SISWA ---

// 1. Definisikan dulu role yang sedang login untuk mempermudah pembacaan kode.
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

// 2. Aturan utama: Cek jika pengguna BUKAN Siswa DAN BUKAN Admin.
// Jika salah satu dari mereka (siswa atau admin) login, kondisi ini akan false dan halaman akan lanjut dimuat.
if (!$is_siswa && !$is_admin) {

    // 3. Jika tidak diizinkan, baru kita cek siapa pengguna ini.
    // Apakah dia seorang Guru yang mencoba masuk?
    if (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
        // Jika benar guru, kembalikan ke halaman dasbor guru.
        header('Location: ../halaman_guru.php'); // Sesuaikan path jika perlu
        exit();
    }
    // 4. Jika bukan siapa-siapa dari role di atas, artinya pengguna belum login.
    else {
        // Arahkan paksa ke halaman login.
        header('Location: ../login.php'); // Sesuaikan path jika perlu
        exit();
    }
}
// 2. Sertakan file koneksi database
include 'partials/db.php';

/**
 * Fungsi untuk menampilkan SweetAlert dan melakukan redirect via JavaScript.
 * (Dibuat ulang di sini agar mandiri, atau bisa di-include dari file helper)
 *
 * @param string $icon        Ikon alert ('success', 'error', 'warning', 'info')
 * @param string $title       Judul alert
 * @param string $text        Teks atau pesan dalam alert
 * @param string $redirectUrl URL tujuan setelah alert ditutup
 */
function showAlertAndRedirect($icon, $title, $text, $redirectUrl)
{
    ob_clean();
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Notifikasi</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{$icon}',
                    title: '{$title}',
                    text: '{$text}',
                    confirmButtonColor: '#696cff',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{$redirectUrl}';
                    }
                });
            });
        </script>
    </body>
    </html>
HTML;
    exit();
}

// Keamanan: Periksa apakah siswa sudah login
if (!isset($_SESSION['id_siswa'])) {
    showAlertAndRedirect('error', 'Akses Ditolak', 'Anda harus login untuk mengakses halaman ini.', '../login.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_jurnal_kegiatan = $_POST['id_jurnal_kegiatan'] ?? null;
    $gambar_lama = $_POST['gambar_lama'] ?? null; // Nama gambar lama dari hidden input
    $nama_pekerjaan = trim($_POST['nama_pekerjaan'] ?? '');
    $perencanaan_kegiatan = trim($_POST['perencanaan_kegiatan'] ?? '');
    $pelaksanaan_kegiatan = trim($_POST['pelaksanaan_kegiatan'] ?? '');
    $catatan_instruktur = trim($_POST['catatan_instruktur'] ?? '');

    $gambar_nama_file_baru = $gambar_lama; // Default: tetap pakai gambar lama
    $upload_dir = 'images/'; // Direktori tempat gambar disimpan (relatif dari admin/ folder)

    // Validasi dasar
    if (empty($id_jurnal_kegiatan) || empty($nama_pekerjaan) || empty($perencanaan_kegiatan) || empty($pelaksanaan_kegiatan)) {
        showAlertAndRedirect(
            'error',
            'Gagal Mengupdate',
            'Semua kolom wajib diisi (Nama Kegiatan, Perencanaan, Pelaksanaan).',
            'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan)
        );
    }

    // --- Penanganan Upload Gambar Baru (Opsional, karena di form edit bisa tidak ganti gambar) ---
    if (isset($_FILES['gambar_proyek']) && $_FILES['gambar_proyek']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar_proyek']['tmp_name'];
        $file_name = $_FILES['gambar_proyek']['name'];
        $file_size = $_FILES['gambar_proyek']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        $max_file_size = 2 * 1024 * 1024; // 2 MB

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                showAlertAndRedirect('error', 'Gagal Upload', 'Tidak dapat membuat direktori upload.', 'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan));
            }
        }

        if (!in_array($file_ext, $allowed_extensions)) {
            showAlertAndRedirect('error', 'Gagal Upload', 'Ekstensi file tidak diizinkan. Hanya JPG, JPEG, PNG, GIF.', 'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan));
        } elseif ($file_size > $max_file_size) {
            showAlertAndRedirect('error', 'Gagal Upload', 'Ukuran file terlalu besar. Maksimal 2MB.', 'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan));
        } else {
            // Hapus gambar lama jika ada dan gambar baru berhasil diunggah
            if (!empty($gambar_lama) && file_exists($upload_dir . $gambar_lama)) {
                unlink($upload_dir . $gambar_lama); // Hapus file lama
            }

            // Generate nama file unik untuk gambar baru
            $new_file_name = uniqid('proyek_edit_', true) . '.' . $file_ext;
            $destination_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination_path)) {
                $gambar_nama_file_baru = $new_file_name; // Update nama file untuk disimpan ke DB
            } else {
                showAlertAndRedirect('error', 'Gagal Upload', 'Gagal memindahkan file gambar baru.', 'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan));
            }
        }
    }
    // --- Akhir Penanganan Upload Gambar Baru ---

    // Siapkan query UPDATE menggunakan prepared statement
    // Termasuk kolom 'gambar' dan 'tanggal_laporan' (tanggal_laporan di-update ke NOW() atau dibiarkan seperti sebelumnya jika tidak diubah)
    $sql = "UPDATE jurnal_kegiatan SET 
                nama_pekerjaan = ?, 
                perencanaan_kegiatan = ?, 
                pelaksanaan_kegiatan = ?, 
                catatan_instruktur = ?, 
                gambar = ?,
                tanggal_laporan = NOW() -- Update tanggal laporan ke waktu sekarang
            WHERE id_jurnal_kegiatan = ? AND siswa_id = ?";

    $stmt = $koneksi->prepare($sql);

    if ($stmt) {
        // Bind parameter: s (string) untuk nama_pekerjaan s/d catatan_instruktur, s untuk gambar, i (integer) untuk id_jurnal_kegiatan dan siswa_id
        $stmt->bind_param(
            "sssssii",
            $nama_pekerjaan,
            $perencanaan_kegiatan,
            $pelaksanaan_kegiatan,
            $catatan_instruktur,
            $gambar_nama_file_baru, // Gunakan nama file gambar yang baru (atau lama jika tidak diganti)
            $id_jurnal_kegiatan,
            $_SESSION['id_siswa'] // Pastikan hanya siswa yang login yang bisa mengedit laporannya
        );

        if ($stmt->execute()) {
            showAlertAndRedirect(
                'success',
                'Berhasil!',
                'Laporan kegiatan harian berhasil diperbarui.',
                'master_tugas_project.php' // Redirect ke halaman daftar laporan
            );
        } else {
            showAlertAndRedirect(
                'error',
                'Gagal Mengupdate',
                'Terjadi kesalahan saat memperbarui data: ' . $stmt->error,
                'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan)
            );
        }
        $stmt->close();
    } else {
        showAlertAndRedirect(
            'error',
            'Gagal',
            'Terjadi kesalahan pada persiapan query database: ' . $koneksi->error,
            'master_tugas_project_edit.php?id=' . htmlspecialchars($id_jurnal_kegiatan)
        );
    }

    $koneksi->close();
} else {
    // Jika tidak diakses melalui POST
    showAlertAndRedirect('error', 'Akses Tidak Valid', 'Halaman ini hanya dapat diakses melalui pengiriman formulir.', 'master_tugas_project.php');
}