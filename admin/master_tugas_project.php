<?php
session_start();

include 'partials/db.php'; // Ensure this path is correct and db.php establishes $koneksi

// --- PAGE SECURITY LOGIC ---
$is_siswa = isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in';
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_siswa && !$is_admin && !$is_guru) {
    header('Location: ../login.php');
    exit();
}

// --- INITIALIZE FILTERS AND DISPLAY NAMES ---
$current_siswa_id_filter = null;
$display_siswa_name = "";
$guru_session_id = $_SESSION['id_guru_pendamping'] ?? null;

$queryParams = [];
$queryTypes = "";
$whereClauses = [];

$keyword = trim($_GET['keyword'] ?? '');

// Helper function to build URL query strings
function buildUrlQuery(array $paramsToOverride = []): string
{
    $currentParams = $_GET;
    // Merge current GET params with any overrides
    foreach ($paramsToOverride as $key => $value) {
        $currentParams[$key] = $value;
    }
    // Remove 'page' if it's 1 for cleaner URLs
    if (isset($currentParams['page']) && $currentParams['page'] <= 1) {
        unset($currentParams['page']);
    }
    return !empty($currentParams) ? '?' . http_build_query($currentParams) : '';
}

// --- DETERMINE FILTERS BASED ON USER ROLE AND URL PARAMETERS ---
if ($is_siswa) {
    $current_siswa_id_filter = $_SESSION['id_siswa'] ?? null;
    $display_siswa_name = $_SESSION['siswa_nama'] ?? "Saya";
    if ($current_siswa_id_filter !== null) {
        $whereClauses[] = "jk.siswa_id = ?";
        $queryParams[] = $current_siswa_id_filter;
        $queryTypes .= "i";
    }
} elseif ($is_admin) {
    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $current_siswa_id_filter = (int)$_GET['siswa_id'];
        $whereClauses[] = "jk.siswa_id = ?";
        $queryParams[] = $current_siswa_id_filter;
        $queryTypes .= "i";

        $stmt_get_siswa_name = $koneksi->prepare("SELECT nama_siswa FROM siswa WHERE id_siswa = ?");
        if ($stmt_get_siswa_name) {
            $stmt_get_siswa_name->bind_param("i", $current_siswa_id_filter);
            $stmt_get_siswa_name->execute();
            $result_siswa_name = $stmt_get_siswa_name->get_result();
            if ($result_siswa_name->num_rows > 0) {
                $display_siswa_name = "Siswa: " . htmlspecialchars($result_siswa_name->fetch_assoc()['nama_siswa']);
            } else {
                $display_siswa_name = "Siswa (ID tidak ditemukan)";
            }
            $stmt_get_siswa_name->close();
        } else {
            error_log("Failed to prepare statement for fetching siswa name: " . $koneksi->error);
            $display_siswa_name = "Siswa (Error)";
        }
    } else {
        $display_siswa_name = "Seluruh Siswa";
    }
} elseif ($is_guru) {
    if ($guru_session_id !== null) {
        $whereClauses[] = "s.pembimbing_id = ?";
        $queryParams[] = $guru_session_id;
        $queryTypes .= "i";
        $display_siswa_name = "Siswa Bimbingan Anda";
    } else {
        $display_siswa_name = "Siswa Bimbingan (ID Guru Tidak Ditemukan)";
    }

    if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])) {
        $temp_siswa_id_from_get = (int)$_GET['siswa_id'];
        $stmt_check_siswa_ownership = $koneksi->prepare("SELECT 1 FROM siswa WHERE id_siswa = ? AND pembimbing_id = ?");
        if ($stmt_check_siswa_ownership) {
            $stmt_check_siswa_ownership->bind_param("ii", $temp_siswa_id_from_get, $guru_session_id);
            $stmt_check_siswa_ownership->execute();
            if ($stmt_check_siswa_ownership->get_result()->num_rows > 0) {
                $whereClauses[] = "jk.siswa_id = ?";
                $queryParams[] = $temp_siswa_id_from_get;
                $queryTypes .= "i";
                $stmt_get_siswa_name_guru_view = $koneksi->prepare("SELECT nama_siswa FROM siswa WHERE id_siswa = ?");
                if ($stmt_get_siswa_name_guru_view) {
                    $stmt_get_siswa_name_guru_view->bind_param("i", $temp_siswa_id_from_get);
                    $stmt_get_siswa_name_guru_view->execute();
                    $result_siswa_name_guru_view = $stmt_get_siswa_name_guru_view->get_result();
                    if ($result_siswa_name_guru_view->num_rows > 0) {
                        $display_siswa_name = "Siswa: " . htmlspecialchars($result_siswa_name_guru_view->fetch_assoc()['nama_siswa']);
                    }
                    $stmt_get_siswa_name_guru_view->close();
                }
            } else {
                $temp_siswa_id_from_get = null;
                error_log("Security alert: Guru (ID: $guru_session_id) attempted to view unauthorized siswa ID: " . $_GET['siswa_id']);
            }
            $stmt_check_siswa_ownership->close();
        }
    }
}

// Add universal keyword filter
if (!empty($keyword)) {
    // Add filtering on new columns
    $whereClauses[] = "(jk.nama_pekerjaan LIKE ? OR jk.perencanaan_kegiatan LIKE ? OR jk.pelaksanaan_kegiatan LIKE ? OR jk.catatan_instruktur LIKE ? OR s.nama_siswa LIKE ?)";
    $queryParams[] = "%" . $keyword . "%";
    $queryParams[] = "%" . $keyword . "%";
    $queryParams[] = "%" . $keyword . "%";
    $queryParams[] = "%" . $keyword . "%";
    $queryParams[] = "%" . $keyword . "%";
    $queryTypes .= "sssss"; // 5 's' for 5 string parameters
}

$whereSql = "";
if (!empty($whereClauses)) {
    $whereSql = " WHERE " . implode(" AND ", $whereClauses);
}

// --- PAGINATION LOGIC ---
$records_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

$sql_total_records = "SELECT COUNT(*) as total_records
                      FROM jurnal_kegiatan jk
                      LEFT JOIN siswa s ON jk.siswa_id = s.id_siswa" . $whereSql;

$stmt_total = $koneksi->prepare($sql_total_records);
if ($stmt_total) {
    if (!empty($queryParams)) {
        $stmt_total->bind_param($queryTypes, ...$queryParams);
    }
    $stmt_total->execute();
    $total_records = $stmt_total->get_result()->fetch_assoc()['total_records'];
    $stmt_total->close();
} else {
    error_log("Failed to prepare total records statement: " . $koneksi->error);
    $total_records = 0;
}
$total_pages = ceil($total_records / $records_per_page);

// --- MAIN DATA QUERY ---
$sql_project_journals = "SELECT jk.id_jurnal_kegiatan, jk.nama_pekerjaan, jk.perencanaan_kegiatan, 
                         jk.pelaksanaan_kegiatan, jk.catatan_instruktur, jk.gambar, 
                         jk.tanggal_laporan, jk.siswa_id, s.nama_siswa
                         FROM jurnal_kegiatan jk
                         LEFT JOIN siswa s ON jk.siswa_id = s.id_siswa" . $whereSql .
    " ORDER BY jk.tanggal_laporan DESC LIMIT ? OFFSET ?";

$params_with_pagination = array_merge($queryParams, [$records_per_page, $offset]);
$types_with_pagination = $queryTypes . "ii";

$stmt = $koneksi->prepare($sql_project_journals);

if ($stmt) {
    if (!empty($params_with_pagination)) {
        $stmt->bind_param($types_with_pagination, ...$params_with_pagination);
    }
    $stmt->execute();
    $project_journals_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    error_log("Failed to prepare main data statement: " . $koneksi->error);
    $project_journals_data = [];
}

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<?php include 'partials/head.php' ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div
                            class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom position-relative">
                            <h4 class="fw-bold mb-0 text-primary animate__animated animate__fadeInLeft">
                                <span class="text-muted fw-light">
                                    <?php
                                    if ($is_siswa) echo "Siswa /";
                                    elseif ($is_admin) echo "Admin /";
                                    elseif ($is_guru) echo "Guru /";
                                    ?>
                                </span> Jurnal PKL Per Kegiatan
                            </h4>
                            <i class="fas fa-tasks fa-2x text-info" style="opacity: 0.6;"></i>
                        </div>

                        <div class="card mb-4 shadow-lg">
                            <div class="card-body p-4">
                                <div class="d-flex flex-column flex-sm-row gap-2 w-100 mb-3 mb-sm-0 order-2 order-md-1">
                                    <?php if ($is_siswa || ($is_admin && !empty($current_siswa_id_filter)) || ($is_guru && isset($_GET['siswa_id']) && !empty($_GET['siswa_id']))): ?>
                                        <a href="master_tugas_project_add.php<?= buildUrlQuery(['siswa_id' => $current_siswa_id_filter]) ?>"
                                            class="btn btn-primary w-100 animate__animated animate__fadeInUp animate__delay-0-3s">
                                            <i class="bx bx-plus me-1"></i> Tambah Jurnal PKL Per Kegiatan
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex flex-column flex-sm-row gap-2 w-100 order-1 order-md-2 mt-sm-3">
                                    <a href="<?php
                                                if ($is_siswa) {
                                                    echo 'dashboard_siswa.php';
                                                } elseif ($is_admin) {
                                                    echo 'index.php'; // Corrected path for admin dashboard
                                                } elseif ($is_guru) {
                                                    echo 'dashboard_guru.php';
                                                } else {
                                                    echo 'index.php';
                                                }
                                                ?>"
                                        class="btn btn-outline-secondary w-100 animate__animated animate__fadeInUp animate__delay-0-2s">
                                        <i class="bx bx-arrow-back me-1"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <form method="GET" action="">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="keyword"
                                            placeholder="Cari laporan (pekerjaan, perencanaan, pelaksanaan, catatan instruktur, nama siswa)..."
                                            value="<?= htmlspecialchars($keyword) ?>">
                                        <?php if ($is_admin && !empty($current_siswa_id_filter)): ?>
                                            <input type="hidden" name="siswa_id"
                                                value="<?= htmlspecialchars($current_siswa_id_filter) ?>">
                                        <?php endif; ?>
                                        <?php if ($is_guru && !empty($guru_session_id)): ?>
                                            <input type="hidden" name="pembimbing_id"
                                                value="<?= htmlspecialchars($guru_session_id) ?>">
                                            <?php if (isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])): ?>
                                                <input type="hidden" name="siswa_id"
                                                    value="<?= htmlspecialchars($_GET['siswa_id']) ?>">
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bx bx-search"></i> Cari
                                        </button>
                                        <a href="master_tugas_project.php<?= buildUrlQuery(['keyword' => null, 'page' => null]) ?>"
                                            class="btn btn-outline-secondary">
                                            <i class="bx bx-reset"></i> Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Daftar Jurnal PKL Per Kegiatan
                                    <?= htmlspecialchars($display_siswa_name) ?></h5>
                                <small class="text-muted">Total: <?= $total_records ?> Laporan</small>
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($project_journals_data)): ?>
                                    <div class="table-responsive text-nowrap d-none d-md-block"
                                        style="min-height: calc(100vh - 450px); overflow-y: auto;">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal</th>
                                                    <?php if ($is_admin || ($is_guru && (!isset($_GET['siswa_id']) || empty($_GET['siswa_id'])))): ?>
                                                        <th>Siswa</th>
                                                    <?php endif; ?>
                                                    <th>Nama Pekerjaan / Kegiatan</th>
                                                    <th>Perencanaan</th>
                                                    <th>Pelaksanaan</th>
                                                    <th>Gambar</th>
                                                    <th>Catatan Instruktur</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="table-border-bottom-0">
                                                <?php $no = $offset + 1; ?>
                                                <?php foreach ($project_journals_data as $journal): ?>
                                                    <tr>
                                                        <td><?= $no++ ?></td>
                                                        <td><?= htmlspecialchars(date('d M Y', strtotime($journal['tanggal_laporan']))) ?>
                                                        </td>
                                                        <?php if ($is_admin || ($is_guru && (!isset($_GET['siswa_id']) || empty($_GET['siswa_id'])))): ?>
                                                            <td><?= htmlspecialchars($journal['nama_siswa'] ?? '-') ?></td>
                                                        <?php endif; ?>
                                                        <td><strong><?= htmlspecialchars($journal['nama_pekerjaan']) ?></strong>
                                                        </td>
                                                        <td><?= htmlspecialchars(mb_strimwidth($journal['perencanaan_kegiatan'], 0, 50, "...")) ?>
                                                        </td>
                                                        <td><?= htmlspecialchars(mb_strimwidth($journal['pelaksanaan_kegiatan'], 0, 50, "...")) ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($journal['gambar'])): ?>
                                                                <a href="images/<?= htmlspecialchars($journal['gambar']) ?>"
                                                                    target="_blank">
                                                                    <img src="images/<?= htmlspecialchars($journal['gambar']) ?>"
                                                                        alt="Gambar Proyek"
                                                                        style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                                                                </a>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= htmlspecialchars(mb_strimwidth($journal['catatan_instruktur'] ?? '', 0, 50, "...")) ?>
                                                        </td>
                                                        <td>
                                                            <div class="dropdown">
                                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                                    data-bs-toggle="dropdown">
                                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                                </button>
                                                                <div class="dropdown-menu">
                                                                    <a class="dropdown-item"
                                                                        href="master_tugas_project_edit.php?id=<?= $journal['id_jurnal_kegiatan'] ?>">
                                                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                                                    </a>
                                                                    <?php if ($is_siswa || $is_admin): ?>
                                                                        <a class="dropdown-item text-danger"
                                                                            href="javascript:void(0);"
                                                                            onclick="confirmDeleteProjectJournal('<?= $journal['id_jurnal_kegiatan'] ?>', '<?= htmlspecialchars(addslashes($journal['nama_pekerjaan'])) ?>')">
                                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                                        </a>
                                                                    <?php endif; ?>
                                                                    <div class="dropdown-divider"></div>
                                                                    <a class="dropdown-item"
                                                                        href="master_tugas_project_print.php?id=<?= $journal['id_jurnal_kegiatan'] ?>"
                                                                        target="_blank">
                                                                        <i class="bx bx-printer me-1"></i> Lihat & Print
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-md-none p-3">
                                        <?php $no_mobile = $offset + 1; ?>
                                        <?php foreach ($project_journals_data as $journal): ?>
                                            <div class="card mb-3 shadow-sm border-start border-4 border-primary">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="text-primary mb-0">
                                                            <strong><?= htmlspecialchars($journal['nama_pekerjaan']) ?></strong>
                                                        </h6>
                                                        <div class="dropdown">
                                                            <button type="button"
                                                                class="btn btn-sm btn-icon-only p-0 dropdown-toggle hide-arrow"
                                                                data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item"
                                                                    href="master_tugas_project_edit.php?id=<?= $journal['id_jurnal_kegiatan'] ?>">
                                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                                </a>
                                                                <?php if ($is_siswa || $is_admin): ?>
                                                                    <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                        onclick="confirmDeleteProjectJournal('<?= $journal['id_jurnal_kegiatan'] ?>', '<?= htmlspecialchars(addslashes($journal['nama_pekerjaan'])) ?>')">
                                                                        <i class="bx bx-trash me-1"></i> Hapus
                                                                    </a>
                                                                <?php endif; ?>
                                                                <a class="dropdown-item"
                                                                    href="master_tugas_project_print.php?id=<?= $journal['id_jurnal_kegiatan'] ?>"
                                                                    target="_blank">
                                                                    <i class="bx bx-printer me-1"></i> Lihat & Print
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted mb-2 d-block"><i class="bx bx-calendar me-1"></i>
                                                        <?= htmlspecialchars(date('d M Y', strtotime($journal['tanggal_laporan']))) ?></small>
                                                    <?php if (!empty($journal['gambar'])): ?>
                                                        <div class="mb-2 text-center">
                                                            <a href="images/<?= htmlspecialchars($journal['gambar']) ?>"
                                                                target="_blank">
                                                                <img src="images/<?= htmlspecialchars($journal['gambar']) ?>"
                                                                    alt="Bukti Kegiatan" class="img-fluid rounded shadow-sm"
                                                                    style="max-height: 200px; object-fit: contain;">
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>

                                                    <p class="mb-2">
                                                        <strong>Perencanaan:</strong><br><?= nl2br(htmlspecialchars($journal['perencanaan_kegiatan'])) ?>
                                                    </p>
                                                    <p class="mb-0">
                                                        <strong>Pelaksanaan:</strong><br><?= nl2br(htmlspecialchars($journal['pelaksanaan_kegiatan'])) ?>
                                                    </p>
                                                    <p class="mb-0 text-muted small mt-2"><strong>Catatan
                                                            Instruktur:</strong><br><?= nl2br(htmlspecialchars($journal['catatan_instruktur'] ?? 'Belum ada catatan')) ?>
                                                    </p>
                                                    <?php if ($is_admin || ($is_guru && (!isset($_GET['siswa_id']) || empty($_GET['siswa_id'])))): ?>
                                                        <p class="mb-0 text-muted small mt-2"><strong>Siswa:</strong>
                                                            <?= htmlspecialchars($journal['nama_siswa'] ?? '-') ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                <?php else: ?>
                                    <div class="alert alert-warning text-center mt-4 mx-3" role="alert"
                                        style="min-height: 200px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                        <h5 class="alert-heading"><i class="bx bx-info-circle"></i> Data Tidak Ditemukan
                                        </h5>
                                        <p class="mb-0">
                                            <?php if (!empty($keyword)): ?>
                                                Tidak ada Jurnal PKL Per Kegiatan yang cocok dengan kata kunci
                                                "<strong><?= htmlspecialchars($keyword) ?></strong>".
                                            <?php elseif ($is_siswa): ?>
                                                Anda belum memiliki Jurnal PKL Per Kegiatan yang tercatat. Silakan tambahkan
                                                laporan pertama Anda.
                                            <?php elseif ($is_admin && !empty($current_siswa_id_filter)): ?>
                                                Siswa ini belum memiliki Jurnal PKL Per Kegiatan.
                                            <?php elseif ($is_admin && ($current_siswa_id_filter === null || $current_siswa_id_filter === "")): ?>
                                                Tidak ada Jurnal PKL Per Kegiatan yang ditemukan di sistem.
                                            <?php elseif ($is_guru && ($guru_session_id !== null) && (!isset($_GET['siswa_id']) || empty($_GET['siswa_id']))): ?>
                                                Belum ada Jurnal PKL Per Kegiatan dari siswa bimbingan Anda.
                                            <?php elseif ($is_guru && isset($_GET['siswa_id']) && !empty($_GET['siswa_id'])): ?>
                                                Siswa ini belum memiliki Jurnal PKL Per Kegiatan.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($total_pages > 1): ?>
                                <div class="card-footer bg-light border-top pt-3 pb-2">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center mb-0">
                                            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                                <a class="page-link"
                                                    href="<?= buildUrlQuery(['page' => $current_page - 1]) ?>"
                                                    aria-label="Previous">
                                                    <i class="tf-icon bx bx-chevrons-left"></i>
                                                </a>
                                            </li>
                                            <?php
                                            $num_links = 5;
                                            $start_page = max(1, $current_page - floor($num_links / 2));
                                            $end_page = min($total_pages, $current_page + floor($num_links / 2));

                                            if ($end_page - $start_page + 1 < $num_links) {
                                                if ($start_page == 1) {
                                                    $end_page = min($total_pages, $num_links);
                                                } elseif ($end_page == $total_pages) {
                                                    $start_page = max(1, $total_pages - $num_links + 1);
                                                }
                                            }

                                            if ($start_page > 1) {
                                                echo '<li class="page-item"><a class="page-link" href="' . buildUrlQuery(['page' => 1]) . '">1</a></li>';
                                                if ($start_page > 2) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }
                                            }

                                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                <li class="page-item <?= ($current_page == $i) ? 'active' : '' ?>">
                                                    <a class="page-link"
                                                        href="<?= buildUrlQuery(['page' => $i]) ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor;

                                            if ($end_page < $total_pages) {
                                                if ($end_page < $total_pages - 1) {
                                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                                }
                                                echo '<li class="page-item"><a class="page-link" href="' . buildUrlQuery(['page' => $total_pages]) . '">' . $total_pages . '</a></li>';
                                            }
                                            ?>
                                            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                                <a class="page-link"
                                                    href="<?= buildUrlQuery(['page' => $current_page + 1]) ?>"
                                                    aria-label="Next">
                                                    <i class="tf-icon bx bx-chevrons-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteProjectJournal(id, namaProyek) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: "Yakin ingin menghapus laporan <strong>" + namaProyek +
                    "</strong>?<br><small>Tindakan ini tidak dapat dibatalkan!</small>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    let deleteUrl = 'master_tugas_project_delete.php?id=' + id;

                    // Dynamically append all current GET parameters (excluding 'id')
                    let currentUrlParams = new URLSearchParams(window.location.search);
                    currentUrlParams.forEach((value, key) => {
                        if (key !==
                            'id') { // Ensure the 'id' of the current page is not passed as redirect_id
                            deleteUrl += '&redirect_' + key + '=' + encodeURIComponent(value);
                        }
                    });
                    window.location.href = deleteUrl;
                }
            });
        }
    </script>

    <?php include './partials/script.php'; ?>
</body>

</html>