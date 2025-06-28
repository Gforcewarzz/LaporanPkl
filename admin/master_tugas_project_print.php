<?php
session_start();

include 'partials/db.php';

$id_jurnal_kegiatan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$laporan_data = null;

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_siswa && !$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

$location = "Bandung"; // Anda bisa mengubah ini atau mengambil dari konfigurasi/database


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

            $authorized_to_view = false;
            if ($is_siswa && ($laporan_data['siswa_id'] == ($_SESSION['id_siswa'] ?? null))) {
                $authorized_to_view = true;
            } elseif ($is_admin) {
                $authorized_to_view = true;
            }

            if (!$authorized_to_view) {
                $redirect_url_on_fail = 'master_tugas_project.php';
                if ($is_admin && isset($laporan_data['siswa_id'])) {
                    $redirect_url_on_fail .= '?siswa_id=' . htmlspecialchars($laporan_data['siswa_id']);
                }

                echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Akses Ditolak</title><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire({icon: 'error',title: 'Akses Ditolak!',text: 'Anda tidak memiliki izin untuk melihat laporan ini.',confirmButtonText: 'OK'}).then(() => {window.location.href = '{$redirect_url_on_fail}';});</script></body></html>";
                exit();
            }
        } else {
            echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Laporan Tidak Ditemukan</title><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire({icon: 'error',title: 'Tidak Ditemukan!',text: 'Laporan tugas proyek dengan ID ini tidak ada.',confirmButtonText: 'OK'}).then(() => {window.location.href = 'master_tugas_project.php';});</script></body></html>";
            exit();
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement for print: " . $koneksi->error);
        echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Error</title><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire({icon: 'error',title: 'Error!',text: 'Terjadi kesalahan internal. Mohon coba lagi nanti.',confirmButtonText: 'OK'}).then(() => {window.location.href = 'master_tugas_project.php';});</script></body></html>";
        exit();
    }
} else {
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>ID Tidak Valid</title><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire({icon: 'warning',title: 'ID Tidak Valid!',text: 'ID laporan tidak diberikan.',confirmButtonText: 'OK'}).then(() => {window.location.href = 'master_tugas_project.php';});</script></body></html>";
    exit();
}

$currentDate = date('d F Y', strtotime($laporan_data['tanggal_laporan']));

$koneksi->close();
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
            font-size: 10.5pt;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 1.8cm;
            box-sizing: border-box;
            page-break-after: auto;
        }

        .header {
            margin-bottom: 16pt;
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
            margin-bottom: 16pt;
            border-bottom: 1px dashed #bbb;
            padding-bottom: 7pt;
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
            margin-bottom: 14pt;
            page-break-inside: avoid;
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

        .image-container {
            text-align: center;
            margin-top: 10pt;
            margin-bottom: 15pt;
            border: 1px solid #ddd;
            padding: 5pt;
            page-break-inside: avoid;
        }

        .image-container img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            max-height: 100mm;
            /* Batasi tinggi gambar potret agar tidak terlalu panjang */
            object-fit: contain;
            /* Memastikan seluruh gambar terlihat tanpa terpotong */
        }


        .signature-block {
            margin-top: 35pt;
            text-align: right;
            font-size: 10.5pt;
            line-height: 1.4;
            page-break-inside: avoid;
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
            font-size: 10.5pt;
        }

        /* CSS tambahan untuk memecah halaman D ke halaman baru jika mepet */
        .section-block.break-before-if-tight {
            /* Ini akan mencoba memindahkan elemen ke halaman baru
           jika sisa ruang di halaman saat ini terlalu kecil.
           Nilai 'N' ini (misal 5cm) adalah tinggi minimal yang dibutuhkan elemen
           agar tidak dipecah. Jika kurang dari N, elemen akan dipindah. */
            page-break-before: auto;
            /* Default: biarkan browser menentukan */
            page-break-after: auto;
            break-before: auto;
            break-after: auto;
            /* Custom property for advanced control (not standard CSS print) */
            /* -webkit-box-decoration-break: clone; */
            /* box-decoration-break: clone; */
        }

        /* Aturan media print untuk memaksa page break */
        @media print {

            /* Cara paling umum dan efektif untuk memecah halaman */
            .section-block.break-before-if-tight {
                page-break-before: avoid;
                /* Ini mencegah pecah di tengah elemen ini */
                /* Jika Anda ingin memaksa selalu di halaman baru (seperti yang Anda minta): */
                /* page-break-before: always; */
            }

            /* Untuk kasus 'D' (Catatan Instruktur), kita bisa lebih agresif. */
            /* Jika terlalu banyak isi di atasnya sehingga D akan terpotong */
            /* Kita bisa target langsung section-block yang keempat jika selalu D. */
            /* Contoh lebih spesifik: Jika D adalah section-block keempat */
            .section-block:nth-of-type(4) {
                /* Mengambil section-block ke-4 (A, B, C, D) */
                page-break-before: auto;
                /* Biarkan browser decide */
                /* Alternatif jika selalu ingin D di halaman baru: */
                /* page-break-before: always; */
                /* HANYA JIKA ADA KEMUNGKINAN D TERPOTONG, MAKA PINDAH HALAMAN: */
                /* Ini lebih sulit dicapai dengan CSS murni.
               Biasanya memerlukan JS untuk mengukur konten dinamis.
               Namun, kita bisa memberikan 'break-inside: avoid' dan sedikit margin. */
            }

            /* Untuk memaksa "Catatan Instruktur" ke halaman baru jika terlalu mepet */
            /* Ini adalah trik CSS murni, yang terbaik adalah page-break-before: always; */
            /* Tapi kalau mau 'jika mepet', bisa pakai min-height atau padding di footernya
           atau mengandalkan page-break-inside. */
            .section-block.catatan-instruktur-section {
                page-break-before: auto;
                /* Default behaviour */
                /* Jika Anda ingin dia selalu di halaman 2 (atau halaman baru) */
                /* page-break-before: always; */
            }
        }


        @page {
            size: A4 portrait;
            margin: 1.8cm;
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
                margin: 1.8cm;
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