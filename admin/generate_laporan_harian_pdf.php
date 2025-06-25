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

// Inisiasi Dompdf dengan opsi
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Aktifkan jika ada gambar eksternal atau CSS
$options->set('defaultFont', 'Arial'); // Set a default font

$dompdf = new Dompdf($options);

// --- Ambil Data dari Database ---
$filter_keyword = "";
if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    // Sesuaikan kolom pencarian sesuai kebutuhan di tabel jurnal harian Anda
    $filter_keyword = "AND (jh.pekerjaan LIKE '%$keyword%' OR jh.catatan LIKE '%$keyword%')";
}

// --- Fetch Header Information based on Session ---
$nama_peserta_didik = $_SESSION['siswa_nama'] ?? '-'; // Directly use from session for efficiency
$dunia_kerja_tempat_pkl = '-';
$nama_instruktur = '-';
$nama_guru_pembimbing = '-';

if (isset($_SESSION['id_siswa']) && $_SESSION['siswa_status_login'] == 'logged_in') {
    $id_siswa_session = mysqli_real_escape_string($koneksi, $_SESSION['id_siswa']);

    $query_header = "
        SELECT
            s.nama_siswa,
            tp.nama_tempat_pkl,
            tp.nama_instruktur AS nama_instruktur_tempat_pkl,
            gp.nama_pembimbing AS nama_guru_pembimbing
        FROM
            siswa s
        LEFT JOIN
            tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
        LEFT JOIN
            guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
        WHERE
            s.id_siswa = '$id_siswa_session'
        LIMIT 1
    ";

    $result_header = mysqli_query($koneksi, $query_header);

    if ($result_header && mysqli_num_rows($result_header) > 0) {
        $header_data = mysqli_fetch_assoc($result_header);
        $dunia_kerja_tempat_pkl = htmlspecialchars($header_data['nama_tempat_pkl'] ?? '-');
        $nama_instruktur = htmlspecialchars($header_data['nama_instruktur_tempat_pkl'] ?? '-');
        $nama_guru_pembimbing = htmlspecialchars($header_data['nama_guru_pembimbing'] ?? '-');
    }
} else {
    // If session ID is not set or not logged in, provide a default for query to prevent error
    $id_siswa_session = 0; // Use an ID that won't match any real student
}


// Adjust the main query to filter by the session student ID
$query = "SELECT jh.id_jurnal_harian, jh.tanggal, jh.pekerjaan, jh.catatan
          FROM jurnal_harian jh
          WHERE jh.siswa_id = '$id_siswa_session' "; // Always filter by logged-in student

$query .= "$filter_keyword
          ORDER BY jh.tanggal DESC, jh.id_jurnal_harian DESC"; // Urutkan berdasarkan tanggal terbaru

$result = mysqli_query($koneksi, $query);


// --- Hasilkan Konten HTML untuk PDF ---
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Kegiatan PKL Siswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 25px; /* Slightly larger margins for a formal look */
            font-size: 9pt; /* Slightly larger base font */
            color: #333;
        }
        /* MODIFIED: Left-align headers */
        h1 {
            text-align: left; /* Changed from center to left */
            color: #222;
            font-size: 16pt;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        /* MODIFIED: Left-align lampiran-text */
        .lampiran-text {
            text-align: left; /* Changed from center to left */
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
            width: 180px;
            font-weight: bold;
            color: #555;
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
    <h1>JURNAL KEGIATAN PKL</h1> <div class="header-section">
        <div class="header-info">
            <p><strong>Nama Peserta Didik</strong> : ' . $nama_peserta_didik . '</p>
            <p><strong>Dunia Kerja Tempat PKL</strong> : ' . $dunia_kerja_tempat_pkl . '</p>
            <p><strong>Nama Instruktur</strong> : ' . $nama_instruktur . '</p>
            <p><strong>Nama Guru Pembimbing</strong> : ' . $nama_guru_pembimbing . '</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Hari/Tanggal</th>
                <th>Unit Kerja/Pekerjaan</th>
                <th>Catatan*</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Format tanggal agar lebih mudah dibaca
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
                    <td>" . htmlspecialchars($formatted_date_id) . "</td>
                    <td>" . nl2br(htmlspecialchars($row['pekerjaan'])) . "</td>
                    <td>" . nl2br(htmlspecialchars($row['catatan'])) . "</td>
                  </tr>";
        $no++;
    }
} else {
    $html .= "<tr><td colspan='4' style='text-align: center; padding: 20px;'>Tidak ada laporan harian ditemukan untuk siswa ini.</td></tr>";
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

// Muat HTML ke Dompdf
$dompdf->loadHtml($html);

// (Opsional) Atur ukuran dan orientasi kertas
$dompdf->setPaper('A4', 'portrait');

// Render HTML sebagai PDF
$dompdf->render();

// Keluarkan PDF yang dihasilkan ke Browser (untuk preview di tab baru)
$dompdf->stream("jurnal_kegiatan_pkl.pdf", array("Attachment" => false));

// Tutup koneksi database
mysqli_close($koneksi);
