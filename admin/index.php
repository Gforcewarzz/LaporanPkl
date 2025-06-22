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
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center"
                                            style="background-color: #007bff; width: 50px; height: 50px; font-size: 1.8rem; color: white;">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Total Siswa</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark">125</h3> <small
                                            class="text-muted d-block mt-1" style="font-size: 0.85rem;">Siswa aktif
                                            terdaftar</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center"
                                            style="background-color: #28a745; width: 50px; height: 50px; font-size: 1.8rem; color: white;">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Total Guru
                                            Pembimbing</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark">50</h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Guru
                                            pembimbing yang tersedia</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body d-flex flex-column align-items-start p-4">
                                        <div class="avatar flex-shrink-0 mb-3 rounded-circle d-flex justify-content-center align-items-center"
                                            style="background-color: #ffc107; width: 50px; height: 50px; font-size: 1.8rem; color: white;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <span class="text-muted fw-semibold d-block mb-1 fs-6">Jumlah Tempat PKL</span>
                                        <h3 class="card-title fw-bold mb-0 display-5 text-dark">30</h3>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.85rem;">Mitra
                                            perusahaan aktif</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="layout-overlay layout-menu-toggle"></div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

                <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

                <?php include './partials/script.php'; ?>
</body>

</html>