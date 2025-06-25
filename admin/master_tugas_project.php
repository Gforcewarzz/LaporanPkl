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
                                <span class="text-muted fw-light">Laporan /</span> Tugas Proyek
                            </h4>
                            <i class="fas fa-tasks fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>
                        <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                            <div
                                class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                                <div class="text-center text-sm-start mb-3 mb-sm-0">
                                    <h5 class="card-title text-white mb-1">Jelajahi Detail Tugas & Proyek Anda</h5>
                                    <p class="card-text text-white-75 small">Pantau setiap progres dari perencanaan
                                        hingga hasil akhir.</p>
                                </div>
                                <div class="text-center text-sm-end position-relative">
                                    <div class="rounded-circle bg-white d-flex justify-content-center align-items-center animate__animated animate__zoomIn animate__delay-0-5s"
                                        style="width: 80px; height: 80px; opacity: 0.2; position: relative; overflow: hidden; z-index: 1;">
                                        <i class="bx bx-briefcase bx-lg text-primary"
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
                                    <a href="index.php"
                                        class="btn btn-outline-secondary w-100 animate__animated animate__fadeInUp animate__delay-0-2s">
                                        <i class="bx bx-arrow-back me-1"></i> Kembali
                                    </a>
                                    <a href="laporan_tugas_add.php"
                                        class="btn btn-primary w-100 animate__animated animate__fadeInUp animate__delay-0-3s">
                                        <i class="bx bx-plus me-1"></i> Tambah Laporan Tugas
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
                                            placeholder="Cari laporan berdasarkan nama proyek atau tanggal..."
                                            aria-label="Search" />
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
                                <h5 class="mb-0">Daftar Laporan Tugas Proyek</h5>
                                <small class="text-muted">Riwayat detail setiap proyek/kegiatan</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap d-none d-md-block">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Pekerjaan / Proyek</th>
                                                <th>Perencanaan Kegiatan</th>
                                                <th>Pelaksanaan Kegiatan</th>
                                                <th>Catatan Instruktur</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <tr>
                                                <td>1</td>
                                                <td><strong>Pengembangan Website E-Commerce</strong></td>
                                                <td>Analisis kebutuhan, desain UI/UX, database schema.</td>
                                                <td>Frontend: Halaman produk & keranjang. Backend: API autentikasi.</td>
                                                <td>Progres sangat baik, memahami alur kerja MVC. Tingkatkan efisiensi
                                                    query database.</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="laporan_tugas_edit.php?id=1">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item text-danger"
                                                                href="javascript:void(0);"
                                                                onclick="confirmDeleteLaporanTugas('1', 'Pengembangan Website E-Commerce')">
                                                                <i class="bx bx-trash me-1"></i> Hapus
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item"
                                                                href="master_tugas_project_print.php?id=1"
                                                                target="_blank">
                                                                <i class="bx bx-printer me-1"></i> Print
                                                            </a>
                                                            <a class="dropdown-item" href="generate_tugas_pdf.php?id=1"
                                                                target="_blank">
                                                                <i class="bx bxs-download me-1"></i> Download PDF
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td><strong>Instalasi & Konfigurasi Server Jaringan</strong></td>
                                                <td>Survei lokasi, daftar perangkat, IP addressing plan.</td>
                                                <td>Pemasangan rack server, konfigurasi DHCP & DNS, testing
                                                    konektivitas.</td>
                                                <td>Pemahaman dasar jaringan cukup kuat, namun perlu pendalaman lebih
                                                    lanjut pada subnetting.</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="laporan_tugas_edit.php?id=2">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item text-danger"
                                                                href="javascript:void(0);"
                                                                onclick="confirmDeleteLaporanTugas('2', 'Instalasi & Konfigurasi Server Jaringan')">
                                                                <i class="bx bx-trash me-1"></i> Hapus
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item"
                                                                href="master_tugas_project_print.php?id=2"
                                                                target="_blank">
                                                                <i class="bx bx-printer me-1"></i> Print
                                                            </a>
                                                            <a class="dropdown-item" href="generate_tugas_pdf.php?id=2"
                                                                target="_blank">
                                                                <i class="bx bxs-download me-1"></i> Download PDF
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td><strong>Desain Materi Promosi Digital</strong></td>
                                                <td>Brainstorming ide, riset target audiens, pemilihan warna & font.
                                                </td>
                                                <td>Pembuatan banner iklan untuk sosial media (3 desain), revisi minor.
                                                </td>
                                                <td>Kreativitas sangat baik, perlu lebih fokus pada optimasi ukuran file
                                                    untuk web.</td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="laporan_tugas_edit.php?id=3">
                                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                                            </a>
                                                            <a class="dropdown-item text-danger"
                                                                href="javascript:void(0);"
                                                                onclick="confirmDeleteLaporanTugas('3', 'Desain Materi Promosi Digital')">
                                                                <i class="bx bx-trash me-1"></i> Hapus
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item"
                                                                href="master_tugas_project_print.php?id=3"
                                                                target="_blank">
                                                                <i class="bx bx-printer me-1"></i> Print
                                                            </a>
                                                            <a class="dropdown-item" href="generate_tugas_pdf.php?id=3"
                                                                target="_blank">
                                                                <i class="bx bxs-download me-1"></i> Download PDF
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
                                            proyek</small>
                                    </div>

                                    <div
                                        class="card mb-4 shadow-lg border-start border-4 border-primary rounded-3 animate__animated animate__fadeInUp">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1 text-primary"><i class="bx bx-folder me-1"></i>
                                                        <strong>Pengembangan Website E-Commerce</strong>
                                                    </h6>
                                                    <span class="badge bg-label-primary"><i class="bx bx-hash me-1"></i>
                                                        Proyek #1</span>
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="laporan_tugas_edit.php?id=1">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit Laporan
                                                        </a>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            onclick="confirmDeleteLaporanTugas('1', 'Pengembangan Website E-Commerce')">
                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item"
                                                            href="master_tugas_project_print.php?id=1" target="_blank">
                                                            <i class="bx bx-printer me-1"></i> Print
                                                        </a>
                                                        <a class="dropdown-item" href="generate_tugas_pdf.php?id=1"
                                                            target="_blank">
                                                            <i class="bx bxs-download me-1"></i> Download PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-list-check me-1"></i>
                                                    Perencanaan Kegiatan:</strong><br>
                                                Analisis kebutuhan, desain UI/UX, penentuan struktur database.
                                            </div>
                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-code-alt me-1"></i>
                                                    Pelaksanaan Kegiatan:</strong><br>
                                                Pengembangan frontend: halaman produk dan keranjang belanja.
                                                Pengembangan backend: API autentikasi pengguna.
                                            </div>
                                            <div class="mb-2 text-wrap">
                                                <strong class="text-dark"><i class="bx bx-message-square-dots me-1"></i>
                                                    Catatan Instruktur:</strong><br>
                                                Progres sangat baik, menunjukkan pemahaman kuat pada alur kerja MVC.
                                                Disarankan untuk lebih fokus pada efisiensi query database di tahap
                                                selanjutnya.
                                            </div>
                                            <div class="d-flex justify-content-end mt-3">
                                                <small class="text-muted"><i class="bx bx-time me-1"></i> Terakhir
                                                    Diperbarui: 23 Juni 2025</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="card mb-4 shadow-lg border-start border-4 border-warning rounded-3 animate__animated animate__fadeInUp animate__delay-0-1s">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1 text-warning"><i class="bx bx-folder me-1"></i>
                                                        <strong>Instalasi & Konfigurasi Server Jaringan</strong>
                                                    </h6>
                                                    <span class="badge bg-label-warning"><i class="bx bx-hash me-1"></i>
                                                        Proyek #2</span>
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="laporan_tugas_edit.php?id=2">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit Laporan
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            onclick="confirmDeleteLaporanTugas('2', 'Instalasi & Konfigurasi Server Jaringan')">
                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item"
                                                            href="master_tugas_project_print.php?id=2" target="_blank">
                                                            <i class="bx bx-printer me-1"></i> Print
                                                        </a>
                                                        <a class="dropdown-item" href="generate_tugas_pdf.php?id=2"
                                                            target="_blank">
                                                            <i class="bx bxs-download me-1"></i> Download PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-list-check me-1"></i>
                                                    Perencanaan Kegiatan:</strong><br>
                                                Survei lokasi server, inventarisasi perangkat keras, pembuatan IP
                                                addressing plan.
                                            </div>
                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-code-alt me-1"></i>
                                                    Pelaksanaan Kegiatan:</strong><br>
                                                Pemasangan rack server, konfigurasi layanan DHCP dan DNS, pengujian
                                                konektivitas jaringan.
                                            </div>
                                            <div class="mb-2 text-wrap">
                                                <strong class="text-dark"><i class="bx bx-message-square-dots me-1"></i>
                                                    Catatan Instruktur:</strong><br>
                                                Pemahaman dasar jaringan cukup kuat. Namun, perlu pendalaman lebih
                                                lanjut pada konsep subnetting dan routing untuk tugas yang lebih
                                                kompleks.
                                            </div>
                                            <div class="d-flex justify-content-end mt-3">
                                                <small class="text-muted"><i class="bx bx-time me-1"></i> Terakhir
                                                    Diperbarui: 20 Juni 2025</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="card mb-4 shadow-lg border-start border-4 border-info rounded-3 animate__animated animate__fadeInUp animate__delay-0-2s">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1 text-info"><i class="bx bx-folder me-1"></i>
                                                        <strong>Desain Materi Promosi Digital</strong>
                                                    </h6>
                                                    <span class="badge bg-label-info"><i class="bx bx-hash me-1"></i>
                                                        Proyek #3</span>
                                                </div>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-atas"> <a
                                                            class="dropdown-item" href="laporan_tugas_edit.php?id=3">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit Laporan
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            onclick="confirmDeleteLaporanTugas('3', 'Desain Materi Promosi Digital')">
                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item"
                                                            href="master_tugas_project_print.php?id=3" target="_blank">
                                                            <i class="bx bx-printer me-1"></i> Print
                                                        </a>
                                                        <a class="dropdown-item" href="generate_tugas_pdf.php?id=3"
                                                            target="_blank">
                                                            <i class="bx bxs-download me-1"></i> Download PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-list-check me-1"></i>
                                                    Perencanaan Kegiatan:</strong><br>
                                                Brainstorming ide konsep visual, riset target audiens, pemilihan palet
                                                warna dan tipografi.
                                            </div>
                                            <div class="mb-2">
                                                <strong class="text-dark"><i class="bx bx-code-alt me-1"></i>
                                                    Pelaksanaan Kegiatan:</strong><br>
                                                Pembuatan 3 variasi desain banner iklan untuk platform sosial media.
                                                Dilakukan revisi minor berdasarkan feedback awal.
                                            </div>
                                            <div class="mb-2 text-wrap">
                                                <strong class="text-dark"><i class="bx bx-message-square-dots me-1"></i>
                                                    Catatan Instruktur:</strong><br>
                                                Kreativitas sangat baik, perlu perhatian lebih pada optimasi ukuran file
                                                gambar untuk web agar tidak memberatkan loading halaman.
                                            </div>
                                            <div class="d-flex justify-content-end mt-3">
                                                <small class="text-muted"><i class="bx bx-time me-1"></i> Terakhir
                                                    Diperbarui: 18 Juni 2025</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info text-center mt-5 py-4 animate__animated animate__fadeInUp animate__delay-0-3s"
                                        role="alert" style="border-radius: 8px;">
                                        <h5 class="alert-heading mb-3"><i class="bx bx-task-x bx-lg text-info"></i></h5>
                                        <p class="mb-3">Belum ada laporan tugas proyek yang tercatat di sini.</p>
                                        <p class="mb-0">
                                            Ayo, <a href="laporan_tugas_add.php" class="alert-link fw-bold">tambahkan
                                                laporan proyek pertama Anda</a> sekarang!
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmDeleteLaporanTugas(id, namaProyek) {
            Swal.fire({
                title: 'Konfirmasi Hapus Laporan Proyek',
                html: "Apakah Anda yakin ingin menghapus laporan proyek <strong>" + namaProyek +
                    "</strong>?<br>Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545', // Warna merah untuk konfirmasi hapus
                cancelButtonColor: '#6c757d', // Warna abu-abu untuk batal
                confirmButtonText: 'Ya, Hapus Sekarang!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika dikonfirmasi, arahkan ke skrip PHP untuk delete
                    // Pastikan Anda membuat file 'proses_delete_laporan_tugas.php'
                    window.location.href = 'proses_delete_laporan_tugas.php?id=' + id;
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <?php include './partials/script.php'; ?>
</body>

</html>