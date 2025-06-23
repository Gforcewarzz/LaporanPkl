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

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">Siswa /</span> Laporan Harian
                            </h4>
                            <i class="fas fa-edit fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>
                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Catat Progres PKLmu di Sini!</h5>
                                    <p class="card-text text-white-75 small">Setiap laporan adalah langkah menuju
                                        kesuksesan.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; overflow: hidden; z-index: 1;">
                                        <i class="bx bx-check-circle bx-lg text-primary"
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
                        <div class="card mb-4 shadow-lg position-relative" style="border-radius: 10px;">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
                                style="pointer-events: none; z-index: 0; opacity: 0.05;">
                                <svg width="100%" height="100%" viewBox="0 0 200 100" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 20 C 50 0, 150 0, 200 20 L 200 80 C 150 100, 50 100, 0 80 Z"
                                        fill="currentColor" opacity="0.1"
                                        class="text-primary animate__animated animate__fadeIn animate__delay-0-1s" />
                                    <path d="M0 30 C 50 10, 150 10, 200 30 L 200 70 C 150 90, 50 90, 0 70 Z"
                                        fill="currentColor" opacity="0.15"
                                        class="text-info animate__animated animate__fadeIn animate__delay-0-2s" />
                                </svg>
                            </div>
                            <div
                                class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 position-relative z-1 p-4">
                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-2 order-md-1">
                                    <a href="dashboard_siswa.php"
                                        class="btn btn-outline-secondary w-100 animate__animated animate__fadeInUp animate__delay-0-2s">
                                        <i class="bx bx-arrow-back me-1"></i> Kembali
                                    </a>
                                    <a href="kegiatan_harian_add.php"
                                        class="btn btn-primary w-100 animate__animated animate__fadeInUp animate__delay-0-3s">
                                        <i class="bx bx-plus me-1"></i> Tambah Laporan
                                    </a>
                                </div>
                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-1 order-md-2">
                                    <button type="button"
                                        class="btn btn-outline-danger w-100 animate__animated animate__fadeInDown animate__delay-0-3s">
                                        <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                    </button>
                                    <button type="button"
                                        class="btn btn-outline-success w-100 animate__animated animate__fadeInDown animate__delay-0-2s">
                                        <i class="bx bxs-file-excel me-1"></i> Ekspor Excel
                                    </button>
                                </div>
                            </div>
                            <div class="card-footer bg-light border-top p-3 pt-md-2 pb-md-2 position-relative z-1">
                                <div
                                    class="row align-items-center animate__animated animate__fadeInUp animate__delay-0-4s">
                                    <div class="col-12 col-md-8 mb-2 mb-md-0">
                                        <input type="text" class="form-control"
                                            placeholder="Cari laporan berdasarkan kata kunci..." aria-label="Search" />
                                    </div>
                                    <div class="col-12 col-md-4 text-md-end">
                                        <button class="btn btn-outline-dark w-100 w-md-auto"><i
                                                class="bx bx-filter-alt me-1"></i> Filter Laporan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Laporan Harian Anda</h5>
                                <small class="text-muted">Riwayat lengkap aktivitas PKL</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap d-none d-md-block">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Hari/Tanggal</th>
                                                <th>Pekerjaan</th>
                                                <th>Catatan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <tr>
                                                <td>1</td>
                                                <td><strong>Senin, 23 Juni 2025</strong></td>
                                                <td>Membantu setup jaringan kantor dan instalasi OS.</td>
                                                <td>Selesai 80%, perlu melanjutkan esok hari.</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item"
                                                                href="kegiatan_harian_edit.php?id=1">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item"
                                                                href="kegiatan_harian_delete.php?id=1">
                                                                <i class="bx bx-trash me-1"></i> Hapus
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td><strong>Selasa, 24 Juni 2025</strong></td>
                                                <td>Menganalisis kebutuhan sistem inventaris baru.</td>
                                                <td>Sudah berdiskusi dengan tim, memahami alur kerja.</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item"
                                                                href="kegiatan_harian_edit.php?id=2">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item"
                                                                href="kegiatan_harian_delete.php?id=2">
                                                                <i class="bx bx-trash me-1"></i> Hapus
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td><strong>Rabu, 25 Juni 2025</strong></td>
                                                <td>Membuat desain antarmuka pengguna (UI) modul laporan.</td>
                                                <td>Menyelesaikan 3 mock-up awal menggunakan Figma.</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item"
                                                                href="kegiatan_harian_edit.php?id=3">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item"
                                                                href="kegiatan_harian_delete.php?id=3">
                                                                <i class="bx bx-trash me-1"></i> Hapus
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-md-none p-3">
                                    <div class="text-center text-muted mb-4 animate__animated animate__fadeInUp">
                                        <small><i class="bx bx-mobile me-1"></i> Geser ke bawah untuk melihat laporan
                                            Anda</small>
                                    </div>

                                    <div
                                        class="card mb-4 shadow-lg border-start border-4 border-primary rounded-3 animate__animated animate__fadeInUp">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1 text-primary"><i
                                                            class="bx bx-calendar-event me-1"></i> <strong>Senin, 23
                                                            Juni 2025</strong></h6>
                                                    <span class="badge bg-label-primary"><i class="bx bx-file me-1"></i>
                                                        Laporan #1</span>
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="kegiatan_harian_edit.php?id=1">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit Laporan
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger"
                                                            href="kegiatan_harian_delete.php?id=1">
                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-task me-1"></i>
                                                    Pekerjaan:</strong><br>
                                                Membantu setup jaringan kantor dan instalasi sistem operasi pada 3 unit
                                                komputer baru.
                                            </div>
                                            <div class="mb-0 text-wrap">
                                                <strong class="text-dark"><i class="bx bx-info-circle me-1"></i>
                                                    Catatan:</strong><br>
                                                Progres sudah mencapai 80%, perlu dilanjutkan esok hari. Terdapat
                                                sedikit kendala teknis saat menginstalasi driver printer, namun berhasil
                                                diatasi setelah berkonsultasi dengan instruktur lapangan.
                                            </div>
                                            <div class="d-flex justify-content-end mt-3">
                                                <small class="text-muted"><i class="bx bx-calendar-check me-1"></i>
                                                    Dilaporkan: 23 Juni 2025, 16:30 WIB</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="card mb-4 shadow-lg border-start border-4 border-warning rounded-3 animate__animated animate__fadeInUp animate__delay-0-1s">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1 text-warning"><i
                                                            class="bx bx-calendar-event me-1"></i> <strong>Selasa, 24
                                                            Juni 2025</strong></h6>
                                                    <span class="badge bg-label-warning"><i class="bx bx-file me-1"></i>
                                                        Laporan #2</span>
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="kegiatan_harian_edit.php?id=2">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit Laporan
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger"
                                                            href="kegiatan_harian_delete.php?id=2">
                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-task me-1"></i>
                                                    Pekerjaan:</strong><br>
                                                Menganalisis kebutuhan sistem inventaris baru bersama tim pengembangan
                                                IT perusahaan.
                                            </div>
                                            <div class="mb-0 text-wrap">
                                                <strong class="text-dark"><i class="bx bx-info-circle me-1"></i>
                                                    Catatan:</strong><br>
                                                Diskusi berjalan sangat produktif, berhasil memahami alur kerja dan
                                                mendapatkan banyak masukan berharga untuk pengembangan fitur-fitur baru.
                                            </div>
                                            <div class="d-flex justify-content-end mt-3">
                                                <small class="text-muted"><i class="bx bx-calendar-check me-1"></i>
                                                    Dilaporkan: 24 Juni 2025, 17:00 WIB</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="card mb-4 shadow-lg border-start border-4 border-info rounded-3 animate__animated animate__fadeInUp animate__delay-0-2s">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1 text-info"><i class="bx bx-calendar-event me-1"></i>
                                                        <strong>Rabu, 25 Juni 2025</strong>
                                                    </h6>
                                                    <span class="badge bg-label-info"><i class="bx bx-file me-1"></i>
                                                        Laporan #3</span>
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="kegiatan_harian_edit.php?id=3">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit Laporan
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger"
                                                            href="kegiatan_harian_delete.php?id=3">
                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-task me-1"></i>
                                                    Pekerjaan:</strong><br>
                                                Membuat desain antarmuka pengguna (UI) untuk modul laporan menggunakan
                                                Figma.
                                            </div>
                                            <div class="mb-2 text-wrap">
                                                <strong class="text-dark"><i class="bx bx-info-circle me-1"></i>
                                                    Catatan:</strong><br>
                                                Berhasil menyelesaikan 3 mock-up awal yang sesuai dengan kebutuhan, siap
                                                untuk dipresentasikan dan mendapatkan *feedback* besok pagi.
                                            </div>
                                            <div class="d-flex justify-content-end mt-3">
                                                <small class="text-muted"><i class="bx bx-calendar-check me-1"></i>
                                                    Dilaporkan: 25 Juni 2025, 16:45 WIB</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info text-center mt-5 py-4 animate__animated animate__fadeInUp animate__delay-0-3s"
                                        role="alert" style="border-radius: 8px;">
                                        <h5 class="alert-heading mb-3"><i class="bx bx-list-plus bx-lg text-info"></i>
                                        </h5>
                                        <p class="mb-3">Sepertinya belum ada laporan kegiatan yang tercatat untuk
                                            periode ini.</p>
                                        <p class="mb-0">
                                            Yuk, <a href="kegiatan_harian_add.php" class="alert-link fw-bold">mulai
                                                tambahkan laporan pertama Anda sekarang</a> agar progres PKL Anda selalu
                                            terupdate!
                                        </p>
                                    </div>
                                </div>
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
    <?php include './partials/script.php'; ?>
</body>

</html>