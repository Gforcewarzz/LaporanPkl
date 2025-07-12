<?php
session_start();

// Keamanan: Hanya admin atau guru yang boleh mengakses halaman ini
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    // Redirect ke halaman login jika tidak memiliki akses
    // Asumsi login.php ada di luar folder admin, jadi perlu ../
    header('Location: ../login.php');
    exit();
}

include 'partials/db.php'; // Sertakan file koneksi database

$id_absensi_exist = $_GET['id'] ?? null;    // Parameter untuk mode EDIT (jika ada ID Absensi)
$siswa_id_new = $_GET['siswa_id'] ?? null;   // Parameter untuk mode ADD (jika ada ID Siswa)
$tanggal_new = $_GET['tanggal'] ?? null;     // Parameter untuk mode ADD (jika ada Tanggal)

$is_editing = !empty($id_absensi_exist); // Flag: TRUE jika mode EDIT, FALSE jika mode ADD

$data_absensi = []; // Variabel untuk menyimpan data absensi yang akan mengisi form
$data_siswa = [];   // Variabel untuk menyimpan data detail siswa

// --- Penentuan Mode (EDIT atau ADD) dan Pengambilan Data ---
if ($is_editing) {
    // ===============================
    // Mode EDIT: Ambil data absensi yang sudah ada dari database
    // ===============================
    $query = "SELECT
                abs.id_absensi,
                abs.tanggal_absen,
                abs.status_absen,
                abs.keterangan,
                abs.bukti_foto,
                s.id_siswa,
                s.nama_siswa,
                s.kelas,
                s.no_induk
              FROM
                absensi_siswa abs
              JOIN
                siswa s ON abs.siswa_id = s.id_siswa
              WHERE
                abs.id_absensi = ?";
    $stmt = $koneksi->prepare($query);

    if (!$stmt) {
        die("Error preparing query: " . $koneksi->error); // Hentikan script jika query gagal disiapkan
    }
    $stmt->bind_param("i", $id_absensi_exist); // 'i' untuk integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Jika ID absensi tidak ditemukan di DB
        $_SESSION['alert_message'] = 'Data absensi tidak ditemukan untuk diedit.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Error!';
        header('Location: master_data_absensi_siswa.php'); // Kembali ke halaman daftar
        exit();
    }
    $data_absensi = $result->fetch_assoc(); // Ambil data absensi

    // Isi data siswa dari hasil query absensi
    $data_siswa['id_siswa'] = $data_absensi['id_siswa'];
    $data_siswa['nama_siswa'] = $data_absensi['nama_siswa'];
    $data_siswa['kelas'] = $data_absensi['kelas'];
    $data_siswa['no_induk'] = $data_absensi['no_induk'];

    $page_title = "Edit Absensi Siswa"; // Judul halaman untuk mode edit
    $stmt->close();
} else {
    // ===============================
    // Mode ADD: Ambil data siswa untuk membuat absensi baru
    // ===============================
    if (empty($siswa_id_new) || empty($tanggal_new)) {
        // Jika parameter tidak lengkap untuk mode ADD
        $_SESSION['alert_message'] = 'Parameter siswa atau tanggal tidak lengkap untuk menambah absensi.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Error!';
        header('Location: master_data_absensi_siswa.php'); // Kembali ke halaman daftar
        exit();
    }

    $query_siswa = "SELECT id_siswa, nama_siswa, kelas, no_induk FROM siswa WHERE id_siswa = ?";
    $stmt_siswa = $koneksi->prepare($query_siswa);
    if (!$stmt_siswa) {
        die("Error preparing siswa query: " . $koneksi->error);
    }
    $stmt_siswa->bind_param("i", $siswa_id_new); // 'i' untuk integer
    $stmt_siswa->execute();
    $result_siswa = $stmt_siswa->get_result();

    if ($result_siswa->num_rows === 0) {
        // Jika ID siswa tidak ditemukan
        $_SESSION['alert_message'] = 'Data siswa tidak ditemukan.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Error!';
        header('Location: master_data_absensi_siswa.php');
        exit();
    }
    $data_siswa = $result_siswa->fetch_assoc(); // Ambil data siswa
    $stmt_siswa->close();

    // Inisialisasi data absensi untuk form baru (default values)
    $data_absensi = [
        'id_absensi' => null, // null menandakan bahwa ini adalah record baru (untuk master_data_absensi_siswa_edit_act.php)
        'tanggal_absen' => $tanggal_new,
        'status_absen' => 'Alfa', // Default status untuk input absensi baru (biasanya ketika admin/guru manual input untuk siswa yg tidak absen)
        'keterangan' => null,     // Keterangan default kosong
        'bukti_foto' => null      // Bukti foto default kosong
    ];
    $page_title = "Tambah Absensi Siswa"; // Judul halaman untuk mode tambah
}

$koneksi->close(); // Tutup koneksi setelah semua data diambil
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
                            unset($_SESSION['alert_message']);
                            unset($_SESSION['alert_type']);
                            unset($_SESSION['alert_title']);
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
                                    <?php if ($is_editing): ?>
                                    <input type="hidden" name="id_absensi"
                                        value="<?= htmlspecialchars($data_absensi['id_absensi']) ?>">
                                    <input type="hidden" name="old_bukti_foto"
                                        value="<?= htmlspecialchars($data_absensi['bukti_foto'] ?? '') ?>">
                                    <?php else: ?>
                                    <input type="hidden" name="siswa_id"
                                        value="<?= htmlspecialchars($data_siswa['id_siswa']) ?>">
                                    <input type="hidden" name="tanggal_absen"
                                        value="<?= htmlspecialchars($data_absensi['tanggal_absen']) ?>">
                                    <?php endif; ?>

                                    <div class="row mb-3">
                                        <label for="siswaInfo" class="col-sm-3 col-form-label">Siswa:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="siswaInfo"
                                                value="<?= htmlspecialchars($data_siswa['nama_siswa']) ?> (<?= htmlspecialchars($data_siswa['no_induk']) ?> - <?= htmlspecialchars($data_siswa['kelas']) ?>)"
                                                disabled readonly>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="tanggalAbsenInfo" class="col-sm-3 col-form-label">Tanggal
                                            Absen:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="tanggalAbsenInfo"
                                                value="<?= date('d F Y', strtotime(htmlspecialchars($data_absensi['tanggal_absen']))) ?>"
                                                disabled readonly>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-sm-3 col-form-label fw-bold">Status Absensi:</label>
                                        <div class="col-sm-9 d-flex flex-wrap align-items-center pt-2">
                                            <div class="form-check me-4 mb-2">
                                                <input class="form-check-input" type="radio" name="statusAbsen"
                                                    id="editRadioHadir" value="Hadir"
                                                    <?= ($data_absensi['status_absen'] == 'Hadir') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="editRadioHadir">
                                                    <span class="badge bg-success"><i
                                                            class="bx bx-check-circle me-1"></i> Hadir</span>
                                                </label>
                                            </div>
                                            <div class="form-check me-4 mb-2">
                                                <input class="form-check-input" type="radio" name="statusAbsen"
                                                    id="editRadioSakit" value="Sakit"
                                                    <?= ($data_absensi['status_absen'] == 'Sakit') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="editRadioSakit">
                                                    <span class="badge bg-warning"><i
                                                            class="bx bx-plus-medical me-1"></i> Sakit</span>
                                                </label>
                                            </div>
                                            <div class="form-check me-4 mb-2">
                                                <input class="form-check-input" type="radio" name="statusAbsen"
                                                    id="editRadioIzin" value="Izin"
                                                    <?= ($data_absensi['status_absen'] == 'Izin') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="editRadioIzin">
                                                    <span class="badge bg-info"><i class="bx bx-receipt me-1"></i>
                                                        Izin</span>
                                                </label>
                                            </div>
                                            <div class="form-check me-4 mb-2">
                                                <input class="form-check-input" type="radio" name="statusAbsen"
                                                    id="editRadioLibur" value="Libur"
                                                    <?= ($data_absensi['status_absen'] == 'Libur') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="editRadioLibur">
                                                    <span class="badge bg-secondary"><i
                                                            class="bx bx-calendar-alt me-1"></i> Libur</span> </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="statusAbsen"
                                                    id="editRadioAlfa" value="Alfa"
                                                    <?= ($data_absensi['status_absen'] == 'Alfa') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="editRadioAlfa">
                                                    <span class="badge bg-danger"><i class="bx bx-x-circle me-1"></i>
                                                        Alfa</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="editAdditionalFields"
                                        class="mt-4 p-3 border rounded-3 bg-light animate__animated animate__fadeIn"
                                        style="display: none;">
                                        <p class="text-danger fw-bold mb-3"><i class="bx bx-info-circle me-1"></i> Mohon
                                            lengkapi informasi berikut untuk status Sakit / Izin:</p>

                                        <div class="row mb-3">
                                            <label for="editKeterangan" class="col-sm-3 col-form-label">Keterangan
                                                Tambahan:</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" id="editKeterangan" name="keterangan"
                                                    rows="3"
                                                    placeholder="Masukkan keterangan (misal: alasan sakit/izin)"
                                                    maxlength="255"><?= htmlspecialchars($data_absensi['keterangan'] ?? '') ?></textarea>
                                                <div class="form-text">Jelaskan alasan Anda tidak dapat hadir.</div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="editBuktiFoto" class="col-sm-3 col-form-label">Upload Bukti
                                                Foto:</label>
                                            <div class="col-sm-9">
                                                <?php if (!empty($data_absensi['bukti_foto'])): ?>
                                                <div class="mb-2">
                                                    <img src="image_absensi/<?= htmlspecialchars($data_absensi['bukti_foto']) ?>"
                                                        alt="Bukti Lama" class="img-thumbnail"
                                                        style="max-width: 150px;">
                                                    <small class="text-muted d-block">File saat ini:
                                                        <?= htmlspecialchars($data_absensi['bukti_foto']) ?></small>
                                                </div>
                                                <?php endif; ?>
                                                <input class="form-control" type="file" id="editBuktiFoto"
                                                    name="buktiFoto" accept="image/jpeg,image/png">
                                                <div class="form-text">Unggah foto baru jika ingin mengganti bukti
                                                    (Maks. 2MB, format JPG/PNG).</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-end">
                                        <a href="master_data_absensi_siswa.php"
                                            class="btn btn-outline-secondary me-2">Batal</a>
                                        <button type="submit"
                                            class="btn btn-primary"><?= $is_editing ? 'Update Absensi' : 'Simpan Absensi Baru' ?></button>
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
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include './partials/script.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editRadioHadir = document.getElementById('editRadioHadir');
        const editRadioSakit = document.getElementById('editRadioSakit');
        const editRadioIzin = document.getElementById('editRadioIzin');
        const editRadioAlfa = document.getElementById('editRadioAlfa');
        const editRadioLibur = document.getElementById('editRadioLibur'); // PERUBAHAN DI SINI
        const editAdditionalFields = document.getElementById('editAdditionalFields');
        const editKeterangan = document.getElementById('editKeterangan');
        const editBuktiFoto = document.getElementById('editBuktiFoto');

        function toggleEditAdditionalFields() {
            // Tampilkan jika Sakit atau Izin. Tidak tampil untuk Hadir, Alfa, ATAU LIBUR
            if (editRadioSakit.checked || editRadioIzin.checked) {
                editAdditionalFields.style.display = 'block';
                editKeterangan.setAttribute('required', 'required');

                const currentBuktiFoto = "<?= htmlspecialchars($data_absensi['bukti_foto'] ?? '') ?>";
                // Jika tidak ada bukti foto lama DAN input file bukti foto kosong, maka wajib
                // Ini untuk kasus ADD absensi sakit/izin atau EDIT absensi sakit/izin tanpa bukti sebelumnya
                if (currentBuktiFoto === "" && editBuktiFoto.files.length === 0) {
                    editBuktiFoto.setAttribute('required', 'required');
                } else {
                    editBuktiFoto.removeAttribute('required'); // Tidak wajib jika sudah ada
                }

            } else {
                // Sembunyikan jika Hadir, Alfa, ATAU LIBUR
                editAdditionalFields.style.display = 'none';
                editKeterangan.removeAttribute('required');
                editBuktiFoto.removeAttribute('required');
                // Penting: Kosongkan nilai field saat disembunyikan agar tidak terkirim data yang tidak relevan
                editKeterangan.value = '';
                editBuktiFoto.value = ''; // Mengatur ulang input file
            }
        }

        // Panggil saat halaman dimuat untuk inisialisasi
        toggleEditAdditionalFields();

        // Panggil setiap kali pilihan radio berubah
        editRadioHadir.addEventListener('change', toggleEditAdditionalFields);
        editRadioSakit.addEventListener('change', toggleEditAdditionalFields);
        editRadioIzin.addEventListener('change', toggleEditAdditionalFields);
        editRadioAlfa.addEventListener('change', toggleEditAdditionalFields);
        editRadioLibur.addEventListener('change', toggleEditAdditionalFields); // PERUBAHAN DI SINI
    });
    </script>
</body>

</html>