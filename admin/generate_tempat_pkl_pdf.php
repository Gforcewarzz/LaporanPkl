<?php
// Pastikan koneksi database tersedia
include 'partials/db.php';

// Sertakan Dompdf Autoloader (sesuaikan path jika perlu)
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
    $filter = "WHERE tp.nama_tempat_pkl LIKE '%$keyword%' 
                         OR tp.alamat LIKE '%$keyword%'
                         OR tp.alamat_kontak LIKE '%$keyword%'
                         OR tp.nama_instruktur LIKE '%$keyword%'";
}

$query = "SELECT tp.id_tempat_pkl, tp.nama_tempat_pkl, tp.alamat, tp.alamat_kontak, 
                 tp.nama_instruktur, tp.kuota_siswa, j.nama_jurusan 
          FROM tempat_pkl tp
          LEFT JOIN jurusan j ON tp.jurusan_id = j.id_jurusan
          $filter
          ORDER BY tp.id_tempat_pkl ASC";

$result = mysqli_query($koneksi, $query);

// --- Hasilkan Konten HTML untuk PDF ---
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tempat PKL</title>
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
            vertical-align: top; /* Agar konten panjang tidak merusak tata letak */
        }
        th { 
            background-color: #f2f2f2; 
            font-size: 8pt; 
        }
    </style>
</head>
<body>
    <h1>Data Tempat PKL</h1>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Perusahaan</th>
                <th>Alamat</th>
                <th>Kontak</th>
                <th>Instruktur</th>
                <th>Kuota</th>
                <th>Jurusan</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $html .= "<tr>
                    <td>{$no}</td>
                    <td>" . htmlspecialchars($row['nama_tempat_pkl']) . "</td>
                    <td>" . htmlspecialchars($row['alamat']) . "</td>
                    <td>" . htmlspecialchars($row['alamat_kontak']) . "</td>
                    <td>" . htmlspecialchars($row['nama_instruktur']) . "</td>
                    <td>" . htmlspecialchars($row['kuota_siswa']) . "</td>
                    <td>" . htmlspecialchars($row['nama_jurusan'] ?: '-') . "</td>
                  </tr>";
        $no++;
    }
} else {
    $html .= "<tr><td colspan='7' style='text-align: center;'>Tidak ada data tempat PKL ditemukan.</td></tr>";
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

// Keluarkan PDF yang dihasilkan ke Browser (untuk preview di tab baru)
$dompdf->stream("data_tempat_pkl.pdf", array("Attachment" => false));

// Tutup koneksi database
mysqli_close($koneksi);