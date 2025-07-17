<?php
// Pastikan sesi sudah dimulai di halaman yang memuat sidebar ini.
// Variabel $is_admin, $is_siswa, $is_guru diharapkan sudah didefinisikan
// di file utama (misalnya index.php, dashboard_siswa.php, master_data_siswa.php, dll.)
// sebelum meng-include file sidebar ini.

// Untuk keamanan, tambahkan inisialisasi jika file sidebar ini diakses di luar konteks normal.
// Meskipun demikian, di lingkungan produksi, pastikan file utama yang menangani sesi.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi variabel status login dari sesi
// Ini harus konsisten dengan bagaimana Anda mengatur sesi di file login Anda.
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Inisialisasi variabel sesi spesifik untuk guru agar tidak undefined jika tidak login sebagai guru
// Gunakan $_SESSION['id_guru_pendamping'] dan $_SESSION['nama_guru'] seperti yang diatur di login_petugas_act.php
$id_guru_pendamping = $_SESSION['id_guru_pendamping'] ?? null;
$nama_guru = $_SESSION['nama_guru'] ?? 'Guru Pendamping'; // Default nama jika tidak ada di sesi

// Inisialisasi variabel sesi universal jika Anda menggunakannya (disarankan untuk konsistensi)
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'Pengguna';
$user_role = $_SESSION['user_role'] ?? null;

?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="<?php echo ($is_siswa ? 'dashboard_siswa.php' : ($is_admin ? 'index.php' : ($is_guru ? 'dashboard_guru.php' : '../login.php'))); ?>"
            class="app-brand-link">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                style="color: #696cff;">
                <path
                    d="M5 2H19C19.5523 2 20 2.44772 20 3V21C20 21.5523 19.5523 22 19 22H5C4.44772 22 4 21.5523 4 21V3C4 2.44772 4.44772 2 5 2Z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    fill="rgba(105, 108, 255, 0.1)" />
                <path d="M8 6H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                <path d="M8 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                <path d="M8 14H12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">
                E-Jurnal PKL
            </span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-item active">
            <a href="<?php echo ($is_siswa ? 'dashboard_siswa.php' : ($is_admin ? 'index.php' : ($is_guru ? 'dashboard_guru.php' : '../login.php'))); ?>"
                class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <?php if ($is_admin): ?>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Data Utama</span>
            </li>
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-collection"></i>
                    <div data-i18n="Master Data">Master Data</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="master_data_siswa.php" class="menu-link">
                            <div data-i18n="Data Siswa PKL">Data Siswa PKL</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="master_guru_pendamping.php" class="menu-link">
                            <div data-i18n="Data Guru Pendamping">Data Guru Pendamping</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="master_tempat_pkl.php" class="menu-link">
                            <div data-i18n="Data Tempat PKL">Data Tempat PKL</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="master_data_absensi_siswa.php" class="menu-link">
                            <div data-i18n="Absensi Siswa">Absensi Siswa</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="master_data_admin.php" class="menu-link">
                            <div data-i18n="Data Admin">Data Admin</div>
                        </a>
                    </li>
                </ul>

            </li>
        <?php endif; ?>

        <?php if ($is_admin || $is_siswa || $is_guru): ?>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Transaksi & Laporan</span>
            </li>
        <?php endif; ?>

        <?php if ($is_admin || $is_siswa): ?>
            <li class="menu-item">
                <a href="master_kegiatan_harian.php" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-book-content"></i>
                    <div data-i18n="Kegiatan Harian">Jurnal PKL Harian</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="master_tugas_project.php" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-book-content"></i>
                    <div data-i18n="Kegiatan Project">Jurnal PKL Per Kegiatan</div>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($is_guru): ?>
            <li class="menu-item">
                <a href="master_data_siswa.php?pembimbing_id=<?= htmlspecialchars($id_guru_pendamping) ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div data-i18n="Siswa Bimbingan">Siswa Bimbingan</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="master_data_absensi_siswa.php?pembimbing_id=<?= htmlspecialchars($id_guru_pendamping) ?>" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                    <div data-i18n="Absensi Bimbingan">Absensi Bimbingan</div>
                </a>
            </li>
        <?php endif; ?>

        <?php /*
        <?php if ($is_admin || $is_guru): ?>
        <li class="menu-item">
            <a href="penilaian_pkl.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-star"></i>
                <div data-i18n="Penilaian PKL">Penilaian PKL</div>
            </a>
        </li>
        <?php endif; ?>

        <?php if ($is_admin || $is_guru): ?>
        <li class="menu-item">
            <a href="absensi_pkl.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                <div data-i18n="Observasi PKL">Observasi PKL</div>
            </a>
        </li>
        <?php endif; ?>

        <?php if ($is_admin || $is_guru): ?>
        <li class="menu-item">
            <a href="laporan_pkl.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Laporan PKL">Laporan Akhir</div>
            </a>
        </li>
        <?php endif; ?>
        */ ?>

        <?php if ($is_admin || $is_siswa || $is_guru): ?>
            <li class="menu-item">
                <a href="ganti_password.php" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-lock-alt"></i>
                    <div data-i18n="Ganti Password">Ganti Password</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link" onclick="confirmLogout()">
                    <i class="menu-icon tf-icons bx bx-log-out"></i>
                    <div data-i18n="Logout">Logout</div>
                </a>
            </li>
        <?php endif; ?>

    </ul>
</aside>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Konfirmasi Logout',
            html: 'Apakah Anda yakin ingin keluar dari aplikasi?',
            icon: 'question', // Atau 'warning', 'info'
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // Merah untuk Logout
            cancelButtonColor: '#6c757d', // Abu-abu untuk Batal
            confirmButtonText: 'Ya, Logout!',
            cancelButtonText: 'Batal',
            reverseButtons: true, // Membalik posisi tombol (Batal di kiri)
            showClass: { // Animasi saat muncul
                popup: 'animate__animated animate__zoomIn animate__faster'
            },
            hideClass: { // Animasi saat menghilang
                popup: 'animate__animated animate__zoomOut animate__faster'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Arahkan ke script logout Anda
                // Asumsi logout.php berada satu tingkat di atas folder admin/
                window.location.href = '../logout.php';
            }
        });
    }
</script>
}
</script>