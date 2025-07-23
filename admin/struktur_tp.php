<?php
session_start();
require_once 'partials/db.php';

// Keamanan: Hanya admin dan guru yang boleh akses
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';
if (!$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// Ambil semua data jurusan untuk filter dropdown
$jurusan_options = $koneksi->query("SELECT * FROM jurusan ORDER BY nama_jurusan ASC");

// --- LOGIKA FILTER ---
$filter_jurusan_id = isset($_GET['jurusan_id']) ? (int)$_GET['jurusan_id'] : 0;
$params = [];
$types = '';

$query_tp_sql = "SELECT * FROM tujuan_pembelajaran";

if ($filter_jurusan_id > 0) {
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

$semua_tp = [];
$tp_anak = [];
while($row = $tp_result->fetch_assoc()){
    $semua_tp[$row['id_tp']] = $row;
    $tp_anak[$row['id_induk']][] = $row['id_tp'];
}

// Fungsi rekursif untuk menampilkan daftar TP yang sudah ada
function tampilkan_tp_list($id_induk, $semua_tp, $tp_anak, $filter_jurusan_id, $level = 0) {
    if (!isset($tp_anak[$id_induk])) return;
    
    $ul_class = ($level == 0) ? 'list-group' : 'list-group list-group-flush';
    echo "<ul class='{$ul_class}'>";
    foreach ($tp_anak[$id_induk] as $id_tp) {
        $item = $semua_tp[$id_tp];
        $has_children = isset($tp_anak[$id_tp]);
        
        $item_class = 'list-group-item';
        if ($level == 0) $item_class .= ' list-group-item-secondary fw-bold';
        
        echo "<li class='{$item_class}' style='padding-left: " . (20 + ($level * 30)) . "px;'>";
        echo "  <div class='d-flex justify-content-between align-items-center'>";
        echo "      <span><strong>" . htmlspecialchars($item['kode_tp']) . "</strong> - " . htmlspecialchars($item['deskripsi_tp']) . "</span>";
        echo "      <div class='btn-group btn-group-sm'>";
        echo "          <a href='form_edit_tp.php?id=" . $id_tp . "&jurusan_id=" . $filter_jurusan_id . "' class='btn btn-outline-warning'><i class='bx bx-edit'></i></a>";
        echo "          <a href='javascript:void(0);' onclick='confirmDelete(" . $id_tp . ", \"" . addslashes(htmlspecialchars($item['kode_tp'])) . "\")' class='btn btn-outline-danger'><i class='bx bx-trash'></i></a>";
        echo "      </div>";
        echo "  </div>";
        
        if ($has_children) {
            tampilkan_tp_list($id_tp, $semua_tp, $tp_anak, $filter_jurusan_id, $level + 1);
        }
        echo "</li>";
    }
    echo "</ul>";
}

// Jika ini adalah request AJAX, hanya kirim daftar TP nya saja, lalu hentikan script.
if (isset($_GET['ajax'])) {
    tampilkan_tp_list(NULL, $semua_tp, $tp_anak, $filter_jurusan_id);
    exit();
}

$jurusan_options_for_select = $koneksi->query("SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
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
                        
                        <?php
                        if (isset($_SESSION['pesan_notifikasi'])) {
                            $notif = $_SESSION['pesan_notifikasi'];
                            echo "
                            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: '{$notif['tipe']}',
                                        title: '{$notif['judul']}',
                                        text: '{$notif['pesan']}',
                                        confirmButtonColor: '#696cff'
                                    });
                                });
                            </script>
                            ";
                            unset($_SESSION['pesan_notifikasi']);
                        }
                        ?>

                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Master Data /</span> Struktur Kompetensi</h4>

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <div class="d-flex gap-2 align-items-end">
                                        <div>
                                            <label for="jurusan_id_filter" class="form-label">Filter Berdasarkan Jurusan:</label>
                                            <select id="jurusan_id_filter" name="jurusan_id" class="form-select">
                                                <option value="">Tampilkan Semua</option>
                                                <?php if($jurusan_options_for_select && $jurusan_options_for_select->num_rows > 0) { while($jurusan = $jurusan_options_for_select->fetch_assoc()): ?>
                                                    <option value="<?= $jurusan['id_jurusan'] ?>" <?= ($filter_jurusan_id == $jurusan['id_jurusan']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($jurusan['nama_jurusan']) ?>
                                                    </option>
                                                <?php endwhile; } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="btn-group">
                                        <a href="laporan_penilaian_siswa.php" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Kembali ke Laporan
                                        </a>
                                        <a href="form_tambah_tp.php" class="btn btn-primary">
                                            <i class="bx bx-plus me-1"></i> Tambah Kompetensi
                                        </a>
                                    </div>
                                    </div>
                            </div>
                        </div>

                        <div class="card">
                            <h5 class="card-header">Daftar Kompetensi</h5>
                            <div class="card-body p-0" id="tp-list-container">
                                <?php tampilkan_tp_list(NULL, $semua_tp, $tp_anak, $filter_jurusan_id); ?>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Script AJAX untuk filter otomatis
        document.addEventListener('DOMContentLoaded', function() {
            const jurusanFilter = document.getElementById('jurusan_id_filter');
            const tpListContainer = document.getElementById('tp-list-container');

            jurusanFilter.addEventListener('change', function() {
                const selectedJurusanId = this.value;
                
                tpListContainer.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

                const baseUrl = window.location.pathname;
                const url = `${baseUrl}?ajax=1&jurusan_id=${selectedJurusanId}`;

                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        tpListContainer.innerHTML = html;
                        const newUrl = `${baseUrl}?jurusan_id=${selectedJurusanId}`;
                        history.pushState(null, '', newUrl);
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        tpListContainer.innerHTML = '<div class="alert alert-danger m-4">Gagal memuat data. Silakan coba lagi.</div>';
                    });
            });
        });

        // Script untuk konfirmasi hapus
        function confirmDelete(id, kode) {
            Swal.fire({
                title: 'Anda Yakin?',
                html: `Kompetensi dengan kode <strong>${kode}</strong> beserta seluruh turunannya akan dihapus permanen.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `proses_tp.php?action=delete&id=${id}`;
                }
            });
        }
    </script>
</body>
</html>