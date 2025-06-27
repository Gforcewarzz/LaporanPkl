<?php
// penilaian_proses.php

// === Aktifkan error reporting penuh untuk debugging di development (NONAKTIFKAN DI PRODUKSI) ===
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// ==============================================================================================

// Sertakan file koneksi database
include 'partials/db.php';

// Sertakan file head untuk SweetAlert2
include 'partials/head.php';

// Pastikan koneksi DB berhasil
if (!$koneksi) {
    error_log("Koneksi database GAGAL di penilaian_proses.php: " . mysqli_connect_error());
    die("Terjadi kesalahan koneksi database saat memproses penilaian.");
}

// Cek apakah request datang dari metode POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari POST dan lakukan sanitasi
    $id_siswa = mysqli_real_escape_string($koneksi, $_POST['id_siswa'] ?? '');
    $tanggal_penilaian = mysqli_real_escape_string($koneksi, $_POST['tanggal_penilaian'] ?? '');

    // Data deskripsi umum (menggunakan null coalescing operator ?? untuk nilai default jika tidak ada)
    $deskripsi_soft_skills = mysqli_real_escape_string($koneksi, $_POST['deskripsi_umum_soft_skills'] ?? '');
    $deskripsi_norma_pos_k3lh = mysqli_real_escape_string($koneksi, $_POST['deskripsi_umum_norma_pos_k3lh'] ?? '');
    $deskripsi_kompetensi_teknis = mysqli_real_escape_string($koneksi, $_POST['deskripsi_umum_kompetensi_teknis'] ?? '');
    $deskripsi_bisnis_wirausaha = mysqli_real_escape_string($koneksi, $_POST['deskripsi_umum_bisnis_wirausaha'] ?? '');

    // Catatan pembimbing
    $catatan_guru_pembimbing = mysqli_real_escape_string($koneksi, $_POST['catatan_guru_pembimbing'] ?? '');
    $catatan_instruktur_industri = mysqli_real_escape_string($koneksi, $_POST['catatan_instruktur_industri'] ?? '');

    // Validasi dasar (pastikan id_siswa dan tanggal tidak kosong)
    if (empty($id_siswa) || empty($tanggal_penilaian)) {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal!',
                    text: 'ID Siswa atau Tanggal Penilaian tidak boleh kosong.',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'master_observasi.php';
                });
              </script>";
        exit();
    }


    // Mulai transaksi untuk memastikan semua insert berhasil atau tidak sama sekali
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Insert data ke tabel penilaian_siswa
        $query_insert_penilaian = "INSERT INTO penilaian_siswa (id_siswa, tanggal_penilaian, deskripsi_soft_skills, deskripsi_norma_pos_k3lh, deskripsi_kompetensi_teknis, deskripsi_bisnis_wirausaha, catatan_guru_pembimbing, catatan_instruktur_industri)
                                   VALUES ('$id_siswa', '$tanggal_penilaian', '$deskripsi_soft_skills', '$deskripsi_norma_pos_k3lh', '$deskripsi_kompetensi_teknis', '$deskripsi_bisnis_wirausaha', '$catatan_guru_pembimbing', '$catatan_instruktur_industri')";

        if (!mysqli_query($koneksi, $query_insert_penilaian)) {
            throw new Exception("Gagal menyimpan data penilaian utama: " . mysqli_error($koneksi) . " Query: " . $query_insert_penilaian);
        }

        $id_penilaian_baru = mysqli_insert_id($koneksi); // Ambil ID penilaian yang baru dibuat

        // 2. Insert data ke tabel detail_penilaian
        if (isset($_POST['nilai']) && is_array($_POST['nilai'])) {
            foreach ($_POST['nilai'] as $id_indikator => $nilai) {
                // Pastikan $id_indikator adalah ID dari DB, bukan indeks numerik acak
                $id_indikator_db = mysqli_real_escape_string($koneksi, $id_indikator);
                $nilai_db = mysqli_real_escape_string($koneksi, $nilai);

                // Pastikan $id_indikator_db adalah angka valid (bukan string kosong dari problem sebelumnya)
                if (!is_numeric($id_indikator_db) || $id_indikator_db <= 0) {
                    // Ini akan menangkap jika ID indikator yang masuk bukan angka valid dari form
                    throw new Exception("ID Indikator tidak valid atau kosong (bukan angka): '$id_indikator_db'. Pastikan form mengirimkan ID indikator yang benar.");
                }

                if (!is_numeric($nilai_db)) {
                    throw new Exception("Nilai untuk indikator ID '$id_indikator_db' tidak valid (bukan angka). Nilai diterima: '$nilai_db'");
                }

                $query_insert_detail = "INSERT INTO detail_penilaian (id_penilaian, id_indikator, nilai, deskripsi_tambahan)
                                        VALUES ('$id_penilaian_baru', '$id_indikator_db', '$nilai_db', NULL)";

                if (!mysqli_query($koneksi, $query_insert_detail)) {
                    throw new Exception("Gagal menyimpan detail penilaian untuk indikator ID '$id_indikator_db': " . mysqli_error($koneksi) . " Query: " . $query_insert_detail);
                }
            }
        } else {
            // Ini bisa terjadi jika tidak ada input nilai dikirimkan,
            // meskipun form penilaian_form.php seharusnya memastikan 'required'
            throw new Exception("Tidak ada data nilai indikator yang diterima dari formulir. Pastikan semua input nilai terisi.");
        }

        // Jika semua berhasil, commit transaksi
        mysqli_commit($koneksi);

        // Notifikasi sukses menggunakan SweetAlert2
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Penilaian siswa berhasil disimpan.',
                    showConfirmButton: false,
                    timer: 2000
                }).then(function() {
                    window.location.href = 'laporan_penilaian_histori.php?id_siswa=" . $id_siswa . "';
                });
              </script>";
    } catch (Exception $e) {
        // Jika ada error, rollback transaksi
        mysqli_rollback($koneksi);

        // Notifikasi error menggunakan SweetAlert2
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: 'Terjadi kesalahan saat menyimpan penilaian:<br><b>" . htmlspecialchars($e->getMessage()) . "</b>',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'master_observasi_form_penilaian.php?id_siswa=" . $id_siswa . "'; // Kembali ke form
                });
              </script>";
        error_log("Penilaian Proses Error: " . $e->getMessage()); // Log error untuk debugging
    }

    // Tutup koneksi database
    mysqli_close($koneksi);
} else {
    // Jika akses langsung ke file ini tanpa POST request
    header("Location: master_observasi.php"); // Kembali ke daftar siswa
    exit("Akses tidak sah.");
}
