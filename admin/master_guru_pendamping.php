<?php
include 'partials/head.php';
include 'partials/db.php';
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/" data-template="vertical-menu-template-free">

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include './partials/sidebar.php'; ?>
        <div class="layout-page">
            <?php include './partials/navbar.php'; ?>
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <h4 class="fw-bold mb-0 text-primary">
                            <span class="text-muted fw-light">Master /</span> Data Guru Pendamping
                        </h4>
                        <i class="fas fa-chalkboard-teacher fa-2x text-info" style="opacity: 0.6;"></i>
                    </div>

                    <!-- Banner -->
                    <div class="card bg-gradient-primary-to-secondary text-white mb-4 shadow-lg"
                         style="border-radius: 12px; overflow: hidden;">
                        <div class="card-body p-4 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                            <div class="text-center text-sm-start mb-3 mb-sm-0">
                                <h5 class="card-title text-white mb-1">Manajemen Data Guru Pendamping</h5>
                                <p class="card-text text-white-75 small">Kelola informasi pembimbing PKL siswa.</p>
                            </div>
                            <div class="text-center text-sm-end">
                                <div class="rounded-circle bg-white d-flex justify-content-center align-items-center"
                                     style="width: 80px; height: 80px; opacity: 0.2;">
                                    <i class="bx bx-user-voice bx-lg text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card mb-4 shadow-lg">
                        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-4">
                            <div class="d-flex gap-2 w-100 w-md-auto">
                                <a href="index.php" class="btn btn-outline-secondary w-100">
                                    <i class="bx bx-arrow-back me-1"></i> Kembali
                                </a>
                                <a href="master_guru_pendamping_add.php" class="btn btn-primary w-100">
                                    <i class="bx bx-plus me-1"></i> Tambah Guru
                                </a>
                            </div>
                            <div class="d-flex gap-2 w-100 w-md-auto">
                                <button type="button" class="btn btn-outline-danger w-100">
                                    <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
                                </button>
                                <button type="button" class="btn btn-outline-success w-100">
                                    <i class="bx bxs-file-excel me-1"></i> Ekspor Excel
                                </button>
                            </div>
                        </div>
                        <div class="card-footer bg-light border-top p-3">
                            <div class="row align-items-center">
                                <div class="col-md-8 mb-2 mb-md-0">
                                    <input type="text" class="form-control" placeholder="Cari guru berdasarkan nama, NIP, atau bidang keahlian...">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <button class="btn btn-outline-dark w-100 w-md-auto">
                                        <i class="bx bx-filter-alt me-1"></i> Filter Guru
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Lengkap Guru Pendamping</h5>
                            <small class="text-muted">Informasi dari database</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="table table-hover" style="min-width: 800px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Nama Guru</th>
                                            <th>NIP</th>
                                            <th>Password</th>
                                            <th style="width: 100px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        $query = "SELECT * FROM guru_pembimbing ORDER BY id_pembimbing ASC";
                                        $result = mysqli_query($koneksi, $query);

                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>
                                                    <td>{$no}</td>
                                                    <td class='text-wrap'>{$row['nama_pembimbing']}</td>
                                                    <td>{$row['nip']}</td>
                                                    <td>{$row['password']}</td>
                                                    <td>
                                                        <div class='dropdown'>
                                                            <button class='btn p-0 dropdown-toggle' data-bs-toggle='dropdown'>
                                                                <i class='bx bx-dots-vertical-rounded'></i>
                                                            </button>
                                                            <div class='dropdown-menu'>
                                                                <a class='dropdown-item' href='master_guru_pendamping_edit.php?id={$row['id_pembimbing']}'>
                                                                    <i class='bx bx-edit-alt me-1'></i> Edit
                                                                </a>
                                                                <a class='dropdown-item text-danger' href='javascript:void(0);' onclick=\"confirmDeleteGuru('{$row['id_pembimbing']}', '{$row['nama_pembimbing']}')\">
                                                                    <i class='bx bx-trash me-1'></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                  </tr>";
                                            $no++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
    </div>
</div>

<!-- SweetAlert & Script -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteGuru(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus Data Guru',
        html: `Apakah Anda yakin ingin menghapus data guru <strong>${nama}</strong>?<br>Tindakan ini tidak dapat dibatalkan!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Sekarang!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'master_guru_pendamping_delete.php?id=' + id;
        }
    });
}
</script>

<?php include './partials/script.php'; ?>
</body>
</html>
