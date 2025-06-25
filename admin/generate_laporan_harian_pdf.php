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
$filter_keyword = "";
if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    // Sesuaikan kolom pencarian sesuai kebutuhan di tabel jurnal harian Anda
    $filter_keyword = "AND (jh.pekerjaan LIKE '%$keyword%' OR jh.catatan LIKE '%$keyword%')";
}

// Asumsi: Anda akan mendapatkan id_siswa dari sesi setelah login.
// Jika ini adalah halaman yang bisa diakses admin/guru untuk melihat semua laporan,
// Anda bisa menghapus filter id_siswa di sini atau menambahkan kondisi lain.
// Untuk saat ini, kita anggap ini bisa mencetak laporan untuk semua siswa
// atau hanya siswa tertentu jika parameter id_siswa_filter ditambahkan di URL.

$query = "SELECT jh.id_jurnal_harian, jh.tanggal, jh.pekerjaan, jh.catatan, s.nama_siswa
          FROM jurnal_harian jh  -- Perubahan nama tabel di sini
          LEFT JOIN siswa s ON jh.siswa_id = s.id_siswa -- Perubahan kolom foreign key di sini
          WHERE 1=1  
          $filter_keyword
          ORDER BY jh.tanggal DESC, jh.id_jurnal_harian DESC"; // Urutkan berdasarkan tanggal terbaru

$result = mysqli_query($koneksi, $query);

// --- Hasilkan Konten HTML untuk PDF ---
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian Siswa PKL</title>
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
        h2 {
            font-size: 12pt;
            color: #555;
            margin-top: 15px;
            margin-bottom: 10px;
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
            vertical-align: top;
        }
        th { 
            background-color: #f2f2f2; 
            font-size: 8pt; 
        }
        .note {
            font-style: italic;
            color: #666;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Laporan Harian Kegiatan PKL</h1>';


$html .= '
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Hari/Tanggal</th>
                <th>Pekerjaan</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Format tanggal agar lebih mudah dibaca
        // Menggunakan kolom 'tanggal' dari tabel jurnal_harian
        $formatted_date = date('l, d F Y', strtotime($row['tanggal']));
        $hari_indonesia = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $nama_hari_inggris = date('l', strtotime($row['tanggal'])); // Menggunakan kolom 'tanggal'
        $formatted_date_id = $hari_indonesia[$nama_hari_inggris] . ', ' . date('d F Y', strtotime($row['tanggal'])); // Menggunakan kolom 'tanggal'

        $html .= "<tr>
                    <td>{$no}</td>
                    <td>" . htmlspecialchars($row['nama_siswa'] ?? '-') . "</td>
                    <td>" . htmlspecialchars($formatted_date_id) . "</td>
                    <td>" . nl2br(htmlspecialchars($row['pekerjaan'])) . "</td> 
                    <td>" . nl2br(htmlspecialchars($row['catatan'])) . "</td> </tr>";
        $no++;
    }
} else {
    $html .= "<tr><td colspan='5' style='text-align: center;'>Tidak ada laporan harian ditemukan.</td></tr>";
}

$html .= '
        </tbody>
    </table>
    <p class="note">Catatan: Laporan ini dibuat pada ' . date('d F Y H:i:s') . ' WIB.</p>
</body>
</html>';

// Muat HTML ke Dompdf
$dompdf->loadHtml($html);

// (Opsional) Atur ukuran dan orientasi kertas
$dompdf->setPaper('A4', 'portrait');

// Render HTML sebagai PDF
$dompdf->render();

// Keluarkan PDF yang dihasilkan ke Browser (untuk preview di tab baru)
$dompdf->stream("laporan_harian_siswa.pdf", array("Attachment" => false));

// Tutup koneksi database
mysqli_close($koneksi);