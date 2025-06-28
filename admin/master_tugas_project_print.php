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
            }

            if (!$authorized_to_view) {
                // Tentukan URL redirect jika akses ditolak
                $redirect_url_on_fail = 'master_tugas_project.php';
                if ($is_admin && isset($laporan_data['siswa_id'])) {
                    $redirect_url_on_fail .= '?siswa_id=' . htmlspecialchars($laporan_data['siswa_id']);
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
            line-height: 1.4;
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 10pt;
            /* Ukuran font umum untuk kerapian */
        }

        .page {
            width: 178mm;
            /* Lebar konten efektif A4 (210mm - 2*16mm margin) */
            margin: 0 auto;
            /* Menengahkan konten di halaman */
            min-height: 297mm;
            /* Tinggi A4 */
            padding: 0;
            /* Padding di elemen ini nol karena margin @page sudah mengatur */
            box-sizing: border-box;
            page-break-after: auto;
            /* Biarkan browser/printer menentukan pecah halaman */
        }

        .header {
            margin-bottom: 14pt;
            /* Jarak setelah header */
        }

        .header h1,
        .header h2 {
            margin: 0;
            font-size: 12pt;
            line-height: 1.1;
            text-align: left;
            font-weight: normal;
        }

        .header h2 {
            margin-top: 2pt;
        }

        .info-section {
            margin-bottom: 14pt;
            border-bottom: 1px dashed #bbb;
            padding-bottom: 6pt;
        }

        .info-item {
            display: flex;
            margin-bottom: 1.5pt;
            font-size: 10pt;
            padding-left: 6pt;
        }

        .info-item strong {
            flex-shrink: 0;
            width: 130pt;
            margin-right: 3pt;
        }

        .info-item span {
            flex-grow: 1;
            text-align: left;
        }

        .section-block {
            margin-bottom: 12pt;
            page-break-inside: avoid;
            /* Usahakan blok section tidak terpecah di tengah halaman */
            page-break-after: avoid;
            /* Usahakan tidak ada page break langsung setelah section block */
        }

        .section-title {
            font-weight: bold;
            margin-top: 8pt;
            margin-bottom: 3pt;
            text-decoration: none;
            font-size: 11pt;
        }

        .content-box {
            border: 1pt solid #000;
            padding: 6pt 8pt;
            min-height: 40pt;
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
            margin-top: 8pt;
            margin-bottom: 12pt;
            border: 1px solid #ddd;
            padding: 4pt;
            page-break-inside: avoid;
            /* Pastikan gambar tidak terpotong di tengah halaman */
        }

        .image-container img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            max-height: 90mm;
            /* Batasi tinggi maksimum gambar potret */
            object-fit: contain;
            /* Memastikan seluruh gambar terlihat tanpa terpotong */
        }

        .signature-block {
            margin-top: 30pt;
            /* Jarak dari konten di atasnya */
            text-align: right;
            font-size: 10pt;
            line-height: 1.35;
            page-break-inside: avoid;
            /* Pastikan blok tanda tangan tidak terpecah */
        }

        .signature-block p {
            margin: 0;
        }

        /* Menghilangkan garis bawah pada tanda tangan */
        .signature-line {
            display: none;
        }

        .signature-block .location-date {
            margin-bottom: 10pt;
            /* Jarak setelah lokasi dan tanggal */
        }

        .signature-block .signature-title {
            margin-bottom: 25pt;
            /* Jarak setelah "Tanda Tangan Instruktur" */
        }

        .signature-name-placeholder {
            margin-top: 0;
            /* Tidak perlu margin-top tambahan di sini, sudah diatur di p.signature-title */
            font-size: 10pt;
            display: block;
            min-height: 1.5em;
            /* Memberi sedikit ruang untuk tulisan nama */
        }


        /* Memaksa Bagian D ke halaman baru */
        .section-block.catatan-instruktur-section {
            page-break-before: always;
            /* Ini akan memaksa elemen untuk selalu dimulai di halaman baru */
        }

        /* Pengaturan margin halaman cetak secara keseluruhan */
        @page {
            size: A4 portrait;
            margin: 1.6cm;
            /* Margin di setiap sisi halaman */
        }

        /* Media query @media print untuk konsistensi di browser desktop */
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
                /* Pastikan konsisten dengan @page utama */
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
                <span><?php echo htmlspecialchars($laporan_data['nama_peserta'] ?? '-'); ?></span>
            </div>
            <div class="info-item">
                <strong>Dunia Kerja Tempat PKL</strong> :
                <span><?php echo htmlspecialchars($laporan_data['dunia_kerja_tempat_pkl'] ?? '-'); ?></span>
            </div>
            <div class="info-item">
                <strong>Nama Instruktur</strong> :
                <span><?php echo htmlspecialchars($laporan_data['nama_instruktur'] ?? '-'); ?></span>
            </div>
            <div class="info-item">
                <strong>Nama Guru Pembimbing</strong> :
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
            <p class="location-date"><?php echo htmlspecialchars($location); ?>,
                <?php echo htmlspecialchars($currentDate); ?></p>
            <p class="signature-title">Tanda Tangan Instruktur</p>
            <p class="signature-name-placeholder">(................................................)</p>
        </div>
    </div>
</body>

</html>
<?php
// Ambil konten HTML yang sudah di-buffer
$html = ob_get_clean();

// Deteksi Mobile dan Proses dengan Dompdf atau window.print()
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
