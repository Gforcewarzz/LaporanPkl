<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // Pastikan zona waktu konsisten untuk cap waktu dan tanggal

// Sertakan file koneksi database
include 'partials/db.php'; // Pastikan path ini benar dan $koneksi tersedia

require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- LOGIKA KEAMANAN HALAMAN ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Redirect jika tidak ada peran yang login
if (!$is_siswa && !$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// --- INISIALISASI VARIABEL FILTER UNTUK QUERY SQL ---
$id_siswa_filter = null;
$guru_id_bimbingan = $_SESSION['id_guru_pendamping'] ?? null;

$where_clauses = []; // Array untuk menampung kondisi WHERE (e.g., "siswa_id = ?")
$query_params = [];  // Array untuk menampung nilai parameter (e.g., $id_siswa_filter)
$query_types = "";   // String untuk menampung tipe parameter (e.g., "isss")

// Ambil nilai filter dari parameter URL (GET request)
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : '';

// --- PENENTUAN HAK AKSES DAN FILTER UTAMA BERDASARKAN PERAN ---
if ($is_siswa) {
    // Siswa hanya bisa melihat data mereka sendiri
    $id_siswa_filter = $_SESSION['id_siswa'] ?? null;
    if ($id_siswa_filter) {
        $where_clauses[] = "jh.siswa_id = ?";
        $query_params[] = $id_siswa_filter;
        $query_types .= "i";
    }
} elseif ($is_admin) {
    // Admin bisa melihat semua atau spesifik siswa (jika siswa_id di URL)
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $id_siswa_filter = (int)$_GET['siswa_id'];
        $where_clauses[] = "jh.siswa_id = ?";
        $query_params[] = $id_siswa_filter;
        $query_types .= "i";
    }
} elseif ($is_guru) {
    // Guru hanya bisa melihat siswa bimbingannya
    if ($guru_id_bimbingan) {
        $where_clauses[] = "s.pembimbing_id = ?";
        $query_params[] = $guru_id_bimbingan;
        $query_types .= "i";
    }
    // Jika guru ingin melihat siswa spesifik (dari bimbingannya)
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $id_siswa_filter = (int)$_GET['siswa_id'];
        $where_clauses[] = "jh.siswa_id = ?";
        $query_params[] = $id_siswa_filter;
        $query_types .= "i";
    }
}

// --- PENERAPAN FILTER KEYWORD (jika ada) ---
if (!empty($keyword)) {
    // Filter di kolom pekerjaan atau catatan
    $where_clauses[] = "(jh.pekerjaan LIKE ? OR jh.catatan LIKE ?)";
    $query_params[] = "%" . $keyword . "%";
    $query_params[] = "%" . $keyword . "%";
    $query_types .= "ss";
}

// --- PENERAPAN FILTER RENTANG TANGGAL (jika ada) ---
if (!empty($start_date)) {
    $where_clauses[] = "jh.tanggal >= ?";
    $query_params[] = $start_date;
    $query_types .= "s";
}
if (!empty($end_date)) {
    $where_clauses[] = "jh.tanggal <= ?";
    $query_params[] = $end_date;
    $query_types .= "s";
}

// --- BANGUN QUERY UTAMA UNTUK MENGAMBIL DATA LAPORAN ---
$query_sql = "
    SELECT
        jh.id_jurnal_harian, jh.tanggal, jh.pekerjaan, jh.catatan,
        s.nama_siswa, s.kelas, s.no_induk,
        j.nama_jurusan,
        tp.nama_tempat_pkl, tp.nama_instruktur AS nama_instruktur_pkl,
        gp.nama_pembimbing AS nama_guru_pembimbing
    FROM
        jurnal_harian jh
    LEFT JOIN
        siswa s ON jh.siswa_id = s.id_siswa
    LEFT JOIN
        tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
    LEFT JOIN
        guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
    LEFT JOIN
        jurusan j ON s.jurusan_id = j.id_jurusan";

// Tambahkan klausa WHERE jika ada filter
if (!empty($where_clauses)) {
    $query_sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Urutkan data berdasarkan tanggal dan ID jurnal (ascending untuk laporan)
$query_sql .= " ORDER BY jh.tanggal ASC, jh.id_jurnal_harian ASC";

$stmt = $koneksi->prepare($query_sql);

// Cek jika prepared statement gagal
if ($stmt === false) {
    error_log("Error preparing statement: " . $koneksi->error);
    die("Terjadi kesalahan sistem saat menyiapkan laporan.");
}

// Bind parameter ke prepared statement jika ada
if (!empty($query_params)) {
    // Menggunakan call_user_func_array untuk bind_param dengan array dinamis
    $stmt->bind_param($query_types, ...$query_params);
}

$stmt->execute();
$result = $stmt->get_result();
$laporan_harian_data = []; // Inisialisasi array untuk menampung semua data laporan

// Ambil semua hasil query
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $laporan_harian_data[] = $row;
    }
}
$stmt->close(); // Tutup statement

// --- Tentukan Informasi Header PDF (diambil dari data atau sesi) ---
$nama_peserta_didik_header = '-';
$dunia_kerja_tempat_pkl_header = '-';
$nama_instruktur_header = '-';
$nama_guru_pembimbing_header = '-';
$info_tambahan_pdf = []; // Untuk menampilkan info filter di PDF

// Atur info header berdasarkan data yang diambil atau peran
if (!empty($laporan_harian_data)) {
    $first_row = $laporan_harian_data[0];
    $nama_peserta_didik_header = htmlspecialchars($first_row['nama_siswa'] ?? '-');
    $dunia_kerja_tempat_pkl_header = htmlspecialchars($first_row['nama_tempat_pkl'] ?? '-');
    $nama_instruktur_header = htmlspecialchars($first_row['nama_instruktur_pkl'] ?? '-');
    $nama_guru_pembimbing_header = htmlspecialchars($first_row['nama_guru_pembimbing'] ?? '-');

    // Jika admin melihat semua data, ganti header spesifik siswa
    if ($is_admin && !isset($_GET['siswa_id'])) {
        $nama_peserta_didik_header = 'Seluruh Siswa';
        $dunia_kerja_tempat_pkl_header = 'Beragam';
        $nama_instruktur_header = 'Beragam';
        $nama_guru_pembimbing_header = 'Beragam';
    } elseif ($is_guru && !isset($_GET['siswa_id'])) {
        // Jika guru melihat semua siswa bimbingan
        $nama_peserta_didik_header = 'Siswa Bimbingan Anda';
        $dunia_kerja_tempat_pkl_header = 'Beragam';
        $nama_instruktur_header = 'Beragam';
    }
} else {
    // Jika tidak ada data laporan, coba ambil info dasar dari sesi/database
    if ($is_siswa && ($id_siswa = $_SESSION['id_siswa'] ?? null)) {
        $nama_peserta_didik_header = $_SESSION['siswa_nama'] ?? '-';
        $query_siswa_detail = "SELECT s.nama_siswa, tp.nama_tempat_pkl, tp.nama_instruktur AS nama_instruktur_pkl, gp.nama_pembimbing AS nama_guru_pembimbing FROM siswa s LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing WHERE s.id_siswa = ?";
        $stmt_siswa_detail = $koneksi->prepare($query_siswa_detail);
        if ($stmt_siswa_detail) {
            $stmt_siswa_detail->bind_param("i", $id_siswa);
            $stmt_siswa_detail->execute();
            $res_siswa_detail = $stmt_siswa_detail->get_result()->fetch_assoc();
            $dunia_kerja_tempat_pkl_header = htmlspecialchars($res_siswa_detail['nama_tempat_pkl'] ?? '-');
            $nama_instruktur_header = htmlspecialchars($res_siswa_detail['nama_instruktur_pkl'] ?? '-');
            $nama_guru_pembimbing_header = htmlspecialchars($res_siswa_detail['nama_guru_pembimbing'] ?? '-');
            $stmt_siswa_detail->close();
        }
    } elseif ($is_admin && isset($_GET['siswa_id']) && ($id_siswa_from_get = (int)$_GET['siswa_id'])) {
        $query_siswa_detail = "SELECT nama_siswa FROM siswa WHERE id_siswa = ?";
        $stmt_siswa_detail = $koneksi->prepare($query_siswa_detail);
        if ($stmt_siswa_detail) {
            $stmt_siswa_detail->bind_param("i", $id_siswa_from_get);
            $stmt_siswa_detail->execute();
            $res_siswa_detail = $stmt_siswa_detail->get_result()->fetch_assoc();
            $nama_peserta_didik_header = htmlspecialchars($res_siswa_detail['nama_siswa'] ?? '-') . ' (Tidak Ada Laporan)';
            $stmt_siswa_detail->close();
        }
    } elseif ($is_guru && isset($_GET['siswa_id']) && ($id_siswa_from_get = (int)$_GET['siswa_id'])) {
        $query_siswa_detail = "SELECT nama_siswa FROM siswa WHERE id_siswa = ?";
        $stmt_siswa_detail = $koneksi->prepare($query_siswa_detail);
        if ($stmt_siswa_detail) {
            $stmt_siswa_detail->bind_param("i", $id_siswa_from_get);
            $stmt_siswa_detail->execute();
            $res_siswa_detail = $stmt_siswa_detail->get_result()->fetch_assoc();
            $nama_peserta_didik_header = htmlspecialchars($res_siswa_detail['nama_siswa'] ?? '-') . ' (Tidak Ada Laporan)';
            $stmt_siswa_detail->close();
        }
    } else {
        // Default jika tidak ada data dan tidak ada spesifik siswa
        $nama_peserta_didik_header = 'Tidak Ada Data Laporan';
    }
}

// Tambahkan info filter ke header PDF
if (!empty($keyword)) {
    $info_tambahan_pdf[] = '<strong>Kata Kunci:</strong> "' . htmlspecialchars($keyword) . '"';
}
if (!empty($start_date) && !empty($end_date)) {
    $info_tambahan_pdf[] = '<strong>Rentang Tanggal:</strong> ' . date('d F Y', strtotime($start_date)) . ' - ' . date('d F Y', strtotime($end_date));
} elseif (!empty($start_date)) {
    $info_tambahan_pdf[] = '<strong>Dari Tanggal:</strong> ' . date('d F Y', strtotime($start_date));
} elseif (!empty($end_date)) {
    $info_tambahan_pdf[] = '<strong>Sampai Tanggal:</strong> ' . date('d F Y', strtotime($end_date));
}


$koneksi->close(); // Tutup koneksi database setelah semua data dan header disiapkan

// --- GENERASI KONTEN HTML untuk PDF ---
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Kegiatan PKL Harian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 25px; /* Sedikit lebih besar untuk tampilan formal */
            font-size: 9pt; /* Ukuran font dasar */
            color: #333;
        }
        h1 {
            text-align: left;
            color: #222;
            font-size: 16pt;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .lampiran-text {
            text-align: left;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #444;
        }
        .header-section {
            padding: 0;
            margin-bottom: 30px;
        }
        .header-info p {
            margin: 5px 0;
            line-height: 1.5;
            font-size: 9.5pt;
        }
        .header-info strong {
            display: inline-block;
            width: 180px; /* Lebar tetap untuk label */
            font-weight: bold;
            color: #555;
        }
        .header-info strong::after {
            content: " :"; /* Tambahkan spasi sebelum titik dua */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px 10px;
            text-align: left;
            font-size: 8.5pt;
            vertical-align: top;
            line-height: 1.4;
        }
        th {
            background-color: #e0e0e0;
            font-weight: bold;
            color: #444;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        .note-section {
            margin-top: 30px;
            border-top: 1px dashed #bbb;
            padding-top: 15px;
            font-size: 8.5pt;
            color: #666;
        }
        .note {
            margin-bottom: 5px;
        }
        .footer-timestamp {
            text-align: right;
            font-size: 7.5pt;
            color: #888;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="lampiran-text">LAMPIRAN 2</div>
    <h1>JURNAL KEGIATAN PKL</h1>
    <div class="header-section">
        <div class="header-info">
            <p><strong>Nama Peserta Didik</strong> ' . $nama_peserta_didik_header . '</p>
            <p><strong>Dunia Kerja Tempat PKL</strong> ' . $dunia_kerja_tempat_pkl_header . '</p>
            <p><strong>Nama Instruktur</strong> ' . $nama_instruktur_header . '</p>
            <p><strong>Nama Guru Pembimbing</strong> ' . $nama_guru_pembimbing_header . '</p>
            ' . (!empty($info_tambahan_pdf) ? '<p>' . implode('<br>', $info_tambahan_pdf) . '</p>' : '') . '
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Jurusan</th>
                <th>Hari/Tanggal</th>
                <th>Unit Kerja/Pekerjaan</th>
                <th>Catatan*</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
if (!empty($laporan_harian_data)) {
    foreach ($laporan_harian_data as $row) {
        $hari_indonesia = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $nama_hari_inggris = date('l', strtotime($row['tanggal']));
        $formatted_date_id = $hari_indonesia[$nama_hari_inggris] . ', ' . date('d F Y', strtotime($row['tanggal']));

        $html .= "<tr>
            <td>{$no}</td>
            <td>" . htmlspecialchars($row['nama_siswa'] ?? '-') . "</td>
            <td>" . htmlspecialchars($row['nama_jurusan'] ?? '-') . "</td>
            <td>" . htmlspecialchars($formatted_date_id) . "</td>
            <td>" . nl2br(htmlspecialchars($row['pekerjaan'] ?? '-')) . "</td>
            <td>" . nl2br(htmlspecialchars($row['catatan'] ?? '-')) . "</td>
          </tr>";
        $no++;
    }
} else {
    $html .= "<tr><td colspan='6' style='text-align: center; padding: 20px;'>Tidak ada laporan harian ditemukan untuk kriteria ini.</td></tr>";
}
$html .= '
        </tbody>
    </table>

    <div class="note-section">
        <p class="note">Jurnal kegiatan disusun oleh peserta didik sebagai dokumen pekerjaan yang dilaksanakan.</p>
        <p class="note">*) Catatan diberikan oleh pembimbing dunia kerja pada setiap kegiatan atau waktu tertentu.</p>
    </div>

    <div class="footer-timestamp">
        Laporan ini dibuat pada ' . date('d F Y H:i:s') . ' WIB.
    </div>
</body>
</html>';

// --- INISIALISASI DOMPDF DAN GENERASI PDF ---
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Aktifkan jika Anda punya gambar/CSS dari URL
$options->set('defaultFont', 'Arial'); // Atur font default

// Set base path untuk memuat sumber daya relatif (misal: gambar di folder yang sama)
// __DIR__ adalah direktori tempat file PHP saat ini berada
$options->set('chroot', realpath(__DIR__));

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); // Ukuran kertas A4, orientasi portrait
$dompdf->render();

// --- OUTPUT PDF ---
$filename = "Jurnal_Kegiatan_PKL_Harian_" . date('Ymd_His');
if (!empty($id_siswa_filter)) {
    $filename .= "_Siswa_" . $id_siswa_filter;
}
if (!empty($start_date) || !empty($end_date)) {
    $filename .= "_Periode";
    if (!empty($start_date)) $filename .= "_" . str_replace('-', '', $start_date);
    if (!empty($end_date)) $filename .= "_" . str_replace('-', '', $end_date);
}
$filename .= ".pdf";

$dompdf->stream($filename, ["Attachment" => false]); // Tampilkan di browser, tidak langsung diunduh
exit();
