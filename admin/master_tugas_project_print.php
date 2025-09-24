<?php

session_start();
date_default_timezone_set('Asia/Jakarta');

require_once 'partials/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$id_jurnal_kegiatan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$laporan_data = null;

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Logika keamanan: Redirect jika tidak ada peran yang diizinkan
if (!$is_siswa && !$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// Data lokasi untuk tanda tangan (sekarang selalu titik-titik)
$location = "..................................................";


// Ambil data laporan dari database jika ID valid
if ($id_jurnal_kegiatan > 0) {
    $sql = "SELECT
                jk.id_jurnal_kegiatan,
                jk.nama_pekerjaan,
                jk.perencanaan_kegiatan,
                jk.pelaksanaan_kegiatan,
                jk.catatan_instruktur,
                jk.gambar,
                jk.tanggal_laporan,
                jk.siswa_id,

                s.nama_siswa AS nama_peserta,
                s.kelas AS kelas_siswa,
                s.no_induk AS no_induk_siswa,
                s.pembimbing_id AS id_guru_pembimbing_siswa,

                tp.nama_tempat_pkl AS dunia_kerja_tempat_pkl,
                tp.alamat AS alamat_tempat_pkl,
                tp.alamat_kontak AS kontak_tempat_pkl,
                tp.nama_instruktur AS nama_instruktur,

                gp.nama_pembimbing AS nama_guru_pembimbing
            FROM
                jurnal_kegiatan jk
            LEFT JOIN
                siswa s ON jk.siswa_id = s.id_siswa
            LEFT JOIN
                tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
            LEFT JOIN
                guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
            WHERE
                jk.id_jurnal_kegiatan = ?";

    $stmt = $koneksi->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id_jurnal_kegiatan);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $laporan_data = $result->fetch_assoc();

            // Logika otorisasi
            $authorized_to_view = false;
            if ($is_siswa && ($laporan_data['siswa_id'] == ($_SESSION['id_siswa'] ?? null))) {
                $authorized_to_view = true;
            } elseif ($is_admin) {
                $authorized_to_view = true;
            } elseif ($is_guru && ($laporan_data['id_guru_pembimbing_siswa'] == ($_SESSION['id_guru_pendamping'] ?? null))) {
                $authorized_to_view = true;
            }


            if (!$authorized_to_view) {
                $redirect_url_on_fail = 'master_tugas_project.php';
                echo "<!DOCTYPE html><html><body><script>alert('Akses Ditolak!'); window.location.href = '{$redirect_url_on_fail}';</script></body></html>";
                exit();
            }
        } else {
            echo "<!DOCTYPE html><html><body><script>alert('Laporan tidak ditemukan!'); window.location.href = 'master_tugas_project.php';</script></body></html>";
            exit();
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement for print: " . $koneksi->error);
        echo "<!DOCTYPE html><html><body><script>alert('Terjadi kesalahan sistem.'); window.location.href = 'master_tugas_project.php';</script></body></html>";
        exit();
    }
} else {
    echo "<!DOCTYPE html><html><body><script>alert('ID Laporan tidak valid.'); window.location.href = 'master_tugas_project.php';</script></body></html>";
    exit();
}

// Tanggal laporan diambil dari database
$currentDate = date('d F Y', strtotime($laporan_data['tanggal_laporan']));
$koneksi->close();

ob_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Tugas PKL: <?php echo htmlspecialchars($laporan_data['nama_pekerjaan']); ?></title>
    <style>
    body {
        font-family: 'Times New Roman', serif;
        line-height: 1.35;
        margin: 0;
        padding: 0;
        color: #000;
        font-size: 10pt;
    }

    .page {
        width: 178mm;
        margin: 0 auto;
        min-height: 297mm;
        padding: 0;
        box-sizing: border-box;
        page-break-after: auto;
    }

    .header {
        margin-bottom: 12pt;
        text-align: center;
    }

    .header h1 {
        font-size: 16pt;
        font-weight: bold;
        margin-bottom: 5px;
        text-transform: uppercase;
        color: #444;
    }

    .header h2 {
        font-size: 12pt;
        margin-top: 0;
        color: #666;
        line-height: 1.1;
    }

    /* [REVISI] CSS Tabel Info: border-bottom dihapus */
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12pt;
    }

    .info-table td {
        font-size: 10pt;
        padding: 2px 4px 2px 0;
        vertical-align: top;
    }

    .info-table td.label {
        width: 130pt;
        font-weight: bold;
        color: #444;
    }

    .section-block {
        margin-bottom: 10pt;
        page-break-inside: avoid;
    }

    .section-title {
        font-weight: bold;
        margin-top: 7pt;
        margin-bottom: 2pt;
        font-size: 11pt;
    }

    .content-box {
        border: 1pt solid #000;
        padding: 5pt 7pt;
        min-height: 35pt;
        white-space: pre-line;
        word-wrap: break-word;
        text-align: justify;
    }

    .small-note {
        font-size: 7.5pt;
        color: #555;
        margin-top: 1pt;
        text-align: left;
    }

    .image-container {
        text-align: center;
        margin-top: 7pt;
        margin-bottom: 10pt;
        border: 1px solid #ddd;
        padding: 3pt;
        page-break-inside: avoid;
    }

    .image-container img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
        max-height: 85mm;
        object-fit: contain;
    }

    .signature-block {
        margin-top: 5mm;
        padding-top: 0;
        font-size: 10pt;
        line-height: 1.3;
        page-break-inside: avoid;
    }

    .signature-block table {
        width: 100%;
    }

    .signature-block .right-align {
        width: 40%;
        text-align: left;
    }

    .signature-block p {
        margin: 0;
    }

    .section-block.catatan-instruktur-section {
        page-break-before: always;
    }

    @page {
        size: A4 portrait;
        margin: 1.6cm;
    }
    </style>
</head>

<body>
    <div class="page">
        <div class="header">
            <h1>CATATAN KEGIATAN PKL</h1>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Nama Peserta Didik</td>
                <td>: <?php echo htmlspecialchars($laporan_data['nama_peserta'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="label">Dunia Kerja Tempat PKL</td>
                <td>: <?php echo htmlspecialchars($laporan_data['dunia_kerja_tempat_pkl'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="label">Pembimbing Dunia Kerja</td>
                <td>: <?php echo htmlspecialchars($laporan_data['nama_instruktur'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="label">Guru Pembimbing Sekolah</td>
                <td>: <?php echo htmlspecialchars($laporan_data['nama_guru_pembimbing'] ?? '-'); ?></td>
            </tr>
        </table>

        <div class="section-block">
            <div class="section-title">A. Nama Pekerjaan</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($laporan_data['nama_pekerjaan'] ?? '-')); ?>
            </div>
        </div>

        <div class="section-block">
            <div class="section-title">B. Perencanaan Kegiatan</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($laporan_data['perencanaan_kegiatan'] ?? '-')); ?>
                <div class="small-note">(Jadwal kegiatan/dokumen perencanaan)</div>
            </div>
        </div>

        <div class="section-block">
            <div class="section-title">C. Pelaksanaan Kegiatan/Hasil</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($laporan_data['pelaksanaan_kegiatan'] ?? '-')); ?>
                <div class="small-note">(Uraian proses kerja dan foto hasil)</div>
            </div>
            <?php if (!empty($laporan_data['gambar'])): ?>
            <div class="image-container">
                <img src="images/<?php echo htmlspecialchars($laporan_data['gambar']); ?>" alt="Bukti Kegiatan">
                <div class="small-note" style="text-align: center; margin-top: 3pt;">(Bukti Kegiatan Visual)</div>
            </div>
            <?php endif; ?>
        </div>

        <div class="section-block catatan-instruktur-section">
            <div class="section-title">D. Catatan Instruktur</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($laporan_data['catatan_instruktur'] ?? '-')); ?>
            </div>
        </div>

        <div class="signature-block">
            <table>
                <tr>
                    <td></td>
                    <td class="right-align" style="padding-left: 20px;">
                        <p>...................., <?php echo htmlspecialchars($currentDate); ?></p>
                        <p style="margin-top: 2cm;">Pembimbing Dunia Kerja</p>
                        <p style="margin-top: 70pt;">(................................................)</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
<?php
// Ambil konten HTML yang sudah di-buffer
$html = ob_get_clean();

// Instansiasi dan konfigurasi Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('chroot', realpath(__DIR__));

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$filename = 'Laporan_Tugas_Proyek_' . preg_replace("/[^a-zA-Z0-9\s]/", "", $laporan_data['nama_pekerjaan']) . '_' . date('Ymd') . '.pdf';

$dompdf->stream($filename, ["Attachment" => false]);
exit();

?>