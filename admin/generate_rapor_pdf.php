<?php
session_start();
require_once 'partials/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Keamanan
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
if (!$is_admin && !$is_guru) die("Akses ditolak.");

$siswa_id = isset($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;
if ($siswa_id === 0) die("ID Siswa tidak valid.");

// --- PERUBAHAN 1: Ambil kolom tanggal_selesai_pkl dari tabel siswa ---
$query_detail = "
    SELECT 
        s.nama_siswa, s.nisn, s.kelas, s.tanggal_selesai_pkl,
        j.nama_jurusan,
        j.prodi,
        tp.nama_tempat_pkl,
        gp.nama_pembimbing
    FROM siswa s
    LEFT JOIN jurusan j ON s.jurusan_id = j.id_jurusan
    LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
    WHERE s.id_siswa = ?
";
$stmt_siswa = $koneksi->prepare($query_detail);
$stmt_siswa->bind_param("i", $siswa_id);
$stmt_siswa->execute();
$siswa = $stmt_siswa->get_result()->fetch_assoc();
$stmt_siswa->close();

if (!$siswa) die("Data siswa tidak ditemukan.");

// --- PERUBAHAN 2: Logika baru untuk menentukan tanggal mulai dan selesai PKL ---
$tanggal_pkl_mulai = '...';
$tanggal_pkl_selesai = '...';
$tahun_ajaran = date('Y') . '/' . (date('Y') + 1);

// Tetap ambil tanggal mulai dan tanggal absen terakhir dari tabel absensi sebagai referensi
$query_absen = "SELECT MIN(tanggal_absen) AS tanggal_mulai, MAX(tanggal_absen) AS tanggal_selesai 
                  FROM absensi_siswa WHERE siswa_id = ?";
$stmt_absen = $koneksi->prepare($query_absen);
$stmt_absen->bind_param("i", $siswa_id);
$stmt_absen->execute();
$absen_info = $stmt_absen->get_result()->fetch_assoc();
$stmt_absen->close();

// Tentukan tanggal yang akan digunakan untuk loop dan tampilan
$tanggal_mulai_loop = null;
$tanggal_selesai_loop = null;

if ($absen_info && $absen_info['tanggal_mulai']) {
    $tanggal_mulai_loop = $absen_info['tanggal_mulai'];
    $tahun_awal = date('Y', strtotime($absen_info['tanggal_mulai']));
    $tahun_ajaran = $tahun_awal . '/' . ($tahun_awal + 1);
    
    // Prioritaskan tanggal_selesai_pkl dari tabel siswa. 
    // Jika kosong, baru gunakan tanggal absensi terakhir sebagai fallback.
    $tanggal_selesai_loop = !empty($siswa['tanggal_selesai_pkl']) ? $siswa['tanggal_selesai_pkl'] : $absen_info['tanggal_selesai'];

    // Format tanggal untuk ditampilkan di rapor
    $tanggal_pkl_mulai = date('d F Y', strtotime($tanggal_mulai_loop));
    $tanggal_pkl_selesai = date('d F Y', strtotime($tanggal_selesai_loop));
}


// --- PERUBAHAN 3: Perhitungan absensi menggunakan tanggal yang sudah ditentukan ---
$jumlah_sakit = 0;
$jumlah_izin = 0;
$jumlah_alfa = 0;

$query_absensi_all = "SELECT tanggal_absen, status_absen FROM absensi_siswa WHERE siswa_id = ?";
$stmt_absensi_all = $koneksi->prepare($query_absensi_all);
$stmt_absensi_all->bind_param("i", $siswa_id);
$stmt_absensi_all->execute();
$result_absensi = $stmt_absensi_all->get_result();
$absensi_lookup = [];
while ($row = $result_absensi->fetch_assoc()) {
    $absensi_lookup[$row['tanggal_absen']] = $row['status_absen'];
}
$stmt_absensi_all->close();

// Pastikan tanggal mulai dan selesai valid sebelum melakukan perulangan
if ($tanggal_mulai_loop && $tanggal_selesai_loop) {
    $start_ts = strtotime($tanggal_mulai_loop);
    $end_ts = strtotime($tanggal_selesai_loop); // Menggunakan tanggal selesai yang sudah diprioritaskan
    
    for ($i = $start_ts; $i <= $end_ts; $i = strtotime('+1 day', $i)) {
        $day_of_week = date('N', $i);
        // Hanya hitung hari kerja (Senin-Jumat)
        if ($day_of_week >= 1 && $day_of_week <= 5) {
            $current_date_str = date('Y-m-d', $i);
            if (isset($absensi_lookup[$current_date_str])) {
                $status = $absensi_lookup[$current_date_str];
                if ($status == 'Sakit') $jumlah_sakit++;
                elseif ($status == 'Izin') $jumlah_izin++;
                // Jika statusnya 'Hadir' atau lainnya, tidak dihitung sebagai S, I, atau A
            } else {
                // Jika tidak ada catatan absen di hari kerja, dianggap Alfa
                $jumlah_alfa++;
            }
        }
    }
}
// --- AKHIR PERUBAHAN ---

// Ambil semua TP statis dan susun dalam hierarki
$tp_result = $koneksi->query("SELECT * FROM tujuan_pembelajaran ORDER BY id_induk, kode_tp");
$semua_tp = [];
$tp_anak = [];
while ($row = $tp_result->fetch_assoc()) {
    $semua_tp[$row['id_tp']] = $row;
    $tp_anak[$row['id_induk']][] = $row['id_tp'];
}

// --- LOGIKA BARU: Ambil Jurnal Kegiatan Siswa sebagai Sub-TP Dinamis ---
$id_tp_teknis = null;
foreach ($semua_tp as $id => $tp) {
    if ($tp['kode_tp'] === '3') {
        $id_tp_teknis = $id;
        break;
    }
}

if ($id_tp_teknis) {
    $query_jurnal_dinamis = "
        SELECT 
            ns.jurnal_kegiatan_id, 
            jk.nama_pekerjaan
        FROM nilai_siswa ns
        JOIN jurnal_kegiatan jk ON ns.jurnal_kegiatan_id = jk.id_jurnal_kegiatan
        WHERE ns.siswa_id = ? AND ns.jurnal_kegiatan_id IS NOT NULL
        GROUP BY ns.jurnal_kegiatan_id, jk.nama_pekerjaan
    ";
    $stmt_jurnal = $koneksi->prepare($query_jurnal_dinamis);
    $stmt_jurnal->bind_param("i", $siswa_id);
    $stmt_jurnal->execute();
    $result_jurnal = $stmt_jurnal->get_result();
    $sub_kode_jurnal = 1;
    while ($jurnal = $result_jurnal->fetch_assoc()) {
        $id_tp_jurnal = 'jurnal_' . $jurnal['jurnal_kegiatan_id'];
        $semua_tp[$id_tp_jurnal] = [
            'id_tp' => $id_tp_jurnal,
            'id_induk' => $id_tp_teknis,
            'kode_tp' => '3.' . $sub_kode_jurnal++,
            'deskripsi_tp' => $jurnal['nama_pekerjaan']
        ];
        $tp_anak[$id_tp_teknis][] = $id_tp_jurnal;
    }
    $stmt_jurnal->close();
}
// --- AKHIR LOGIKA BARU ---

$cache_nilai = [];

// Fungsi hitung_nilai() dan generate_deskripsi_narasi()
function hitung_nilai($id_siswa, $id_tp, $koneksi, $tp_anak, &$cache_nilai)
{
    $cache_key = "$id_siswa-$id_tp";
    if (isset($cache_nilai[$cache_key])) return $cache_nilai[$cache_key];
    $punya_anak = isset($tp_anak[$id_tp]);
    if (!$punya_anak) {
        $nilai = 0;
        if (strpos((string)$id_tp, 'jurnal_') === 0) {
            $jurnal_id = (int) str_replace('jurnal_', '', $id_tp);
            $stmt = $koneksi->prepare("SELECT nilai FROM nilai_siswa WHERE siswa_id = ? AND jurnal_kegiatan_id = ?");
            $stmt->bind_param("ii", $id_siswa, $jurnal_id);
        } else {
            $stmt = $koneksi->prepare("SELECT nilai FROM nilai_siswa WHERE siswa_id = ? AND id_tp = ?");
            $stmt->bind_param("ii", $id_siswa, $id_tp);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $nilai = $result['nilai'] ?? 0;
        $cache_nilai[$cache_key] = $nilai;
        return $nilai;
    } else {
        $nilai_anak_arr = [];
        foreach ($tp_anak[$id_tp] as $id_anak) {
            $nilai_anak_arr[] = hitung_nilai($id_siswa, $id_anak, $koneksi, $tp_anak, $cache_nilai);
        }
        $total = array_sum($nilai_anak_arr);
        $jumlah = count($nilai_anak_arr);
        $rata_rata = ($jumlah > 0) ? $total / $jumlah : 0;
        $cache_nilai[$cache_key] = $rata_rata;
        return $rata_rata;
    }
}

function generate_deskripsi_narasi($id_siswa, $id_tp_utama, $koneksi, $semua_tp, $tp_anak)
{
    $anak_utama = $tp_anak[$id_tp_utama] ?? [];
    if (empty($anak_utama)) return "-";
    $nilai_kompetensi = [];
    $cache_nilai_lokal = [];
    foreach ($anak_utama as $id_anak) {
        $nilai_kompetensi[] = ['deskripsi' => $semua_tp[$id_anak]['deskripsi_tp'], 'nilai' => hitung_nilai($id_siswa, $id_anak, $koneksi, $tp_anak, $cache_nilai_lokal)];
    }
    if (empty(array_filter($nilai_kompetensi, fn($n) => $n['nilai'] > 0))) return "Nilai belum terisi.";
    $tertinggi = ['nilai' => -1, 'deskripsi' => ''];
    $terendah = ['nilai' => 101, 'deskripsi' => ''];
    foreach ($nilai_kompetensi as $kompetensi) {
        if ($kompetensi['nilai'] > 0) {
            if ($kompetensi['nilai'] > $tertinggi['nilai']) $tertinggi = $kompetensi;
            if ($kompetensi['nilai'] < $terendah['nilai']) $terendah = $kompetensi;
        }
    }
    $tertinggi['deskripsi'] = explode('(', $tertinggi['deskripsi'])[0];
    $terendah['deskripsi'] = explode('(', $terendah['deskripsi'])[0];
    if ($tertinggi['nilai'] <= 0) return "Nilai belum lengkap.";
    if ($tertinggi['nilai'] == $terendah['nilai']) {
        return "Peserta didik sudah memiliki soft skills sesuai harapan dalam " . lcfirst(trim($tertinggi['deskripsi'])) . ".";
    }
    return "Peserta didik sudah memiliki soft skills sesuai harapan dalam " . lcfirst(trim($tertinggi['deskripsi'])) . " (Y) namun masih perlu ditingkatkan dalam hal " . lcfirst(trim($terendah['deskripsi'])) . " (T).";
}

function generate_rapor_rows($id_siswa, $koneksi, $semua_tp, $tp_anak, &$cache_nilai)
{
    $html_rows = '';
    $tp_utama = $tp_anak[NULL] ?? [];
    foreach ($tp_utama as $id_tp) {
        $item = $semua_tp[$id_tp];
        $nilai = hitung_nilai($id_siswa, $id_tp, $koneksi, $tp_anak, $cache_nilai);
        if ($nilai > 0) {
            $html_rows .= "<tr>";
            $html_rows .= "<td style='text-align:center;'>" . htmlspecialchars($item['kode_tp']) . "</td>";
            $html_rows .= "<td>" . nl2br(htmlspecialchars($item['deskripsi_tp'])) . "</td>";
            $html_rows .= "<td style='text-align:center;'>" . number_format($nilai, 2) . "</td>";
            $html_rows .= "<td>" . htmlspecialchars(generate_deskripsi_narasi($id_siswa, $id_tp, $koneksi, $semua_tp, $tp_anak)) . "</td>";
            $html_rows .= "</tr>";
        }
    }
    return $html_rows;
}

$table_content = generate_rapor_rows($siswa_id, $koneksi, $semua_tp, $tp_anak, $cache_nilai);

// --- Bagian HTML (tidak ada perubahan signifikan, hanya memastikan variabel tanggal benar) ---
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rapor Penilaian PKL - ' . htmlspecialchars($siswa['nama_siswa']) . '</title>
    <style>
        body { font-family: Times, serif; font-size: 12pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h4, .header h5 { margin: 0; padding: 0; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 3px 0; vertical-align: top;}
        .info-table .label { width: 180px; }
        .info-table .separator { width: 10px; }
        .report-table { width: 100%; border-collapse: collapse; }
        .report-table th, .report-table td { border: 1px solid black; padding: 7px; vertical-align: top; }
        .report-table th { text-align: center; font-weight: bold; }
        .kehadiran-table { border-collapse: collapse; }
        .kehadiran-table td { border: 1px solid black; padding: 5px; }
        .kehadiran-container { page-break-inside: avoid; margin-top: 20px; }
        .signature-table { width: 100%; margin-top: 20px; border: none; page-break-inside: avoid; }
        .signature-table td { width: 50%; text-align: center; border: none; vertical-align: top; }
        .signature-name {
            text-decoration: underline;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h4>SMK NEGERI 1 GANTAR</h4>
        <h5>Tahun Ajaran ' . $tahun_ajaran . '</h5>
    </div>
    <table class="info-table">
        <tr><td class="label">Nama Peserta Didik</td><td class="separator">:</td><td>' . htmlspecialchars($siswa['nama_siswa']) . '</td></tr>
        <tr><td class="label">NISN</td><td class="separator">:</td><td>' . htmlspecialchars($siswa['nisn']) . '</td></tr>
        <tr><td class="label">Kelas</td><td class="separator">:</td><td>' . htmlspecialchars($siswa['kelas']) . '</td></tr>
        <tr><td class="label">Program Keahlian</td><td class="separator">:</td><td>' . htmlspecialchars($siswa['prodi'] ?? '-') . '</td></tr>
        <tr><td class="label">Konsentrasi Keahlian</td><td class="separator">:</td><td>' . htmlspecialchars($siswa['nama_jurusan'] ?? '-') . '</td></tr>
        <tr><td class="label">Tempat PKL</td><td class="separator">:</td><td>' . htmlspecialchars($siswa['nama_tempat_pkl'] ?? '-') . '</td></tr>
        <tr><td class="label">Tanggal PKL</td><td class="separator">:</td><td>Mulai: ' . $tanggal_pkl_mulai . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Selesai: ' . $tanggal_pkl_selesai . '</td></tr>
        <tr><td class="label">Nama Instruktur</td><td class="separator">:</td><td>.......................................</td></tr>
        <tr><td class="label">Nama Pembimbing</td><td class="separator">:</td><td>' . htmlspecialchars($siswa['nama_pembimbing'] ?? '-') . '</td></tr>
    </table>
    
    <table class="report-table">
        <thead>
            <tr><th style="width:5%;">No.</th><th>Tujuan Pembelajaran</th><th style="width:10%;">Skor</th><th style="width:40%;">Deskripsi</th></tr>
        </thead>
        <tbody>
            ' . $table_content . '
            <tr>
                <td colspan="4">
                    <strong>Catatan:</strong>
                    <br><br><br><br>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div class="kehadiran-container">
        <table class="kehadiran-table" style="width: 50%;">
            <tr><td colspan="3" style="text-align:left; border:none; padding-bottom:5px;"><strong>Kehadiran</strong></td></tr>
            <tr><td>Sakit</td><td>:</td><td style="text-align:center;">' . $jumlah_sakit . ' Hari</td></tr>
            <tr><td>Ijin</td><td>:</td><td style="text-align:center;">' . $jumlah_izin . ' Hari</td></tr>
            <tr><td>Tanpa Keterangan</td><td>:</td><td style="text-align:center;">' . $jumlah_alfa . ' Hari</td></tr>
        </table>
    </div>

    <table class="signature-table">
        <tbody>
            <tr>
                <td>
                    Guru Pembimbing
                    <br><br><br><br><br>
                    <span class="signature-name">' . htmlspecialchars($siswa['nama_pembimbing'] ?? '.........................') . '</span>
                </td>
                <td>
                    Gantar, ...................................... ' . date('Y') . '
                    <br>
                    Pembimbing Dunia Kerja
                    <br><br><br><br><br>
                    <span class="signature-name">.........................</span>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>';

// Proses Generate PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Rapor_PKL_" . str_replace(' ', '_', $siswa['nama_siswa']) . ".pdf", ["Attachment" => false]);
exit();