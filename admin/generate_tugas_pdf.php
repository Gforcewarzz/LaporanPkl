<?php
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Data dummy by ID (bisa disesuaikan kalau mau looping siswa)
$taskId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$nama_peserta = 'Budi Santoso';
$dunia_kerja_tempat_pkl = 'PT. Inovasi Digital (Software House)';
$nama_instruktur = 'Bpk. Joni Iskandar, S.T.';
$nama_guru_pembimbing = 'Ibu Endang Susanti, S.Kom., M.TI.';
$nama_pekerjaan = 'Pengembangan Modul Login Aplikasi Web';
$perencanaan_kegiatan = "1. Mempelajari alur autentikasi OAuth2.\n2. Merancang ERD untuk tabel user.\n3. Membuat wireframe halaman login & register.";
$pelaksanaan_kegiatan = "1. Implementasi frontend halaman login (HTML, CSS, JS).\n2. Integrasi API login dengan backend.\n3. Melakukan unit testing pada modul login dan registrasi.\n4. Menganalisis log error dan melakukan debugging.";
$catatan_instruktur = "Progres sangat memuaskan, Budi menunjukkan inisiatif tinggi dalam memahami teknologi baru. Kemampuan debugging baik. Pertahankan kualitas kode yang bersih.";
$location = 'Bandung';
$tanggal = date('d F Y');

// HTML + CSS Clean Center Layout
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan PKL</title>
    <style>
       body {
    font-family: "Times New Roman", serif;
    font-size: 9pt;
    margin: 0;
    padding: 0;
}

      .page {
    width: 17cm; /* Ini kunci utama */
    margin: 2cm auto; /* Atas-bawah 2cm, kiri-kanan auto biar tengah */
}
        h1, h2 {
            margin: 0;
            font-weight: normal;
        }

        h1 {
            font-size: 11pt;
        }

        h2 {
            font-size: 10pt;
            margin-bottom: 10pt;
        }

        .info-section {
            margin-bottom: 12pt;
            padding-bottom: 6pt;
            border-bottom: 1px dashed #999;
        }

        .info-item {
            margin-bottom: 4pt;
        }

        .info-item strong {
            display: inline-block;
            width: 140pt;
        }

        .section-block {
            margin-bottom: 12pt;
        }

        .section-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 4pt;
        }

        .content-box {
            border: 1pt solid #000;
            padding: 6pt 8pt;
            white-space: pre-line;
            background-color: #fff;
        }

        .small-note {
            font-size: 7pt;
            color: #666;
            margin-top: 4pt;
        }

        .signature-block {
            margin-top: 30pt;
            text-align: right;
        }

        .signature-line {
            display: inline-block;
            width: 180pt;
            border-bottom: 1pt solid #000;
            margin-top: 30pt;
            margin-bottom: 5pt;
        }

        .signature-name-placeholder {
            margin-top: 2pt;
        }
    </style>
</head>
<body>
    <div class="page">
        <h1>LAMPIRAN 3</h1>
        <h2>CATATAN KEGIATAN PKL</h2>

        <div class="info-section">
            <div class="info-item"><strong>Nama Peserta Didik</strong>: ' . htmlspecialchars($nama_peserta) . '</div>
            <div class="info-item"><strong>Dunia Kerja Tempat PKL</strong>: ' . htmlspecialchars($dunia_kerja_tempat_pkl) . '</div>
            <div class="info-item"><strong>Nama Instruktur</strong>: ' . htmlspecialchars($nama_instruktur) . '</div>
            <div class="info-item"><strong>Nama Guru Pembimbing</strong>: ' . htmlspecialchars($nama_guru_pembimbing) . '</div>
        </div>

        <div class="section-block">
            <div class="section-title">A. Nama Pekerjaan</div>
            <div class="content-box">' . nl2br(htmlspecialchars($nama_pekerjaan)) . '</div>
        </div>

        <div class="section-block">
            <div class="section-title">B. Perencanaan Kegiatan</div>
            <div class="content-box">' . nl2br(htmlspecialchars($perencanaan_kegiatan)) . '<div class="small-note">(Jadwal kegiatan/dokumen perencanaan)</div></div>
        </div>

        <div class="section-block">
            <div class="section-title">C. Pelaksanaan Kegiatan/Hasil</div>
            <div class="content-box">' . nl2br(htmlspecialchars($pelaksanaan_kegiatan)) . '<div class="small-note">(Uraian proses kerja dan foto hasil)</div></div>
        </div>

        <div class="section-block">
            <div class="section-title">D. Catatan Instruktur</div>
            <div class="content-box">' . nl2br(htmlspecialchars($catatan_instruktur)) . '</div>
        </div>

        <div class="signature-block">
            <p>' . $location . ', ' . $tanggal . '</p>
            <p>Tanda Tangan Instruktur</p>
            <div class="signature-line"></div>
            <p class="signature-name-placeholder">(................................................)</p>
        </div>
    </div>
</body>
</html>
';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'Times New Roman');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = "Laporan_Tugas_PKL_" . str_replace(' ', '_', $nama_pekerjaan) . "_ID" . $taskId . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]);
exit;