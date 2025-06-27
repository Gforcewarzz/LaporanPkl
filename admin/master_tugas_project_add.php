<?php
// Memulai sesi untuk bisa menggunakan $_SESSION
session_start();

// Keamanan: Periksa apakah siswa sudah login. Jika tidak, arahkan ke halaman login.
if (!isset($_SESSION['id_siswa'])) {
    header('Location: login.php');
    exit(); // Hentikan eksekusi skrip
}

// Mengambil id_siswa dari sesi untuk nanti dimasukkan ke dalam form
$id_siswa_session = $_SESSION['id_siswa'];
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">
<?php include 'partials/head.php' ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">Laporan Harian /</span> Tambah Kegiatan
                            </h4>
                            <i class="fas fa-file-invoice fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card shadow-lg animate__animated animate__fadeInUp" style="border-radius: 10px;">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Isi Detail Kegiatan Harian Anda</h5>
                                <small class="text-muted">Lengkapi semua informasi mengenai tugas atau aktivitas Anda
                                    hari ini.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="master_tugas_project_add_act.php" method="POST"
                                    enctype="multipart/form-data">

                                    <input type="hidden" name="siswa_id"
                                        value="<?php echo htmlspecialchars($id_siswa_session); ?>">

                                    <div class="mb-3">
                                        <label for="nama_pekerjaan" class="form-label fw-bold"><i
                                                class="bx bx-folder me-1"></i> Nama Tugas / Aktivitas Utama:</label>
                                        <input type="text" class="form-control" id="nama_pekerjaan"
                                            name="nama_pekerjaan"
                                            placeholder="Contoh: Perawatan mesin produksi, Meracik obat X, Mendesain logo perusahaan"
                                            required>
                                        <div class="form-text text-muted">Tuliskan nama singkat dari tugas atau
                                            aktivitas utama yang Anda kerjakan hari ini.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="perencanaan_kegiatan" class="form-label fw-bold"><i
                                                class="bx bx-list-check me-1"></i> Perencanaan Kegiatan
                                            (sebelumnya):</label>
                                        <textarea class="form-control" id="perencanaan_kegiatan"
                                            name="perencanaan_kegiatan" rows="5" placeholder="Contoh:&#10;1. Menyiapkan peralatan dan bahan yang dibutuhkan.&#10;2. Membaca instruksi kerja atau prosedur standar.&#10;3. Berkoordinasi dengan supervisor atau rekan kerja.
" required></textarea>
                                        <div class="form-text text-muted">Jelaskan rencana atau tahapan persiapan
                                            kegiatan yang Anda lakukan hari ini.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="pelaksanaan_kegiatan" class="form-label fw-bold"><i
                                                class="bx bx-code-alt me-1"></i> Pelaksanaan Kegiatan & Hasil yang
                                            Dicapai:</label>
                                        <textarea class="form-control" id="pelaksanaan_kegiatan"
                                            name="pelaksanaan_kegiatan" rows="7" placeholder="Contoh:&#10;1. Melakukan kalibrasi alat sesuai prosedur, hasilnya akurasi meningkat 5%.&#10;2. Menggambar denah bangunan dengan skala 1:50, sudah diverifikasi oleh pembimbing.&#10;3. Berhasil membuat laporan keuangan harian dan tidak ada selisih.
" required></textarea>
                                        <div class="form-text text-muted">Uraikan secara detail langkah-langkah yang
                                            sudah Anda lakukan dan hasil konkret yang berhasil dicapai dari kegiatan
                                            tersebut.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="gambar_proyek" class="form-label fw-bold"><i
                                                class="bx bx-image me-1"></i> Unggah Bukti Kegiatan
                                            (Foto/Screenshot):</label>
                                        <input class="form-control" type="file" id="gambar_proyek" name="gambar_proyek"
                                            accept="image/*" required>
                                        <div class="form-text text-muted">Unggah foto atau screenshot sebagai bukti
                                            visual kegiatan Anda. Format: JPG, PNG, GIF. Maks. ukuran 2MB.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="catatan_instruktur" class="form-label fw-bold"><i
                                                class="bx bx-message-square-dots me-1"></i> Catatan Instruktur:</label>
                                        <textarea class="form-control" id="catatan_instruktur" name="catatan_instruktur"
                                            rows="4"
                                            placeholder="Kolom ini akan diisi oleh instruktur pembimbing Anda (contoh: Progres sangat baik, teruskan!)"></textarea>
                                        <div class="form-text text-muted">Kolom ini disediakan untuk catatan atau umpan
                                            balik dari instruktur pembimbing Anda (opsional).</div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                                        <a href="master_tugas_project.php"
                                            class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="reset" class="btn btn-outline-warning w-100 w-sm-auto">
                                            <i class="bx bx-refresh me-1"></i> Reset Form
                                        </button>
                                        <button type="submit" class="btn btn-primary w-100 w-sm-auto">
                                            <i class="bx bx-save me-1"></i> Simpan Laporan Harian
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <?php include './partials/script.php'; ?>
</body>

</html>