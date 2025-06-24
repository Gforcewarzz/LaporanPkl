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

                        <?php
                        // --- DATA STATIS KEGIATAN HARIAN UNTUK SIMULASI PENGAMBILAN DARI DATABASE ---
                        $allActivities = [
                            '1' => [
                                'id' => '1',
                                'tanggal_kegiatan' => '2025-06-23', // Tanggal yang sudah ada
                                'pekerjaan' => 'Membantu setup jaringan kantor dan instalasi OS.',
                                'catatan' => 'Selesai 80%, perlu melanjutkan esok hari. Terdapat sedikit kendala teknis saat menginstalasi driver printer, namun berhasil diatasi setelah berkonsultasi dengan instruktur lapangan.'
                            ],
                            '2' => [
                                'id' => '2',
                                'tanggal_kegiatan' => '2025-06-24',
                                'pekerjaan' => 'Menganalisis kebutuhan sistem inventaris baru.',
                                'catatan' => 'Sudah berdiskusi dengan tim, memahami alur kerja.'
                            ],
                            '3' => [
                                'id' => '3',
                                'tanggal_kegiatan' => '2025-06-25',
                                'pekerjaan' => 'Membuat desain antarmuka pengguna (UI) modul laporan.',
                                'catatan' => 'Menyelesaikan 3 mock-up awal menggunakan Figma.'
                            ],
                        ];

                        // Ambil ID kegiatan dari URL (misal: master_kegiatan_harian_edit.php?id=1)
                        $activityId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null;
                        $activityData = $allActivities[$activityId] ?? null; // Ambil data dari array statis

                        if (!$activityData) {
                            // Jika ID tidak ditemukan di array statis, tampilkan pesan error atau redirect
                            echo '<div class="alert alert-danger" role="alert">Data kegiatan harian tidak ditemukan atau ID tidak valid.</div>';
                            // header('Location: master_kegiatan_harian.php'); // Uncomment ini jika ingin redirect
                            // exit;
                        }

                        // Data Statis untuk Datalist (Saran umum untuk pekerjaan dan catatan)
                        $pekerjaan_options = [
                            'Membantu konfigurasi server',
                            'Melakukan instalasi software',
                            'Riset teknologi baru',
                            'Membuat dokumentasi proyek',
                            'Debugging kode program',
                            'Mendesain UI/UX'
                        ];
                        $catatan_options = [
                            'Kendala teknis berhasil diatasi.',
                            'Perlu follow up besok.',
                            'Sangat memahami materi yang diberikan.',
                            'Menemukan bug minor.',
                            'Belajar fitur baru di aplikasi X.'
                        ];
                        ?>

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">Laporan Harian /</span> Edit Laporan
                            </h4>
                            <i class="fas fa-edit fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>
                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Perbarui Laporan Kegiatanmu!</h5>
                                    <p class="card-text text-white-75 small">Sesuaikan detail kegiatan harianmu.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; overflow: hidden; z-index: 1;">
                                        <i class="bx bx-receipt bx-lg text-primary"
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
                                <h5 class="card-title mb-0">Formulir Edit Laporan Kegiatan</h5>
                                <small class="text-muted">Isi kolom yang ingin diubah, sisanya biarkan.</small>
                            </div>
                            <div class="card-body p-4">
                                <?php if ($activityData) : ?>
                                <form action="proses_edit_kegiatan_harian.php" method="POST">
                                    <input type="hidden" name="id_kegiatan"
                                        value="<?php echo htmlspecialchars($activityData['id']); ?>">

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-2s">
                                        <label for="tanggal_kegiatan" class="form-label fw-bold"><i
                                                class="bx bx-calendar me-1"></i> Hari/Tanggal Kegiatan:</label>
                                        <input type="date" class="form-control" id="tanggal_kegiatan"
                                            name="tanggal_kegiatan" required
                                            value="<?php echo htmlspecialchars($activityData['tanggal_kegiatan']); ?>">
                                        <div class="form-text text-muted">Pilih tanggal kegiatan PKL yang Anda lakukan.
                                        </div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-3s">
                                        <label for="pekerjaan" class="form-label fw-bold"><i
                                                class="bx bx-briefcase-alt me-1"></i> Deskripsi Pekerjaan:</label>
                                        <textarea class="form-control" id="pekerjaan" name="pekerjaan" rows="5"
                                            list="datalistPekerjaan"
                                            placeholder="Contoh: Membantu tim IT dalam konfigurasi jaringan baru di kantor pusat."
                                            required><?php echo htmlspecialchars($activityData['pekerjaan']); ?></textarea>
                                        <datalist id="datalistPekerjaan">
                                            <?php foreach ($pekerjaan_options as $pekerjaan) : ?>
                                            <option value="<?php echo htmlspecialchars($pekerjaan); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Jelaskan secara rinci pekerjaan yang Anda
                                            selesaikan.</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-4s">
                                        <label for="catatan" class="form-label fw-bold"><i
                                                class="bx bx-notepad me-1"></i> Catatan Tambahan (Opsional):</label>
                                        <textarea class="form-control" id="catatan" name="catatan" rows="3"
                                            list="datalistCatatan"
                                            placeholder="Contoh: Menghadapi kendala teknis saat instalasi driver printer, berhasil diatasi dengan bantuan instruktur."><?php echo htmlspecialchars($activityData['catatan']); ?></textarea>
                                        <datalist id="datalistCatatan">
                                            <?php foreach ($catatan_options as $catatan) : ?>
                                            <option value="<?php echo htmlspecialchars($catatan); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Tuliskan hal penting lain, kesulitan, atau
                                            pelajaran baru.</div>
                                    </div>

                                    <hr class="my-4">

                                    <div
                                        class="d-flex flex-column flex-sm-row justify-content-end gap-2 animate__animated animate__fadeInUp animate__delay-0-5s">
                                        <a href="master_kegiatan_harian.php"
                                            class="btn btn-outline-secondary w-100 w-sm-auto order-sm-first">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="submit" class="btn btn-primary w-100 w-sm-auto">
                                            <i class="bx bx-save me-1"></i> Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                                <?php else : ?>
                                <div class="alert alert-warning text-center" role="alert">
                                    Data kegiatan harian tidak ditemukan atau ID tidak valid untuk diedit. Silakan
                                    kembali ke daftar kegiatan.
                                </div>
                                <div class="text-center">
                                    <a href="master_kegiatan_harian.php" class="btn btn-primary mt-3">
                                        <i class="bx bx-list-ul me-1"></i> Lihat Daftar Kegiatan
                                    </a>
                                </div>
                                <?php endif; ?>
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
    <?php include 'partials/script.php' ?>
</body>

</html>