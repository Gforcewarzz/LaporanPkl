<?php include 'partials/db.php';
$jurusanQuery = mysqli_query($koneksi, "SELECT id_jurusan, nama_jurusan FROM jurusan");
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" data-theme="theme-default">
<?php include 'partials/head.php'; ?>

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
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Master Data /</span> Tambah Tempat PKL
                            </h4>
                            <i class="fas fa-plus-circle fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg"
                            style="border-radius: 12px; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex justify-content-between align-items-center flex-column flex-sm-row">
                                <div class="text-center text-sm-start">
                                    <h5 class="card-title text-white mb-1">Formulir Tempat PKL Baru</h5>
                                    <p class="card-text text-white-75 small">Lengkapi informasi lokasi PKL mitra.</p>
                                </div>
                                <div class="text-center text-sm-end">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center"
                                        style="width: 80px; height: 80px; opacity: 0.2;">
                                        <i class="bx bx-building bx-lg text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-lg" style="border-radius: 10px;">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Isi Data Mitra Tempat PKL</h5>
                                <small class="text-muted">Pastikan data diisi dengan benar dan lengkap.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="master_tempat_pkl_add_act.php" method="POST">

                                    <div class="mb-3">
                                        <label for="nama_perusahaan" class="form-label fw-bold">
                                            <i class="bx bx-building-house me-1"></i> Nama Perusahaan/Instansi:
                                        </label>
                                        <input type="text" class="form-control" id="nama_perusahaan"
                                            name="nama_perusahaan" placeholder="Contoh: PT. Inovasi Digital" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="alamat" class="form-label fw-bold">
                                            <i class="bx bx-map me-1"></i> Alamat:
                                        </label>
                                        <input type="text" class="form-control" id="alamat" name="alamat"
                                            placeholder="Contoh: Jl. Merdeka No. 123, Bandung" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="kontak" class="form-label fw-bold">
                                            <i class="bx bx-phone me-1"></i> Kontak:
                                        </label>
                                        <input type="tel" class="form-control" id="kontak" name="kontak"
                                            placeholder="Contoh: (022) 1234567" pattern="[0-9() -+]{7,20}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="kuota_siswa" class="form-label fw-bold">
                                            <i class="bx bx-user-plus me-1"></i> Kuota Siswa:
                                        </label>
                                        <input type="number" class="form-control" id="kuota_siswa" name="kuota_siswa"
                                            min="0" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="nama_instruktur_lapangan" class="form-label fw-bold">
                                            <i class="bx bx-user-check me-1"></i> Nama Instruktur Lapangan:
                                        </label>
                                        <input type="text" class="form-control" id="nama_instruktur_lapangan"
                                            name="nama_instruktur_lapangan" placeholder="Contoh: Bpk. Joni Iskandar">
                                    </div>

                                    <div class="mb-3">
                                        <label for="jurusan_id" class="form-label fw-bold">
                                            <i class="bx bx-layer me-1"></i> Jurusan Terkait:
                                        </label>
                                        <select class="form-select" id="jurusan_id" name="jurusan_id" required>
                                            <option value="" disabled selected>Pilih jurusan</option>
                                            <?php while ($jurusan = mysqli_fetch_assoc($jurusanQuery)) : ?>
                                                <option value="<?= $jurusan['id_jurusan'] ?>">
                                                    <?= htmlspecialchars($jurusan['nama_jurusan']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                                        <a href="master_tempat_pkl.php"
                                            class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-refresh me-1"></i> Reset Form
                                        </button>
                                        <button type="submit" class="btn btn-primary w-100 w-sm-auto">
                                            <i class="bx bx-save me-1"></i> Simpan Data Tempat PKL
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

    <?php include 'partials/script.php'; ?>
</body>

</html>