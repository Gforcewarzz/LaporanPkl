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

// Ambil semua nilai siswa (TP & Jurnal) dalam SATU KALI QUERY
$semua_nilai_siswa = [];
$graded_jurnal_ids = [];
$stmt_nilai = $koneksi->prepare("SELECT id_tp, jurnal_kegiatan_id, nilai FROM nilai_siswa WHERE siswa_id = ?");
$stmt_nilai->bind_param("i", $siswa_id);
$stmt_nilai->execute();
$result_nilai = $stmt_nilai->get_result();
while ($row = $result_nilai->fetch_assoc()) {
    if (!empty($row['jurnal_kegiatan_id'])) {
        $jurnal_id = $row['jurnal_kegiatan_id'];
        $key = 'jurnal_' . $jurnal_id;
        $semua_nilai_siswa[$key] = $row['nilai'];
        $graded_jurnal_ids[] = $jurnal_id;
    } else if (!empty($row['id_tp'])) {
        $semua_nilai_siswa[$row['id_tp']] = $row['nilai'];
    }
}
$stmt_nilai->close();

// Ambil semua TP statis
$tp_result = $koneksi->query("SELECT * FROM tujuan_pembelajaran ORDER BY id_induk, kode_tp");
$semua_tp = [];
while ($row = $tp_result->fetch_assoc()) {
    $semua_tp[$row['id_tp']] = $row;
}

// Gabungkan data jurnal yang sudah dinilai ke dalam struktur TP
if (!empty($graded_jurnal_ids)) {
    $induk_jurnal_id = null;
    foreach ($semua_tp as $tp) {
        if ($tp['kode_tp'] === '3') {
            $induk_jurnal_id = $tp['id_tp'];
            break;
        }
    }

    if ($induk_jurnal_id) {
        $in_placeholders = implode(',', array_fill(0, count($graded_jurnal_ids), '?'));
        $stmt_jurnal = $koneksi->prepare("SELECT id_jurnal_kegiatan, nama_pekerjaan FROM jurnal_kegiatan WHERE id_jurnal_kegiatan IN ($in_placeholders)");
        $stmt_jurnal->bind_param(str_repeat('i', count($graded_jurnal_ids)), ...$graded_jurnal_ids);
        $stmt_jurnal->execute();
        $jurnal_list = $stmt_jurnal->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_jurnal->close();

        $sub_kode = 1;
        foreach ($jurnal_list as $jurnal) {
            $jurnal_tp_id = 'jurnal_' . $jurnal['id_jurnal_kegiatan'];
            $semua_tp[$jurnal_tp_id] = [
                'id_tp' => $jurnal_tp_id,
                'id_induk' => $induk_jurnal_id,
                'kode_tp' => '3.' . $sub_kode++,
                'deskripsi_tp' => $jurnal['nama_pekerjaan']
            ];
        }
    }
}

// Buat struktur pohon (tree) dari data yang sudah digabung
$tp_anak = [];
foreach ($semua_tp as $tp) {
    $id_induk = $tp['id_induk'] ?? 0;
    $tp_anak[$id_induk][] = $tp['id_tp'];
}

$cache_nilai_terhitung = [];

function hitung_nilai($id_tp, $semua_nilai_siswa, $tp_anak, &$cache_nilai_terhitung)
{
    if (isset($cache_nilai_terhitung[$id_tp])) return $cache_nilai_terhitung[$id_tp];

    $punya_anak = isset($tp_anak[$id_tp]);
    if (!$punya_anak) {
        $nilai = $semua_nilai_siswa[$id_tp] ?? 0;
        $cache_nilai_terhitung[$id_tp] = $nilai;
        return $nilai;
    } else {
        $nilai_anak_arr = [];
        foreach ($tp_anak[$id_tp] as $id_anak) {
            $nilai_anak_arr[] = hitung_nilai($id_anak, $semua_nilai_siswa, $tp_anak, $cache_nilai_terhitung);
        }
        $filtered_nilai = array_filter($nilai_anak_arr, fn($n) => $n > 0);
        if (empty($filtered_nilai)) {
            $rata_rata = 0;
        } else {
            $rata_rata = array_sum($filtered_nilai) / count($filtered_nilai);
        }
        $cache_nilai_terhitung[$id_tp] = $rata_rata;
        return $rata_rata;
    }
}

function generate_deskripsi_narasi($id_tp_utama, $semua_nilai_siswa, $semua_tp, $tp_anak)
{
    $anak_utama = $tp_anak[$id_tp_utama] ?? [];
    if (empty($anak_utama)) {
        return "-";
    }

    $cache_nilai_lokal = [];
    $nilai_kompetensi = [];
    foreach ($anak_utama as $id_anak) {
        $nilai = hitung_nilai($id_anak, $semua_nilai_siswa, $tp_anak, $cache_nilai_lokal);
        if ($nilai > 0) {
            $nilai_kompetensi[] = [
                'deskripsi' => $semua_tp[$id_anak]['deskripsi_tp'],
                'nilai' => $nilai
            ];
        }
    }

    if (empty($nilai_kompetensi)) {
        return "Nilai untuk kompetensi ini belum terisi.";
    }

    $tertinggi = null;
    $terendah = null;

    foreach ($nilai_kompetensi as $kompetensi) {
        if ($tertinggi === null || $kompetensi['nilai'] > $tertinggi['nilai']) {
            $tertinggi = $kompetensi;
        }
        if ($terendah === null || $kompetensi['nilai'] < $terendah['nilai']) {
            $terendah = $kompetensi;
        }
    }

    $desc_tertinggi = lcfirst(trim(explode('(', $tertinggi['deskripsi'])[0]));
    $desc_terendah = lcfirst(trim(explode('(', $terendah['deskripsi'])[0]));

    if ($terendah['nilai'] < 80) {
        if ($tertinggi['nilai'] == $terendah['nilai']) {
            return "Peserta didik masih perlu meningkatkan kompetensi dalam hal {$desc_terendah}.";
        }
        return "Peserta didik menunjukkan kompetensi yang baik dalam {$desc_tertinggi}, namun masih perlu bimbingan pada {$desc_terendah}.";
    } else {
        if ($tertinggi['nilai'] == $terendah['nilai']) {
            return "Peserta didik secara konsisten menunjukkan penguasaan yang sangat baik pada semua kompetensi.";
        }
        return "Peserta didik menunjukkan penguasaan yang sangat baik pada seluruh kompetensi, terutama menonjol dalam hal {$desc_tertinggi}.";
    }
}

function tampilkan_baris_tabel_nilai($id_induk, $level, $semua_nilai_siswa, $semua_tp, $tp_anak, &$cache_nilai_terhitung)
{
    if (!isset($tp_anak[$id_induk])) {
        return;
    }

    foreach ($tp_anak[$id_induk] as $id_tp) {
        $item = $semua_tp[$id_tp];
        $nilai = hitung_nilai($id_tp, $semua_nilai_siswa, $tp_anak, $cache_nilai_terhitung);
        $punya_anak = isset($tp_anak[$id_tp]);

        if (!$punya_anak && $nilai <= 0) {
            continue;
        }

        $padding = $level * 25;
        $row_class = $level == 0 ? 'table-secondary' : '';
        $fontWeight_class = ($level == 0 || $punya_anak) ? 'fw-bold' : '';

        echo "<tr class='{$row_class}'>";
        echo "<td style='padding-left: " . ($padding + 15) . "px;' class='{$fontWeight_class}'>" . htmlspecialchars($item['kode_tp']) . ". " . htmlspecialchars($item['deskripsi_tp']) . "</td>";
        echo "<td class='text-center {$fontWeight_class}'>" . (($nilai > 0) ? number_format($nilai, 1) : '-') . "</td>";
        echo "<td class='text-wrap'>";
        if ($level == 0) {
            echo htmlspecialchars(generate_deskripsi_narasi($id_tp, $semua_nilai_siswa, $semua_tp, $tp_anak));
        }
        echo "</td>";
        echo "</tr>";

        if ($punya_anak) {
            tampilkan_baris_tabel_nilai($id_tp, $level + 1, $semua_nilai_siswa, $semua_tp, $tp_anak, $cache_nilai_terhitung);
        }
    }
}
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
                        <h4 class="fw-bold mb-4 text-primary"><span class="text-muted fw-light">Laporan /</span> Rincian
                            Nilai Kompetensi</h4>
                        <div class="card shadow-sm mb-4">
                            <div class="card-body p-4">
                                <div class="row mb-3">
                                    <div class="col-md-6"><small class="text-muted">Nama Siswa:</small>
                                        <h5 class="mb-0"><?= htmlspecialchars($siswa['nama_siswa']) ?></h5>
                                    </div>
                                    <div class="col-md-3"><small class="text-muted">NISN:</small>
                                        <h5 class="mb-0"><?= htmlspecialchars($siswa['nisn']) ?></h5>
                                    </div>
                                    <div class="col-md-3"><small class="text-muted">Kelas:</small>
                                        <h5 class="mb-0"><?= htmlspecialchars($siswa['kelas']) ?></h5>
                                    </div>
                                </div>
                                <hr class="my-3">

                                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
                                    <a href="generate_laporan_nilai_pdf.php?siswa_id=<?= $siswa_id ?>"
                                        class="btn btn-danger" target="_blank"><i class="bx bxs-file-pdf me-1"></i>
                                        Cetak Rincian (Terisi)</a>

                                    <a href="generate_penilaian_dudi_pdf.php?siswa_id=<?= $siswa_id ?>"
                                        class="btn btn-info" target="_blank"><i class="bx bxs-file-blank me-1"></i>
                                        Cetak Form DUDI</a>

                                    <a href="generate_rapor_pdf.php?siswa_id=<?= $siswa_id ?>" class="btn btn-success"
                                        target="_blank"><i class="bx bxs-printer me-1"></i> Cetak Rapor</a>

                                    <a href="laporan_penilaian_siswa.php" class="btn btn-outline-secondary"><i
                                            class="bx bx-arrow-back me-1"></i> Kembali</a>
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
                                            tampilkan_baris_tabel_nilai(0, 0, $semua_nilai_siswa, $semua_tp, $tp_anak, $cache_nilai_terhitung);
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php include './partials/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include './partials/script.php'; ?>
</body>

</html>