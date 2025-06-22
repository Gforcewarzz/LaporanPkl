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
                        <div class="col-lg-6 col-md-12 col-12  d-flex align-items-center">
                            <h4 class="text-muted mb-0"><strong>Master</strong> / Data Siswa</h4>
                        </div>
                        <div class="row">
                            <div
                                class="col-lg-6 col-md-12 col-12 mb-4 d-flex justify-content-center align-items-center">
                                <i class="fas fa-graduation-cap fa-5x text-primary" style="opacity: 0.2;"></i>
                            </div>
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                                        <div class="d-flex flex-wrap align-items-center mb-2 mb-md-0">
                                            <a href="index.php" class="btn btn-secondary me-2 mb-2 mb-md-0">
                                                <i class="bx bx-arrow-back me-1"></i> Kembali ke Dashboard
                                            </a>
                                            <a href="master_data_guru_add.php" class="btn btn-success mb-2 mb-md-0">
                                                <i class="bx bx-plus me-1"></i> Tambah Data
                                            </a>
                                        </div>

                                        <div class="d-flex flex-wrap align-items-center">
                                            <button type="button" class="btn btn-outline-danger me-2 mb-2 mb-md-0">
                                                <i class="bx bxs-file-pdf me-1"></i> PDF
                                            </button>
                                            <button type="button" class="btn btn-outline-success mb-2 mb-md-0">
                                                <i class="bx bxs-file-excel me-1"></i> Excel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Data Siswa PKL</h5>
                                        <small class="text-muted float-end">Informasi Detail Siswa</small>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama</th>
                                                    <th>NISN</th>
                                                    <th>Kelas</th>
                                                    <th>Jurusan</th>
                                                    <th>Tempat PKL</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="table-border-bottom-0">
                                                <tr>
                                                    <td>1</td>
                                                    <td><i class="fab fa-angular fa-lg text-danger me-3"></i>
                                                        <strong>Budi Santoso</strong>
                                                    </td>
                                                    <td>1234567890</td>
                                                    <td>XII RPL 1</td>
                                                    <td>Rekayasa Perangkat Lunak</td>
                                                    <td>PT. Maju Bersama</td>
                                                    <td><span class="badge bg-label-success me-1">Aktif</span></td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button type="button"
                                                                class="btn p-0 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item"
                                                                    href="master_data_siswa_edit.php?action=edit&id=123">
                                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="master_data_siswa_edit.php?action=delete&id=123">
                                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>2</td>
                                                    <td><i class="fab fa-react fa-lg text-info me-3"></i> <strong>Citra
                                                            Dewi</strong></td>
                                                    <td>0987654321</td>
                                                    <td>XI TKJ 2</td>
                                                    <td>Teknik Komputer Jaringan</td>
                                                    <td>CV. IT Jaya</td>
                                                    <td><span class="badge bg-label-success me-1">Aktif</span></td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button type="button"
                                                                class="btn p-0 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item"
                                                                    href="master_data_siswa_edit.php?action=edit&id=456">
                                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="master_data_siswa_edit.php?action=delete&id=456">
                                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>3</td>
                                                    <td><i class="fab fa-vuejs fa-lg text-success me-3"></i>
                                                        <strong>Dani Permana</strong>
                                                    </td>
                                                    <td>1122334455</td>
                                                    <td>X MM 3</td>
                                                    <td>Multimedia</td>
                                                    <td>Studio Kreatif Indah</td>
                                                    <td><span class="badge bg-label-secondary me-1">Tidak Aktif</span>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button type="button"
                                                                class="btn p-0 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item"
                                                                    href="master_data_siswa_edit.php?action=edit&id=789">
                                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="master_data_siswa_edit.php?action=delete&id=789">
                                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>4</td>
                                                    <td><i class="fab fa-bootstrap fa-lg text-primary me-3"></i>
                                                        <strong>Eka Putri</strong>
                                                    </td>
                                                    <td>5566778899</td>
                                                    <td>XII RPL 2</td>
                                                    <td>Rekayasa Perangkat Lunak</td>
                                                    <td>Tech Solutions Inc.</td>
                                                    <td><span class="badge bg-label-success me-1">Aktif</span></td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button type="button"
                                                                class="btn p-0 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item"
                                                                    href="master_data_siswa_edit.php?action=edit&id=012">
                                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="master_data_siswa_edit.php?action=delete&id=012">
                                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
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