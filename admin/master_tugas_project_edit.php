<?php
session_start();
include 'partials/db.php';

// --- LOGIKA KEAMANAN & HAK AKSES ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Jika tidak login sebagai salah satu dari peran yang diizinkan, redirect ke halaman login
if (!$is_siswa && !$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// Ambil ID laporan yang akan diedit dari URL
$id_jurnal_kegiatan = $_GET['id'] ?? null;
if (empty($id_jurnal_kegiatan) || !is_numeric($id_jurnal_kegiatan)) {
    header('Location: master_tugas_project.php'); // Redirect jika ID tidak valid
    exit();
}

// --- PENGAMBILAN DATA LAPORAN ---
$query_laporan = "SELECT id_jurnal_kegiatan, nama_pekerjaan, perencanaan_kegiatan, 
                    pelaksanaan_kegiatan, catatan_instruktur, gambar, tanggal_laporan, siswa_id
                  FROM jurnal_kegiatan WHERE id_jurnal_kegiatan = ?";
$stmt_laporan = $koneksi->prepare($query_laporan);

if (!$stmt_laporan) {
    error_log("Gagal mempersiapkan statement: " . $koneksi->error);
    header('Location: master_tugas_project.php');
    exit();
}

$stmt_laporan->bind_param("i", $id_jurnal_kegiatan);
$stmt_laporan->execute();
$result_laporan = $stmt_laporan->get_result();
$laporan_data = $result_laporan->fetch_assoc();
$stmt_laporan->close();

// Jika laporan tidak ditemukan di database, redirect.
if (!$laporan_data) {
    header('Location: master_tugas_project.php');
    exit();
}

// --- LOGIKA OTORISASI UNTUK AKSES EDIT ---
$is_authorized = false;
if ($is_admin) {
    $is_authorized = true; // Admin dapat mengedit semua laporan
} elseif ($is_siswa) {
    // Siswa hanya bisa mengedit laporannya sendiri
    if ($laporan_data['siswa_id'] == ($_SESSION['id_siswa'] ?? null)) {
        $is_authorized = true;
    }
} elseif ($is_guru) {
    // Guru hanya bisa mengedit laporan siswa bimbingannya
    $guru_id_bimbingan = $_SESSION['id_guru_pendamping'] ?? null;
    $siswa_id_laporan = $laporan_data['siswa_id'];

    if ($guru_id_bimbingan !== null) {
        $query_cek_bimbingan = "SELECT id_siswa FROM siswa WHERE id_siswa = ? AND pembimbing_id = ?";
        $stmt_cek = $koneksi->prepare($query_cek_bimbingan);
        if ($stmt_cek) {
            $stmt_cek->bind_param("ii", $siswa_id_laporan, $guru_id_bimbingan);
            $stmt_cek->execute();
            $stmt_cek->store_result();
            if ($stmt_cek->num_rows > 0) {
                $is_authorized = true; // Otorisasi berhasil jika siswa adalah bimbingannya
            }
            $stmt_cek->close();
        }
    }
}

// Jika setelah semua pengecekan pengguna tidak diotorisasi, redirect
if (!$is_authorized) {
    $_SESSION['alert_message'] = "Anda tidak memiliki izin untuk mengedit laporan ini.";
    $_SESSION['alert_type'] = "error";
    $_SESSION['alert_title'] = "Akses Ditolak";
    header('Location: master_tugas_project.php');
    exit();
}

// Ambil nama siswa terkait (jika yang login adalah admin atau guru)
$siswa_nama_terkait = "";
if ($is_admin || $is_guru) {
    $query_siswa_nama = "SELECT nama_siswa FROM siswa WHERE id_siswa = ?";
    $stmt_siswa_nama = $koneksi->prepare($query_siswa_nama);
    if ($stmt_siswa_nama) {
        $stmt_siswa_nama->bind_param("i", $laporan_data['siswa_id']);
        $stmt_siswa_nama->execute();
        $result_siswa_nama = $stmt_siswa_nama->get_result();
        if ($result_siswa_nama->num_rows > 0) {
            $siswa_nama_terkait = htmlspecialchars($result_siswa_nama->fetch_assoc()['nama_siswa']);
        }
        $stmt_siswa_nama->close();
    }
}

// Ambil pesan notifikasi dari session jika ada
$message = $_SESSION['laporan_message'] ?? '';
$message_type = $_SESSION['laporan_message_type'] ?? '';
$message_title_swal = $_SESSION['laporan_message_title'] ?? '';
unset($_SESSION['laporan_message'], $_SESSION['laporan_message_type'], $_SESSION['laporan_message_title']);

$koneksi->close(); // Tutup konesi setelah semua data diambil
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <?php if ($message): // Tampilkan SweetAlert2 jika ada pesan 
                        ?>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: '<?= htmlspecialchars($message_type); ?>',
                                title: '<?= htmlspecialchars($message_title_swal); ?>',
                                text: '<?= htmlspecialchars($message); ?>',
                                confirmButtonColor: '#696cff',
                                confirmButtonText: 'OK'
                            });
                        });
                        </script>
                        <?php endif; ?>

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">Laporan /</span> Edit Jurnal PKL Per Kegiatan
                                <?php if (!empty($siswa_nama_terkait)): ?>
                                <span class="text-info">(Siswa: <?= $siswa_nama_terkait ?>)</span>
                                <?php endif; ?>
                            </h4>
                            <i class="fas fa-pencil-alt fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <form action="master_tugas_project_edit_act.php" method="POST" class="card p-4 shadow-lg"
                            enctype="multipart/form-data">

                            <input type="hidden" name="id_jurnal_kegiatan"
                                value="<?= htmlspecialchars($laporan_data['id_jurnal_kegiatan']); ?>">
                            <input type="hidden" name="siswa_id_original"
                                value="<?= htmlspecialchars($laporan_data['siswa_id']); ?>">
                            <input type="hidden" name="gambar_lama"
                                value="<?= htmlspecialchars($laporan_data['gambar'] ?? ''); ?>">

                            <?php if ($is_admin || $is_guru): // Kirim siswa_id untuk redirect jika admin/guru 
                            ?>
                            <input type="hidden" name="redirect_siswa_id"
                                value="<?= htmlspecialchars($laporan_data['siswa_id']); ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="nama_pekerjaan" class="form-label fw-bold">Nama Tugas / Aktivitas
                                    Utama:</label>
                                <input type="text" class="form-control" id="nama_pekerjaan" name="nama_pekerjaan"
                                    value="<?= htmlspecialchars($laporan_data['nama_pekerjaan']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="perencanaan_kegiatan" class="form-label fw-bold">Perencanaan Kegiatan
                                    (sebelumnya):</label>
                                <textarea class="form-control" id="perencanaan_kegiatan" name="perencanaan_kegiatan"
                                    rows="5"
                                    required><?= htmlspecialchars($laporan_data['perencanaan_kegiatan']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="pelaksanaan_kegiatan" class="form-label fw-bold">Pelaksanaan Kegiatan &
                                    Hasil yang Dicapai:</label>
                                <textarea class="form-control" id="pelaksanaan_kegiatan" name="pelaksanaan_kegiatan"
                                    rows="7"
                                    required><?= htmlspecialchars($laporan_data['pelaksanaan_kegiatan']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Gambar Bukti Kegiatan Saat Ini:</label>
                                <?php if (!empty($laporan_data['gambar'])): ?>
                                <div class="mb-2">
                                    <a href="images/<?= htmlspecialchars($laporan_data['gambar']); ?>" target="_blank">
                                        <img src="images/<?= htmlspecialchars($laporan_data['gambar']); ?>"
                                            alt="Gambar Proyek Saat Ini" class="img-fluid rounded shadow-sm"
                                            style="max-width: 250px; height: auto; display: block;">
                                    </a>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info py-2" role="alert">Tidak ada gambar yang diunggah.</div>
                                <?php endif; ?>

                                <label for="gambar_proyek" class="form-label fw-bold mt-3"><i
                                        class="bx bx-image me-1"></i> Ganti Gambar Bukti (Opsional):</label>
                                <input class="form-control" type="file" id="gambar_proyek" name="gambar_proyek"
                                    accept="image/*">
                                <div class="form-text text-muted">Unggah file baru jika ingin mengganti gambar. Format:
                                    JPG, PNG, GIF. Maks. 2MB.</div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-3">
                                <label for="catatan_instruktur" class="form-label fw-bold text-primary"><i
                                        class='bx bxs-comment-dots me-1'></i>Catatan Instruktur:</label>
                                <textarea class="form-control" id="catatan_instruktur" name="catatan_instruktur"
                                    rows="4"><?= htmlspecialchars($laporan_data['catatan_instruktur'] ?? ''); ?></textarea>
                                <div class="form-text text-muted">Catatan dapat diisi oleh siswa, guru, atau instruktur.
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <a href="master_tugas_project.php<?php echo (($is_admin || $is_guru) && !empty($laporan_data['siswa_id'])) ? '?siswa_id=' . htmlspecialchars($laporan_data['siswa_id']) : ''; ?>"
                                    class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary"><i class='bx bx-save me-1'></i>Simpan
                                    Perubahan</button>
                            </div>
                        </form>

                    </div>
                    <?php include './partials/footer.php'; ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <?php include 'partials/script.php'; ?>
</body>

</html>