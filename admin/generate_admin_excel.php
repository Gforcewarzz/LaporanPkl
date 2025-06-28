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

// Hanya admin yang diizinkan mengekspor data admin lain (atau dirinya sendiri)
if (!$is_admin) {
    $_SESSION['excel_message'] = 'Anda tidak memiliki izin untuk mengekspor data admin.';
    $_SESSION['excel_message_type'] = 'error';
    $_SESSION['excel_message_title'] = 'Akses Ditolak';
    header('Location: master_data_admin.php');
    exit();
}

// --- LOGIKA FILTER DATA ---
$keyword = $_GET['keyword'] ?? '';
$filter_sql = "";
$params = [];
$types = "";

if (!empty($keyword)) {
    $filter_sql = "WHERE username LIKE ? OR nama_admin LIKE ? OR email LIKE ?";
    $like_keyword = "%" . $keyword . "%";
    $params = [$like_keyword, $like_keyword, $like_keyword];
    $types = "sss"; // Tiga 's' untuk username, nama_admin, email
}

$query_select_admin = "SELECT id_admin, username, nama_admin, email FROM admin $filter_sql ORDER BY nama_admin ASC";

$stmt_admin = $koneksi->prepare($query_select_admin);
$data_admin = [];

if ($stmt_admin) {
    if (!empty($params)) {
        $stmt_admin->bind_param($types, ...$params);
    }
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();
    $data_admin = $result_admin->fetch_all(MYSQLI_ASSOC);
    $stmt_admin->close();
} else {
    $_SESSION['excel_message'] = 'Terjadi kesalahan sistem saat mengambil data admin: ' . $koneksi->error;
    $_SESSION['excel_message_type'] = 'error';
    $_SESSION['excel_message_title'] = 'Gagal Ekspor';
    header('Location: master_data_admin.php');
    exit();
}
$koneksi->close();

ob_start(); // Mulai output buffering

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Data Admin Sistem');

    // Header Kolom (sesuaikan dengan query SELECT)
    $headers = [
        'No',
        'Username',
        'Nama Admin',
        'Email'
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
    foreach ($data_admin as $admin) {
        $data_row = [
            $counter++,
            $admin['username'] ?? '',
            $admin['nama_admin'] ?? '',
            $admin['email'] ?? ''
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

    $filename = 'Data_Admin_Sistem_Export_' . date('Ymd_His') . '.xlsx';

    // Set pesan sukses di sesi sebelum mengirim file
    $_SESSION['excel_message'] = 'Data admin berhasil diekspor ke Excel!';
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
    header('Location: master_data_admin.php');
    exit();
}

exit();
