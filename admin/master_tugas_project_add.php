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
                                <span class="text-muted fw-light">Laporan Tugas /</span> Tambah Proyek
                            </h4>
                            <i class="fas fa-file-invoice fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>
                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Catat Detail Proyek PKLmu!</h5>
                                    <p class="card-text text-white-75 small">Setiap detail kecil sangat berarti untuk
                                        laporanmu.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; overflow: hidden; z-index: 1;">
                                        <i class="bx bx-task bx-lg text-primary"
                                            style="font-size: 3rem; opacity: 1;"></i>
                                    </div>
                                    <div class="position-absolute rounded-circle bg-white"
                                        style="width: 50px; height: 50px; opacity: 0.1; top: -10px; left: -10px; transform: scale(0.6); z-index: 0;">
                                    </div>
                                    <div class="position-absolute rounded-circle bg-white"
                                        style="width: 60px; height: 60px; opacity: 0.15; bottom: -10px; right: -10px; transform: scale(0.8); z-index: 0;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card shadow-lg animate__animated animate__fadeInUp" style="border-radius: 10px;">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Isi Detail Tugas Proyek</h5>
                                <small class="text-muted">Lengkapi semua informasi yang diperlukan.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="proses_tambah_laporan_tugas.php" method="POST">
                                    <div
                                        class="row mb-4 bg-light p-3 rounded animate__animated animate__fadeInLeft animate__delay-0-2s">
                                        <div class="col-12 col-md-6 mb-2">
                                            <strong class="d-block text-dark mb-1"><i class="bx bx-user me-1"></i> Nama
                                                Peserta Didik:</strong>
                                            <p class="mb-0 text-muted">Budi Santoso</p>
                                            <input type="hidden" name="nama_peserta_didik" value="Budi Santoso">
                                        </div>
                                        <div class="col-12 col-md-6 mb-2">
                                            <strong class="d-block text-dark mb-1"><i class="bx bx-building me-1"></i>
                                                Dunia Kerja Tempat PKL:</strong>
                                            <p class="mb-0 text-muted">PT. Inovasi Digital (Software House)</p>
                                            <input type="hidden" name="dunia_kerja_tempat_pkl"
                                                value="PT. Inovasi Digital (Software House)">
                                        </div>
                                        <div class="col-12 col-md-6 mb-2 mb-md-0">
                                            <strong class="d-block text-dark mb-1"><i class="bx bx-user-check me-1"></i>
                                                Nama Instruktur:</strong>
                                            <p class="mb-0 text-muted">Bpk. Joni Iskandar, S.T.</p>
                                            <input type="hidden" name="nama_instruktur"
                                                value="Bpk. Joni Iskandar, S.T.">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <strong class="d-block text-dark mb-1"><i class="bx bx-user-voice me-1"></i>
                                                Nama Guru Pembimbing:</strong>
                                            <p class="mb-0 text-muted">Ibu Endang Susanti, S.Kom., M.TI.</p>
                                            <input type="hidden" name="nama_guru_pembimbing"
                                                value="Ibu Endang Susanti, S.Kom., M.TI.">
                                        </div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-3s">
                                        <label for="nama_pekerjaan" class="form-label fw-bold"><i
                                                class="bx bx-folder me-1"></i> Nama Pekerjaan / Proyek:</label>
                                        <input type="text" class="form-control" id="nama_pekerjaan"
                                            name="nama_pekerjaan"
                                            placeholder="Contoh: Pengembangan Aplikasi Mobile E-Commerce" required>
                                        <div class="form-text text-muted">Tuliskan nama proyek atau pekerjaan utama yang
                                            sedang dikerjakan.</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-4s">
                                        <label for="perencanaan_kegiatan" class="form-label fw-bold"><i
                                                class="bx bx-list-check me-1"></i> Perencanaan Kegiatan:</label>
                                        <textarea class="form-control" id="perencanaan_kegiatan"
                                            name="perencanaan_kegiatan" rows="5"
                                            placeholder="Contoh:&#10;1. Analisis kebutuhan pengguna.&#10;2. Perancangan database dan API.&#10;3. Pembuatan storyboard aplikasi."
                                            required></textarea>
                                        <div class="form-text text-muted">Jelaskan rencana atau tahapan kegiatan proyek
                                            Anda.</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-5s">
                                        <label for="pelaksanaan_kegiatan" class="form-label fw-bold"><i
                                                class="bx bx-code-alt me-1"></i> Pelaksanaan Kegiatan / Hasil:</label>
                                        <textarea class="form-control" id="pelaksanaan_kegiatan"
                                            name="pelaksanaan_kegiatan" rows="7"
                                            placeholder="Contoh:&#10;1. Mengimplementasikan fitur login & registrasi.&#10;2. Berhasil mengintegrasikan API produk.&#10;3. Mengatasi bug pada halaman keranjang.&#10;(Lampirkan foto hasil jika memungkinkan di sistem nanti)"></textarea>
                                        <div class="form-text text-muted">Uraikan proses kerja yang sudah dilakukan dan
                                            hasil yang dicapai.</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-6s">
                                        <label for="catatan_instruktur" class="form-label fw-bold"><i
                                                class="bx bx-message-square-dots me-1"></i> Catatan Instruktur:</label>
                                        <textarea class="form-control" id="catatan_instruktur" name="catatan_instruktur"
                                            rows="4"
                                            placeholder="Diisi oleh instruktur (contoh: Progres sangat baik, perlu ditingkatkan dalam...)"></textarea>
                                        <div class="form-text text-muted">Catatan atau umpan balik dari instruktur Anda
                                            (jika ada).</div>
                                    </div>

                                    <hr class="my-4">

                                    <div
                                        class="d-flex flex-column flex-sm-row justify-content-end gap-2 animate__animated animate__fadeInUp animate__delay-0-7s">
                                        <a href="master_tugas_project.php"
                                            class="btn btn-outline-secondary w-100 w-sm-auto order-sm-first">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-refresh me-1"></i> Reset Form
                                        </button>
                                        <button type="submit" class="btn btn-primary w-100 w-sm-auto">
                                            <i class="bx bx-save me-1"></i> Simpan Laporan Proyek
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="layout-overlay layout-menu-toggle"></div>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <?php include './partials/script.php'; ?>
</body>

</html>