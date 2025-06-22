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
                            <div class="col-lg-6 col-md-12 col-12 mb-4 d-flex align-items-center">
                                <h4 class="text-muted mb-0"><strong>Master</strong> / Data Guru Pendamping</h4>
                            </div>
                            <div class="col-lg-6 col-md-12 col-12 mb-4 d-flex justify-content-end align-items-center">
                                <i class="fas fa-chalkboard-teacher fa-5x text-primary" style="opacity: 0.2;"></i>
                            </div>
                        </div>


                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="d-flex flex-wrap align-items-center mb-2 mb-md-0">
                                        <a href="index.php" class="btn btn-secondary me-2 mb-2 mb-md-0">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali ke Dashboard
                                        </a>
                                        <a href="master_tempat_pkl_add.php" class="btn btn-success mb-2 mb-md-0">
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
                                    <h5 class="mb-0">Data Tempat PKL</h5>
                                    <small class="text-muted float-end">Informasi Detail Mitra Perusahaan</small>
                                </div>
                                <div class="table-responsive text-nowrap">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Perusahaan</th>
                                                <th>Alamat</th>
                                                <th>Kontak</th>
                                                <th>Nama Instruktur</th>
                                                <th>Kuota Siswa</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <tr>
                                                <td>1</td>
                                                <td><i class="fab fa-building fa-lg text-primary me-3"></i> <strong>PT.
                                                        Inovasi Digital</strong></td>
                                                <td>Jl. Merdeka No. 123, Bandung</td>
                                                <td>(022) 1234567</td>
                                                <td>Bpk. Joni Iskandar</td>
                                                <td><span class="badge bg-label-info me-1">10 Siswa</span></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item"
                                                                href="master_data_tempat_pkl_edit.php?action=edit&id=TPKL001">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item"
                                                                href="master_data_tempat_pkl_edit.php?action=delete&id=TPKL001">
                                                                <i class="bx bx-trash me-1"></i> Hapus
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td><i class="fab fa-building fa-lg text-success me-3"></i> <strong>CV.
                                                        Solusi Kreatif</strong></td>
                                                <td>Jl. Diponegoro No. 45, Cimahi</td>
                                                <td>(022) 7654321</td>
                                                <td>Ibu Maya Sari</td>
                                                <td><span class="badge bg-label-info me-1">8 Siswa</span></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item"
                                                                href="master_data_tempat_pkl_edit.php?action=edit&id=TPKL002">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item"
                                                                href="master_data_tempat_pkl_edit.php?action=delete&id=TPKL002">
                                                                <i class="bx bx-trash me-1"></i> Hapus
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td><i class="fab fa-building fa-lg text-warning me-3"></i> <strong>Bumi
                                                        Digital Studio</strong></td>
                                                <td>Jl. Asia Afrika No. 78, Bandung</td>
                                                <td>(022) 9876543</td>
                                                <td>Bpk. Asep Setiawan</td>
                                                <td><span class="badge bg-label-info me-1">5 Siswa</span></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item"
                                                                href="master_data_tempat_pkl_edit.php?action=edit&id=TPKL003">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item"
                                                                href="master_data_tempat_pkl_edit.php?action=delete&id=TPKL003">
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

                        <div class="layout-overlay layout-menu-toggle"></div>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
                    <?php include './partials/script.php'; ?>
                </div>
            </div>
</body>

</html>
</tr>
</tbody>
</table>
</div>
</div>
</div>

<div class="layout-overlay layout-menu-toggle"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<?php include './partials/script.php'; ?>
</div>
</div>
</body>

</html>