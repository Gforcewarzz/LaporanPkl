<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // <-- Tambahkan baris ini di sini

// Variabel status peran agar konsisten dan selalu terdefinisi
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

// Keamanan: Hanya admin atau guru yang boleh mengakses halaman ini
if (!$is_admin && !$is_guru) {
    if ($is_siswa) {
        header('Location: dashboard_siswa.php');
    } else {
        header('Location: ../login.php');
    }
    exit();
}

include 'partials/db.php';

$id_absensi_exist = $_GET['id'] ?? null;
$siswa_id_new = $_GET['siswa_id'] ?? null;
$tanggal_new = $_GET['tanggal'] ?? null;

$is_editing = !empty($id_absensi_exist);

$data_absensi = [];
$data_siswa = [];
$page_title = "Error"; // Default title

// --- Penentuan Mode (EDIT atau ADD) dan Pengambilan Data ---
if ($is_editing) {
    // ===============================
    // Mode EDIT
    // ===============================
    // [DIUBAH] Tambahkan s.pembimbing_id untuk pengecekan otorisasi
    $query = "SELECT
                abs.id_absensi, abs.tanggal_absen, abs.status_absen, abs.keterangan, abs.bukti_foto,
                s.id_siswa, s.nama_siswa, s.kelas, s.no_induk, s.pembimbing_id
              FROM absensi_siswa abs
              JOIN siswa s ON abs.siswa_id = s.id_siswa
              WHERE abs.id_absensi = ?";
    
    $params = [$id_absensi_exist];
    $types = "i";
    
    // [PENTING] OTORISASI GURU
    if ($is_guru) {
        $query .= " AND s.pembimbing_id = ?";
        $params[] = $_SESSION['id_guru_pendamping'];
        $types .= "i";
    }

    $stmt = $koneksi->prepare($query);
    if (!$stmt) die("Error preparing query: " . $koneksi->error);
    
    // Dynamic binding for multiple parameters
    $bind_names = [$types];
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'param_edit_' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['alert_message'] = 'Data absensi tidak ditemukan atau Anda tidak memiliki izin akses.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Akses Ditolak!';
        header('Location: master_data_absensi_siswa.php');
        exit();
    }
    $data_absensi = $result->fetch_assoc();
    $data_siswa = $data_absensi;
    $page_title = "Edit Absensi Siswa";
    $stmt->close();

} else {
    // ===============================
    // Mode ADD
    // ===============================
    if (empty($siswa_id_new) || empty($tanggal_new)) {
        $_SESSION['alert_message'] = 'Parameter tidak lengkap untuk menambah absensi.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Error!';
        header('Location: master_data_absensi_siswa.php');
        exit();
    }

    // [DIUBAH] Tambahkan pembimbing_id untuk pengecekan otorisasi
    $query_siswa = "SELECT id_siswa, nama_siswa, kelas, no_induk, pembimbing_id FROM siswa WHERE id_siswa = ?";
    $params = [$siswa_id_new];
    $types = "i";
    
    // [PENTING] OTORISASI GURU
    if ($is_guru) {
        $query_siswa .= " AND pembimbing_id = ?";
        $params[] = $_SESSION['id_guru_pendamping'];
        $types .= "i";
    }
    
    $stmt_siswa = $koneksi->prepare($query_siswa);
    if (!$stmt_siswa) die("Error preparing siswa query: " . $koneksi->error);

    // Dynamic binding for multiple parameters
    $bind_names_siswa = [$types];
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'param_add_siswa_' . $i;
        $$bind_name = $params[$i];
        $bind_names_siswa[] = &$$bind_name;
    }
    call_user_func_array([$stmt_siswa, 'bind_param'], $bind_names_siswa);

    $stmt_siswa->execute();
    $result_siswa = $stmt_siswa->get_result();

    if ($result_siswa->num_rows === 0) {
        $_SESSION['alert_message'] = 'Data siswa tidak ditemukan atau Anda tidak memiliki izin akses.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Akses Ditolak!';
        header('Location: master_data_absensi_siswa.php');
        exit();
    }
    $data_siswa = $result_siswa->fetch_assoc();
    $stmt_siswa->close();

    $data_absensi = [
        'id_absensi' => null,
        'tanggal_absen' => $tanggal_new,
        'status_absen' => 'Hadir', 
        'keterangan' => null,
        'bukti_foto' => null
    ];
    $page_title = "Tambah Absensi Siswa";
}

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <?php
                        // Menampilkan SweetAlert jika ada pesan dari sesi
                        if (isset($_SESSION['alert_message'])) {
                            $alert_icon = $_SESSION['alert_type'];
                            $alert_title = $_SESSION['alert_title'];
                            $alert_text = $_SESSION['alert_message'];
                            echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: '{$alert_icon}',
                                        title: '{$alert_title}',
                                        text: '{$alert_text}',
                                        confirmButtonColor: '#696cff'
                                    });
                                });
                            </script>";
                            unset($_SESSION['alert_message'], $_SESSION['alert_type'], $_SESSION['alert_title']);
                        }
                        ?>

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">Absensi /</span> <?= $page_title ?>
                            </h4>
                            <i class="fas fa-edit fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card mb-4 shadow-lg">
                            <div class="card-body">
                                <form action="master_data_absensi_siswa_edit_act.php" method="POST"
                                    enctype="multipart/form-data">
                                    
                                    <input type="hidden" name="id_absensi" value="<?= htmlspecialchars($data_absensi['id_absensi'] ?? '') ?>">
                                    <input type="hidden" name="siswa_id" value="<?= htmlspecialchars($data_siswa['id_siswa']) ?>">
                                    <input type="hidden" name="tanggal_absen" value="<?= htmlspecialchars($data_absensi['tanggal_absen']) ?>">
                                    <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($data_absensi['bukti_foto'] ?? '') ?>">

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Siswa:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control"
                                                value="<?= htmlspecialchars($data_siswa['nama_siswa']) ?> (<?= htmlspecialchars($data_siswa['no_induk']) ?> - <?= htmlspecialchars($data_siswa['kelas']) ?>)"
                                                disabled readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label">Tanggal Absen:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control"
                                                value="<?= date('d F Y', strtotime(htmlspecialchars($data_absensi['tanggal_absen']))) ?>"
                                                disabled readonly>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label fw-bold">Status Absensi:</label>
                                        <div class="col-sm-9 d-flex flex-wrap align-items-center pt-2">
                                            <div class="form-check me-4 mb-2">
                                                <input class="form-check-input" type="radio" name="statusAbsen" id="editRadioHadir" value="Hadir" <?= ($data_absensi['status_absen'] == 'Hadir') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="editRadioHadir"><span class="badge bg-success"><i class="bx bx-check-circle me-1"></i> Hadir</span></label>
                                            </div>
                                            <div class="form-check me-4 mb-2">
                                                <input class="form-check-input" type="radio" name="statusAbsen" id="editRadioSakit" value="Sakit" <?= ($data_absensi['status_absen'] == 'Sakit') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="editRadioSakit"><span class="badge bg-warning"><i class="bx bx-plus-medical me-1"></i> Sakit</span></label>
                                            </div>
                                            <div class="form-check me-4 mb-2">
                                                <input class="form-check-input" type="radio" name="statusAbsen" id="editRadioIzin" value="Izin" <?= ($data_absensi['status_absen'] == 'Izin') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="editRadioIzin"><span class="badge bg-info"><i class="bx bx-receipt me-1"></i> Izin</span></label>
                                            </div>
                                            <div class="form-check me-4 mb-2">
                                                <input class="form-check-input" type="radio" name="statusAbsen" id="editRadioLibur" value="Libur" <?= ($data_absensi['status_absen'] == 'Libur') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="editRadioLibur"><span class="badge bg-secondary"><i class="bx bx-calendar-alt me-1"></i> Libur</span></label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="statusAbsen" id="editRadioAlfa" value="Alfa" <?= ($data_absensi['status_absen'] == 'Alfa') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="editRadioAlfa"><span class="badge bg-danger"><i class="bx bx-x-circle me-1"></i> Alfa</span></label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="editAdditionalFields" class="mt-4 p-3 border rounded-3 bg-light" style="display: none;">
                                        <p class="text-danger fw-bold mb-3"><i class="bx bx-info-circle me-1"></i> Mohon lengkapi informasi berikut untuk status Sakit / Izin:</p>
                                        <div class="row mb-3">
                                            <label for="editKeterangan" class="col-sm-3 col-form-label">Keterangan:</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" id="editKeterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan (misal: alasan sakit/izin)" maxlength="255"><?= htmlspecialchars($data_absensi['keterangan'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="editBuktiFoto" class="col-sm-3 col-form-label">Upload Bukti Foto:</label>
                                            <div class="col-sm-9">
                                                <?php if (!empty($data_absensi['bukti_foto'])): ?>
                                                <div class="mb-2">
                                                    <img src="../image_absensi/<?= htmlspecialchars($data_absensi['bukti_foto']) ?>" alt="Bukti Lama" class="img-thumbnail" style="max-width: 150px;">
                                                    <small class="text-muted d-block">File saat ini: <?= htmlspecialchars($data_absensi['bukti_foto']) ?></small>
                                                </div>
                                                <?php endif; ?>
                                                <input class="form-control" type="file" id="editBuktiFoto" name="buktiFoto" accept="image/jpeg,image/png">
                                                <div class="form-text">Unggah foto baru jika ingin mengganti bukti (Maks. 2MB).</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-end">
                                        <a href="master_data_absensi_siswa.php" class="btn btn-outline-secondary me-2">Batal</a>
                                        <button type="submit" name="submit" class="btn btn-primary"><?= $is_editing ? 'Update Absensi' : 'Simpan Absensi' ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                    <?php include './partials/footer.php'; ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include './partials/script.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusRadios = document.querySelectorAll('input[name="statusAbsen"]');
        const additionalFields = document.getElementById('editAdditionalFields');
        const keteranganInput = document.getElementById('editKeterangan');
        const fotoInput = document.getElementById('editBuktiFoto');

        function toggleAdditionalFields() {
            const isSakitOrIzin = document.querySelector('input[name="statusAbsen"]:checked').value === 'Sakit' || document.querySelector('input[name="statusAbsen"]:checked').value === 'Izin';
            
            if (isSakitOrIzin) {
                additionalFields.style.display = 'block';
                keteranganInput.required = true;
                
                // Pastikan foto wajib HANYA jika statusnya sakit/izin dan BELUM ada foto lama ATAU foto baru diupload
                const hasExistingPhoto = "<?= !empty($data_absensi['bukti_foto']) ? 'true' : 'false' ?>"; 
                if (hasExistingPhoto === 'false' || fotoInput.files.length === 0) {
                    fotoInput.required = true;
                } else {
                    fotoInput.required = false; // Jika sudah ada foto lama, atau akan diupload foto baru, tidak perlu required
                }
            } else {
                additionalFields.style.display = 'none';
                keteranganInput.required = false;
                fotoInput.required = false;
                // Kosongkan nilai field saat disembunyikan
                keteranganInput.value = ''; 
                fotoInput.value = ''; 
            }
        }

        // Panggil saat halaman dimuat
        toggleAdditionalFields();

        // Panggil setiap kali pilihan radio berubah
        statusRadios.forEach(radio => {
            radio.addEventListener('change', toggleAdditionalFields);
        });
    });
    </script>
</body>
</html>