<?php
session_start();
require_once 'partials/db.php';

// Keamanan & inisialisasi
if (!isset($_SESSION['admin_status_login']) && !isset($_SESSION['guru_pendamping_status_login'])) {
    header('Location: ../login.php');
    exit();
}
if (!isset($_GET['id_siswa']) || !filter_var($_GET['id_siswa'], FILTER_VALIDATE_INT)) {
    header('Location: form_penilaian.php?status=error&msg=siswa_tidak_valid');
    exit();
}
$id_siswa = (int)$_GET['id_siswa'];

// Ambil data siswa
$stmt_siswa = $koneksi->prepare("SELECT nama_siswa, jurusan_id FROM siswa WHERE id_siswa = ?");
$stmt_siswa->bind_param('i', $id_siswa);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->get_result();
if ($result_siswa->num_rows === 0) {
    header('Location: form_penilaian.php?status=error&msg=siswa_tidak_ditemukan');
    exit();
}
$siswa = $result_siswa->fetch_assoc();
$nama_siswa = htmlspecialchars($siswa['nama_siswa']);
$jurusan_id = $siswa['jurusan_id'];
$stmt_siswa->close();

// Ambil TP statis dari database
$query_tp = "SELECT id_tp, id_induk, kode_tp, deskripsi_tp FROM tujuan_pembelajaran WHERE jurusan_id = ? OR jurusan_id IS NULL ORDER BY kode_tp ASC";
$stmt_tp = $koneksi->prepare($query_tp);
$stmt_tp->bind_param('i', $jurusan_id);
$stmt_tp->execute();
$flat_tp_list = $stmt_tp->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_tp->close();

// Ambil SEMUA jurnal kegiatan untuk diisi ke dropdown
$stmt_jurnal = $koneksi->prepare("SELECT id_jurnal_kegiatan, nama_pekerjaan FROM jurnal_kegiatan WHERE siswa_id = ? ORDER BY tanggal_laporan DESC");
$stmt_jurnal->bind_param('i', $id_siswa);
$stmt_jurnal->execute();
$all_jurnals = $stmt_jurnal->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_jurnal->close();

// --- BLOK YANG DIPERBAIKI ---
// Ambil Nilai & Jurnal yang sudah ada/dipilih sebelumnya
$existing_scores = [];
$selected_jurnals_map = [];
// PERBAIKAN: Query sekarang mengambil dari kolom id_tp DAN jurnal_kegiatan_id
$stmt_nilai = $koneksi->prepare("SELECT id_tp, jurnal_kegiatan_id, nilai FROM nilai_siswa WHERE siswa_id = ?");
$stmt_nilai->bind_param('i', $id_siswa);
$stmt_nilai->execute();
$result_nilai = $stmt_nilai->get_result();
while ($row = $result_nilai->fetch_assoc()) {
    // PERBAIKAN: Logika untuk membedakan nilai TP dan nilai Jurnal
    if (!empty($row['jurnal_kegiatan_id'])) {
        // Ini adalah nilai untuk Jurnal
        $jurnal_id = $row['jurnal_kegiatan_id'];
        $key = 'jurnal_' . $jurnal_id;
        $existing_scores[$key] = $row['nilai'];
        $selected_jurnals_map[$jurnal_id] = true;
    } else if (!empty($row['id_tp'])) {
        // Ini adalah nilai untuk TP statis
        $existing_scores[$row['id_tp']] = $row['nilai'];
    }
}
$stmt_nilai->close();
$pre_selected_jurnal_ids = array_keys($selected_jurnals_map);
// --- AKHIR BLOK YANG DIPERBAIKI ---


// Bangun struktur pohon (tree) dari TP statis
$tp_tree = [];
$tp_map = [];
foreach ($flat_tp_list as $tp_item) {
    $tp_item['children'] = [];
    $tp_map[$tp_item['id_tp']] = $tp_item;
}
foreach ($tp_map as $id => &$tp_item) {
    if ($tp_item['id_induk'] != 0 && isset($tp_map[$tp_item['id_induk']])) {
        $tp_map[$tp_item['id_induk']]['children'][] = &$tp_item;
    } else {
        $tp_tree[] = &$tp_item;
    }
}
unset($tp_item);

function render_static_tp_rows($tp_items, $existing_scores, $depth = 0)
{
    $output = '';
    foreach ($tp_items as $tp) {
        $id_tp = $tp['id_tp'];
        $has_children = !empty($tp['children']);

        if ($tp['kode_tp'] === '3') {
            $output .= render_single_tp_row($tp, $existing_scores, $depth, true);
            $output .= '<div id="jurnal-slots-container" data-parent-tp-id="' . $id_tp . '" style="padding-left: 25px;">';
            for ($i = 1; $i <= 3; $i++) {
                $output .= '
                <div class="jurnal-slot row mb-3 align-items-center border-bottom pb-3" data-slot-index="' . $i . '">
                    <div class="col-md-7">
                        <label class="form-label fw-bold mb-1">3.' . $i . ' - Pilih Kegiatan Jurnal</label>
                        <select class="form-select jurnal-select"><option value="">-- Pilih Kegiatan --</option></select>
                        <small class="text-muted d-block mt-1 jurnal-deskripsi"></small>
                    </div>
                    <div class="col-md-5">
                        <input type="number" class="form-control score-input" min="0" max="100" placeholder="Pilih kegiatan dahulu" disabled>
                    </div>
                </div>';
            }
            $output .= '</div>';
        } else {
            $output .= render_single_tp_row($tp, $existing_scores, $depth, $has_children);
            if ($has_children) {
                $output .= render_static_tp_rows($tp['children'], $existing_scores, $depth + 1);
            }
        }
    }
    return $output;
}

function render_single_tp_row($tp, $existing_scores, $depth, $is_parent)
{
    $id_tp = $tp['id_tp'];
    $current_value = $existing_scores[$id_tp] ?? '';
    $indent_style = 'padding-left: ' . ($depth * 25) . 'px;';
    $label_class = $is_parent ? 'fw-bold text-dark' : '';
    $input_attributes = $is_parent ? 'readonly style="background-color: #e9ecef; font-weight: bold;"' : 'required';

    return '
    <div class="row mb-3 align-items-center border-bottom pb-3">
        <div class="col-md-7"><div style="' . $indent_style . '">
            <label class="form-label mb-0 ' . $label_class . '">' . htmlspecialchars($tp['kode_tp']) . '</label>
            <small class="text-muted d-block">' . htmlspecialchars($tp['deskripsi_tp']) . '</small>
        </div></div>
        <div class="col-md-5"><input type="number" class="form-control score-input"
            id="nilai_' . $id_tp . '" name="nilai[' . $id_tp . ']" value="' . $current_value . '"
            min="0" max="100" placeholder="' . ($is_parent ? 'Nilai Rata-rata' : '0-100') . '"
            data-id-tp="' . $id_tp . '" data-id-induk="' . $tp['id_induk'] . '" ' . $input_attributes . '>
        </div>
    </div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'partials/head.php'; ?>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="card shadow-lg">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Formulir Penilaian untuk: <span
                                        class="text-primary fw-bold"><?= $nama_siswa ?></span></h5>
                                <small class="text-muted">Pilih hingga 3 kegiatan dari dropdown untuk dinilai.</small>
                            </div>
                            <div class="card-body p-4">
                                <form action="proses_nilai.php" method="POST" id="form-penilaian">
                                    <input type="hidden" name="id_siswa" value="<?= $id_siswa ?>">
                                    <?= render_static_tp_rows($tp_tree, $existing_scores) ?>
                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="form_penilaian.php" class="btn btn-outline-secondary">Kembali</a>
                                        <button type="submit" name="submit" class="btn btn-primary">Simpan
                                            Penilaian</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'partials/script.php' ?>
    <script>
    // Sisa JavaScript tidak perlu diubah, karena sudah dirancang untuk menerima data yang benar dari PHP
    document.addEventListener('DOMContentLoaded', function() {
        const allJurnals = <?= json_encode($all_jurnals) ?>;
        const preSelectedJurnalIds = <?= json_encode($pre_selected_jurnal_ids) ?>;
        const existingScores = <?= json_encode($existing_scores) ?>;

        const form = document.getElementById('form-penilaian');
        const jurnalSlotsContainer = document.getElementById('jurnal-slots-container');
        const parentTP3_ID = jurnalSlotsContainer ? jurnalSlotsContainer.dataset.parentTpId : null;

        if (jurnalSlotsContainer) {
            const slots = jurnalSlotsContainer.querySelectorAll('.jurnal-slot');
            const selects = jurnalSlotsContainer.querySelectorAll('.jurnal-select');

            function initializeDropdowns() {
                let optionsHtml = '<option value="">-- Pilih Kegiatan --</option>';
                allJurnals.forEach(jurnal => {
                    optionsHtml +=
                        `<option value="${jurnal.id_jurnal_kegiatan}">${jurnal.nama_pekerjaan}</option>`;
                });
                selects.forEach(select => select.innerHTML = optionsHtml);

                preSelectedJurnalIds.slice(0, 3).forEach((jurnalId, index) => {
                    if (slots[index]) {
                        const select = slots[index].querySelector('.jurnal-select');
                        select.value = jurnalId;
                        updateSlotUI(select);
                    }
                });
                updateDropdownOptions();
            }

            function updateSlotUI(selectElement) {
                const slot = selectElement.closest('.jurnal-slot');
                const scoreInput = slot.querySelector('.score-input');
                const deskripsiEl = slot.querySelector('.jurnal-deskripsi');
                const jurnalId = selectElement.value;

                if (jurnalId) {
                    deskripsiEl.textContent = selectElement.options[selectElement.selectedIndex].text;
                    scoreInput.disabled = false;
                    scoreInput.placeholder = '0-100';
                    scoreInput.name = `nilai[jurnal_${jurnalId}]`;
                    scoreInput.dataset.idTp = `jurnal_${jurnalId}`;
                    scoreInput.dataset.idInduk = parentTP3_ID;
                    scoreInput.value = existingScores[`jurnal_${jurnalId}`] || '';
                } else {
                    deskripsiEl.textContent = '';
                    scoreInput.disabled = true;
                    scoreInput.placeholder = 'Pilih kegiatan dahulu';
                    scoreInput.name = '';
                    scoreInput.value = '';
                }
            }

            function updateDropdownOptions() {
                const selectedValues = Array.from(selects).map(s => s.value).filter(v => v);
                selects.forEach(select => {
                    Array.from(select.options).forEach(option => {
                        if (option.value) {
                            option.style.display = (selectedValues.includes(option.value) &&
                                select.value !== option.value) ? 'none' : '';
                        }
                    });
                });
            }

            selects.forEach(select => {
                select.addEventListener('change', function() {
                    updateSlotUI(this);
                    updateDropdownOptions();
                    updateParentAverage(this.closest('.jurnal-slot').querySelector(
                        '.score-input'));
                });
            });

            if (parentTP3_ID) {
                initializeDropdowns();
            }
        }

        function updateParentAverage(childElement) {
            const parentId = childElement.dataset.idInduk;
            if (!parentId || parentId == '0') return;

            const parentInput = document.querySelector(`.score-input[data-id-tp='${parentId}']`);
            if (!parentInput) return;

            const childInputs = document.querySelectorAll(`.score-input[data-id-induk='${parentId}']`);
            let total = 0,
                count = 0;

            childInputs.forEach(input => {
                if (!input.disabled && input.value.trim() !== '') {
                    const value = parseFloat(input.value);
                    if (!isNaN(value)) {
                        total += value;
                        count++;
                    }
                }
            });

            parentInput.value = (count > 0) ? Math.round((total / count) * 10) / 10 : '';
            updateParentAverage(parentInput);
        }

        form.addEventListener('input', function(e) {
            if (e.target.classList.contains('score-input') && !e.target.readOnly) {
                updateParentAverage(e.target);
            }
        });

        document.querySelectorAll('.score-input:not([readonly])').forEach(input => {
            if (input.value.trim() !== '') {
                updateParentAverage(input);
            }
        });

        // Panggil kalkulasi awal setelah inisialisasi dropdown selesai
        if (jurnalSlotsContainer) {
            updateParentAverage(jurnalSlotsContainer.querySelector('.score-input'));
        }
    });
    </script>
</body>

</html>