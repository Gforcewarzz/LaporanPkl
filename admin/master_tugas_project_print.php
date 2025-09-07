<?php
session_start();

include 'partials/db.php';

// Pastikan Dompdf ter-load. Sesuaikan path ini jika Anda tidak menggunakan Composer
// atau jika folder vendor berada di lokasi yang berbeda.
require_once 'vendor/autoload.php';

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

            // Logika otorisasi: pastikan pengguna yang login berhak melihat laporan ini
            $authorized_to_view = false;
            if ($is_siswa && ($laporan_data['siswa_id'] == ($_SESSION['id_siswa'] ?? null))) {
                $authorized_to_view = true;
            } elseif ($is_admin) {
                $authorized_to_view = true; // Admin bisa melihat semua laporan
            } elseif ($is_guru && ($laporan_data['id_guru_pembimbing_siswa'] == ($_SESSION['id_guru_pendamping'] ?? null))) {
                // Guru bisa melihat jika laporan ini milik siswa bimbingannya
                $authorized_to_view = true;
            }


            if (!$authorized_to_view) {
                // Tentukan URL redirect jika akses ditolak
                $redirect_url_on_fail = 'master_tugas_project.php';
                if ($is_admin && isset($laporan_data['siswa_id'])) {
                    $redirect_url_on_fail .= '?siswa_id=' . htmlspecialchars($laporan_data['siswa_id']);
                } elseif ($is_guru && isset($_SESSION['id_guru_pendamping'])) {
                    $redirect_url_on_fail .= '?pembimbing_id=' . htmlspecialchars($_SESSION['id_guru_pendamping']);
                }


                // Tampilkan pesan error dan redirect menggunakan SweetAlert2
                echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Akses Ditolak</title><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire({icon: 'error',title: 'Akses Ditolak!',text: 'Anda tidak memiliki izin untuk melihat laporan ini.',confirmButtonText: 'OK'}).then(() => {window.location.href = '{$redirect_url_on_fail}';});</script></body></html>";
                exit();
            }
        } else {
            // Laporan tidak ditemukan di database
            echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Laporan Tidak Ditemukan</title><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire({icon: 'error',title: 'Tidak Ditemukan!',text: 'Laporan tugas proyek dengan ID ini tidak ada.',confirmButtonText: 'OK'}).then(() => {window.location.href = 'master_tugas_project.php';});</script></body></html>";
            exit();
        }
        $stmt->close();
    } else {
        // Gagal menyiapkan statement database
        error_log("Failed to prepare statement for print: " . $koneksi->error);
        echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Error</title><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire({icon: 'error',title: 'Error!',text: 'Terjadi kesalahan internal. Mohon coba lagi nanti.',confirmButtonText: 'OK'}).then(() => {window.location.href = 'master_tugas_project.php';});</script></body></html>";
        exit();
    }
} else {
    // ID laporan tidak valid atau tidak diberikan
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>ID Tidak Valid</title><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire({icon: 'warning',title: 'ID Tidak Valid!',text: 'ID laporan tidak diberikan.',confirmButtonText: 'OK'}).then(() => {window.location.href = 'master_tugas_project.php';});</script></body></html>";
    exit();
}

// Tanggal laporan diambil dari database
$currentDate = date('d F Y', strtotime($laporan_data['tanggal_laporan']));
$tahun_laporan = date('Y', strtotime($laporan_data['tanggal_laporan']));
$koneksi->close(); // Tutup koneksi database setelah semua query selesai

// Deteksi user agent untuk menentukan apakah mobile atau desktop
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$is_mobile = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|windows ce|wireless|xda|xiino)/i", $user_agent);

// Mulai output buffering untuk menangkap semua HTML
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

        .info-section {
            margin-bottom: 12pt;
            border-bottom: 1px dashed #bbb;
            padding-bottom: 5pt;
        }

        .info-item {
            display: flex;
            margin-bottom: 1pt;
            font-size: 10pt;
            padding-left: 6pt;
        }

        .info-item strong {
            flex-shrink: 0;
            min-width: 130pt;
            max-width: 130pt;
            margin-right: 0;
            text-align: left;
            position: relative;
            color: #444;
            font-weight: bold;
        }

        .info-item strong::after {
            content: ":";
            position: absolute;
            right: 0;
        }

        .info-item span {
            flex-grow: 1;
            text-align: left;
            padding-left: 10pt;
        }

        .section-block {
            margin-bottom: 10pt;
            page-break-inside: avoid;
            page-break-after: avoid;
        }

        .section-title {
            font-weight: bold;
            margin-top: 7pt;
            margin-bottom: 2pt;
            text-decoration: none;
            font-size: 11pt;
        }

        .content-box {
            border: 1pt solid #000;
            padding: 5pt 7pt;
            min-height: 35pt;
            white-space: pre-line;
            word-wrap: break-word;
            text-align: justify;
            font-size: 10pt;
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
            text-align: right;
            font-size: 10pt;
            line-height: 1.3;
            page-break-inside: avoid;
        }

        .signature-block .location-date {
            margin-bottom: 2cm;
            float: right;
            clear: right;
        }

        .signature-block .signature-title {
            /* Margin top ini untuk membuat spasi antara tanggal dan judul ttd */
            margin-top: 2cm;
            margin-bottom: 70pt;
            clear: both;
        }

        .signature-block p {
            margin: 0;
        }

        .signature-name-placeholder {
            /* Perubahan di sini: Tambahkan margin-top agar ttd lebih ke bawah */
            margin-top: 50pt;
            /* Nilai 50pt ini bisa kamu sesuaikan */
            font-size: 10pt;
            display: block;
            min-height: 1.5em;
        }

        .section-block.catatan-instruktur-section {
            page-break-before: always;
        }

        @page {
            size: A4 portrait;
            margin: 1.6cm;
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
                margin: 1.6cm;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="header">
            <h1>JURNAL KEGIATAN HARIAN PRAKTEK KERJA LAPANGAN</h1>
            <h2>PESERTA DIDIK SMKN 1 GANTAR</h2>
        </div>

        <div class="info-section">
            <div class="info-item">
                <strong>Nama Peserta Didik</strong>
                <span><?php echo htmlspecialchars($laporan_data['nama_peserta'] ?? '-'); ?></span>
            </div>
            <div class="info-item">
                <strong>Dunia Kerja Tempat PKL</strong>
                <span><?php echo htmlspecialchars($laporan_data['dunia_kerja_tempat_pkl'] ?? '-'); ?></span>
            </div>
            <div class="info-item">
                <strong>Pembimbing Dunia Kerja</strong>
                <span><?php echo htmlspecialchars($laporan_data['nama_instruktur'] ?? '-'); ?></span>
            </div>
            <div class="info-item">
                <strong>Guru Pembimbing Sekolah</strong>
                <span><?php echo htmlspecialchars($laporan_data['nama_guru_pembimbing'] ?? '-'); ?></span>
            </div>
        </div>

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
            <p class="location-date">...................., .........................
                <?php echo htmlspecialchars($tahun_laporan); ?></p>
            <p class="signature-title">Pembimbing Dunia Kerja</p>
            <p class="signature-name-placeholder">(................................................)</p>
        </div>
    </div>
</body>

</html>
<?php
// Ambil konten HTML yang sudah di-buffer
$html = ob_get_clean();

// Deteksi user agent untuk menentukan apakah mobile atau desktop
if ($is_mobile) {
    // Instansiasi dan konfigurasi Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    // Path ke folder root proyek Anda jika folder 'images' ada di sana
    // atau ke folder 'admin' jika folder 'images' ada di dalam 'admin'
    // Asumsi folder 'images' ada di direktori yang sama dengan file ini (admin/)
    $options->set('chroot', realpath(__DIR__));

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);

    $dompdf->setPaper('A4', 'portrait');

    $dompdf->render();

    // Nama file untuk di-download
    $filename = 'Laporan_Tugas_Proyek_' . preg_replace("/[^a-zA-Z0-9\s]/", "", $laporan_data['nama_pekerjaan']) . '_' . date('Ymd') . '.pdf';
    $dompdf->stream($filename, ["Attachment" => true]); // true = download, false = open in browser
    exit();
} else {
    // Jika bukan mobile, tampilkan HTML di browser dan panggil window.print()
    echo $html;
?>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
<?php
}
