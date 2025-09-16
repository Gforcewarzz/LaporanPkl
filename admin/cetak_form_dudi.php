<?php
session_start();
require_once 'partials/db.php';

// Keamanan: Admin dan Guru boleh akses
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// Logika Filter & Pencarian Siswa
$keyword = $_GET['keyword'] ?? '';
$conditions = [];
$params = [];
$types = '';

if ($is_guru) {
    $conditions[] = 's.pembimbing_id = ?';
    $params[] = $_SESSION['id_guru_pendamping'];
    $types .= 'i';
}

if (!empty($keyword)) {
    $like_keyword = "%" . $keyword . "%";
    $conditions[] = '(s.nama_siswa LIKE ? OR s.nisn LIKE ?)';
    $params[] = $like_keyword;
    $params[] = $like_keyword;
    $types .= 'ss';
}

$query_siswa = "SELECT s.id_siswa, s.nama_siswa, s.nisn, s.kelas FROM siswa s";
if (!empty($conditions)) {
    $query_siswa .= " WHERE " . implode(' AND ', $conditions);
}
$query_siswa .= " ORDER BY s.kelas, s.nama_siswa";

$stmt = $koneksi->prepare($query_siswa);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result_siswa = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'partials/head.php'; ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <h4 class="fw-bold mb-4 text-primary"><span class="text-muted fw-light">Penilaian /</span> Cetak
                            Formulir DUDI</h4>

                        <div class="card shadow-lg">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Pilih Siswa</h5>
                                <small class="text-muted">Cari siswa, lalu klik tombol "Pilih Kegiatan & Cetak" untuk
                                    memilih jurnal yang akan dimasukkan ke formulir.</small>
                            </div>
                            <div class="card-body p-4">
                                <form method="GET" action="" class="mb-4">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <input type="text" name="keyword" class="form-control"
                                                placeholder="Cari berdasarkan Nama atau NISN..."
                                                value="<?= htmlspecialchars($keyword) ?>">
                                        </div>
                                        <div class="col-md-4 d-flex">
                                            <button type="submit" class="btn btn-primary me-2 w-100"><i
                                                    class="bx bx-search"></i> Cari</button>
                                            <a href="cetak_form_dudi.php" class="btn btn-outline-secondary"><i
                                                    class="bx bx-reset"></i></a>
                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive text-nowrap">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nama Siswa</th>
                                                <th>NISN</th>
                                                <th>Kelas</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result_siswa->num_rows > 0): ?>
                                            <?php while ($row = $result_siswa->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($row['nama_siswa']) ?></strong></td>
                                                <td><?= htmlspecialchars($row['nisn']) ?></td>
                                                <td><?= htmlspecialchars($row['kelas']) ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-info btn-pilih-kegiatan"
                                                        data-bs-toggle="modal" data-bs-target="#jurnalModal"
                                                        data-siswa-id="<?= $row['id_siswa'] ?>"
                                                        data-nama-siswa="<?= htmlspecialchars($row['nama_siswa']) ?>">
                                                        <i class="bx bx-list-check me-1"></i> Pilih Kegiatan & Cetak
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Siswa tidak ditemukan.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="jurnalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Jurnal Kegiatan (Maks. 3) untuk <strong id="namaSiswaModal"></strong>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="jurnalListContainer">
                        <div class="text-center p-4">
                            <div class="spinner-border text-primary"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="selectedSiswaId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="lanjutCetakBtn">Lanjutkan Cetak</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/script.php' ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const jurnalModal = new bootstrap.Modal(document.getElementById('jurnalModal'));
        const jurnalListContainer = document.getElementById('jurnalListContainer');
        const namaSiswaModal = document.getElementById('namaSiswaModal');
        const selectedSiswaIdInput = document.getElementById('selectedSiswaId');
        const lanjutCetakBtn = document.getElementById('lanjutCetakBtn');

        // Saat tombol "Pilih Kegiatan & Cetak" di klik
        document.querySelectorAll('.btn-pilih-kegiatan').forEach(button => {
            button.addEventListener('click', async function() {
                const siswaId = this.dataset.siswaId;
                const namaSiswa = this.dataset.namaSiswa;

                namaSiswaModal.textContent = namaSiswa;
                selectedSiswaIdInput.value = siswaId;
                jurnalListContainer.innerHTML =
                    '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';

                // Ambil daftar jurnal dari backend
                const response = await fetch(`get_jurnals_siswa.php?siswa_id=${siswaId}`);
                const jurnals = await response.json();

                let tableHtml =
                    '<p class="text-center text-muted">Siswa ini belum memiliki jurnal kegiatan.</p>';
                if (jurnals.length > 0) {
                    tableHtml = `
                        <table class="table table-hover">
                            <thead><tr><th>Pilih</th><th>Tanggal</th><th>Nama Pekerjaan</th></tr></thead>
                            <tbody>`;
                    jurnals.forEach(jurnal => {
                        tableHtml += `
                            <tr>
                                <td><input class="form-check-input jurnal-checkbox" type="checkbox" value="${jurnal.id_jurnal_kegiatan}"></td>
                                <td>${new Date(jurnal.tanggal_laporan).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'})}</td>
                                <td>${jurnal.nama_pekerjaan}</td>
                            </tr>`;
                    });
                    tableHtml += '</tbody></table>';
                }
                jurnalListContainer.innerHTML = tableHtml;
            });
        });

        // Batasi pilihan checkbox di dalam modal
        jurnalListContainer.addEventListener('change', function(e) {
            if (e.target.classList.contains('jurnal-checkbox')) {
                const checkedCount = jurnalListContainer.querySelectorAll('.jurnal-checkbox:checked')
                    .length;
                if (checkedCount > 3) {
                    alert('Anda hanya dapat memilih maksimal 3 kegiatan.');
                    e.target.checked = false;
                }
            }
        });

        // Saat tombol "Lanjutkan Cetak" di klik
        lanjutCetakBtn.addEventListener('click', function() {
            const siswaId = selectedSiswaIdInput.value;
            const selectedJurnals = [];
            jurnalListContainer.querySelectorAll('.jurnal-checkbox:checked').forEach(cb => {
                selectedJurnals.push(cb.value);
            });

            if (selectedJurnals.length === 0) {
                alert('Pilih setidaknya satu kegiatan jurnal untuk dicetak.');
                return;
            }

            // Buat URL untuk membuka tab baru ke skrip PDF
            const jurnalIdsParam = selectedJurnals.join(',');
            const pdfUrl =
                `generate_penilaian_dudi_pdf.php?siswa_id=${siswaId}&jurnal_ids=${jurnalIdsParam}`;

            window.open(pdfUrl, '_blank');
            jurnalModal.hide();
        });
    });
    </script>
</body>

</html>