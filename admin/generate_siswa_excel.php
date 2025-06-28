<?php
session_start();

include 'partials/db.php';

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin) {
    // Simpan pesan error di sesi jika tidak diizinkan
    $_SESSION['excel_message'] = 'Anda tidak memiliki izin untuk mengekspor data siswa.';
    $_SESSION['excel_message_type'] = 'error';
    $_SESSION['excel_message_title'] = 'Akses Ditolak';
    header('Location: ../login.php');
    exit();
}

$keyword = $_GET['keyword'] ?? '';
$filter_sql = "";
$params = [];
$types = "";

if (!empty($keyword)) {
    $filter_sql = "WHERE siswa.nama_siswa LIKE ? 
                    OR siswa.no_induk LIKE ?
                    OR siswa.nisn LIKE ? 
                    OR siswa.jenis_kelamin LIKE ?
                    OR siswa.kelas LIKE ?
                    OR jurusan.nama_jurusan LIKE ?
                    OR guru_pembimbing.nama_pembimbing LIKE ?
                    OR tempat_pkl.nama_tempat_pkl LIKE ?
                    OR siswa.status LIKE ?";
    $like_keyword = "%" . $keyword . "%";
    $params = array_fill(0, 9, $like_keyword);
    $types = "sssssssss";
}

$query_select_siswa = "SELECT 
            siswa.id_siswa,
            siswa.nama_siswa,
            siswa.no_induk,
            siswa.nisn, 
            siswa.jenis_kelamin,
            siswa.kelas,
            siswa.status,
            jurusan.nama_jurusan,
            guru_pembimbing.nama_pembimbing,
            tempat_pkl.nama_tempat_pkl
        FROM siswa
        LEFT JOIN jurusan ON siswa.jurusan_id = jurusan.id_jurusan
        LEFT JOIN guru_pembimbing ON siswa.pembimbing_id = guru_pembimbing.id_pembimbing
        LEFT JOIN tempat_pkl ON siswa.tempat_pkl_id = tempat_pkl.id_tempat_pkl
        $filter_sql
        ORDER BY siswa.nama_siswa ASC";

$stmt_siswa = $koneksi->prepare($query_select_siswa);
$data_siswa = [];

if ($stmt_siswa) {
    if (!empty($params)) {
        $stmt_siswa->bind_param($types, ...$params);
    }
    $stmt_siswa->execute();
    $result_siswa = $stmt_siswa->get_result();
    $data_siswa = $result_siswa->fetch_all(MYSQLI_ASSOC);
    $stmt_siswa->close();
} else {
    // Simpan pesan error di sesi jika gagal mengambil data
    $_SESSION['excel_message'] = 'Terjadi kesalahan sistem saat mengambil data siswa: ' . $koneksi->error;
    $_SESSION['excel_message_type'] = 'error';
    $_SESSION['excel_message_title'] = 'Gagal Ekspor';
    header('Location: master_data_siswa.php'); // Redirect kembali
    exit();
}
$koneksi->close();

ob_start(); // Mulai output buffering

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Data Siswa PKL');

    $headers = [
        'No',
        'Nama Siswa',
        'No Induk',
        'NISN',
        'Jenis Kelamin',
        'Kelas',
        'Jurusan',
        'Guru Pembimbing',
        'Tempat PKL',
        'Status'
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
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

    $row_num = 2;
    $counter = 1;
    foreach ($data_siswa as $siswa) {
        $data_row = [
            $counter++,
            $siswa['nama_siswa'],
            $siswa['no_induk'],
            $siswa['nisn'],
            $siswa['jenis_kelamin'],
            $siswa['kelas'],
            $siswa['nama_jurusan'] ?? '-',
            $siswa['nama_pembimbing'] ?? '-',
            $siswa['nama_tempat_pkl'] ?? '-',
            $siswa['status']
        ];
        $sheet->fromArray($data_row, null, 'A' . $row_num++);
    }

    $allDataRange = 'A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow();
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
    $sheet->getStyle('A2:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray($dataStyle);

    $sheet->getStyle('A2:A' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $highestColumn = $sheet->getHighestColumn();
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $filename = 'Data_Siswa_PKL_Export_' . date('Ymd_His') . '.xlsx';

    // Set pesan sukses di sesi sebelum mengirim file
    $_SESSION['excel_message'] = 'Data siswa berhasil diekspor ke Excel!';
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
    // Simpan pesan error di sesi
    $_SESSION['excel_message'] = 'Terjadi kesalahan saat membuat file Excel: ' . $e->getMessage();
    $_SESSION['excel_message_type'] = 'error';
    $_SESSION['excel_message_title'] = 'Gagal Ekspor';
    // Karena file tidak bisa di-stream, kita perlu me-redirect
    header('Location: master_data_siswa.php');
    exit();
}

// Tidak perlu header('Location') di sini, karena file sudah di-stream ke browser
exit();
