<?php
session_start();
include 'partials/db.php';

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';

if (!$is_admin) {
    $_SESSION['excel_message'] = 'Anda tidak memiliki izin untuk mengekspor data guru.';
    $_SESSION['excel_message_type'] = 'error';
    $_SESSION['excel_message_title'] = 'Akses Ditolak';
    header('Location: master_guru_pendamping.php');
    exit();
}

$keyword = $_GET['keyword'] ?? '';
$filter_sql = "";
$params = [];
$types = "";

if (!empty($keyword)) {
    $filter_sql = "WHERE nama_pembimbing LIKE ? OR nip LIKE ?";
    $like_keyword = "%" . $keyword . "%";
    $params = [$like_keyword, $like_keyword];
    $types = "ss";
}

// PERUBAHAN: Tambahkan kolom jenis_kelamin di SELECT query
$query_select_guru = "SELECT 
            id_pembimbing, 
            nama_pembimbing, 
            nip,
            jenis_kelamin 
        FROM guru_pembimbing 
        $filter_sql 
        ORDER BY nama_pembimbing ASC";

$stmt_guru = $koneksi->prepare($query_select_guru);
$data_guru = [];

if ($stmt_guru) {
    if (!empty($params)) {
        $stmt_guru->bind_param($types, ...$params);
    }
    $stmt_guru->execute();
    $result_guru = $stmt_guru->get_result();
    $data_guru = $result_guru->fetch_all(MYSQLI_ASSOC);
    $stmt_guru->close();
} else {
    $_SESSION['excel_message'] = 'Terjadi kesalahan sistem saat mengambil data guru: ' . $koneksi->error;
    $_SESSION['excel_message_type'] = 'error';
    $_SESSION['excel_message_title'] = 'Gagal Ekspor';
    header('Location: master_guru_pendamping.php');
    exit();
}
$koneksi->close();

ob_start();

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Data Guru Pendamping');

    // PERUBAHAN: Tambahkan 'Jenis Kelamin' di Header Kolom
    $headers = [
        'No',
        'Nama Guru',
        'NIP',
        'Jenis Kelamin' // Kolom baru
    ];
    $sheet->fromArray($headers, null, 'A1');

    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['argb' => 'FFFFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'rotation' => 90,
            'startColor' => ['argb' => 'FF4F81BD'],
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


    $row_num = 2;
    $counter = 1;
    foreach ($data_guru as $guru) {
        // PERUBAHAN: Tambahkan jenis_kelamin ke baris data
        $data_row = [
            $counter,
            $guru['nama_pembimbing'] ?? '',
            "'" . (string)($guru['nip'] ?? ''), // NIP tetap diformat sebagai teks
            $guru['jenis_kelamin'] ?? '' // Data jenis_kelamin
        ];
        $sheet->fromArray($data_row, null, 'A' . $row_num);

        // Atur format sel NIP menjadi teks (kolom C, indeks 3)
        $nipColumn = Coordinate::stringFromColumnIndex(3);
        $sheet->getStyle($nipColumn . $row_num)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $counter++;
        $row_num++;
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

    $sheet->getStyle('A2:A' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


    // Auto-size kolom
    for ($col_index = 1; $col_index <= count($headers); $col_index++) {
        $col_char = Coordinate::stringFromColumnIndex($col_index);
        $sheet->getColumnDimension($col_char)->setAutoSize(true);
    }

    $filename = 'Data_Guru_Pendamping_Export_' . date('Ymd_His') . '.xlsx';

    $_SESSION['excel_message'] = 'Data guru berhasil diekspor ke Excel!';
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
    header('Location: master_guru_pendamping.php');
    exit();
}

exit();
