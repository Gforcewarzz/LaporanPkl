<?php
session_start(); // Pastikan sesi dimulai di awal file

// Pastikan koneksi database tersedia
include 'partials/db.php';

// Sertakan Dompdf Autoloader (sesuaikan path jika perlu)
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- LOGIKA KEAMANAN ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

// Hanya admin yang diizinkan mencetak data guru
if (!$is_admin) {
    // Jika tidak diizinkan, bisa langsung keluar atau redirect dengan pesan
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Akses Ditolak</title><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire({icon: 'error',title: 'Akses Ditolak!',text: 'Anda tidak memiliki izin untuk melihat laporan ini.',confirmButtonText: 'OK'}).then(() => {window.close();});</script></body></html>";
    exit();
}

// Inisiasi Dompdf dengan opsi
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
// Set base path jika ada gambar yang di-load relatif
// Asumsi folder images ada di folder yang sama dengan file ini (admin/)
$options->set('chroot', realpath(__DIR__));

$dompdf = new Dompdf($options);

// --- Ambil Data dari Database (Menggunakan Prepared Statement) ---
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$filter_sql = "";
$params = [];
$types = '';

if (!empty($keyword)) {
    $filter_sql = "WHERE nama_pembimbing LIKE ? OR nip LIKE ?";
    $like_keyword = "%" . $keyword . "%";
    $params = [$like_keyword, $like_keyword];
    $types = "ss";
}

// PERUBAHAN: Tambahkan kolom jenis_kelamin di SELECT query
$query_guru_data = "SELECT id_pembimbing, nama_pembimbing, nip, jenis_kelamin
                    FROM guru_pembimbing
                    $filter_sql
                    ORDER BY nama_pembimbing ASC"; // Order by nama_pembimbing untuk konsistensi

$stmt_guru_data = $koneksi->prepare($query_guru_data);
$result = null; // Inisialisasi

if ($stmt_guru_data) {
    if (!empty($params)) {
        $stmt_guru_data->bind_param($types, ...$params);
    }
    $stmt_guru_data->execute();
    $result = $stmt_guru_data->get_result();
    $stmt_guru_data->close();
} else {
    error_log("Failed to prepare statement for PDF generation: " . $koneksi->error);
    echo "Terjadi kesalahan sistem saat mengambil data.";
    exit();
}


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
            font-size: 9pt; /* Sedikit lebih besar dari 8pt agar terbaca baik */
        }
        h1 { 
            text-align: center; 
            color: #333; 
            font-size: 14pt; 
            margin-bottom: 15px; /* Kurangi margin bawah */
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; /* Kurangi margin atas */
            font-size: 9pt; /* Konsisten dengan body */
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 6px 8px; /* Padding disesuaikan */
            text-align: left; 
            font-size: 9pt; 
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold; /* Lebih menonjolkan header */
            font-size: 9pt; 
        }
        /* Penyesuaian untuk NIP agar tidak terpotong */
        td:nth-child(3) { /* Kolom NIP (ke-3) */
            white-space: nowrap; /* Mencegah pemotongan baris */
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
                <th>Jenis Kelamin</th> </tr>
        </thead>
        <tbody>';

$no = 1;
if ($result->num_rows > 0) { // Pastikan $result valid
    while ($row = $result->fetch_assoc()) {
        $html .= "<tr>
                    <td>{$no}</td>
                    <td>" . htmlspecialchars($row['nama_pembimbing'] ?? '-') . "</td>
                    <td>" . htmlspecialchars($row['nip'] ?? '-') . "</td>
                    <td>" . htmlspecialchars($row['jenis_kelamin'] ?? '-') . "</td> </tr>";
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
// Jika diakses dari mobile, Anda bisa memaksa download. Di sini Attachment => false akan menampilkan di browser.
$filename = "data_guru_pendamping_" . date('Ymd_His') . ".pdf";
$dompdf->stream($filename, array("Attachment" => false)); // Attachment true untuk download langsung

// Tutup koneksi database
mysqli_close($koneksi);
