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
                                <span class="text-muted fw-light">Master Data /</span> Tambah Guru
                            </h4>
                            <i class="fas fa-user-plus fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>
                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Formulir Data Guru Baru</h5>
                                    <p class="card-text text-white-75 small">Lengkapi informasi guru untuk keperluan
                                        pembimbingan.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; overflow: hidden; z-index: 1;">
                                        <i class="bx bx-user-voice bx-lg text-primary"
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
                                <h5 class="card-title mb-0">Isi Data Diri Guru</h5>
                                <small class="text-muted">Pastikan data guru diisi dengan benar dan lengkap.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="proses_tambah_data_guru.php" method="POST">
                                    <?php
                                    $nama_guru_options = ['Ibu Endang Susanti, S.Kom.', 'Bapak Anto Wijaya, M.Pd.', 'Ibu Siti Aminah, S.T.', 'Bapak Rina Sari, S.Kom.'];
                                    $nip_options = ['198001012005011002', '197505102000021005', '198811202015032001', '199003032015042002'];
                                    $bidang_keahlian_options = ['Rekayasa Perangkat Lunak', 'Teknik Komputer Jaringan', 'Multimedia', 'Desain Komunikasi Visual'];
                                    ?>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-2s">
                                        <label for="nama_guru" class="form-label fw-bold"><i
                                                class="bx bx-user me-1"></i> Nama Guru:</label>
                                        <input type="text" class="form-control" id="nama_guru" name="nama_guru"
                                            list="datalistNamaGuru" placeholder="Contoh: Ibu Endang Susanti, S.Kom."
                                            required>
                                        <datalist id="datalistNamaGuru">
                                            <?php foreach ($nama_guru_options as $nama_guru) : ?>
                                            <option value="<?php echo htmlspecialchars($nama_guru); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Nama lengkap guru beserta gelar.</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-3s">
                                        <label for="nip" class="form-label fw-bold"><i class="bx bx-id-card me-1"></i>
                                            NIP:</label>
                                        <input type="text" class="form-control" id="nip" name="nip" list="datalistNIP"
                                            placeholder="Contoh: 198001012005011002" pattern="[0-9]{18}"
                                            title="NIP harus 18 digit angka" required>
                                        <datalist id="datalistNIP">
                                            <?php foreach ($nip_options as $nip) : ?>
                                            <option value="<?php echo htmlspecialchars($nip); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Nomor Induk Pegawai (18 digit angka).</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-4s">
                                        <label for="bidang_keahlian" class="form-label fw-bold"><i
                                                class="bx bx-chalkboard me-1"></i> Bidang Keahlian:</label>
                                        <input type="text" class="form-control" id="bidang_keahlian"
                                            name="bidang_keahlian" list="datalistBidangKeahlian"
                                            placeholder="Pilih atau ketik bidang keahlian..." required>
                                        <datalist id="datalistBidangKeahlian">
                                            <?php foreach ($bidang_keahlian_options as $bidang) : ?>
                                            <option value="<?php echo htmlspecialchars($bidang); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Jurusan atau bidang keahlian yang diajarkan
                                            guru.</div>
                                    </div>

                                    <hr class="my-4">

                                    <div
                                        class="d-flex flex-column flex-sm-row justify-content-end gap-2 animate__animated animate__fadeInUp animate__delay-0-5s">
                                        <a href="master_guru_pendamping.php"
                                            class="btn btn-outline-secondary w-100 w-sm-auto order-sm-first">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-refresh me-1"></i> Reset Form
                                        </button>
                                        <button type="submit" class="btn btn-primary w-100 w-sm-auto">
                                            <i class="bx bx-save me-1"></i> Simpan Data Guru
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
    <?php include 'partials/script.php' ?>
</body>

</html>