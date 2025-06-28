<?php
session_start();
include 'partials/db.php'; // Koneksi database

require_once 'vendor/autoload.php'; // Pastikan path ini sesuai

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// --- LOGIKA KEAMANAN ---
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

// Hanya admin yang diizinkan mengekspor data tempat PKL
if (!$is_admin) {
    $_SESSION['excel_message'] = 'Anda tidak memiliki izin untuk mengekspor data tempat PKL.';
    $_SESSION['excel_message_type'] = 'error';
    $_SESSION['excel_message_title'] = 'Akses Ditolak';
    header('Location: master_tempat_pkl.php');
    exit();
}

// --- LOGIKA FILTER DATA ---
$keyword = $_GET['keyword'] ?? '';
$filter_sql = "";
$params = [];
$types = "";

if (!empty($keyword)) {
    $filter_sql = "WHERE tp.nama_tempat_pkl LIKE ? OR tp.nama_instruktur LIKE ?";
    $like_keyword = "%" . $keyword . "%";
    $params = [$like_keyword, $like_keyword];
    $types = "ss";
}

$query_select_tempat_pkl = "
    SELECT 
        tp.id_tempat_pkl, 
        tp.nama_tempat_pkl, 
        tp.alamat, 
        tp.alamat_kontak, 
        tp.nama_instruktur, 
        tp.kuota_siswa, 
        j.nama_jurusan 
    FROM 
        tempat_pkl tp
    LEFT JOIN 
        jurusan j ON tp.jurusan_id = j.id_jurusan
    $filter_sql
    ORDER BY tp.nama_tempat_pkl ASC
";

$stmt_tempat_pkl = $koneksi->prepare($query_select_tempat_pkl);
$data_tempat_pkl = [];

if ($stmt_tempat_pkl) {
    if (!empty($params)) {
        $stmt_tempat_pkl->bind_param($types, ...$params);
    }
    $stmt_tempat_pkl->execute();
    $result_tempat_pkl = $stmt_tempat_pkl->get_result();
    $data_tempat_pkl = $result_tempat_pkl->fetch_all(MYSQLI_ASSOC);
    $stmt_tempat_pkl->close();
} else {
    $_SESSION['excel_message'] = 'Terjadi kesalahan sistem saat mengambil data tempat PKL: ' . $koneksi->error;
    $_SESSION['excel_message_type'] = 'error';
    $_SESSION['excel_message_title'] = 'Gagal Ekspor';
    header('Location: master_tempat_pkl.php');
    exit();
}
$koneksi->close();

ob_start(); // Mulai output buffering

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Data Tempat PKL');

    // Header Kolom (sesuaikan dengan query SELECT)
    $headers = [
        'No',
        'Nama Perusahaan',
        'Alamat',
        'Kontak',
        'Instruktur',
        'Kuota Siswa',
        'Jurusan'
    ];
    $sheet->fromArray($headers, null, 'A1');

    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['argb' => 'FFFFFFFF'], // Putih
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FF4F81BD'], // Biru Gelap
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];
    $sheet->getStyle('A1:' . Coordinate::stringFromColumnIndex(count($headers)) . '1')->applyFromArray($headerStyle);


    // Isi Data
    $row_num = 2;
    $counter = 1;
    foreach ($data_tempat_pkl as $tempat_pkl) {
        $data_row = [
            $counter++,
            $tempat_pkl['nama_tempat_pkl'] ?? '',
            $tempat_pkl['alamat'] ?? '',
            (string)($tempat_pkl['alamat_kontak'] ?? ''),
            $tempat_pkl['nama_instruktur'] ?? '',
            $tempat_pkl['kuota_siswa'] ?? '',
            $tempat_pkl['nama_jurusan'] ?? '-'
        ];
        $sheet->fromArray($data_row, null, 'A' . $row_num++);
    }

    $allDataRange = 'A1:' . Coordinate::stringFromColumnIndex(count($headers)) . $sheet->getHighestRow();
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_LEFT,
            'vertical' => Alignment::VERTICAL_TOP,
            'wrapText' => true,
        ],
    ];
    $sheet->getStyle('A2:' . Coordinate::stringFromColumnIndex(count($headers)) . $sheet->getHighestRow())->applyFromArray($dataStyle);

    // Perataan khusus untuk kolom 'No' (Kolom A) menjadi tengah
    $sheet->getStyle('A2:A' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Auto-size kolom
    for ($col_index = 1; $col_index <= count($headers); $col_index++) {
        $col_char = Coordinate::stringFromColumnIndex($col_index);
        $sheet->getColumnDimension($col_char)->setAutoSize(true);
    }

    $filename = 'Data_Tempat_PKL_Export_' . date('Ymd_His') . '.xlsx';

    // Set pesan sukses di sesi sebelum mengirim file
    $_SESSION['excel_message'] = 'Data tempat PKL berhasil diekspor ke Excel!';
    $_SESSION['excel_message_type'] = 'success';
    $_SESSION['excel_message_title'] = 'Ekspor Berhasil';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} catch (Exception $e) {
    error_log("Error during Excel generation: " . $e->getMessage());
    ob_end_clean();
    $_SESSION['excel_message'] = 'Terjadi kesalahan saat membuat file Excel: ' . $e->getMessage();
    $_SESSION['excel_message_type'] = 'error';
    $_SESSION['excel_message_title'] = 'Gagal Ekspor';
    header('Location: master_tempat_pkl.php');
    exit();
}

exit();
