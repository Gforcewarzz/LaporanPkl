<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.php" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bolder ms-2">
                Laporan PKL
            </span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item">
            <a href="index.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>

        <!-- Master -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Master">Master</div>
            </a>

            <ul class="menu-sub">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'Super Admin'): ?>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div data-i18n="Data Pengguna">Data Pengguna</div>
                    </a>
                </li>
                <?php endif; ?>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div data-i18n="Data Siswa PKL">Data Siswa PKL</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div data-i18n="Data Pembimbing PKL">Data Pembimbing PKL</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div data-i18n="Data Laporan PKL">Data Laporan PKL</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div data-i18n="Penilaian PKL">Penilaian PKL</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <div data-i18n="Absensi PKL">Absensi PKL</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Permintaan</span>
        </li>
        <li class="menu-item">
            <a href="./permintaan_barang.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Permintaan Barang</div>
            </a>
        </li> -->

        <!-- Report -->
        <!-- <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Report</span>
        </li> -->
        <!-- Cards -->
        <!--  <li class="menu-item">
            <a href="./report_barang_masuk.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-collection"></i>
                <div data-i18n="Basic">Report Barang Masuk</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="./report_barang.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-collection"></i>
                <div data-i18n="Basic">Report Barang Keluar</div>
            </a>
        </li> -->
    </ul>
</aside>