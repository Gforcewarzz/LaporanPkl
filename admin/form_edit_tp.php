<?php
session_start();
require_once 'partials/db.php';

// Keamanan
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
if (!$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// Ambil ID TP yang akan diedit dari URL
$id_to_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_to_edit === 0) die("ID tidak valid.");

// --- PERUBAHAN DI SINI: Ambil jurusan_id dari URL ---
$filter_jurusan_id = isset($_GET['jurusan_id']) ? (int)$_GET['jurusan_id'] : 0;
// --- AKHIR PERUBAHAN ---

// Ambil semua data jurusan untuk dropdown
$jurusan_options = $koneksi->query("SELECT * FROM jurusan ORDER BY nama_jurusan ASC");


// --- PERUBAHAN DI SINI: Filter query TP berdasarkan jurusan ---
$query_tp_sql = "SELECT * FROM tujuan_pembelajaran";
$params = [];
$types = '';
if ($filter_jurusan_id > 0) {
    // Ambil SEMUA TP yang relevan dengan filter. Ini penting agar hierarki tetap utuh.
    $query_tp_sql .= " WHERE jurusan_id = ? OR jurusan_id IS NULL";
    $params[] = $filter_jurusan_id;
    $types .= 'i';
}
$query_tp_sql .= " ORDER BY id_induk, kode_tp";

$stmt = $koneksi->prepare($query_tp_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tp_result = $stmt->get_result();
// --- AKHIR PERUBAHAN ---


$semua_tp = [];
$tp_anak = [];
while($row = $tp_result->fetch_assoc()){
    $semua_tp[$row['id_tp']] = $row;
    $tp_anak[$row['id_induk']][] = $row['id_tp'];
}

// Fungsi untuk mendapatkan semua turunan dari sebuah ID (TETAP SAMA)
function get_all_descendants($id_induk, $tp_anak, &$descendants = []) {
    if (!isset($tp_anak[$id_induk])) return $descendants;
    foreach ($tp_anak[$id_induk] as $id_tp) {
        $descendants[] = $id_tp;
        get_all_descendants($id_tp, $tp_anak, $descendants);
    }
    return $descendants;
}

// Dapatkan item utama dan semua turunannya (TETAP SAMA)
$item_utama = $semua_tp[$id_to_edit] ?? null;
if (!$item_utama) die("Data kompetensi tidak ditemukan atau tidak sesuai dengan filter jurusan.");
$items_to_edit_ids = array_merge([$id_to_edit], get_all_descendants($id_to_edit, $tp_anak));

// Fungsi untuk render form edit secara rekursif (TETAP SAMA)
function render_edit_form_fields($id_tp, $semua_tp, $tp_anak, $jurusan_options_result, $level = 0) {
    // Cek apakah item ini ada di dalam data yang sudah difilter
    if (!isset($semua_tp[$id_tp])) return;

    $item = $semua_tp[$id_tp];
    $padding = $level * 30;

    echo "<div class='p-3 mb-2 rounded' style='border-left: 5px solid #ccc; margin-left: {$padding}px; background-color: #f9f9f9;'>";
    echo "  <div class='mb-3'>";
    echo "      <label for='kode_tp_{$id_tp}' class='form-label fw-bold'>Kode Kompetensi</label>";
    echo "      <input type='text' class='form-control' id='kode_tp_{$id_tp}' name='tp[{$id_tp}][kode_tp]' value='" . htmlspecialchars($item['kode_tp']) . "' required>";
    echo "  </div>";
    echo "  <div class='mb-3'>";
    echo "      <label for='deskripsi_tp_{$id_tp}' class='form-label fw-bold'>Deskripsi</label>";
    echo "      <textarea class='form-control' id='deskripsi_tp_{$id_tp}' name='tp[{$id_tp}][deskripsi_tp]' rows='2' required>" . htmlspecialchars($item['deskripsi_tp']) . "</textarea>";
    echo "  </div>";
    echo "  <div class='mb-3'>";
    echo "      <label for='jurusan_id_{$id_tp}' class='form-label fw-bold'>Khusus Jurusan</label>";
    echo "      <select class='form-select' id='jurusan_id_{$id_tp}' name='tp[{$id_tp}][jurusan_id]'>";
    echo "          <option value=''>-- Semua Jurusan --</option>";
    mysqli_data_seek($jurusan_options_result, 0); // Reset pointer
    while($jurusan = $jurusan_options_result->fetch_assoc()) {
        $selected = ($item['jurusan_id'] == $jurusan['id_jurusan']) ? 'selected' : '';
        echo "      <option value='" . $jurusan['id_jurusan'] . "' {$selected}>" . htmlspecialchars($jurusan['nama_jurusan']) . "</option>";
    }
    echo "      </select>";
    echo "  </div>";
    echo "</div>";

    if (isset($tp_anak[$id_tp])) {
        foreach ($tp_anak[$id_tp] as $id_anak) {
            render_edit_form_fields($id_anak, $semua_tp, $tp_anak, $jurusan_options_result, $level + 1);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/" data-template="vertical-menu-template-free">
<?php include 'partials/head.php'; ?>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Master Data /</span> Edit Kompetensi</h4>
                        <div class="card">
                            <h5 class="card-header">Edit Kompetensi Beserta Turunannya</h5>
                            <div class="card-body">
                                <form action="proses_tp.php" method="POST">
                                    <input type="hidden" name="id_tp_utama" value="<?= $id_to_edit ?>">
                                    <?php render_edit_form_fields($id_to_edit, $semua_tp, $tp_anak, $jurusan_options); ?>
                                    <div class="mt-4">
                                        <a href="struktur_tp.php" class="btn btn-outline-secondary">Batal</a>
                                        <button type="submit" name="update_tp" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php include './partials/footer.php'; ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <?php include 'partials/script.php' ?>
</body>
</html>