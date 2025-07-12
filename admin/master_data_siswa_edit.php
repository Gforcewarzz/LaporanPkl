<?php
session_start();

// Standarisasi pengecekan peran
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

// [UBAH] Keamanan: Hanya admin atau guru yang boleh mengakses halaman ini
if (!$is_admin && !$is_guru) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php');
    } else {
        header('Location: ../login.php');
    }
    exit();
}

include 'partials/db.php';

// Ambil ID siswa dari URL
$id_siswa = $_GET['id'] ?? 0;
if ($id_siswa == 0) {
    // Jika tidak ada ID, redirect atau tampilkan error
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'Error', 'text' => 'ID Siswa tidak valid.'];
    header('Location: master_data_siswa.php');
    exit();
}

// [UBAH] Query untuk mengambil data siswa dengan Prepared Statement (LEBIH AMAN + OTORISASI)
$sql = "SELECT 
            s.*,
            j.nama_jurusan,
            gp.nama_pembimbing,
            tp.nama_tempat_pkl
        FROM siswa s
        LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
        LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
        LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
        WHERE s.id_siswa = ?";

$params = [$id_siswa];
$types = "i";

// Jika yang login adalah guru, tambahkan kondisi untuk memastikan dia hanya bisa edit siswanya sendiri
if ($is_guru) {
    $sql .= " AND s.pembimbing_id = ?";
    $params[] = $_SESSION['id_guru_pendamping'];
    $types .= "i";
}

$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Data tidak ditemukan, atau guru tidak punya hak akses ke siswa ini.
    $_SESSION['alert'] = ['type' => 'error', 'title' => 'Akses Ditolak', 'text' => 'Data siswa tidak ditemukan atau Anda tidak memiliki izin untuk mengedit data ini.'];
    header('Location: master_data_siswa.php');
    exit();
}

$data = $result->fetch_assoc();
$stmt->close();

// Ambil data untuk opsi dropdown
$jurusan_options = $koneksi->query("SELECT id_jurusan, nama_jurusan FROM jurusan ORDER BY nama_jurusan ASC");
$guru_options = $koneksi->query("SELECT id_pembimbing, nama_pembimbing FROM guru_pembimbing ORDER BY nama_pembimbing ASC");
$tempat_pkl_options = $koneksi->query("SELECT id_tempat_pkl, nama_tempat_pkl FROM tempat_pkl ORDER BY nama_tempat_pkl ASC");

?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">
<?php include 'partials/head.php';?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="fw-bold mb-0 text-primary">
                                <span class="text-muted fw-light">Master Data /</span> Edit Siswa
                            </h4>
                            <i class="fas fa-user-edit fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card shadow-lg">
                            <div class="card-header border-bottom">
                                <h5 class="mb-0">Formulir Edit Data Siswa</h5>
                                <small class="text-muted">Anda sedang mengedit data untuk: <strong><?= htmlspecialchars($data['nama_siswa']) ?></strong></small>
                            </div>
                            <div class="card-body p-4">
                                <form action="master_data_siswa_edit_act.php" method="POST">
                                    <input type="hidden" name="id_siswa" value="<?= htmlspecialchars($data['id_siswa']) ?>">

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nama Siswa</label>
                                        <input type="text" name="nama_siswa" class="form-control" required value="<?= htmlspecialchars($data['nama_siswa']) ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Jenis Kelamin</label><br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="jenis_kelamin" value="Laki-laki" id="jk1" <?= ($data['jenis_kelamin'] === 'Laki-laki') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="jk1">Laki-laki</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="jenis_kelamin" value="Perempuan" id="jk2" <?= ($data['jenis_kelamin'] === 'Perempuan') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="jk2">Perempuan</label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">NISN</label>
                                        <input type="text" name="nisn" class="form-control" required value="<?= htmlspecialchars($data['nisn']) ?>" title="NISN harus 10 digit angka">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">No Induk</label>
                                        <input type="text" name="no_induk" class="form-control" required value="<?= htmlspecialchars($data['no_induk']) ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Kelas</label>
                                        <input type="text" name="kelas" class="form-control" required value="<?= htmlspecialchars($data['kelas']) ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="jurusan_id" class="form-label fw-bold">Jurusan</label>
                                        <select name="jurusan_id" id="jurusan_id" class="form-select" required>
                                            <option value="">Pilih Jurusan</option>
                                            <?php while($j = $jurusan_options->fetch_assoc()) : ?>
                                                <option value="<?= $j['id_jurusan'] ?>" <?= ($data['jurusan_id'] == $j['id_jurusan']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($j['nama_jurusan']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="pembimbing_id" class="form-label fw-bold">Guru Pendamping</label>
                                        <?php if ($is_admin) : ?>
                                            <select name="pembimbing_id" id="pembimbing_id" class="form-select" required>
                                                <option value="">Pilih Guru</option>
                                                <?php while($g = $guru_options->fetch_assoc()) : ?>
                                                    <option value="<?= $g['id_pembimbing'] ?>" <?= ($data['pembimbing_id'] == $g['id_pembimbing']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($g['nama_pembimbing']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        <?php elseif ($is_guru) : ?>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_pembimbing']) ?>" readonly>
                                            <input type="hidden" name="pembimbing_id" value="<?= htmlspecialchars($data['pembimbing_id']) ?>">
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label for="tempat_pkl_id" class="form-label fw-bold">Tempat PKL</label>
                                        <select name="tempat_pkl_id" id="tempat_pkl_id" class="form-select" required>
                                            <option value="">Pilih Tempat PKL</option>
                                            <?php while($t = $tempat_pkl_options->fetch_assoc()) : ?>
                                                <option value="<?= $t['id_tempat_pkl'] ?>" <?= ($data['tempat_pkl_id'] == $t['id_tempat_pkl']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($t['nama_tempat_pkl']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status</label>
                                        <select name="status" class="form-select" required>
                                            <option value="Aktif" <?= $data['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                            <option value="Tidak Aktif" <?= $data['status'] === 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                                            <option value="Selesai" <?= $data['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                        </select>
                                    </div>
                                    
                                    <hr>
                                    <h6 class="text-muted">Ubah Password</h6>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Password Baru</label>
                                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengganti password">
                                        <div class="form-text">Isi kolom ini hanya jika Anda ingin mengubah password siswa.</div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="master_data_siswa.php" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Kembali</a>
                                        <button type="submit" name="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
                <?php include './partials/footer.php'; ?>
            </div>
        </div>
    </div>
    <?php include 'partials/script.php'; ?>
</body>
</html>