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

// Ambil ID siswa dari URL
$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
if ($siswa_id === 0) {
    die("ID Siswa tidak valid.");
}

// Ambil data detail siswa
$stmt_siswa = $koneksi->prepare("SELECT nama_siswa, nisn, kelas FROM siswa WHERE id_siswa = ?");
$stmt_siswa->bind_param("i", $siswa_id);
$stmt_siswa->execute();
$siswa = $stmt_siswa->get_result()->fetch_assoc();
$stmt_siswa->close();

if (!$siswa) {
    die("Data siswa tidak ditemukan.");
}

// Ambil semua TP dan susun dalam hierarki
$tp_result = $koneksi->query("SELECT * FROM tujuan_pembelajaran ORDER BY id_induk, kode_tp");
$semua_tp = [];
$tp_anak = [];
while($row = $tp_result->fetch_assoc()){
    $semua_tp[$row['id_tp']] = $row;
    $tp_anak[$row['id_induk']][] = $row['id_tp'];
}

$cache_nilai = [];

// FUNGSI 1: Menghitung nilai secara rekursif
function hitung_nilai($id_siswa, $id_tp, $koneksi, $tp_anak, &$cache_nilai) {
    $cache_key = "$id_siswa-$id_tp";
    if (isset($cache_nilai[$cache_key])) return $cache_nilai[$cache_key];
    $punya_anak = isset($tp_anak[$id_tp]);
    if (!$punya_anak) {
        $stmt = $koneksi->prepare("SELECT nilai FROM nilai_siswa WHERE siswa_id = ? AND id_tp = ?");
        $stmt->bind_param("ii", $id_siswa, $id_tp); $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc(); $stmt->close();
        $nilai = $result['nilai'] ?? 0; $cache_nilai[$cache_key] = $nilai; return $nilai;
    } else {
        $nilai_anak_arr = [];
        foreach ($tp_anak[$id_tp] as $id_anak) { $nilai_anak_arr[] = hitung_nilai($id_siswa, $id_anak, $koneksi, $tp_anak, $cache_nilai); }
        $total = array_sum($nilai_anak_arr); $jumlah = count($nilai_anak_arr);
        $rata_rata = ($jumlah > 0) ? $total / $jumlah : 0;
        $cache_nilai[$cache_key] = $rata_rata; return $rata_rata;
    }
}

// FUNGSI 2: Membuat narasi deskriptif
function generate_deskripsi_narasi($id_siswa, $id_tp_utama, $koneksi, $semua_tp, $tp_anak) {
    $anak_utama = $tp_anak[$id_tp_utama] ?? [];
    if (empty($anak_utama)) return "-";
    $nilai_kompetensi = [];
    $cache_nilai_lokal = [];
    foreach ($anak_utama as $id_anak) {
        $nilai_kompetensi[] = ['deskripsi' => $semua_tp[$id_anak]['deskripsi_tp'], 'nilai' => hitung_nilai($id_siswa, $id_anak, $koneksi, $tp_anak, $cache_nilai_lokal)];
    }
    if (empty(array_filter($nilai_kompetensi, fn($n) => $n['nilai'] > 0))) return "Nilai belum terisi.";
    $tertinggi = ['nilai' => -1, 'deskripsi' => '']; $terendah = ['nilai' => 101, 'deskripsi' => ''];
    foreach ($nilai_kompetensi as $kompetensi) {
        if ($kompetensi['nilai'] > 0) {
            if ($kompetensi['nilai'] > $tertinggi['nilai']) { $tertinggi = $kompetensi; }
            if ($kompetensi['nilai'] < $terendah['nilai']) { $terendah = $kompetensi; }
        }
    }
    $deskripsi_utama = $semua_tp[$id_tp_utama]['deskripsi_tp'];
    $tertinggi['deskripsi'] = explode('(', $tertinggi['deskripsi'])[0]; $terendah['deskripsi'] = explode('(', $terendah['deskripsi'])[0];
    if ($tertinggi['nilai'] <= 0) return "Nilai belum lengkap.";
    if ($tertinggi['nilai'] == $terendah['nilai']) {
        return "Peserta didik sudah menunjukkan penguasaan yang baik terutama dalam hal " . lcfirst(trim($tertinggi['deskripsi'])) . ".";
    }
    return "Peserta didik menunjukkan kompetensi yang baik dalam " . lcfirst(trim($tertinggi['deskripsi'])) . ", namun masih perlu bimbingan pada " . lcfirst(trim($terendah['deskripsi'])) . ".";
}

// FUNGSI 3: Menampilkan baris tabel secara rekursif
function tampilkan_baris_tabel_nilai($id_siswa, $id_induk, $level, $koneksi, $semua_tp, $tp_anak, &$cache_nilai) {
    if (!isset($tp_anak[$id_induk])) {
        return;
    }
    foreach ($tp_anak[$id_induk] as $id_tp) {
        $item = $semua_tp[$id_tp];
        $nilai = hitung_nilai($id_siswa, $id_tp, $koneksi, $tp_anak, $cache_nilai);
        $punya_anak = isset($tp_anak[$id_tp]);
        
        $padding = $level * 25;
        $row_class = '';
        $fontWeight_class = '';

        if ($level == 0) {
            $row_class = 'table-secondary';
            $fontWeight_class = 'fw-bold';
        } elseif ($punya_anak) {
            $fontWeight_class = 'fw-bold';
        }

        if ($nilai > 0) {
            echo "<tr class='{$row_class}'>";
            // Kolom 1: Tujuan Pembelajaran/Indikator
            echo "  <td style='padding-left: " . ($padding + 15) . "px;' class='{$fontWeight_class}'>";
            echo      htmlspecialchars($item['kode_tp']) . ". " . htmlspecialchars($item['deskripsi_tp']);
            echo "  </td>";
            // Kolom 2: Nilai
            echo "  <td class='text-center {$fontWeight_class}'>";
            echo      number_format($nilai, 2);
            echo "  </td>";
            // Kolom 3: Deskripsi (hanya untuk level utama)
            echo "  <td class='text-wrap'>";
            if ($level == 0) {
                echo htmlspecialchars(generate_deskripsi_narasi($id_siswa, $id_tp, $koneksi, $semua_tp, $tp_anak));
            }
            echo "  </td>";
            echo "</tr>";
        }
        
        if ($nilai > 0) {
            tampilkan_baris_tabel_nilai($id_siswa, $id_tp, $level + 1, $koneksi, $semua_tp, $tp_anak, $cache_nilai);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/" data-template="vertical-menu-template-free">

<?php include 'partials/head.php'; ?>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <h4 class="fw-bold mb-4 text-primary">
                            <span class="text-muted fw-light">Laporan /</span> Rincian Nilai Kompetensi
                        </h4>

                        <div class="card shadow-sm mb-4">
                            <div class="card-body p-4">
                                <div class="row mb-3">
                                    <div class="col-md-6"><small class="text-muted">Nama Siswa:</small><h5 class="mb-0"><?= htmlspecialchars($siswa['nama_siswa']) ?></h5></div>
                                    <div class="col-md-3"><small class="text-muted">NISN:</small><h5 class="mb-0"><?= htmlspecialchars($siswa['nisn']) ?></h5></div>
                                    <div class="col-md-3"><small class="text-muted">Kelas:</small><h5 class="mb-0"><?= htmlspecialchars($siswa['kelas']) ?></h5></div>
                                </div>
                                <hr class="my-3"> <div class="d-flex flex-column flex-md-row justify-content-end align-items-start gap-3">
                                    <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-md-3">
                                        <a href="laporan_penilaian_siswa.php" class="btn btn-outline-secondary w-100">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali
                                        </a>
                                    </div>

                                    <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto order-md-2">
                                        <a href="generate_laporan_nilai_pdf.php?siswa_id=<?= $siswa_id ?>" class="btn btn-danger w-100" target="_blank">
                                            <i class="bx bxs-file-pdf me-1"></i> Cetak Detail
                                        </a>
                                        <a href="generate_rapor_pdf.php?siswa_id=<?= $siswa_id ?>" class="btn btn-success w-100" target="_blank">
                                            <i class="bx bxs-printer me-1"></i> Cetak Rapor
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Rincian Pencapaian Kompetensi</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 50%;">Tujuan Pembelajaran / Indikator</th>
                                                <th class="text-center" style="width: 10%;">Nilai</th>
                                                <th>Deskripsi Pencapaian</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <?php
                                            tampilkan_baris_tabel_nilai($siswa_id, NULL, 0, $koneksi, $semua_tp, $tp_anak, $cache_nilai);
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
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
    <?php include './partials/script.php'; ?>
</body>
</html>