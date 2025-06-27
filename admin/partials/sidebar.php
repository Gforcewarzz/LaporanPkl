<?php
// Pastikan sesi sudah dimulai di awal file
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }
// Ambil role dari sesi (dikomentari)
// $userRole = $_SESSION['role'] ?? 'Guest';

// --- DATA STATIS UNTUK PENGUJIAN TAMPILAN ---
$userRole = 'Super Admin'; // Gunakan role statis untuk menguji kondisi Super Admin
// --- AKHIR DATA STATIS ---

?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.php" class="app-brand-link">
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
            <a href="index.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Data Utama</span>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-collection"></i>
                <div data-i18n="Master Data">Master Data</div>
            </a>
            <ul class="menu-sub">
                <?php // if ($userRole == 'Super Admin'): 
                ?>

                <?php // endif; 
                ?>
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
                    <a href="master_data_admin.php" class="menu-link">
                        <div data-i18n="Data Tempat PKL">Data Admin</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Transaksi & Laporan</span>
        </li>
        <li class="menu-item">
            <a href="master_kegiatan_harian.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-book-content"></i>
                <div data-i18n="Kegiatan Harian">Kegiatan Harian</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="master_tugas_project.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-book-content"></i>
                <div data-i18n="Kegiatan Harian">Kegiatan Project</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="penilaian_pkl.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-star"></i>
                <div data-i18n="Penilaian PKL">Penilaian PKL</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="absensi_pkl.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                <div data-i18n="Absensi PKL">Absensi PKL</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="laporan_pkl.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Laporan PKL">Laporan PKL</div>
            </a>
        </li>
    </ul>
</aside>