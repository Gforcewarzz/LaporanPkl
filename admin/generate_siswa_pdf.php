<?php
// Include your database connection and any necessary partials
include 'partials/db.php';

// Include Dompdf Autoloader (adjust path if necessary)
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Instantiate Dompdf with options
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Enable if you have external images or CSS
$dompdf = new Dompdf($options);

// --- Fetch Data from Database (similar to your existing table query) ---
$filter = "";
if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    $filter = "WHERE siswa.nama_siswa LIKE '%$keyword%' 
                     OR siswa.no_induk LIKE '%$keyword%'
                     OR siswa.jenis_kelamin LIKE '%$keyword%'
                     OR siswa.kelas LIKE '%$keyword%'";
}

$query = "SELECT 
              siswa.id_siswa,
              siswa.nama_siswa,
              siswa.jenis_kelamin,
              siswa.no_induk,
              siswa.kelas,
              siswa.status,
              jurusan.nama_jurusan,
              guru_pembimbing.nama_pembimbing,
              tempat_pkl.nama_tempat_pkl
          FROM siswa
          LEFT JOIN jurusan ON siswa.jurusan_id = jurusan.id_jurusan
          LEFT JOIN guru_pembimbing ON siswa.pembimbing_id = guru_pembimbing.id_pembimbing
          LEFT JOIN tempat_pkl ON siswa.tempat_pkl_id = tempat_pkl.id_tempat_pkl
          $filter
          ORDER BY siswa.id_siswa ASC";

$result = mysqli_query($koneksi, $query);

// --- Generate HTML Content for PDF ---
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa PKL</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            font-size: 8pt; /* Reduced font size for the whole body */
        }
        h1 { 
            text-align: center; 
            color: #333; 
            font-size: 14pt; /* Adjusted heading size */
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; /* Slightly reduced margin */
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 6px; /* Reduced padding for smaller cells */
            text-align: left; 
            font-size: 8pt; /* Explicitly set font size for table cells */
        }
        th { 
            background-color: #f2f2f2; 
            font-size: 8pt; /* Explicitly set font size for table headers */
        }
        /* Removed .badge styles as status will be plain text */
    </style>
</head>
<body>
    <h1>Data Siswa Peserta PKL</h1>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Jenis Kelamin</th>
                <th>No Induk</th>
                <th>Kelas</th>
                <th>Jurusan</th>
                <th>Guru Pendamping</th>
                <th>Tempat PKL</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    // Removed $badgeColor and the <span> with badge classes
    $html .= "<tr>
                <td>{$no}</td>
                <td>{$row['nama_siswa']}</td>
                <td>{$row['jenis_kelamin']}</td>
                <td>{$row['no_induk']}</td>
                <td>{$row['kelas']}</td>
                <td>{$row['nama_jurusan']}</td>
                <td>{$row['nama_pembimbing']}</td>
                <td>{$row['nama_tempat_pkl']}</td>
                <td>{$row['status']}</td> </tr>";
    $no++;
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// Load HTML to Dompdf
$dompdf->loadHtml($html);

// (Optional) Set paper size and orientation
$dompdf->setPaper('A4', 'portrait'); // or 'portrait'

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream("data_siswa_pkl.pdf", array("Attachment" => false));

// Close database connection
mysqli_close($koneksi);