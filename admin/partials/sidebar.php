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
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-book-open" style="color: #007bff;">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M16 2h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7a3 3 0 0 0-3-3V6a4 4 0 0 1 4-4z" opacity="0"></path>
                <line x1="18" y1="2" x2="22" y2="6" stroke-linecap="round" stroke-linejoin="round"
                    style="transform: rotate(15deg); transform-origin: 18px 2px;"></line>
                <path d="M20 18l-1-1 4-4 1 1zM18 20l-1-1 4-4 1 1z" fill="#007bff" stroke="none"></path>
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
        <li class="menu-item active"> <a href="index.php" class="menu-link">
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
                <li class="menu-item">
                    <a href="master_data_pengguna.php" class="menu-link">
                        <div data-i18n="Data Pengguna">Data Pengguna</div>
                    </a>
                </li>
                <?php // endif; 
                ?>
                <li class="menu-item">
                    <a href="master_data_siswa.php" class="menu-link">
                        <div data-i18n="Data Siswa PKL">Data Siswa PKL</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="master_data_guru.php" class="menu-link">
                        <div data-i18n="Data Pembimbing PKL">Data Pembimbing PKL</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="master_data_tempat_pkl.php" class="menu-link">
                        <div data-i18n="Data Tempat PKL">Data Tempat PKL</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Transaksi & Laporan</span>
        </li>
        <li class="menu-item">
            <a href="jurnal_harian.php" class="menu-link"> <i class="menu-icon tf-icons bx bx-book"></i>
                <div data-i18n="Jurnal Harian PKL">Jurnal Harian PKL</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="penilaian_pkl.php" class="menu-link"> <i class="menu-icon tf-icons bx bx-star"></i>
                <div data-i18n="Penilaian PKL">Penilaian PKL</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="absensi_pkl.php" class="menu-link"> <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                <div data-i18n="Absensi PKL">Absensi PKL</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="laporan_pkl.php" class="menu-link"> <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Laporan PKL">Laporan PKL</div>
            </a>
        </li>
    </ul>
</aside>