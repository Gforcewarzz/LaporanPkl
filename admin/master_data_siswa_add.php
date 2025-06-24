<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; include 'partials/db.php' ?>

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
                                <span class="text-muted fw-light">Master Data /</span> Tambah Siswa
                            </h4>
                            <i class="fas fa-user-plus fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>
                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Formulir Data Siswa Baru</h5>
                                    <p class="card-text text-white-75 small">Lengkapi informasi siswa untuk keperluan
                                        PKL.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; overflow: hidden; z-index: 1;">
                                        <i class="bx bx-child bx-lg text-primary"
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
                                <h5 class="card-title mb-0">Isi Data Diri Siswa</h5>
                                <small class="text-muted">Pastikan data siswa diisi dengan benar dan lengkap.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="master_data_siswa_add_act.php" method="POST">
                                    <?php
                                    $jurusan_options = querys("SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
                                    $no_induk_options = querys("SELECT no_induk FROM siswa");
                                    $nisn_options = querys("SELECT nisn FROM siswa");
                                    $nama_siswa_options = querys(("SELECT nama_siswa FROM siswa"));
                                    

                                    $kelas_options = [
                                        // RPL
                                        // 'X RPL 1', 'X RPL 2',
                                        // 'XI RPL 1', 'XI RPL 2',
                                        'XII RPL 1', 'XII RPL 2',
                                    
                                        // DKV
                                        // 'X DKV 1', 'X DKV 2',
                                        // 'XI DKV 1', 'XI DKV 2',
                                        'XII DKV 1', 'XII DKV 2',
                                    
                                        // DPIB
                                        // 'X DPIB 1', 'X DPIB 2',
                                        // 'XI DPIB 1', 'XI DPIB 2',
                                        'XII DPIB 1', 'XII DPIB 2',
                                    
                                        // FI (Farmasi Industri)
                                        // 'X FI 1',
                                        // 'XI FI 1',
                                        'XII FI 1',
                                    
                                        // TP (Teknik Pengelasan)
                                        // 'X TP 1', 'X TP 2',
                                        // 'XI TP 1', 'XI TP 2',
                                        'XII TP 1',
                                    
                                        // TKR (Teknik Kendaraan Ringan)
                                        // 'X TKR 1', 'X TKR 2', 'X TKR 3',
                                        // 'XI TKR 1', 'XI TKR 2', 'XI TKR 3',
                                        'XII TKR 1', 'XII TKR 2', 'XII TKR 3',
                                    
                                        // TBO (Teknik Bodi Otomotif)
                                        // 'X TBO 1',
                                        // 'XI TBO 1',
                                        'XII TBO 1',
                                    
                                        // AKKL (Akuntansi Keuangan Lembaga)
                                        // 'X AKKL 1', 'X AKKL 2',
                                        // 'XI AKKL 1', 'XI AKKL 2',
                                        'XII AKKL 1', 'XII AKKL 2',
                                    ];
                                    
                                    $guru_pendamping_options = querys("SELECT * from guru_pembimbing");
                                    
                                    $tempat_pkl_options = querys("SELECT * FROM tempat_pkl");
                                    ?>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-2s">
                                        <label for="nama_siswa" class="form-label fw-bold"><i
                                                class="bx bx-user me-1"></i> Nama Siswa:</label>
                                        <input type="text" class="form-control" id="nama_siswa" name="nama_siswa"
                                            list="datalistNamaSiswa" placeholder="Ketik nama siswa atau pilih..."
                                            required>
                                        <datalist id="datalistNamaSiswa">
                                            <?php foreach ($nama_siswa_options as $nama_siswa) : ?>
                                            <option value="<?php echo htmlspecialchars($nama_siswa['nama_siswa']); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Nama lengkap siswa (akan menyarankan nama yang
                                            sudah ada).</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-3s">
                                        <label for="no_induk" class="form-label fw-bold"><i class="bx bx-hash me-1"></i>
                                            No Induk:</label>
                                        <input type="text" class="form-control" id="no_induk" name="no_induk"
                                            list="datalistNoInduk" placeholder="Ketik nomor induk atau pilih..."
                                            required>
                                        <datalist id="datalistNoInduk">
                                            <?php foreach ($no_induk_options as $no_induk) : ?>
                                            <option value="<?php echo htmlspecialchars($no_induk['no_induk']); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Nomor Induk siswa (contoh: 2022001).</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-4s">
                                        <label for="nisn" class="form-label fw-bold"><i class="bx bx-id-card me-1"></i>
                                            NISN:</label>
                                        <input type="text" class="form-control" id="nisn" name="nisn"
                                            list="datalistNisn" placeholder="Ketik NISN atau pilih..."
                                            title="NISN harus 10 digit angka" required>
                                        <datalist id="datalistNisn">
                                            <?php foreach ($nisn_options as $nisn) : ?>
                                            <option value="<?php echo htmlspecialchars($nisn['nisn']); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Nomor Induk Siswa Nasional (10 digit angka,
                                            akan menyarankan NISN yang sudah ada).</div>
                                    </div>
                                    

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-9s">
                                        <label for="jenis_kelamin" class="form-label fw-bold"><i
                                                class="fa-solid fa-venus-mars"></i> Jenis Kelamin:</label>
                                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                            <option value="">Pilih JK</option>
                                            <option value="Laki-laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                            
                                        </select>
                                        <div class="form-text text-muted">Jenis Kelamin siswa.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-5s">
                                        <label for="kelas" class="form-label fw-bold"><i class="bx bx-award me-1"></i>
                                            Kelas:</label>
                                        <input type="text" class="form-control" id="kelas" name="kelas"
                                            list="datalistKelas" placeholder="Pilih atau ketik kelas..." required>
                                        <datalist id="datalistKelas">
                                            <?php foreach ($kelas_options as $kelas) : ?>
                                            <option value="<?php echo htmlspecialchars($kelas); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Kelas siswa saat ini (contoh: XII RPL 1).
                                        </div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-6s">
                                        <label for="jurusan" class="form-label fw-bold"><i
                                                class="bx bx-book-open me-1"></i> Jurusan:</label>
                                                <select class="form-select" id="jurusan" name="jurusan" required>
                                            <option value="">Pilih Jurusan</option>
                                            <?php foreach($jurusan_options as $jurusan): ?>
                                            <option value="<?= $jurusan['id_jurusan'] ?>"><?= $jurusan['nama_jurusan'] ?></option>
                                                <?php endforeach; ?>
                                        </select>
                                        <div class="form-text text-muted">Jurusan
                                        </div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-7s">
                                        <label for="guru_pendamping" class="form-label fw-bold"><i
                                                class="bx bx-user-voice me-1"></i> Guru Pendamping:</label>
                                        <input type="text" class="form-control" id="guru_pendamping"
                                            name="guru_pendamping" list="datalistGuruPendamping"
                                            placeholder="Pilih atau ketik nama guru..." required>
                                        <datalist id="datalistGuruPendamping">
                                            <?php foreach ($guru_pendamping_options as $guru) : ?>
                                            <option value="<?php echo htmlspecialchars($guru['nama_pembimbing']); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Guru yang mendampingi siswa selama PKL.</div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-8s">
                                        <label for="tempat_pkl" class="form-label fw-bold"><i
                                                class="bx bx-building-house me-1"></i> Tempat PKL:</label>
                                        <input type="text" class="form-control" id="tempat_pkl" name="tempat_pkl"
                                            list="datalistTempatPKL" placeholder="Pilih atau ketik tempat PKL..."
                                            required>
                                        <datalist id="datalistTempatPKL">
                                            <?php foreach ($tempat_pkl_options as $tempat) : ?>
                                            <option value="<?php echo htmlspecialchars($tempat['nama_tempat_pkl']); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                        <div class="form-text text-muted">Nama perusahaan/instansi tempat siswa PKL.
                                        </div>
                                    </div>

                                    <div class="mb-3 animate__animated animate__fadeInLeft animate__delay-0-9s">
                                        <label for="status_siswa" class="form-label fw-bold"><i
                                                class="bx bx-check-circle me-1"></i> Status Siswa:</label>
                                        <select class="form-select" id="status_siswa" name="status_siswa" required>
                                            <option value="">Pilih Status</option>
                                            <option value="Aktif">Aktif</option>
                                            <option value="Tidak Aktif">Tidak Aktif</option>
                                            <option value="Selesai">Selesai</option>
                                        </select>
                                        <div class="form-text text-muted">Status keaktifan siswa dalam program PKL.
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div
                                        class="d-flex flex-column flex-sm-row justify-content-end gap-2 animate__animated animate__fadeInUp animate__delay-1s">
                                        <a href="master_data_siswa.php"
                                            class="btn btn-outline-secondary w-100 w-sm-auto order-sm-first">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-refresh me-1"></i> Reset Form
                                        </button>
                                        <button type="submit" name="submit" class="btn btn-primary w-100 w-sm-auto">
                                            <i class="bx bx-save me-1"></i> Simpan Data Siswa
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