<?php
// master_observasi_form_penilaian.php

// === Aktifkan error reporting penuh untuk debugging di development (NONAKTIFKAN DI PRODUKSI) ===
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// ==============================================================================================

// --- SERTAKAN FILE PARTIALS AWAL ---
// NOTE PENTING: Pastikan partials/head.php hanya berisi <head> dan isinya, tanpa tag <body> atau </html>
include 'partials/head.php';
include 'partials/db.php'; // Asumsi file ini berisi koneksi ke database ($koneksi)

// --- AWAL DARI SEMUA KODE PHP UNTUK MENGAMBIL DATA DAN LOGIKA ---

// Pastikan id_siswa diterima dari URL
if (!isset($_GET['id_siswa']) || empty($_GET['id_siswa'])) {
    header("Location: master_observasi.php"); // Kembali ke daftar siswa
    exit("ID Siswa tidak ditemukan.");
}

$id_siswa = mysqli_real_escape_string($koneksi, $_GET['id_siswa']);

// --- 1. Ambil data siswa yang akan dinilai ---
// Pastikan $koneksi sudah valid di sini
if (!$koneksi) {
    die("Koneksi database GAGAL di awal master_observasi_form_penilaian.php: " . mysqli_connect_error());
}

$query_siswa = "SELECT
                    s.id_siswa,
                    s.nama_siswa,
                    s.nisn, -- Pastikan ini NISN di DB
                    s.kelas,
                    j.nama_jurusan,
                    tp.nama_tempat_pkl
                FROM
                    siswa s
                LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
                LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
                WHERE
                    s.id_siswa = '$id_siswa'"; // Gunakan quote untuk string
$result_siswa = mysqli_query($koneksi, $query_siswa);

if (!$result_siswa) {
    error_log("Query data siswa gagal di master_observasi_form_penilaian.php: " . mysqli_error($koneksi));
    die("Gagal memuat data siswa. Silakan coba lagi.");
}

if (mysqli_num_rows($result_siswa) == 0) {
    header("Location: master_observasi.php");
    exit("Data Siswa tidak ditemukan.");
}
$siswa_data = mysqli_fetch_assoc($result_siswa);

// --- 2. Ambil ID Indikator dari database untuk setiap item ---
// Ini penting agar input form bisa mapping ke ID indikator di database
$indikator_ids = [];
$query_get_indikator_ids = "SELECT id_indikator, no_urut, is_nilai_langsung FROM indikator_pembelajaran";
$result_get_indikator_ids = mysqli_query($koneksi, $query_get_indikator_ids);

if (!$result_get_indikator_ids) {
    error_log("Query indikator gagal di master_observasi_form_penilaian.php: " . mysqli_error($koneksi));
    die("Gagal memuat data indikator. Silakan coba lagi.");
}

// Memastikan $indikator_ids terisi, dan trim spasi dari no_urut
while ($row = mysqli_fetch_assoc($result_get_indikator_ids)) {
    $trimmed_no_urut = trim($row['no_urut']); // Penting: Hapus spasi di awal/akhir jika ada dari DB
    $indikator_ids[$trimmed_no_urut] = [
        'id_indikator' => $row['id_indikator'],
        'is_nilai_langsung' => $row['is_nilai_langsung']
    ];
}

// Fungsi helper untuk mendapatkan id_indikator
function getIndikatorId($no_urut, $indikator_ids_array)
{
    $trimmed_no_urut_search = trim($no_urut); // Trim juga no_urut yang dicari
    return $indikator_ids_array[$trimmed_no_urut_search]['id_indikator'] ?? null;
}

// --- AKHIR DARI SEMUA KODE PHP UNTUK MENGAMBIL DATA DAN LOGIKA ---

// === DEBUGGING UNTUK PENGEMBANGAN ===
// Uncomment blok ini untuk melihat isi $indikator_ids dan hasil getIndikatorId
/*
echo '<div style="background: #e6f7ff; color: #004085; border: 1px solid #b3e0ff; padding: 10px; margin-bottom: 20px;">';
echo '<strong>DEBUG: Isi array $indikator_ids (ini harusnya berisi mapping no_urut ke id_indikator):</strong><br>';
echo '<pre>';
print_r($indikator_ids);
echo '</pre>';
echo '</div>';

echo '<div style="background: #fff0b3; color: #664d03; border: 1px solid #ffd766; padding: 10px; margin-bottom: 20px;">';
echo '<strong>DEBUG: Cek fungsi getIndikatorId():</strong><br>';
echo 'ID untuk 1.1: ' . (getIndikatorId('1.1', $indikator_ids) ?? 'NULL / Tidak Ditemukan') . '<br>';
echo 'ID untuk 1.6: ' . (getIndikatorId('1.6', $indikator_ids) ?? 'NULL / Tidak Ditemukan') . '<br>';
echo 'ID untuk 3.1.1: ' . (getIndikatorId('3.1.1', $indikator_ids) ?? 'NULL / Tidak Ditemukan') . '<br>';
echo 'ID untuk 3.4.2: ' . (getIndikatorId('3.4.2', $indikator_ids) ?? 'NULL / Tidak Ditemukan') . '<br>';
echo '</div>';
// die("Menghentikan eksekusi untuk debugging master_observasi_form_penilaian.php. Silakan lihat output debug di atas.");
*/
// === AKHIR DEBUGGING ===
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<head>
</head>

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
                                <span class="text-muted fw-light">Penilaian /</span> Form Penilaian Siswa
                            </h4>
                            <i class="bx bx-file-text fa-2x text-info animate__animated animate__fadeInRight"
                                style="opacity: 0.6;"></i>
                        </div>

                        <div class="card mb-4 shadow-lg animate__animated animate__fadeInDown"
                            style="border-radius: 12px;">
                            <div class="card-header bg-gradient-primary-to-secondary text-white p-4"
                                style="border-top-left-radius: 12px; border-top-right-radius: 12px; background: linear-gradient(135deg, #696cff 0%, #a4bdfa 100%);">
                                <h5 class="card-title text-white mb-0">Penilaian Siswa:
                                    <?= htmlspecialchars($siswa_data['nama_siswa'] ?? '') ?></h5>
                                <p class="card-text text-white-75 small mb-0">NISN:
                                    <?= htmlspecialchars($siswa_data['nisn'] ?? '') ?> | Kelas:
                                    <?= htmlspecialchars($siswa_data['kelas'] ?? '') ?> | Jurusan:
                                    <?= htmlspecialchars($siswa_data['nama_jurusan'] ?? '-') ?> | Tempat PKL:
                                    <?= htmlspecialchars($siswa_data['nama_tempat_pkl'] ?? '-') ?></p>
                            </div>
                            <div class="card-body p-4">
                                <form action="penilaian_proses.php" method="POST">
                                    <input type="hidden" name="id_siswa"
                                        value="<?= htmlspecialchars($siswa_data['id_siswa'] ?? '') ?>">

                                    <div class="mb-4">
                                        <label for="tanggal_penilaian" class="form-label">Tanggal Penilaian</label>
                                        <input type="date" class="form-control" id="tanggal_penilaian"
                                            name="tanggal_penilaian" value="<?= date('Y-m-d') ?>" required>
                                    </div>

                                    <h6 class="mb-3 fw-bold text-primary">Detail Penilaian Indikator:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width: 10%;">No.</th>
                                                    <th style="width: 50%;">Tujuan Pembelajaran / Indikator</th>
                                                    <th style="width: 15%;">Nilai</th>
                                                    <th style="width: 25%;">Deskripsi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>1</strong></td>
                                                    <td><strong>Menerapkan soft skills yang dibutuhkan dalam dunia kerja
                                                            (tempat PKL)</strong></td>
                                                    <td></td>
                                                    <td>
                                                        <textarea name="deskripsi_umum_soft_skills" class="form-control"
                                                            rows="2"
                                                            placeholder="Peserta didik menunjukkan peningkatan signifikan dalam komunikasi dan integritas."></textarea>
                                                    </td>
                                                </tr>
                                                <?php $indikator_id_1_1 = getIndikatorId('1.1', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;1.1</td>
                                                    <td>Melaksanakan komunikasi telepon sesuai kaidah</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_1_1; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_1_2 = getIndikatorId('1.2', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;1.2</td>
                                                    <td>Menunjukkan integritas (antara lain jujur, disiplin, komitmen,
                                                        dan tanggung jawab)</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_1_2; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_1_3 = getIndikatorId('1.3', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;1.3</td>
                                                    <td>Memiliki etos kerja</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_1_3; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_1_4 = getIndikatorId('1.4', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;1.4</td>
                                                    <td>Menunjukkan kemandirian</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_1_4; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_1_5 = getIndikatorId('1.5', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;1.5</td>
                                                    <td>Menunjukkan Kerjasama</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_1_5; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_1_6 = getIndikatorId('1.6', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;1.6</td>
                                                    <td>Menunjukkan kepedulian sosial dan lingkungan</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_1_6; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td class="text-end"><strong>Skor</strong></td>
                                                    <td><span class="text-muted">Akan dihitung otomatis</span></td>
                                                    <td></td>
                                                </tr>

                                                <tr>
                                                    <td><strong>2</strong></td>
                                                    <td><strong>Menerapkan norma, POS dan K3LH yang ada pada dunia kerja
                                                            (tempat PKL)</strong></td>
                                                    <td></td>
                                                    <td>
                                                        <textarea name="deskripsi_umum_norma_pos_k3lh"
                                                            class="form-control" rows="2"
                                                            placeholder="Pemahaman dan penerapan norma, POS, serta K3LH sudah baik..."></textarea>
                                                    </td>
                                                </tr>
                                                <?php $indikator_id_2_1 = getIndikatorId('2.1', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;2.1</td>
                                                    <td>Menggunakan APD dengan tertib dan benar</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_2_1; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_2_2 = getIndikatorId('2.2', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;2.2</td>
                                                    <td>Melaksanakan pekerjaan sesuai POS</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_2_2; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td class="text-end"><strong>Skor</strong></td>
                                                    <td><span class="text-muted">Akan dihitung otomatis</span></td>
                                                    <td></td>
                                                </tr>

                                                <tr>
                                                    <td><strong>3</strong></td>
                                                    <td><strong>Menerapkan kompetensi teknis yang sudah dipelajari di
                                                            sekolah dan/atau baru dipelajari pada dunia kerja (tempat
                                                            PKL)</strong></td>
                                                    <td></td>
                                                    <td>
                                                        <textarea name="deskripsi_umum_kompetensi_teknis"
                                                            class="form-control" rows="2"
                                                            placeholder="Mampu menerapkan kompetensi teknis pada pembuatan box power supply..."></textarea>
                                                    </td>
                                                </tr>
                                                <?php $indikator_id_3_1 = getIndikatorId('3.1', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;3.1</td>
                                                    <td>Membuat box power supply</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_1_1 = getIndikatorId('3.1.1', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.1.1</td>
                                                    <td>Memotong plat</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_1_1; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_1_2 = getIndikatorId('3.1.2', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.1.2</td>
                                                    <td>Mengebor plat</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_1_2; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_1_3 = getIndikatorId('3.1.3', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.1.3</td>
                                                    <td>Menekuk plat</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_1_3; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_1_4 = getIndikatorId('3.1.4', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.1.4</td>
                                                    <td>Mengecat plat</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_1_4; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_1_5 = getIndikatorId('3.1.5', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.1.5</td>
                                                    <td>Memeriksa produk</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_1_5; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>

                                                <?php $indikator_id_3_2 = getIndikatorId('3.2', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;3.2</td>
                                                    <td>Merakit komponen power supply</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_2_1 = getIndikatorId('3.2.1', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.2.1</td>
                                                    <td>Membuat PCB</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_2_1; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_2_2 = getIndikatorId('3.2.2', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.2.2</td>
                                                    <td>Menyolder komponen pada PCB</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_2_2; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_2_3 = getIndikatorId('3.2.3', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.2.3</td>
                                                    <td>Merakit Power Supply</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_2_3; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_2_4 = getIndikatorId('3.2.4', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.2.4</td>
                                                    <td>Memeriksa Produk</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_2_4; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_3 = getIndikatorId('3.3', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;3.3</td>
                                                    <td>Membuat rancangan box power supply.</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_3_1 = getIndikatorId('3.3.1', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.3.1</td>
                                                    <td>Membuat rancangan produk dengan software</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_3_1; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_3_2 = getIndikatorId('3.3.2', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.3.2</td>
                                                    <td>Menghitung kebutuhan bahan</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_3_2; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_3_3 = getIndikatorId('3.3.3', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.3.3</td>
                                                    <td>Membuat rancangan pekerjaan dengan software.</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_3_3; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_3_4 = getIndikatorId('3.3.4', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.3.4</td>
                                                    <td>Menghitung kebutuhan bahan rangkaian power supply</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_3_4; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td class="text-end"><strong>Skor</strong></td>
                                                    <td><span class="text-muted">Akan dihitung otomatis</span></td>
                                                    <td></td>
                                                </tr>

                                                <?php $indikator_id_3_4 = getIndikatorId('3.4', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;3.4</td>
                                                    <td>Menerapkan kompetensi teknis pengiriman produk</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_4_1 = getIndikatorId('3.4.1', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.4.1</td>
                                                    <td>Pembuatan faktur</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_4_1; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_3_4_2 = getIndikatorId('3.4.2', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3.4.2</td>
                                                    <td>Pengecekan pengiriman barang</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_3_4_2; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td class="text-end"><strong>Skor</strong></td>
                                                    <td><span class="text-muted">Akan dihitung otomatis</span></td>
                                                    <td></td>
                                                </tr>

                                                <tr>
                                                    <td><strong>4</strong></td>
                                                    <td><strong>Memahami alur bisnis dunia kerja tempat PKL dan wawasan
                                                            wirausaha</strong></td>
                                                    <td></td>
                                                    <td>
                                                        <textarea name="deskripsi_umum_bisnis_wirausaha"
                                                            class="form-control" rows="2"
                                                            placeholder="Memahami alur bisnis, namun perlu pendalaman lebih lanjut pada rencana usaha..."></textarea>
                                                    </td>
                                                </tr>
                                                <?php $indikator_id_4_1 = getIndikatorId('4.1', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;4.1</td>
                                                    <td>Mengidentifikasi kegiatan usaha di tempat kerja</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_4_1; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <?php $indikator_id_4_2 = getIndikatorId('4.2', $indikator_ids); ?>
                                                <tr>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;4.2</td>
                                                    <td>Menjelaskan rencana usaha yang akan dilaksanakan</td>
                                                    <td>
                                                        <input type="number"
                                                            name="nilai[<?php echo $indikator_id_4_2; ?>]"
                                                            class="form-control form-control-sm" min="0" max="100"
                                                            placeholder="0-100" required>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td class="text-end"><strong>Skor</strong></td>
                                                    <td><span class="text-muted">Akan dihitung otomatis</span></td>
                                                    <td></td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>

                                    <hr class="my-4">

                                    <h6 class="mb-3 fw-bold text-primary">Catatan Pembimbing:</h6>
                                    <div class="mb-3">
                                        <label for="catatan_guru_pembimbing" class="form-label">Catatan Guru
                                            Pembimbing</label>
                                        <textarea class="form-control" id="catatan_guru_pembimbing"
                                            name="catatan_guru_pembimbing" rows="4"
                                            placeholder="Masukan catatan umum dari Guru Pembimbing..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="catatan_instruktur_industri" class="form-label">Catatan Instruktur
                                            Industri</label>
                                        <textarea class="form-control" id="catatan_instruktur_industri"
                                            name="catatan_instruktur_industri" rows="4"
                                            placeholder="Masukan catatan umum dari Instruktur Industri..."></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="master_observasi.php" class="btn btn-outline-secondary">Batal</a>
                                        <button type="submit" class="btn btn-primary">Simpan Penilaian</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="layout-overlay layout-menu-toggle"></div>
            </div>
        </div>
    </div>

    <?php include './partials/script.php'; ?>
</body>

</html>