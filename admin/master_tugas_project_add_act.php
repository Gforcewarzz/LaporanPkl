<?php
// Sertakan file koneksi database Anda
include 'partials/db.php';
session_start(); // Pastikan session sudah dimulai

/**
 * Fungsi untuk menampilkan SweetAlert dan melakukan redirect via JavaScript.
 *
 * @param string $icon        Ikon alert ('success', 'error', 'warning', 'info')
 * @param string $title       Judul alert
 * @param string $text        Teks atau pesan dalam alert
 * @param string $redirectUrl URL tujuan setelah alert ditutup
 */
function showAlertAndRedirect($icon, $title, $text, $redirectUrl)
{
    ob_clean(); // Membersihkan output buffer jika ada
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

// Pastikan skrip ini diakses melalui metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Ambil data dari form
    $siswa_id = $_POST['siswa_id'] ?? null;
    // $tanggal_laporan tidak lagi diambil dari POST, akan menggunakan NOW() di SQL
    $nama_pekerjaan = trim($_POST['nama_pekerjaan'] ?? '');
    $perencanaan_kegiatan = trim($_POST['perencanaan_kegiatan'] ?? '');
    $pelaksanaan_kegiatan = trim($_POST['pelaksanaan_kegiatan'] ?? '');
    $catatan_instruktur = trim($_POST['catatan_instruktur'] ?? '');

    $gambar_nama_file = null; // Inisialisasi untuk nama file gambar yang akan disimpan
    $upload_dir = 'images/'; // Direktori tempat gambar akan disimpan.

    // --- Validasi Input Teks (Wajib) ---
    // Hapus $tanggal_laporan dari validasi empty, karena tidak dari POST lagi
    if (empty($siswa_id) || empty($nama_pekerjaan) || empty($perencanaan_kegiatan) || empty($pelaksanaan_kegiatan)) {
        showAlertAndRedirect(
            'error',
            'Gagal Menyimpan',
            'Semua kolom teks (Nama Kegiatan, Perencanaan, Pelaksanaan) wajib diisi.',
            'master_tugas_project_add.php' // Kembali ke form
        );
    }

    // --- Penanganan dan Validasi Upload Gambar (Wajib) ---
    // Pastikan direktori upload ada dan writable
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            showAlertAndRedirect(
                'error',
                'Gagal Upload Gambar',
                'Tidak dapat membuat direktori upload. Periksa izin folder server.',
                'master_tugas_project_add.php'
            );
        }
    }

    if (!isset($_FILES['gambar_proyek']) || $_FILES['gambar_proyek']['error'] == UPLOAD_ERR_NO_FILE) {
        showAlertAndRedirect(
            'error',
            'Gagal Upload Gambar',
            'Bukti kegiatan (foto/screenshot) wajib diunggah.', // Pesan lebih generik
            'master_tugas_project_add.php'
        );
    } elseif ($_FILES['gambar_proyek']['error'] != UPLOAD_ERR_OK) {
        showAlertAndRedirect(
            'error',
            'Gagal Upload Gambar',
            'Terjadi kesalahan saat mengunggah bukti kegiatan. Kode error: ' . $_FILES['gambar_proyek']['error'],
            'master_tugas_project_add.php'
        );
    } else {
        $file_tmp = $_FILES['gambar_proyek']['tmp_name'];
        $file_name = $_FILES['gambar_proyek']['name'];
        $file_size = $_FILES['gambar_proyek']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        $max_file_size = 2 * 1024 * 1024; // 2 MB

        if (!in_array($file_ext, $allowed_extensions)) {
            showAlertAndRedirect(
                'error',
                'Gagal Upload Gambar',
                'Ekstensi bukti kegiatan tidak diizinkan. Hanya JPG, JPEG, PNG, GIF.',
                'master_tugas_project_add.php'
            );
        } elseif ($file_size > $max_file_size) {
            showAlertAndRedirect(
                'error',
                'Gagal Upload Gambar',
                'Ukuran bukti kegiatan terlalu besar. Maksimal 2MB.',
                'master_tugas_project_add.php'
            );
        } else {
            // Generate nama file unik
            $new_file_name = uniqid('proyek_', true) . '.' . $file_ext;
            $destination_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination_path)) {
                $gambar_nama_file = $new_file_name; // Simpan nama file jika upload berhasil
            } else {
                showAlertAndRedirect(
                    'error',
                    'Gagal Upload Gambar',
                    'Gagal memindahkan file gambar ke direktori penyimpanan.',
                    'master_tugas_project_add.php'
                );
            }
        }
    }
    // --- Akhir Penanganan dan Validasi Upload Gambar ---

    // Siapkan query INSERT menggunakan prepared statement
    // PASTIKAN 'jurnal_kegiatan' adalah nama tabel yang benar di DB Anda
    // PASTIKAN Anda memiliki kolom 'gambar' di tabel 'jurnal_kegiatan'
    // PASTIKAN Anda memiliki kolom 'tanggal_laporan' di tabel 'jurnal_kegiatan'
    // Kolom 'tanggal_laporan' akan diisi dengan NOW()
    $sql = "INSERT INTO jurnal_kegiatan
                (siswa_id, nama_pekerjaan, perencanaan_kegiatan, pelaksanaan_kegiatan, catatan_instruktur, gambar, tanggal_laporan)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $koneksi->prepare($sql);

    if ($stmt) {
        // Bind parameter: i (integer) untuk siswa_id, s (string) untuk 5 kolom berikutnya
        // Total 6 parameter yang dibind dari PHP: siswa_id, nama_pekerjaan, perencanaan_kegiatan, pelaksanaan_kegiatan, catatan_instruktur, gambar
        // NOW() adalah fungsi SQL, jadi tidak dibind dari PHP
        $stmt->bind_param(
            "isssss",
            $siswa_id,
            $nama_pekerjaan,
            $perencanaan_kegiatan,
            $pelaksanaan_kegiatan,
            $catatan_instruktur,
            $gambar_nama_file
        );

        // Eksekusi statement
        if ($stmt->execute()) {
            showAlertAndRedirect(
                'success',
                'Berhasil!',
                'Laporan kegiatan harian telah berhasil ditambahkan.',
                'master_tugas_project.php' // Arahkan ke halaman daftar laporan
            );
        } else {
            showAlertAndRedirect(
                'error',
                'Gagal Menyimpan',
                'Terjadi kesalahan saat menyimpan data ke database: ' . $stmt->error,
                'master_tugas_project_add.php'
            );
        }
        $stmt->close();
    } else {
        showAlertAndRedirect(
            'error',
            'Gagal',
            'Terjadi kesalahan pada persiapan query database: ' . $koneksi->error,
            'master_tugas_project_add.php'
        );
    }

    $koneksi->close();
} else {
    // Jika tidak diakses melalui POST, redirect ke halaman utama
    header("Location: index.php"); // Atau ke master_tugas_project_add.php
    exit();
}