<?php
$tasks = [
    1 => [
        'nama_peserta' => 'Budi Santoso',
        'dunia_kerja_tempat_pkl' => 'PT. Inovasi Digital (Software House)',
        'nama_instruktur' => 'Bpk. Joni Iskandar, S.T.',
        'nama_guru_pembimbing' => 'Ibu Endang Susanti, S.Kom., M.TI.',
        'nama_pekerjaan' => 'Pengembangan Modul Login Aplikasi Web',
        'perencanaan_kegiatan' => "1. Mempelajari alur autentikasi OAuth2.\n2. Merancang ERD untuk tabel user.\n3. Membuat wireframe halaman login & register.",
        'pelaksanaan_kegiatan' => "1. Implementasi frontend halaman login (HTML, CSS, JS).\n2. Integrasi API login dengan backend.\n3. Melakukan unit testing pada modul login dan registrasi.\n4. Menganalisis log error dan melakukan debugging.",
        'catatan_instruktur' => "Progres sangat memuaskan, Budi menunjukkan inisiatif tinggi dalam memahami teknologi baru. Kemampuan debugging baik. Pertahankan kualitas kode yang bersih.",
    ],
    2 => [
        'nama_peserta' => 'Citra Dewi',
        'dunia_kerja_tempat_pkl' => 'CV. Solusi Kreatif (Desain Grafis & Multimedia)',
        'nama_instruktur' => 'Ibu Maya Sari, S.Ds.',
        'nama_guru_pembimbing' => 'Bapak Anto Wijaya, M.Pd.',
        'nama_pekerjaan' => 'Desain Infografis Kampanye Lingkungan',
        'perencanaan_kegiatan' => "1. Riset data statistik tentang polusi plastik.\n2. Mengumpulkan referensi desain infografis yang efektif.\n3. Membuat sketsa layout dan memilih palet warna.",
        'pelaksanaan_kegiatan' => "1. Membuat 3 draf infografis menggunakan Adobe Illustrator.\n2. Melakukan revisi minor berdasarkan masukan tim kreatif.\n3. Menyiapkan aset final dalam format JPG dan PDF untuk web dan cetak.",
        'catatan_instruktur' => "Desain Citra sangat inovatif dan komunikatif. Pemilihan visual tepat sasaran. Perhatikan lagi konsistensi ukuran font di elemen kecil untuk keterbacaan optimal.",
    ],
    3 => [
        'nama_peserta' => 'Dani Permana',
        'dunia_kerja_tempat_pkl' => 'Bumi Digital Studio (Game Development)',
        'nama_instruktur' => 'Bpk. Asep Setiawan, S.Kom.',
        'nama_guru_pembimbing' => 'Ibu Siti Aminah, S.T.',
        'nama_pekerjaan' => 'Pengembangan Karakter 3D untuk Game Edukasi',
        'perencanaan_kegiatan' => "1. Studi referensi gaya visual game edukasi.\n2. Membuat konsep sketsa karakter (pose & ekspresi).\n3. Menentukan topologi mesh model 3D.",
        'pelaksanaan_kegiatan' => "1. Modeling dasar karakter di Blender.\n2. Texturing menggunakan Substance Painter.\n3. Rigging sederhana untuk pose awal karakter.\n4. Render preview karakter di karakter dari berbagai sudut.",
        'catatan_instruktur' => "Dani memiliki bakat kuat dalam modeling 3D. Perlu lebih banyak latihan dalam teknik retopologi untuk optimasi aset game. Tingkatkan kecepatan kerja.",
    ],
];

$taskId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$taskData = $tasks[$taskId] ?? null;

if (!$taskData) {
    echo "Data tugas tidak ditemukan.";
    exit;
}

$location = "Bandung";
$currentDate = date('d F Y', time());
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Tugas PKL: <?php echo htmlspecialchars($taskData['nama_pekerjaan']); ?></title>
    <style>
    body {
        font-family: 'Times New Roman', serif;
        line-height: 1.4;
        margin: 0;
        padding: 0;
        color: #000;
        font-size: 10.5pt;
    }

    .page {
        width: 210mm;
        min-height: 297mm;
        padding: 2.0cm;
        box-sizing: border-box;
    }

    .header {
        margin-bottom: 18pt;
    }

    .header h1,
    .header h2 {
        margin: 0;
        font-size: 13pt;
        line-height: 1.1;
        text-align: left;
        font-weight: normal;
    }

    .header h2 {
        margin-top: 3pt;
    }

    .info-section {
        margin-bottom: 18pt;
        border-bottom: 1px dashed #bbb;
        padding-bottom: 8pt;
    }

    .info-item {
        display: flex;
        margin-bottom: 2pt;
        font-size: 10.5pt;
        padding-left: 8pt;
    }

    .info-item strong {
        flex-shrink: 0;
        width: 135pt;
        margin-right: 4pt;
    }

    .info-item span {
        flex-grow: 1;
        text-align: left;
    }

    .section-block {
        margin-bottom: 15pt;
    }

    .section-title {
        font-weight: bold;
        margin-top: 10pt;
        margin-bottom: 4pt;
        text-decoration: none;
        font-size: 11.5pt;
    }

    .content-box {
        border: 1pt solid #000;
        padding: 7pt 9pt;
        min-height: 45pt;
        white-space: pre-line;
        word-wrap: break-word;
        text-align: justify;
        font-size: 10.5pt;
    }

    .small-note {
        font-size: 8.5pt;
        color: #555;
        margin-top: 1pt;
        text-align: left;
    }

    .signature-block {
        margin-top: 35pt;
        text-align: right;
        font-size: 10.5pt;
        line-height: 1.4;
    }

    .signature-block p {
        margin: 0;
    }

    .signature-line {
        display: block;
        width: 180pt;
        border-bottom: 1pt solid #000;
        margin: 50pt 0 4pt auto;
        text-align: center;
    }

    .signature-name-placeholder {
        margin-top: 1pt;
    }

    @page {
        size: A4 portrait;
        margin: 2.0cm;
    }

    @media print {
        body {
            background: none;
        }

        .page {
            border: none;
            box-shadow: none;
            margin: 0;
            padding: 0;
        }

        @page {
            margin: 2.0cm;
        }
    }
    </style>
</head>

<body>
    <div class="page">
        <div class="header">
            <h1>LAMPIRAN 3</h1>
            <h2>CATATAN KEGIATAN PKL</h2>
        </div>

        <div class="info-section">
            <div class="info-item">
                <strong>Nama Peserta Didik</strong> :
                <span><?php echo htmlspecialchars($taskData['nama_peserta']); ?></span>
            </div>
            <div class="info-item">
                <strong>Dunia Kerja Tempat PKL</strong> :
                <span><?php echo htmlspecialchars($taskData['dunia_kerja_tempat_pkl']); ?></span>
            </div>
            <div class="info-item">
                <strong>Nama Instruktur</strong> :
                <span><?php echo htmlspecialchars($taskData['nama_instruktur']); ?></span>
            </div>
            <div class="info-item">
                <strong>Nama Guru Pembimbing</strong> :
                <span><?php echo htmlspecialchars($taskData['nama_guru_pembimbing']); ?></span>
            </div>
        </div>

        <div class="section-block">
            <div class="section-title">A. Nama Pekerjaan</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($taskData['nama_pekerjaan'])); ?>
            </div>
        </div>

        <div class="section-block">
            <div class="section-title">B. Perencanaan Kegiatan</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($taskData['perencanaan_kegiatan'])); ?>
                <div class="small-note">(Jadwal kegiatan/dokumen perencanaan)</div>
            </div>
        </div>

        <div class="section-block">
            <div class="section-title">C. Pelaksanaan Kegiatan/Hasil</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($taskData['pelaksanaan_kegiatan'])); ?>
                <div class="small-note">(Uraian proses kerja dan foto hasil)</div>
            </div>
        </div>

        <div class="section-block">
            <div class="section-title">D. Catatan Instruktur</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($taskData['catatan_instruktur'])); ?>
            </div>
        </div>

        <div class="signature-block">
            <p><?php echo htmlspecialchars($location); ?>, <?php echo htmlspecialchars($currentDate); ?></p>
            <p>Tanda Tangan Instruktur</p>
            <div class="signature-line"></div>
            <p class="signature-name-placeholder">(................................................)</p>
        </div>
    </div>

    <script>
    window.onload = function() {
        window.print();
    };
    </script>
</body>

</html>