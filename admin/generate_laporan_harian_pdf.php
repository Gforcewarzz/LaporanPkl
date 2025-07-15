<?php
// Pastikan koneksi database tersedia
include 'partials/db.php'; // Make sure this path is correct and db.php establishes $koneksi

// Start the session (if not already started)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sertakan Dompdf Autoloader (sesuaikan path jika perlu)
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- Inisialisasi Peran Pengguna ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// --- Logika Keamanan: Redirect jika tidak ada peran yang diizinkan ---
if (!$is_siswa && !$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// --- Inisialisasi Variabel Filter ---
$id_siswa_filter = null;
$guru_id_bimbingan = $_SESSION['id_guru_pendamping'] ?? null;
$filter_keyword = "";
$where_clauses = [];
$params = [];
$types = "";

// --- Tentukan Kondisi WHERE Clause Berdasarkan Peran dan Parameter URL ---
if ($is_siswa) {
    $id_siswa_filter = $_SESSION['id_siswa'] ?? null;
    if ($id_siswa_filter) {
        $where_clauses[] = "jh.siswa_id = ?";
        $params[] = $id_siswa_filter;
        $types .= "i";
    }
} elseif ($is_admin) {
    // Admin bisa mencetak laporan siswa spesifik (jika ada siswa_id di URL)
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $id_siswa_filter = (int)$_GET['siswa_id'];
        $where_clauses[] = "jh.siswa_id = ?";
        $params[] = $id_siswa_filter;
        $types .= "i";
    }
    // Jika tidak ada siswa_id di URL, admin melihat semua laporan (tidak perlu tambahan where_clause)
} elseif ($is_guru) {
    // Guru hanya bisa mencetak laporan siswa bimbingannya
    if ($guru_id_bimbingan) {
        $where_clauses[] = "s.pembimbing_id = ?";
        $params[] = $guru_id_bimbingan;
        $types .= "i";
    }
    // Jika guru ingin mencetak laporan siswa spesifik dari bimbingannya
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $id_siswa_filter = (int)$_GET['siswa_id'];
        $where_clauses[] = "jh.siswa_id = ?";
        $params[] = $id_siswa_filter;
        $types .= "i";
    }
}

// --- Tambahkan Filter Keyword jika ada ---
if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']);
    $where_clauses[] = "(jh.pekerjaan LIKE ? OR jh.catatan LIKE ?)";
    $params[] = "%" . $keyword . "%";
    $params[] = "%" . $keyword . "%";
    $types .= "ss";
}

// --- Bangun Query Utama ---
$query_base = "
   SELECT
    jh.id_jurnal_harian, jh.tanggal, jh.pekerjaan, jh.catatan,
    s.nama_siswa, s.kelas, s.no_induk,
    j.nama_jurusan, -- ← ini penting!
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
    jurusan j ON s.jurusan_id = j.id_jurusan
";

// Tambahkan WHERE clause
if (!empty($where_clauses)) {
    $query_base .= " WHERE " . implode(" AND ", $where_clauses);
}

$query_base .= " ORDER BY jh.tanggal DESC, jh.id_jurnal_harian DESC";

$stmt = $koneksi->prepare($query_base);

if ($stmt === false) {
    die("Error preparing statement: " . $koneksi->error);
}

// Bind parameter jika ada
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$laporan_harian_data = []; // Inisialisasi array untuk menampung semua data laporan
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $laporan_harian_data[] = $row;
    }
}
$stmt->close();

// --- Tentukan Info Header PDF Berdasarkan Data yang Diperoleh ---
$nama_peserta_didik_header = '-';
$dunia_kerja_tempat_pkl_header = '-';
$nama_instruktur_header = '-';
$nama_guru_pembimbing_header = '-';

// Jika ada data laporan, ambil info dari baris pertama (asumsi info header sama untuk semua baris)
if (!empty($laporan_harian_data)) {
    $first_row = $laporan_harian_data[0];
    $nama_peserta_didik_header = htmlspecialchars($first_row['nama_siswa'] ?? '-');
    $dunia_kerja_tempat_pkl_header = htmlspecialchars($first_row['nama_tempat_pkl'] ?? '-');
    $nama_instruktur_header = htmlspecialchars($first_row['nama_instruktur_pkl'] ?? '-');
    $nama_guru_pembimbing_header = htmlspecialchars($first_row['nama_guru_pembimbing'] ?? '-');

    // Jika admin melihat semua data, ubah nama peserta menjadi "Seluruh Siswa"
    if ($is_admin && $id_siswa_filter === null) {
        $nama_peserta_didik_header = 'Seluruh Siswa';
        $dunia_kerja_tempat_pkl_header = 'Beragam'; // Atau biarkan kosong/default
        $nama_instruktur_header = 'Beragam'; // Atau biarkan kosong/default
        $nama_guru_pembimbing_header = 'Beragam'; // Atau biarkan kosong/default
    } elseif ($is_guru && $id_siswa_filter === null) {
        $nama_peserta_didik_header = 'Siswa Bimbingan Anda';
        $dunia_kerja_tempat_pkl_header = 'Beragam'; // Atau biarkan kosong/default
        $nama_instruktur_header = 'Beragam'; // Atau biarkan kosong/default
    }
} else {
    // Jika tidak ada data ditemukan, sesuaikan header
    if ($is_siswa) {
        $nama_peserta_didik_header = $_SESSION['siswa_nama'] ?? '-';
        // Coba ambil detail PKL siswa jika tidak ada laporan
        $query_siswa_detail = "SELECT s.nama_siswa, tp.nama_tempat_pkl, tp.nama_instruktur AS nama_instruktur_pkl, gp.nama_pembimbing AS nama_guru_pembimbing FROM siswa s LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing WHERE s.id_siswa = ?";
        $stmt_siswa_detail = $koneksi->prepare($query_siswa_detail);
        if ($stmt_siswa_detail && ($_SESSION['id_siswa'] ?? null)) {
            $stmt_siswa_detail->bind_param("i", $_SESSION['id_siswa']);
            $stmt_siswa_detail->execute();
            $res_siswa_detail = $stmt_siswa_detail->get_result()->fetch_assoc();
            $dunia_kerja_tempat_pkl_header = htmlspecialchars($res_siswa_detail['nama_tempat_pkl'] ?? '-');
            $nama_instruktur_header = htmlspecialchars($res_siswa_detail['nama_instruktur_pkl'] ?? '-');
            $nama_guru_pembimbing_header = htmlspecialchars($res_siswa_detail['nama_guru_pembimbing'] ?? '-');
            $stmt_siswa_detail->close();
        }
    } elseif ($is_admin && $id_siswa_filter === null) {
        $nama_peserta_didik_header = 'Seluruh Siswa (Tidak Ada Laporan)';
    } elseif ($is_admin && $id_siswa_filter !== null) {
        $query_siswa_detail = "SELECT nama_siswa FROM siswa WHERE id_siswa = ?";
        $stmt_siswa_detail = $koneksi->prepare($query_siswa_detail);
        if ($stmt_siswa_detail && $id_siswa_filter) {
            $stmt_siswa_detail->bind_param("i", $id_siswa_filter);
            $stmt_siswa_detail->execute();
            $res_siswa_detail = $stmt_siswa_detail->get_result()->fetch_assoc();
            $nama_peserta_didik_header = htmlspecialchars($res_siswa_detail['nama_siswa'] ?? '-') . ' (Tidak Ada Laporan)';
            $stmt_siswa_detail->close();
        }
    } elseif ($is_guru && $id_siswa_filter === null) { // Guru melihat semua siswa bimbingan
        $nama_peserta_didik_header = 'Siswa Bimbingan Anda (Tidak Ada Laporan)';
    } elseif ($is_guru && $id_siswa_filter !== null) { // Guru melihat siswa bimbingan spesifik
        $query_siswa_detail = "SELECT nama_siswa FROM siswa WHERE id_siswa = ?";
        $stmt_siswa_detail = $koneksi->prepare($query_siswa_detail);
        if ($stmt_siswa_detail && $id_siswa_filter) {
            $stmt_siswa_detail->bind_param("i", $id_siswa_filter);
            $stmt_siswa_detail->execute();
            $res_siswa_detail = $stmt_siswa_detail->get_result()->fetch_assoc();
            $nama_peserta_didik_header = htmlspecialchars($res_siswa_detail['nama_siswa'] ?? '-') . ' (Tidak Ada Laporan)';
            $stmt_siswa_detail->close();
        }
    }
}


// --- Hasilkan Konten HTML untuk PDF ---
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Kegiatan PKL Harian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 25px; /* Slightly larger margins for a formal look */
            font-size: 9pt; /* Slightly larger base font */
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
            width: 180px; /* Lebar tetap untuk teks label */
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
            <td>" . htmlspecialchars($row['nama_siswa']) . "</td>
            <td>" . htmlspecialchars($row['nama_jurusan']) . "</td>
            <td>" . htmlspecialchars($formatted_date_id) . "</td>
            <td>" . nl2br(htmlspecialchars($row['pekerjaan'])) . "</td>
            <td>" . nl2br(htmlspecialchars($row['catatan'])) . "</td>
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

// --- INISIALISASI DOMPDF DISINI, SETELAH SEMUA HTML SIAP ---
// Inisiasi Dompdf dengan opsi
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Aktifkan jika ada gambar eksternal atau CSS
$options->set('defaultFont', 'Arial'); // Set a default font

// Jika gambar Anda ada di folder 'images' yang relatif terhadap generate_laporan_harian_pdf.php
// Pastikan path ini benar agar gambar bisa dimuat di PDF
// Misalnya, jika 'images' ada di folder yang sama dengan file PDF ini (admin/)
$options->set('chroot', realpath(__DIR__));


$dompdf = new Dompdf($options);


// Muat HTML ke Dompdf
$dompdf->loadHtml($html);

// (Opsional) Atur ukuran dan orientasi kertas
$dompdf->setPaper('A4', 'portrait');

// Render HTML sebagai PDF
$dompdf->render();

// Keluarkan PDF yang dihasilkan ke Browser (untuk preview di tab baru)
$dompdf->stream("jurnal_kegiatan_pkl_harian.pdf", array("Attachment" => false));

// Tutup koneksi database
mysqli_close($koneksi);