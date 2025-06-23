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
                        // --- DATA STATIS TEMPAT PKL UNTUK SIMULASI PENGAMBILAN DARI DATABASE ---
                        $allPlaces = [
                            'TPKL001' => [
                                'id' => 'TPKL001',
                                'nama_perusahaan' => 'PT. Inovasi Digital',
                                'alamat' => 'Jl. Merdeka No. 123, Bandung',
                                'kontak' => '(022) 1234567',
                                'kuota_siswa' => 10,
                                'nama_instruktur_lapangan' => 'Bpk. Joni Iskandar'
                            ],
                            'TPKL002' => [
                                'id' => 'TPKL002',
                                'nama_perusahaan' => 'CV. Solusi Kreatif',
                                'alamat' => 'Jl. Diponegoro No. 45, Cimahi',
                                'kontak' => '(022) 7654321',
                                'kuota_siswa' => 8,
                                'nama_instruktur_lapangan' => 'Ibu Maya Sari'
                            ],
                            'TPKL003' => [
                                'id' => 'TPKL003',
                                'nama_perusahaan' => 'Bumi Digital Studio',
                                'alamat' => 'Jl. Asia Afrika No. 78, Bandung',
                                'kontak' => '(022) 9876543',
                                'kuota_siswa' => 5,
                                'nama_instruktur_lapangan' => 'Bpk. Asep Setiawan'
                            ],
                            'TPKL004' => [
                                'id' => 'TPKL004',
                                'nama_perusahaan' => 'Tech Solutions Inc.',
                                'alamat' => 'Jl. Teknologi No. 10, Jakarta',
                                'kontak' => '(021) 56789012',
                                'kuota_siswa' => 12,
                                'nama_instruktur_lapangan' => 'Ibu Karina Dewi'
                            ],
                        ];

                        // Ambil ID tempat PKL dari URL (misal: master_tempat_pkl_edit.php?id=TPKL001)
                        $placeId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null;
                        $placeData = $allPlaces[$placeId] ?? null; // Ambil data dari array statis

                        if (!$placeData) {
                            // Jika ID tidak ditemukan di array statis, tampilkan pesan error atau redirect
                            echo '<div class="alert alert-danger" role="alert">Data tempat PKL tidak ditemukan atau ID tidak valid.</div>';
                            // header('Location: master_tempat_pkl.php'); // Uncomment ini jika ingin redirect
                            // exit;
                        }

                        // Data Statis untuk Datalist (Sama seperti di form tambah)
                        $nama_perusahaan_options = ['PT. Inovasi Digital', 'CV. Solusi Kreatif', 'Bumi Digital Studio', 'Tech Solutions Inc.'];
                        $alamat_options = ['Jl. Merdeka No. 123, Bandung', 'Jl. Diponegoro No. 45, Cimahi', 'Jl. Asia Afrika No. 78, Bandung', 'Jl. Teknologi No. 10, Jakarta'];
                        $kontak_options = ['(022) 1234567', '(022) 7654321', '(022) 9876543', '(021) 56789012'];
                        $nama_instruktur_options = ['Bpk. Joni Iskandar', 'Ibu Maya Sari', 'Bpk. Asep Setiawan', 'Ibu Karina Dewi'];
                        ?>

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">Master Data /</span> Edit Tempat PKL
                            </h4>
                            <i class="fas fa-edit fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>
                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Perbarui Data Tempat PKL</h5>
                                    <p class="card-text text-white-75 small">Sesuaikan informasi jika ada perubahan.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; overflow: hidden; z-index: 1;">
                                        <i class="bx bx-map-pin bx-lg text-primary"
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
                                <h5 class="card-title mb-0">Formulir Edit Data Tempat PKL</h5>
                                <small class="text-muted">Isi kolom yang ingin diubah, sisanya biarkan.</small>
                            </div>
                            <div class="card-body p-4">
                                <?php if ($placeData) : ?>
                                <form action="proses_edit_tempat_pkl.php" method="POST">
                                    <input type="hidden" name="id_tempat_pkl"
                                        value="<?php echo htmlspecialchars($placeData['id']); ?>">

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-2s">
                                        <label for="nama_perusahaan" class="form-label fw-bold"><i
                                                class="bx bx-building-house me-1"></i> Nama Perusahaan/Instansi:</label>
                                        <input type="text" class="form-control" id="nama_perusahaan"
                                            name="nama_perusahaan" list="datalistNamaPerusahaan"
                                            placeholder="Contoh: PT. Inovasi Digital" required
                                            value="<?php echo htmlspecialchars($placeData['nama_perusahaan']); ?>">
                                        <datalist id="datalistNamaPerusahaan">
                                            <?php foreach ($nama_perusahaan_options as $nama) : ?>
                                            <option value="<?php echo htmlspecialchars($nama); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Nama lengkap perusahaan atau instansi.</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-3s">
                                        <label for="alamat" class="form-label fw-bold"><i class="bx bx-map me-1"></i>
                                            Alamat:</label>
                                        <input type="text" class="form-control" id="alamat" name="alamat"
                                            list="datalistAlamat" placeholder="Contoh: Jl. Merdeka No. 123, Bandung"
                                            required value="<?php echo htmlspecialchars($placeData['alamat']); ?>">
                                        <datalist id="datalistAlamat">
                                            <?php foreach ($alamat_options as $alamat) : ?>
                                            <option value="<?php echo htmlspecialchars($alamat); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Alamat lengkap tempat PKL.</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-4s">
                                        <label for="kontak" class="form-label fw-bold"><i class="bx bx-phone me-1"></i>
                                            Kontak:</label>
                                        <input type="tel" class="form-control" id="kontak" name="kontak"
                                            list="datalistKontak" placeholder="Contoh: (022) 1234567"
                                            pattern="[0-9() -+]{7,20}" title="Nomor telepon atau kontak yang valid."
                                            required value="<?php echo htmlspecialchars($placeData['kontak']); ?>">
                                        <datalist id="datalistKontak">
                                            <?php foreach ($kontak_options as $kontak) : ?>
                                            <option value="<?php echo htmlspecialchars($kontak); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Nomor telepon atau kontak perusahaan.</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-5s">
                                        <label for="kuota_siswa" class="form-label fw-bold"><i
                                                class="bx bx-user-plus me-1"></i> Kuota Siswa:</label>
                                        <input type="number" class="form-control" id="kuota_siswa" name="kuota_siswa"
                                            placeholder="Contoh: 10" min="0" required
                                            value="<?php echo htmlspecialchars($placeData['kuota_siswa']); ?>">
                                        <div class="form-text text-muted">Jumlah maksimum siswa yang bisa diterima PKL.
                                        </div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-6s">
                                        <label for="nama_instruktur_lapangan" class="form-label fw-bold"><i
                                                class="bx bx-user-check me-1"></i> Nama Instruktur Lapangan:</label>
                                        <input type="text" class="form-control" id="nama_instruktur_lapangan"
                                            name="nama_instruktur_lapangan" list="datalistNamaInstruktur"
                                            placeholder="Pilih atau ketik nama instruktur..."
                                            value="<?php echo htmlspecialchars($placeData['nama_instruktur_lapangan']); ?>">
                                        <datalist id="datalistNamaInstruktur">
                                            <?php foreach ($nama_instruktur_options as $instruktur) : ?>
                                            <option value="<?php echo htmlspecialchars($instruktur); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Nama instruktur atau pembimbing dari pihak
                                            perusahaan.</div>
                                    </div>

                                    <hr class="my-4">

                                    <div
                                        class="d-flex flex-column flex-sm-row justify-content-end gap-2 animate__animated animate__fadeInUp animate__delay-0-7s">
                                        <a href="master_tempat_pkl.php"
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
                                    Data tempat PKL tidak ditemukan atau ID tidak valid untuk diedit. Silakan kembali ke
                                    daftar tempat PKL.
                                </div>
                                <div class="text-center">
                                    <a href="master_tempat_pkl.php" class="btn btn-primary mt-3">
                                        <i class="bx bx-list-ul me-1"></i> Lihat Daftar Tempat PKL
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