<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

include 'partials/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* ========== Helpers ========== */

function tgl_id($dateYmd)
{
    static $hari  = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    static $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $ts = strtotime($dateYmd);
    if ($ts === false) return htmlspecialchars($dateYmd);
    $h = (int)date('N', $ts);
    $d = (int)date('j', $ts);
    $m = (int)date('n', $ts);
    $y = date('Y', $ts);
    return $hari[$h] . ', ' . $d . ' ' . $bulan[$m] . ' ' . $y;
}

function tgl_id_nama($dateYmd)
{
    static $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $ts = strtotime($dateYmd);
    if ($ts === false) return htmlspecialchars($dateYmd);
    $d = (int)date('j', $ts);
    $m = (int)date('n', $ts);
    $y = date('Y', $ts);
    return $d . ' ' . $bulan[$m] . ' ' . $y;
}

function fmt_jam($val)
{
    $val = trim((string)($val ?? ''));
    if ($val === '') return '';
    $ts = strtotime($val);
    return $ts ? date('H:i', $ts) : htmlspecialchars($val);
}

/* ========== Access control ========== */
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru  = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
if (!$is_admin && !$is_guru && !$is_siswa) {
    header('Location: ../login.php');
    exit();
}

/* ========== Filters ========== */
$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-t');
$filter_status = $_GET['status'] ?? 'Semua';
$kelas_filter_pdf = $_GET['kelas_pdf'] ?? '';
$siswa_id_from_form = $_GET['siswa_id_pdf'] ?? null;
$pembimbing_id_from_form = $_GET['pembimbing_id_pdf'] ?? null;

/* ========== Where builder ========== */
$where_clauses = [];
$query_params  = [];
$query_types   = "";

$final_filter_siswa_id = null;
$final_filter_pembimbing_id = null;

if ($is_siswa) {
    $final_filter_siswa_id = $_SESSION['id_siswa'] ?? null;
    if (!$final_filter_siswa_id) {
        die("Siswa ID tidak ditemukan dalam sesi.");
    }
    $where_clauses[] = 'as_abs.siswa_id = ?';
    $query_params[]  = &$final_filter_siswa_id;
    $query_types    .= 'i';
} elseif ($is_guru) {
    $final_filter_pembimbing_id = $_SESSION['id_guru_pendamping'] ?? null;
    if ($siswa_id_from_form !== null) {
        $final_filter_siswa_id = $siswa_id_from_form;
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[]  = &$final_filter_siswa_id;
        $query_types    .= 'i';
    } elseif ($final_filter_pembimbing_id !== null) {
        $where_clauses[] = 's.pembimbing_id = ?';
        $query_params[]  = &$final_filter_pembimbing_id;
        $query_types    .= 'i';
    } else {
        die("ID Guru tidak ditemukan dalam sesi.");
    }
} elseif ($is_admin) {
    $param_siswa_id = $_GET['siswa_id'] ?? null;
    if ($param_siswa_id !== null) {
        $final_filter_siswa_id = $param_siswa_id;
        $where_clauses[] = 'as_abs.siswa_id = ?';
        $query_params[]  = &$final_filter_siswa_id;
        $query_types    .= 'i';
    } elseif ($pembimbing_id_from_form !== null) {
        $final_filter_pembimbing_id = $pembimbing_id_from_form;
        $where_clauses[] = 's.pembimbing_id = ?';
        $query_params[]  = &$final_filter_pembimbing_id;
        $query_types    .= 'i';
    }
}

/* Rentang tanggal wajib */
$where_clauses[] = 'as_abs.tanggal_absen BETWEEN ? AND ?';
$query_params[]  = &$tanggal_mulai;
$query_params[]  = &$tanggal_akhir;
$query_types    .= 'ss';

/* Status filter hanya untuk mode detail */
$is_detailed_report = $is_siswa || ($final_filter_siswa_id !== null);
if ($is_detailed_report && $filter_status !== 'Semua' && !empty($filter_status)) {
    $where_clauses[] = 'as_abs.status_absen = ?';
    $query_params[]  = &$filter_status;
    $query_types    .= 's';
}

/* Filter kelas (admin) */
if ($is_admin && !empty($kelas_filter_pdf)) {
    $where_clauses[] = 's.kelas = ?';
    $query_params[]  = &$kelas_filter_pdf;
    $query_types    .= 's';
}

$filter_sql = " WHERE " . implode(" AND ", $where_clauses);
$generate_recap_report = !$is_detailed_report;

/* ========== Query absensi detail mentah ========== */
$query_sql = "
    SELECT
        s.id_siswa,
        s.nama_siswa,
        s.kelas,
        tp.nama_tempat_pkl,
        gp.nama_pembimbing AS nama_guru_pembimbing,
        as_abs.tanggal_absen,
        as_abs.status_absen,
        as_abs.jam_datang,
        as_abs.jam_pulang
    FROM absensi_siswa as_abs
    JOIN siswa s ON as_abs.siswa_id = s.id_siswa
    LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
    $filter_sql
    ORDER BY s.kelas ASC, s.nama_siswa ASC, as_abs.tanggal_absen ASC
";
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
    call_user_func_array([$stmt, 'bind_param'], $bind_args);
}
$stmt->execute();
$result = $stmt->get_result();
$absensi_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ========== CARI TANGGAL SELESAI DARI TABEL SISWA ========== */
$selesai_pkl_map = []; // siswa_id => tanggal_selesai_pkl

$selesai_where_clauses = [];
$selesai_query_params = [];
$selesai_query_types = "";

if ($is_siswa) {
    $selesai_where_clauses[] = 's.id_siswa = ?';
    $selesai_query_params[] = &$final_filter_siswa_id;
    $selesai_query_types .= 'i';
} elseif ($is_guru) {
    if ($final_filter_pembimbing_id !== null) {
        $selesai_where_clauses[] = 's.pembimbing_id = ?';
        $selesai_query_params[] = &$final_filter_pembimbing_id;
        $selesai_query_types .= 'i';
    }
    if ($final_filter_siswa_id !== null) {
        $selesai_where_clauses[] = 's.id_siswa = ?';
        $selesai_query_params[] = &$final_filter_siswa_id;
        $selesai_query_types .= 'i';
    }
} elseif ($is_admin) {
    if ($final_filter_pembimbing_id !== null) {
        $selesai_where_clauses[] = 's.pembimbing_id = ?';
        $selesai_query_params[] = &$final_filter_pembimbing_id;
        $selesai_query_types .= 'i';
    }
    if ($final_filter_siswa_id !== null) {
        $selesai_where_clauses[] = 's.id_siswa = ?';
        $selesai_query_params[] = &$final_filter_siswa_id;
        $selesai_query_types .= 'i';
    }
    if (!empty($kelas_filter_pdf)) {
        $selesai_where_clauses[] = 's.kelas = ?';
        $selesai_query_params[] = &$kelas_filter_pdf;
        $selesai_query_types .= 's';
    }
}

$selesai_filter_sql = !empty($selesai_where_clauses) ? " WHERE " . implode(" AND ", $selesai_where_clauses) : "";

$selesai_query_sql = "
    SELECT 
        s.id_siswa,
        s.tanggal_selesai_pkl
    FROM siswa s
    $selesai_filter_sql
";

$stmt_selesai = $koneksi->prepare($selesai_query_sql);
if ($stmt_selesai === false) {
    die("Gagal menyiapkan query tanggal selesai: " . $koneksi->error);
}

if (!empty($selesai_query_params)) {
    $selesai_bind_args = [];
    $selesai_bind_args[] = $selesai_query_types;
    foreach ($selesai_query_params as $k => $v) {
        $selesai_bind_args[] = &$selesai_query_params[$k];
    }
    call_user_func_array([$stmt_selesai, 'bind_param'], $selesai_bind_args);
}

$stmt_selesai->execute();
$result_selesai = $stmt_selesai->get_result();
while ($row = $result_selesai->fetch_assoc()) {
    $selesai_pkl_map[$row['id_siswa']] = $row['tanggal_selesai_pkl']; // bisa null
}
$stmt_selesai->close();

/* ========== Data rekap untuk Admin/Guru ========== */
$all_relevant_students = [];
if ($generate_recap_report) {
    $siswa_where = [];
    $siswa_params = [];
    $siswa_types = "";

    if ($final_filter_pembimbing_id !== null) {
        $siswa_where[] = 's.pembimbing_id = ?';
        $siswa_params[] = &$final_filter_pembimbing_id;
        $siswa_types .= 'i';
    }
    if (!empty($kelas_filter_pdf)) {
        $siswa_where[] = 's.kelas = ?';
        $siswa_params[] = &$kelas_filter_pdf;
        $siswa_types .= 's';
    }

    $siswa_filter_sql = !empty($siswa_where) ? " WHERE " . implode(" AND ", $siswa_where) : "";
    $siswa_detail_sql = "
        SELECT s.id_siswa, s.nama_siswa, s.kelas, gp.nama_pembimbing,
               (SELECT MIN(tanggal_absen) FROM absensi_siswa WHERE siswa_id = s.id_siswa) AS tanggal_mulai_pkl
        FROM siswa s
        LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
        $siswa_filter_sql
        ORDER BY s.kelas ASC, s.nama_siswa ASC
    ";
    $stmt_siswa = $koneksi->prepare($siswa_detail_sql);
    if ($stmt_siswa === false) {
        die("Gagal menyiapkan query siswa untuk rekap: " . $koneksi->error);
    }
    if (!empty($siswa_params)) {
        $bind_args_siswa = [];
        $bind_args_siswa[] = $siswa_types;
        foreach ($siswa_params as $k => $v) {
            $bind_args_siswa[] = &$siswa_params[$k];
        }
        call_user_func_array([$stmt_siswa, 'bind_param'], $bind_args_siswa);
    }
    $stmt_siswa->execute();
    $result_siswa = $stmt_siswa->get_result();
    $all_relevant_students = $result_siswa->fetch_all(MYSQLI_ASSOC);
    $stmt_siswa->close();
}

$koneksi->close();

/* ========== Bangun map absensi per siswa per tanggal ========== */
$absensi_per_siswa_tanggal = [];
foreach ($absensi_data as $rec) {
    $absensi_per_siswa_tanggal[$rec['id_siswa']][$rec['tanggal_absen']] = $rec['status_absen'];
}

/* ========== Tanggal terakhir absen untuk TTD ========== */
$tanggal_terakhir_absen = null;
foreach ($absensi_data as $row) {
    if (!$tanggal_terakhir_absen || $row['tanggal_absen'] > $tanggal_terakhir_absen) {
        $tanggal_terakhir_absen = $row['tanggal_absen'];
    }
}
if (!$tanggal_terakhir_absen) {
    $tanggal_terakhir_absen = date('Y-m-d');
}

/* ========== PDF content ========== */
$nama_sekolah = "SMKN 1 GANTAR";
$tahun_pkl = date('Y');

$html = '
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekapitulasi Daftar Hadir PKL</title>
<style>
body { font-family: Arial, sans-serif; font-size: 10pt; margin: 15mm; color: #333; }
.header-title { text-align: center; font-size: 14pt; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
.school-info { text-align: center; font-size: 12pt; margin-bottom: 20px; border-bottom: 1px solid #333; padding-bottom: 10px; }
.report-period { text-align: center; font-size: 11pt; margin-bottom: 20px; }

.student-info { font-size: 10pt; margin-bottom: 15px; line-height: 1.6; }
.student-info table { width: 100%; border-collapse: collapse; }
.student-info td { border: none !important; padding: 2px 0; vertical-align: top; }
.student-info td:first-child { width: 170px; font-weight: bold; text-align: left; }
.student-info td:last-child { text-align: left; }

table.attendance, table.recap { width: 100%; border-collapse: collapse; margin-top: 15px; }
table.attendance th, table.attendance td,
table.recap th, table.recap td { border: 1px solid #333; padding: 8px; text-align: center; font-size: 9.5pt; }
table.attendance th, table.recap th { background-color: #e0e0e0; font-weight: bold; }
.left-align { text-align: left !important; }

.signature-section { margin: 40px 0 0 40px; font-size: 10pt; page-break-inside: avoid; width: 100%; }
</style>
</head>
<body>

<div class="header-title">REKAPITULASI DAFTAR HADIR PESERTA PKL</div>
<div class="school-info">' . htmlspecialchars($nama_sekolah) . ' TAHUN ' . htmlspecialchars($tahun_pkl) . '</div>
<div class="report-period">Periode: ' . tgl_id_nama($tanggal_mulai) . ' s.d. ' . tgl_id_nama($tanggal_akhir) . '</div>
';

/* ===== BODY ===== */
if ($generate_recap_report) {
    if (empty($all_relevant_students)) {
        $html .= '<p style="text-align:center;">Tidak ada data rekapitulasi untuk ditampilkan.</p>';
    } else {
        $start_ts = strtotime($tanggal_mulai);
        $end_ts   = strtotime($tanggal_akhir);

        $html .= '<table class="recap">
            <thead>
                <tr>
                    <th style="width:6%;">No</th>
                    <th class="left-align" style="width:38%;">Nama</th>
                    <th style="width:10%;">Kelas</th>
                    <th style="width:9%;">Hadir</th>
                    <th style="width:9%;">Sakit</th>
                    <th style="width:9%;">Izin</th>
                    <th style="width:9%;">Libur</th>
                    <th style="width:10%;">Alfa</th>
                </tr>
            </thead>
            <tbody>';

        $no = 1;
        foreach ($all_relevant_students as $s) {
            $sid = $s['id_siswa'];
            $mulai_ts_siswa = !empty($s['tanggal_mulai_pkl']) ? strtotime($s['tanggal_mulai_pkl']) : $start_ts;
            $tanggal_selesai_siswa = $selesai_pkl_map[$sid] ?? null;
            $selesai_ts_siswa = $tanggal_selesai_siswa ? strtotime($tanggal_selesai_siswa) : null;

            $rekap = ['Hadir' => 0, 'Sakit' => 0, 'Izin' => 0, 'Libur' => 0, 'Alfa' => 0];

            for ($i = $start_ts; $i <= $end_ts; $i = strtotime('+1 day', $i)) {
                $dow = (int)date('N', $i);
                $current_ts = $i;
                $tgl = date('Y-m-d', $i);

                // Lewati jika sebelum mulai PKL
                if ($current_ts < $mulai_ts_siswa) continue;

                // ✅ INI PERBAIKAN UTAMA: Lewati jika sudah selesai PKL
                if ($selesai_ts_siswa !== null && $current_ts > $selesai_ts_siswa) {
                    continue;
                }

                if ($dow >= 1 && $dow <= 5) { // Senin-Jumat
                    if (isset($absensi_per_siswa_tanggal[$sid][$tgl])) {
                        $st = $absensi_per_siswa_tanggal[$sid][$tgl];
                        if (isset($rekap[$st])) {
                            $rekap[$st]++;
                        }
                        // Jika status tidak dikenali, abaikan (tapi biasanya tidak terjadi)
                    } else {
                        $rekap['Alfa']++;
                    }
                }
            }

            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td class="left-align">' . htmlspecialchars($s['nama_siswa']) . '</td>
                <td>' . htmlspecialchars($s['kelas'] ?? '-') . '</td>
                <td>' . $rekap['Hadir'] . '</td>
                <td>' . $rekap['Sakit'] . '</td>
                <td>' . $rekap['Izin'] . '</td>
                <td>' . $rekap['Libur'] . '</td>
                <td>' . $rekap['Alfa'] . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';
    }
} else {
    if (empty($absensi_data)) {
        $html .= '<p style="text-align:center;">Tidak ada data absensi untuk ditampilkan.</p>';
    } else {
        $first = $absensi_data[0];
        $html .= '
        <div class="student-info">
            <table>
                <tr><td>Nama Peserta Didik</td><td>: ' . htmlspecialchars($first['nama_siswa']) . '</td></tr>
                <tr><td>Tempat PKL</td><td>: ' . htmlspecialchars($first['nama_tempat_pkl'] ?? '-') . '</td></tr>
            </table>
        </div>

        <table class="attendance">
            <thead>
                <tr>
                    <th style="width:6%;">No</th>
                    <th style="width:34%;" class="left-align">Hari/Tanggal</th>
                    <th style="width:15%;">Jam Masuk</th>
                    <th style="width:15%;">Jam Pulang</th>
                    <th style="width:15%;">Paraf</th>
                    <th style="width:15%;" class="left-align">Keterangan</th>
                </tr>
            </thead>
            <tbody>';

        $no = 1;
        foreach ($absensi_data as $r) {
            $hariTanggal = tgl_id($r['tanggal_absen']);
            $status    = trim((string)($r['status_absen'] ?? ''));
            $jamDatang = fmt_jam($r['jam_datang'] ?? '');
            $jamPulang = fmt_jam($r['jam_pulang'] ?? '');

            if (strcasecmp($status, 'Hadir') === 0) {
                if ($jamDatang === '') $jamDatang = '-';
                if ($jamPulang === '') $jamPulang = '-';
                $ket = 'Hadir';
            } else {
                $jamDatang = '-';
                $jamPulang = '-';
                $ket = ($status === '') ? '-' : $status;
            }

            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td class="left-align">' . $hariTanggal . '</td>
                <td>' . htmlspecialchars($jamDatang) . '</td>
                <td>' . htmlspecialchars($jamPulang) . '</td>
                <td></td>
                <td class="left-align">' . htmlspecialchars($ket) . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';
    }
}

/* ===== Signature ===== */
$label_ttd = ($is_admin || $is_guru) ? 'Pembimbing Sekolah' : 'Pembimbing Dunia Kerja';
$html .= '
<div class="signature-section">
  <table style="width:100%; border-collapse:collapse;">
    <tbody>
      <tr>
        <td style="width:60%; border:none;"></td>
        <td style="width:40%; border:none; text-align:left;">
          <p>...................., ' . tgl_id_nama($tanggal_terakhir_absen) . '</p>
          <p><b>' . htmlspecialchars($label_ttd) . '</b></p>
          <div style="height:60px;"></div>
          <p><b>( .................................... )</b></p>
        </td>
      </tr>
    </tbody>
  </table>
</div>';

$html .= '
</body>
</html>';

/* ========== DOMPDF ========== */
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Rekap_Hadir_PKL_" . date('Ymd_His') . ".pdf";
$dompdf->stream($filename, ["Attachment" => false]);
exit();
