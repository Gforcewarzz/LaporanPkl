<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu konsisten

// Aktifkan pelaporan error untuk debugging, nonaktifkan di production
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

include 'partials/db.php'; // Pastikan file ini mengembalikan objek $koneksi yang valid dan terbuka

// Sertakan Dompdf Autoloader
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- LOGIKA KEAMANAN HALAMAN ---
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru  = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru && !$is_siswa) {
    header('Location: ../login.php');
    exit();
}

// --- INISIALISASI FILTER DARI URL ---
$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-t');
$filter_status = $_GET['status'] ?? 'Semua';
$keyword = $_GET['keyword_pdf'] ?? '';
$kelas_filter_pdf = $_GET['kelas_pdf'] ?? '';
$siswa_id_from_form = $_GET['siswa_id_pdf'] ?? null;
$pembimbing_id_from_form = $_GET['pembimbing_id_pdf'] ?? null;

// --- INISIALISASI QUERY PARAMS DAN KONDISI ---
$where_clauses = [];
$query_params = [];
$query_types = "";

// --- LOGIKA FILTER UTAMA BERDASARKAN PERAN ---
$final_filter_siswa_id = null;
$final_filter_pembimbing_id = null;

if ($is_siswa) {
    $final_filter_siswa_id = $_SESSION['id_siswa'] ?? null;
    if ($final_filter_siswa_id) {
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = &$final_filter_siswa_id;
        $query_types .= 'i';
    } else {
        die("Siswa ID tidak ditemukan dalam sesi.");
    }
} elseif ($is_guru) {
    $final_filter_pembimbing_id = $_SESSION['id_guru_pendamping'] ?? null;
    if ($siswa_id_from_form !== null) {
        $final_filter_siswa_id = $siswa_id_from_form;
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = &$final_filter_siswa_id;
        $query_types .= 'i';
    } elseif ($final_filter_pembimbing_id !== null) {
        $where_clauses[] = 's.pembimbing_id = ?';
        $query_params[] = &$final_filter_pembimbing_id;
        $query_types .= 'i';
    } else {
        die("ID Guru tidak ditemukan dalam sesi.");
    }
} elseif ($is_admin) {
    $param_siswa_id = $_GET['siswa_id'] ?? null;
    if ($param_siswa_id !== null) {
        $final_filter_siswa_id = $param_siswa_id;
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[] = &$final_filter_siswa_id;
        $query_types .= 'i';
    } elseif ($pembimbing_id_from_form !== null) {
        $final_filter_pembimbing_id = $pembimbing_id_from_form;
        $where_clauses[] = 's.pembimbing_id = ?';
        $query_params[] = &$final_filter_pembimbing_id;
        $query_types .= 'i';
    }
}

// Filter Rentang Tanggal
if (strtotime($tanggal_mulai) && strtotime($tanggal_akhir) && $tanggal_mulai <= $tanggal_akhir) {
    $where_clauses[] = 'as_abs.tanggal_absen BETWEEN ? AND ?';
    $query_params[] = &$tanggal_mulai;
    $query_params[] = &$tanggal_akhir;
    $query_types .= 'ss';
}

// Filter Status Absensi untuk laporan detil
$is_detailed_report = $is_siswa || ($final_filter_siswa_id !== null);
if ($is_detailed_report && !empty($filter_status) && $filter_status !== 'Semua') {
    $where_clauses[] = 'as_abs.status_absen = ?';
    $query_params[] = &$filter_status;
    $query_types .= 's';
}

// Filter Kelas (hanya Admin)
if ($is_admin && !empty($kelas_filter_pdf)) {
    $where_clauses[] = 's.kelas = ?';
    $query_params[] = &$kelas_filter_pdf;
    $query_types .= 's';
}

$filter_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";
$generate_recap_report = !$is_detailed_report;

// --- AMBIL DATA ABSENSI ---
$query_sql = "
    SELECT
        s.id_siswa, s.nama_siswa, s.kelas, j.nama_jurusan, tp.nama_tempat_pkl,
        gp.nama_pembimbing AS nama_guru_pembimbing,
        tp.nama_instruktur AS nama_pembimbing_dunia_kerja,
        (SELECT MIN(tanggal_absen) FROM absensi_siswa WHERE siswa_id = s.id_siswa) AS tanggal_mulai_pkl,
        as_abs.tanggal_absen, as_abs.status_absen
    FROM absensi_siswa as_abs
    JOIN siswa s ON as_abs.siswa_id = s.id_siswa
    LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
    LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
    $filter_sql
    ORDER BY s.kelas ASC, s.nama_siswa ASC, as_abs.tanggal_absen ASC";

$stmt = $koneksi->prepare($query_sql);
if ($stmt === false) {
    die("Gagal menyiapkan query absensi: " . $koneksi->error);
}
if (!empty($query_params)) {
    $bind_args = [];
    $bind_args[] = $query_types;
    foreach ($query_params as $key => $value) {
        $bind_args[] = &$query_params[$key];
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_args);
}
$stmt->execute();
$result = $stmt->get_result();
$absensi_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$koneksi->close();

// --- PENGATURAN HTML & PDF ---
$nama_sekolah = "SMKN 1 GANTAR";
$tahun_pkl = date('Y');
$tanggal_tanda_tangan = date('d F Y', strtotime($tanggal_akhir));

ob_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Daftar Hadir PKL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 15mm;
            color: #333;
        }

        .header-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .school-info {
            text-align: center;
            font-size: 12pt;
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }

        /* === BIODATA: pastikan rata kiri dan rapi === */
        .student-info-table {
            border-collapse: collapse;
            width: auto;
            margin-bottom: 20px;
            font-size: 10pt;
        }

        .student-info-table td {
            border: none;
            padding: 3px 0;
            vertical-align: top;
            text-align: left;
        }

        .student-info-table td.label {
            font-weight: bold;
            width: 150px;
            /* Lebar label */
        }

        .student-info-table td.separator {
            width: 10px;
            /* Lebar untuk titik dua */
            text-align: center;
        }

        /* === TABEL ABSENSI: center === */
        table.attendance {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .attendance th,
        .attendance td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
            font-size: 9.5pt;
        }

        .attendance th {
            background-color: #e0e0e0;
            font-weight: bold;
        }

        .signature-section {
            margin-top: 40px;
            font-size: 10pt;
            page-break-inside: avoid;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="header-title">REKAPITULASI DAFTAR HADIR PESERTA PKL</div>
    <div class="school-info"><?= htmlspecialchars($nama_sekolah) ?> TAHUN <?= htmlspecialchars($tahun_pkl) ?></div>

    <?php if ($generate_recap_report): ?>
        <p style="text-align: center;">Tampilan rekapitulasi umum akan muncul di sini.</p>
    <?php else: ?>
        <?php if (empty($absensi_data)): ?>
            <p style="text-align: center;">Tidak ada data absensi untuk ditampilkan pada periode dan filter yang dipilih.</p>
        <?php else: ?>
            <?php $first_record = $absensi_data[0]; ?>
            <table class="student-info-table">
                <tr>
                    <td class="label">Nama Peserta Didik</td>
                    <td class="separator">:</td>
                    <td><?= htmlspecialchars($first_record['nama_siswa']) ?></td>
                </tr>
                <tr>
                    <td class="label">Kelas</td>
                    <td class="separator">:</td>
                    <td><?= htmlspecialchars($first_record['kelas'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">Jurusan</td>
                    <td class="separator">:</td>
                    <td><?= htmlspecialchars($first_record['nama_jurusan'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">Tempat PKL</td>
                    <td class="separator">:</td>
                    <td><?= htmlspecialchars($first_record['nama_tempat_pkl'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">Tanggal Mulai</td>
                    <td class="separator">:</td>
                    <td><?= date('d F Y', strtotime($tanggal_mulai)) ?></td>
                </tr>
                <tr>
                    <td class="label">Tanggal Selesai</td>
                    <td class="separator">:</td>
                    <td><?= date('d F Y', strtotime($tanggal_akhir)) ?></td>
                </tr>
            </table>

            <table class="attendance">
                <thead>
                    <tr>
                        <th style="width:5%;">No</th>
                        <th style="width:25%;">Tanggal</th>
                        <th style="width:20%;">Status Kehadiran</th>
                        <th>Paraf</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($absensi_data as $record): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d F Y', strtotime($record['tanggal_absen'])) ?></td>
                            <td><?= htmlspecialchars($record['status_absen']) ?></td>
                            <td></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!$generate_recap_report && !empty($absensi_data)): ?>
        <div class="signature-section">
            <table style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <td style="width: 60%; border: none;"></td>
                        <td style="width: 40%; border: none; text-align: left;">
                            <p>...................., <?= $tanggal_tanda_tangan ?></p>
                            <p>Mengetahui,</p>
                            <p>Pembimbing Dunia Kerja</p>
                            <div style="height: 60px;"></div>
                            <p><b>(....................................)</b></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</body>

</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Rekap_Hadir_PKL_" . date('Ymd_His') . ".pdf";
// Setting Attachment ke false agar file langsung terbuka di browser
$dompdf->stream($filename, ["Attachment" => false]);
exit();
