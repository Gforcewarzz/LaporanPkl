<?php
session_start();

// [UBAH] Keamanan: Sekarang admin DAN guru boleh mengakses halaman ini
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) { // Jika bukan admin DAN juga bukan guru, maka dilarang
    if ($is_siswa) {
        header('Location: dashboard_siswa.php');
    } else {
        header('Location: ../login.php');
    }
    exit();
}

require_once 'partials/db.php';

function querys($sql)
{
    global $koneksi;
    $result = mysqli_query($koneksi, $sql);
    if (!$result) {
        // Handle query error, misalnya dengan menampilkan pesan atau logging
        die("Query failed: " . mysqli_error($koneksi));
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Ambil data dari database
$jurusan_options         = querys("SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
$guru_pendamping_options = querys("SELECT id_pembimbing, nama_pembimbing FROM guru_pembimbing ORDER BY nama_pembimbing ASC");
$tempat_pkl_options      = querys("SELECT id_tempat_pkl, nama_tempat_pkl FROM tempat_pkl ORDER BY nama_tempat_pkl ASC");

// Daftar kelas disarankan tetap hard-code jika daftarnya statis
$kelas_options = [
    'XII RPL 1', 'XII RPL 2', 'XII DKV 1', 'XII DKV 2', 'XII DPIB 1', 'XII DPIB 2',
    'XII FI 1', 'XII TP 1', 'XII TKR 1', 'XII TKR 2', 'XII TKR 3', 'XII TBO 1',
    'XII AKKL 1', 'XII AKKL 2',
];

?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

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
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">Master Data /</span> Tambah Siswa
                            </h4>
                            <i class="fas fa-user-plus fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card shadow-lg animate__animated animate__fadeInUp" style="border-radius: 10px;">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Isi Data Diri Siswa</h5>
                                <small class="text-muted">Pastikan data siswa diisi dengan benar dan lengkap.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="master_data_siswa_add_act.php" method="POST" id="formTambahSiswa">
                                    
                                    <div class="mb-3">
                                        <label for="nama_siswa" class="form-label fw-bold"><i class="bx bx-user me-1"></i> Nama Siswa:</label>
                                        <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" placeholder="Ketik nama lengkap siswa..." required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="no_induk" class="form-label fw-bold"><i class="bx bx-hash me-1"></i> No Induk:</label>
                                        <input type="text" class="form-control" id="no_induk" name="no_induk" placeholder="Ketik nomor induk siswa..." required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nisn" class="form-label fw-bold"><i class="bx bx-id-card me-1"></i> NISN:</label>
                                        <input type="text" class="form-control" id="nisn" name="nisn" placeholder="Ketik NISN (10 digit)..." required  title="NISN harus terdiri dari 10 digit angka">
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-bold"><i class="bx bx-lock-alt me-1"></i> Password:</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password untuk login siswa" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label fw-bold"><i class="bx bx-lock-check me-1"></i> Konfirmasi Password:</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="jenis_kelamin" class="form-label fw-bold"><i class="fa-solid fa-venus-mars me-1"></i> Jenis Kelamin:</label>
                                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                            <option value="" selected disabled>Pilih Jenis Kelamin</option>
                                            <option value="Laki-laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="kelas" class="form-label fw-bold"><i class="bx bx-award me-1"></i> Kelas:</label>
                                        <input type="text" class="form-control" id="kelas" name="kelas" list="datalistKelas" placeholder="Pilih atau ketik kelas..." required>
                                        <datalist id="datalistKelas">
                                            <?php foreach ($kelas_options as $kelas) : ?>
                                                <option value="<?= htmlspecialchars($kelas); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                    <div class="mb-3">
                                        <label for="jurusan" class="form-label fw-bold"><i class="bx bx-book-open me-1"></i> Jurusan:</label>
                                        <select class="form-select" id="jurusan" name="jurusan_id" required>
                                            <option value="" selected disabled>Pilih Jurusan</option>
                                            <?php foreach ($jurusan_options as $jurusan): ?>
                                                <option value="<?= $jurusan['id_jurusan'] ?>"><?= $jurusan['nama_jurusan'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="guru_pendamping" class="form-label fw-bold"><i class="bx bx-user-voice me-1"></i> Guru Pendamping:</label>
                                        
                                        <?php if ($is_admin): ?>
                                            <select class="form-select" id="guru_pendamping" name="pembimbing_id" required>
                                                <option value="" selected disabled>Pilih seorang guru...</option>
                                                <?php foreach ($guru_pendamping_options as $guru): ?>
                                                    <option value="<?= htmlspecialchars($guru['id_pembimbing']) ?>"><?= htmlspecialchars($guru['nama_pembimbing']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text text-muted">Pilih guru yang akan mendampingi siswa.</div>

                                        <?php elseif ($is_guru): ?>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['nama_guru']) ?>" readonly>
                                            <input type="hidden" name="pembimbing_id" value="<?= htmlspecialchars($_SESSION['id_guru_pendamping']) ?>">
                                            <div class="form-text text-muted">Anda akan otomatis ditugaskan sebagai guru pendamping.</div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="tempat_pkl" class="form-label fw-bold"><i class="bx bx-building-house me-1"></i> Tempat PKL:</label>
                                        <select class="form-select" id="tempat_pkl" name="tempat_pkl_id" required>
                                            <option value="" selected disabled>Pilih tempat PKL...</option>
                                            <?php foreach ($tempat_pkl_options as $tempat): ?>
                                                <option value="<?= htmlspecialchars($tempat['id_tempat_pkl']) ?>"><?= htmlspecialchars($tempat['nama_tempat_pkl']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status_siswa" class="form-label fw-bold"><i class="bx bx-check-circle me-1"></i> Status Siswa:</label>
                                        <select class="form-select" id="status_siswa" name="status_siswa" required>
                                            <option value="Aktif" selected>Aktif</option>
                                            <option value="Tidak Aktif">Tidak Aktif</option>
                                            <option value="Selesai">Selesai</option>
                                        </select>
                                    </div>

                                    <hr class="my-4">
                                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                                        <a href="master_data_siswa.php" class="btn btn-outline-secondary w-100 w-sm-auto">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                        <button type="reset" class="btn btn-outline-warning w-100 w-sm-auto">
                                            <i class="bx bx-refresh me-1"></i> Reset Form
                                        </button>
                                        <button type="submit" name="submit" class="btn btn-primary w-100 w-sm-auto">
                                            <i class="bx bx-save me-1"></i> Simpan Data
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include './partials/footer.php'; ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <?php include 'partials/script.php' ?>
    <script>
        // Validasi konfirmasi password
        document.getElementById('formTambahSiswa').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                e.preventDefault(); // Mencegah form dikirim
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Password dan Konfirmasi Password tidak cocok!',
                    confirmButtonColor: '#696cff'
                });
            }
        });
    </script>
</body>
</html>