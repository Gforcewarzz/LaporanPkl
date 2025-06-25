<?php
// Pastikan koneksi database tersedia
include 'partials/db.php';

// Sertakan Dompdf Autoloader (sesuaikan path jika perlu)
// Asumsi Dompdf terinstal via Composer di folder 'vendor'
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Inisiasi Dompdf dengan opsi
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Aktifkan jika ada gambar eksternal atau CSS

$dompdf = new Dompdf($options);

// --- Ambil Data dari Database ---
$filter = "";
if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    $filter = "WHERE nama_pembimbing LIKE '%$keyword%' OR nip LIKE '%$keyword%'";
}

$query = "SELECT id_pembimbing, nama_pembimbing, nip, password 
          FROM guru_pembimbing
          $filter
          ORDER BY id_pembimbing ASC";

$result = mysqli_query($koneksi, $query);

// --- Hasilkan Konten HTML untuk PDF ---
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru Pendamping PKL</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            font-size: 8pt; 
        }
        h1 { 
            text-align: center; 
            color: #333; 
            font-size: 14pt; 
            margin-bottom: 20px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 6px; 
            text-align: left; 
            font-size: 8pt; 
        }
        th { 
            background-color: #f2f2f2; 
            font-size: 8pt; 
        }
    </style>
</head>
<body>
    <h1>Data Guru Pendamping PKL</h1>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Guru</th>
                <th>NIP</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $html .= "<tr>
                    <td>{$no}</td>
                    <td>" . htmlspecialchars($row['nama_pembimbing']) . "</td>
                    <td>" . htmlspecialchars($row['nip']) . "</td>
                  </tr>";
        $no++;
    }
} else {
    $html .= "<tr><td colspan='4' style='text-align: center;'>Tidak ada data guru pendamping ditemukan.</td></tr>";
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// Muat HTML ke Dompdf
$dompdf->loadHtml($html);

// (Opsional) Atur ukuran dan orientasi kertas
$dompdf->setPaper('A4', 'portrait');

// Render HTML sebagai PDF
$dompdf->render();

// Keluarkan PDF yang dihasilkan ke Browser sebagai unduhan
$dompdf->stream("data_guru_pendamping.pdf", array("Attachment" => false));

// Tutup koneksi database
mysqli_close($koneksi);