<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

require_once 'partials/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* ===================== Access Control ===================== */

$id_jurnal_kegiatan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru  = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_siswa && !$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

/* ===================== Fetch Data ===================== */
if ($id_jurnal_kegiatan <= 0) {
    echo "<!DOCTYPE html><html><body><script>alert('ID Laporan tidak valid.'); window.location.href = 'master_tugas_project.php';</script></body></html>";
    exit();
}

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
        FROM jurnal_kegiatan jk
        LEFT JOIN siswa s ON jk.siswa_id = s.id_siswa
        LEFT JOIN tempat_pkl tp ON s.tempat_pkl_id = tp.id_tempat_pkl
        LEFT JOIN guru_pembimbing gp ON s.pembimbing_id = gp.id_pembimbing
        WHERE jk.id_jurnal_kegiatan = ?";

$stmt = $koneksi->prepare($sql);
if (!$stmt) {
    error_log('Failed to prepare statement for print: ' . $koneksi->error);
    echo "<!DOCTYPE html><html><body><script>alert('Terjadi kesalahan sistem.'); window.location.href = 'master_tugas_project.php';</script></body></html>";
    exit();
}
$stmt->bind_param("i", $id_jurnal_kegiatan);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows <= 0) {
    echo "<!DOCTYPE html><html><body><script>alert('Laporan tidak ditemukan!'); window.location.href = 'master_tugas_project.php';</script></body></html>";
    exit();
}
$laporan_data = $result->fetch_assoc();
$stmt->close();

/* ===================== Authorization ===================== */
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

/* ===================== Layout Heuristics ===================== */
$lenA = mb_strlen($laporan_data['nama_pekerjaan'] ?? '');
$lenB = mb_strlen($laporan_data['perencanaan_kegiatan'] ?? '');
$lenC = mb_strlen($laporan_data['pelaksanaan_kegiatan'] ?? '');
$totalChars = $lenA + $lenB + $lenC;

$hasPhoto = !empty($laporan_data['gambar']);

// Ambang teks untuk memutuskan apakah foto kemungkinan muat di halaman 1
$CHAR_THRESHOLD_FOR_PHOTO_ON_PAGE1 = 900;

// Foto di halaman 1 hanya jika ada foto + teks relatif pendek
$placePhotoOnPage1 = $hasPhoto && ($totalChars <= $CHAR_THRESHOLD_FOR_PHOTO_ON_PAGE1);

// Break sebelum D: pakai indikator pendek/panjang.
// - Jika ada foto: ikuti keputusan foto (kalau foto muat di halaman 1, D mulai halaman 2).
// - Jika tidak ada foto: pakai ambang khusus untuk D.
$BREAK_THRESHOLD_FOR_D = 1100;
$needBreakBeforeD = $hasPhoto ? $placePhotoOnPage1 : ($totalChars <= $BREAK_THRESHOLD_FOR_D);

// Tanggal untuk tanda tangan dari DB
$currentDate = date('d F Y', strtotime($laporan_data['tanggal_laporan'] ?? 'now'));

$koneksi->close();

/* ===================== Render HTML ===================== */
ob_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Tugas PKL: <?php echo htmlspecialchars($laporan_data['nama_pekerjaan']); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.6cm;
        }

        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 10pt;
        }

        /* lebar konten = 210mm - (margin kiri+kanan 16mm) = ~178mm */
        .wrap {
            width: 178mm;
            margin: 0 auto;
            box-sizing: border-box;
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

        /* A & B tetap rapi */
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
            page-break-inside: avoid;
        }

        /* Khusus C: izinkan pecah agar tidak memaksa halaman kosong ekstra */
        .c-block,
        .c-block .content-box {
            page-break-inside: auto;
        }

        .small-note {
            font-size: 7.5pt;
            color: #555;
            margin-top: 1pt;
            text-align: left;
        }

        /* Foto */
        .image-container {
            text-align: center;
            margin-top: 7pt;
            margin-bottom: 10pt;
            border: 1px solid #ddd;
            padding: 3pt;
            page-break-inside: avoid;
        }

        .image-page1 img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            max-height: 70mm;
            /* kecil agar realistis muat di halaman 1 */
            object-fit: contain;
        }

        .image-page2 img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            max-height: 110mm;
            /* lega di halaman 2 */
            object-fit: contain;
        }

        /* Mulai halaman baru jika diperlukan */
        .break-before {
            page-break-before: always;
        }

        .signature-block {
            margin-top: 12mm;
            padding-top: 0;
            font-size: 10pt;
            line-height: 1.3;
            page-break-inside: avoid;
        }

        .signature-block table {
            width: 100%;
        }

        .signature-block .right-align {
            width: 30%;
            text-align: left;
        }

        .signature-block p {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="wrap">

        <!-- ====== HALAMAN 1: Header + Info + A/B/C (Foto opsional di bawah C) ====== -->
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

        <div class="section-block c-block">
            <div class="section-title">C. Pelaksanaan Kegiatan/Hasil</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($laporan_data['pelaksanaan_kegiatan'] ?? '-')); ?>
                <div class="small-note">(Uraian proses
                    kerja<?php echo $hasPhoto ? '; bukti visual di bawah / halaman berikutnya' : ''; ?>)</div>
            </div>

            <?php if ($placePhotoOnPage1): ?>
                <div class="image-container image-page1">
                    <img src="images/<?php echo htmlspecialchars($laporan_data['gambar']); ?>" alt="Bukti Kegiatan">
                    <div class="small-note" style="text-align:center;margin-top:3pt;">(Bukti Kegiatan Visual)</div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ====== HALAMAN 2 (atau lanjutan C): Foto (jika tidak muat), D, TTD ====== -->
        <div class="section-block <?php echo $needBreakBeforeD ? 'break-before' : ''; ?>">
            <?php if ($hasPhoto && !$placePhotoOnPage1): ?>
                <div class="image-container image-page2">
                    <img src="images/<?php echo htmlspecialchars($laporan_data['gambar']); ?>" alt="Bukti Kegiatan">
                    <div class="small-note" style="text-align:center;margin-top:3pt;">(Bukti Kegiatan Visual)</div>
                </div>
            <?php endif; ?>

            <div class="section-title">D. Catatan Instruktur</div>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($laporan_data['catatan_instruktur'] ?? '-')); ?>
            </div>

            <div class="signature-block">
                <table>
                    <tr>
                        <td></td>
                        <td class="right-align" style="padding-left:20px;">
                            <p>...................., <?php echo htmlspecialchars($currentDate); ?></p>
                            <p style="margin-top: 2cm;">Pembimbing Dunia Kerja</p>
                            <p style="margin-top: 70pt;">(................................................)</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
</body>

</html>
<?php
/* ===================== DOMPDF ===================== */
$html = ob_get_clean();

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
