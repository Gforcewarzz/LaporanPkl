<?php
session_start(); // Pastikan session dimulai di paling atas, sebelum output HTML apapun
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; // Memasukkan bagian <head> ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; // Memasukkan sidebar ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; // Memasukkan navbar ?>
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
                                    <a href="master_kegiatan_harian_add.php"
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
                                            <?php
                                            // Memanggil koneksi database
                                            include 'partials/db.php';

                                            // Contoh: Ambil ID siswa dari session. Sesuaikan dengan implementasi otentikasi Anda.
                                            // Jika 'id_siswa' tidak diset di session, gunakan nilai default (misal: 1) untuk demo.
                                            $siswa_id = isset($_SESSION['id_siswa']) ? $_SESSION['id_siswa'] : 1;

                                            // Query untuk mengambil data jurnal harian berdasarkan siswa_id
                                            $sql = "SELECT id_jurnal_harian, tanggal, pekerjaan, catatan FROM jurnal_harian WHERE siswa_id = ? ORDER BY tanggal DESC";
                                            $stmt = $koneksi->prepare($sql);
                                            $stmt->bind_param("i", $siswa_id); // 'i' menandakan integer
                                            $stmt->execute();
                                            $result = $stmt->get_result();

                                            if ($result->num_rows > 0) {
                                                $no = 1;
                                                while ($row = $result->fetch_assoc()) {
                                                    echo '<tr>';
                                                    echo '<td>' . $no++ . '</td>';
                                                    // Format tanggal ke Hari, DD Bulan YYYY (contoh: Senin, 24 Juni 2024)
                                                    echo '<td><strong>' . date('l, d F Y', strtotime($row['tanggal'])) . '</strong></td>';
                                                    echo '<td>' . htmlspecialchars($row['pekerjaan']) . '</td>';
                                                    echo '<td>' . htmlspecialchars($row['catatan']) . '</td>';
                                                    echo '<td>';
                                                    echo '<div class="dropdown">';
                                                    // Tombol aksi dropdown (ini yang harusnya bekerja dengan Bootstrap JS)
                                                    echo '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">';
                                                    echo '<i class="bx bx-dots-vertical-rounded"></i>';
                                                    echo '</button>';
                                                    echo '<div class="dropdown-menu">';
                                                    echo '<a class="dropdown-item" href="master_kegiatan_harian_edit.php?id=' . $row['id_jurnal_harian'] . '">';
                                                    echo '<i class="bx bx-edit-alt me-1"></i> Edit Laporan';
                                                    echo '</a>';
                                                    echo '<div class="dropdown-divider"></div>';
                                                    // Fungsi JavaScript untuk konfirmasi hapus
                                                    echo '<a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmDeleteKegiatanHarian(\'' . $row['id_jurnal_harian'] . '\', \'' . date('l, d F Y', strtotime($row['tanggal'])) . '\')">';
                                                    echo '<i class="bx bx-trash me-1"></i> Hapus';
                                                    echo '</a>';
                                                    echo '</div>';
                                                    echo '</div>';
                                                    echo '</td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                // Pesan jika tidak ada data
                                                echo '<tr><td colspan="5" class="text-center py-4 text-muted">';
                                                echo '<i class="bx bx-info-circle me-1"></i> Belum ada laporan kegiatan yang tercatat.';
                                                echo '</td></tr>';
                                            }
                                            $stmt->close();
                                            // Tidak menutup koneksi di sini karena akan digunakan lagi untuk tampilan mobile
                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-md-none p-3">
                                    <div class="text-center text-muted mb-4 animate__animated animate__fadeInUp">
                                        <small><i class="bx bx-mobile me-1"></i> Geser ke bawah untuk melihat laporan
                                            Anda</small>
                                    </div>

                                    <?php
                                    // Ambil ID siswa untuk tampilan mobile (menggunakan variabel yang sama)
                                    $siswa_id_mobile = isset($_SESSION['id_siswa']) ? $_SESSION['id_siswa'] : 1;

                                    $sql_mobile = "SELECT id_jurnal_harian, tanggal, pekerjaan, catatan FROM jurnal_harian WHERE siswa_id = ? ORDER BY tanggal DESC";
                                    $stmt_mobile = $koneksi->prepare($sql_mobile);
                                    $stmt_mobile->bind_param("i", $siswa_id_mobile);
                                    $stmt_mobile->execute();
                                    $result_mobile = $stmt_mobile->get_result();

                                    if ($result_mobile->num_rows > 0) {
                                        // Array warna untuk border card agar bervariasi
                                        $colors = ['primary', 'warning', 'info', 'success', 'danger'];
                                        $color_index = 0;
                                        while ($row_mobile = $result_mobile->fetch_assoc()) {
                                            $current_color = $colors[$color_index % count($colors)]; // Mengambil warna bergantian
                                            $color_index++;
                                    ?>
                                            <div class="card mb-4 shadow-lg border-start border-4 border-<?= $current_color ?> rounded-3 animate__animated animate__fadeInUp">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div>
                                                            <h6 class="mb-1 text-<?= $current_color ?>"><i class="bx bx-calendar-event me-1"></i> <strong><?= date('l, d F Y', strtotime($row_mobile['tanggal'])) ?></strong></h6>
                                                            <span class="badge bg-label-<?= $current_color ?>"><i class="bx bx-file me-1"></i> Laporan #<?= $row_mobile['id_jurnal_harian'] ?></span>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="master_kegiatan_harian_edit.php?id=<?= $row_mobile['id_jurnal_harian'] ?>">
                                                                    <i class="bx bx-edit-alt me-1"></i> Edit Laporan
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                    onclick="confirmDeleteKegiatanHarian('<?= $row_mobile['id_jurnal_harian'] ?>', '<?= date('l, d F Y', strtotime($row_mobile['tanggal'])) ?>')">
                                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-2">
                                                        <strong class="text-dark"><i class="bx bx-task me-1"></i> Pekerjaan:</strong><br>
                                                        <?= nl2br(htmlspecialchars($row_mobile['pekerjaan'])) ?>
                                                    </div>
                                                    <div class="mb-0 text-wrap">
                                                        <strong class="text-dark"><i class="bx bx-info-circle me-1"></i> Catatan:</strong><br>
                                                        <?= nl2br(htmlspecialchars($row_mobile['catatan'])) ?>
                                                    </div>
                                                    <div class="d-flex justify-content-end mt-3">
                                                        <small class="text-muted"><i class="bx bx-calendar-check me-1"></i> Dilaporkan: <?= date('d F Y, H:i', strtotime($row_mobile['tanggal'])) ?> WIB</small>
                                                    </div>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    } else {
                                        // Pesan jika tidak ada data untuk mobile
                                        echo '<div class="alert alert-info text-center mt-5 py-4 animate__animated animate__fadeInUp animate__delay-0-3s" role="alert" style="border-radius: 8px;">';
                                        echo '<h5 class="alert-heading mb-3"><i class="bx bx-list-plus bx-lg text-info"></i></h5>';
                                        echo '<p class="mb-3">Belum ada laporan kegiatan yang tercatat di sini.</p>';
                                        echo '<p class="mb-0">';
                                        echo 'Ayo, <a href="master_kegiatan_harian_add.php" class="alert-link fw-bold">tambahkan laporan pertama Anda</a> sekarang!';
                                        echo '</p>';
                                        echo '</div>';
                                    }
                                    $stmt_mobile->close();
                                    $koneksi->close(); // Tutup koneksi database setelah semua query selesai
                                    ?>
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
    function confirmDeleteKegiatanHarian(id, tanggal) {
        Swal.fire({
            title: 'Konfirmasi Hapus Laporan Harian',
            html: "Apakah Anda yakin ingin menghapus laporan kegiatan pada tanggal <strong>" + tanggal +
                "</strong>?<br>Tindakan ini tidak dapat dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Sekarang!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect ke script delete jika dikonfirmasi
                window.location.href = 'master_kegiatan_harian_delete.php?id=' + id;
            }
        });
    }
    </script>

    <?php include './partials/script.php'; // Memasukkan semua script JS lainnya, termasuk BOOTSTRAP JS ?>
</body>

</html>