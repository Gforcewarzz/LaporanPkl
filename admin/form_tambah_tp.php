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

// Ambil semua data jurusan untuk dropdown
$jurusan_options = $koneksi->query("SELECT * FROM jurusan ORDER BY nama_jurusan ASC");

// Ambil semua TP untuk dropdown induk (tampilan awal)
$tp_result = $koneksi->query("SELECT * FROM tujuan_pembelajaran ORDER BY id_induk, kode_tp");
$semua_tp = [];
$tp_anak = [];
while($row = $tp_result->fetch_assoc()){
    $semua_tp[$row['id_tp']] = $row;
    $tp_anak[$row['id_induk']][] = $row['id_tp'];
}

// Fungsi rekursif untuk membuat opsi dropdown induk
function generate_tp_options($id_induk, $semua_tp, $tp_anak, $level = 0) {
    if (!isset($tp_anak[$id_induk])) return;
    
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;↳&nbsp;', $level);
    foreach ($tp_anak[$id_induk] as $id_tp) {
        $item = $semua_tp[$id_tp];
        echo "<option value='{$item['id_tp']}'>{$indent}" . htmlspecialchars($item['kode_tp']) . " - " . htmlspecialchars($item['deskripsi_tp']) . "</option>";
        generate_tp_options($id_tp, $semua_tp, $tp_anak, $level + 1);
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
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Master Data /</span> Tambah Kompetensi Baru</h4>
                        
                        <div class="card">
                            <h5 class="card-header">Form Tambah Kompetensi</h5>
                            <div class="card-body">
                                <form action="proses_tp.php" method="POST">
                                    <div class="mb-3">
                                        <label for="jurusan_id" class="form-label">Khusus Untuk Jurusan</label>
                                        <select id="jurusan_id" name="jurusan_id" class="form-select">
                                            <option value="">-- Berlaku untuk Semua Jurusan --</option>
                                            <?php while($jurusan = $jurusan_options->fetch_assoc()): ?>
                                                <option value="<?= $jurusan['id_jurusan'] ?>"><?= htmlspecialchars($jurusan['nama_jurusan']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div class="form-text">Pilihan di sini akan memfilter "Induk Kompetensi" di bawah.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="id_induk" class="form-label">Induk Kompetensi</label>
                                        <select id="id_induk" name="id_induk" class="form-select">
                                            <option value="">-- Jadikan Induk Utama (Level 1) --</option>
                                            <?php generate_tp_options(NULL, $semua_tp, $tp_anak); ?>
                                        </select>
                                        <div class="form-text">Pilih di mana kompetensi baru ini akan ditempatkan.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="kode_tp" class="form-label">Kode Kompetensi</label>
                                        <input type="text" class="form-control" id="kode_tp" name="kode_tp" placeholder="Contoh: 1.7 atau 3.1.6" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="deskripsi_tp" class="form-label">Deskripsi Kompetensi</label>
                                        <textarea class="form-control" id="deskripsi_tp" name="deskripsi_tp" rows="3" required></textarea>
                                    </div>
                                    <div class="mt-4">
                                        <a href="struktur_tp.php" class="btn btn-outline-secondary">Kembali</a>
                                        <button type="submit" name="simpan_tp" class="btn btn-primary">Simpan Kompetensi</button>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const jurusanSelect = document.getElementById('jurusan_id');
            const indukSelect = document.getElementById('id_induk');

            jurusanSelect.addEventListener('change', function() {
                const selectedJurusanId = this.value || '0'; // Gunakan '0' jika tidak ada yang dipilih

                // Simpan opsi pertama ("Jadikan Induk Utama")
                const defaultOption = indukSelect.querySelector('option[value=""]');

                // Tampilkan status loading
                indukSelect.disabled = true;
                indukSelect.innerHTML = '<option>Memuat pilihan...</option>';

                // Panggil API untuk mendapatkan daftar induk yang relevan
                fetch(`api_get_induk.php?jurusan_id=${selectedJurusanId}`)
                    .then(response => response.text()) // Ambil response sebagai HTML
                    .then(optionsHTML => {
                        // Kosongkan select, tambahkan kembali opsi default, lalu tambahkan opsi baru dari API
                        indukSelect.innerHTML = '';
                        indukSelect.appendChild(defaultOption);
                        indukSelect.innerHTML += optionsHTML;
                        indukSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching parent TPs:', error);
                        indukSelect.innerHTML = '';
                        indukSelect.appendChild(defaultOption);
                        indukSelect.innerHTML += '<option disabled>Gagal memuat data</option>';
                        indukSelect.disabled = false;
                    });
            });
        });
    </script>
</body>
</html>