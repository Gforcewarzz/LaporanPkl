<?php
session_start();

// Keamanan: Hanya admin atau guru yang boleh mengakses halaman ini
$is_admin = isset($_SESSION['admin_status_login']) && $_SESSION['admin_status_login'] === 'logged_in';
$is_guru = isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in';

if (!$is_admin && !$is_guru) {
    $_SESSION['alert_message'] = 'Anda tidak memiliki izin untuk melakukan aksi ini.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Akses Ditolak!';
    header('Location: ../login.php'); // Asumsi login.php ada di luar folder admin
    exit();
}

include 'partials/db.php'; // Sertakan file koneksi database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_absensi = $_POST['id_absensi'] ?? null; // Akan ada jika mode UPDATE
    $siswa_id = $_POST['siswa_id'] ?? null;      // Akan ada jika mode INSERT
    // Gunakan tanggal dari form jika ada, jika tidak, gunakan tanggal saat ini
    $tanggal_absen = $_POST['tanggal_absen'] ?? date('Y-m-d');

    $statusAbsen = $_POST['statusAbsen'] ?? '';
    // Keterangan akan diambil jika statusnya Sakit/Izin, jika tidak, akan di-set null
    $keterangan = !empty($_POST['keterangan']) ? trim($_POST['keterangan']) : null;
    $old_bukti_foto = $_POST['old_bukti_foto'] ?? null; // Hanya ada saat mode UPDATE
    $bukti_foto_path = $old_bukti_foto; // Default path ke foto lama, akan diubah jika ada upload baru

    $is_update_mode = !empty($id_absensi); // Flag: true jika UPDATE, false jika INSERT

    // --- Validasi Parameter Utama ---
    if ($is_update_mode) {
        // Mode UPDATE: pastikan ID absensi valid
        if (empty($id_absensi)) {
            $_SESSION['alert_message'] = 'ID Absensi tidak valid untuk update.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Error!';
            header('Location: master_data_absensi_siswa.php'); // Redirect ke daftar absen
            exit();
        }
    } else {
        // Mode INSERT: pastikan ID siswa dan tanggal absensi ada
        if (empty($siswa_id) || empty($tanggal_absen)) {
            $_SESSION['alert_message'] = 'Parameter siswa atau tanggal tidak lengkap untuk menambah absensi.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Error!';
            header('Location: master_data_absensi_siswa.php'); // Redirect ke daftar absen
            exit();
        }
    }

    // PERUBAHAN DI SINI: Tambahkan 'Libur' ke daftar status yang valid
    if (!in_array($statusAbsen, ['Hadir', 'Sakit', 'Izin', 'Alfa', 'Libur'])) {
        $_SESSION['alert_message'] = 'Status absensi tidak valid.';
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Gagal Simpan!';
        // Redirect kembali ke form edit/add dengan parameter yang sesuai
        $redirect_url_params = $is_update_mode ? 'id=' . $id_absensi : 'siswa_id=' . $siswa_id . '&tanggal=' . $tanggal_absen;
        header('Location: master_data_absensi_siswa_edit.php?' . $redirect_url_params);
        exit();
    }

    // --- Logika Upload File Bukti Foto ---
    // Path folder untuk gambar bukti absensi (langsung nama folder dari 'admin/')
    $target_dir = "image_absensi/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0775, true); // Buat folder jika belum ada
    }

    // PERUBAHAN DI SINI: Logika upload/validasi keterangan/bukti foto hanya untuk Sakit atau Izin
    if ($statusAbsen === 'Sakit' || $statusAbsen === 'Izin') {
        if (empty($keterangan)) {
            $_SESSION['alert_message'] = 'Keterangan wajib diisi untuk status ' . htmlspecialchars($statusAbsen) . '.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Gagal Simpan!';
            $redirect_url_params = $is_update_mode ? 'id=' . $id_absensi : 'siswa_id=' . $siswa_id . '&tanggal=' . $tanggal_absen;
            header('Location: master_data_absensi_siswa_edit.php?' . $redirect_url_params);
            exit();
        }

        // Cek apakah ada file baru diunggah ATAU jika tidak ada file lama dan tidak ada file baru
        if (isset($_FILES['buktiFoto']) && $_FILES['buktiFoto']['error'] === UPLOAD_ERR_OK) {
            // Jika ada file baru diunggah, hapus file lama jika ada
            if (!empty($old_bukti_foto) && file_exists($target_dir . $old_bukti_foto)) {
                unlink($target_dir . $old_bukti_foto);
            }

            $file_name = uniqid('bukti_') . '_' . basename($_FILES["buktiFoto"]["name"]);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $allowed_extensions = array("jpg", "png", "jpeg");
            $max_file_size = 2 * 1024 * 1024; // 2MB

            if (!in_array($imageFileType, $allowed_extensions)) {
                $_SESSION['alert_message'] = 'Maaf, hanya file JPG, JPEG, & PNG yang diizinkan.';
                $_SESSION['alert_type'] = 'error';
                $_SESSION['alert_title'] = 'Gagal Upload!';
                $redirect_url_params = $is_update_mode ? 'id=' . $id_absensi : 'siswa_id=' . $siswa_id . '&tanggal=' . $tanggal_absen;
                header('Location: master_data_absensi_siswa_edit.php?' . $redirect_url_params);
                exit();
            }
            if ($_FILES["buktiFoto"]["size"] > $max_file_size) {
                $_SESSION['alert_message'] = 'Maaf, ukuran file Anda terlalu besar. Maksimal 2MB.';
                $_SESSION['alert_type'] = 'error';
                $_SESSION['alert_title'] = 'Gagal Upload!';
                $redirect_url_params = $is_update_mode ? 'id=' . $id_absensi : 'siswa_id=' . $siswa_id . '&tanggal=' . $tanggal_absen;
                header('Location: master_data_absensi_siswa_edit.php?' . $redirect_url_params);
                exit();
            }

            if (move_uploaded_file($_FILES["buktiFoto"]["tmp_name"], $target_file)) {
                $bukti_foto_path = $file_name; // Simpan nama file baru untuk database
            } else {
                $_SESSION['alert_message'] = 'Terjadi kesalahan saat mengunggah file bukti baru.';
                $_SESSION['alert_type'] = 'error';
                $_SESSION['alert_title'] = 'Gagal Upload!';
                error_log("Error moving uploaded file: " . $_FILES["buktiFoto"]["error"]);
                $redirect_url_params = $is_update_mode ? 'id=' . $id_absensi : 'siswa_id=' . $siswa_id . '&tanggal=' . $tanggal_absen;
                header('Location: master_data_absensi_siswa_edit.php?' . $redirect_url_params);
                exit();
            }
        } elseif (empty($old_bukti_foto) && empty($_FILES['buktiFoto']['tmp_name'])) {
            // Jika status Sakit/Izin, tidak ada file lama, DAN tidak ada file baru diunggah
            // Ini validasi jika admin/guru mencoba mengubah ke Sakit/Izin tanpa upload bukti atau bukti sebelumnya kosong
            $_SESSION['alert_message'] = 'Bukti foto wajib diunggah untuk status ' . htmlspecialchars($statusAbsen) . '.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Gagal Simpan!';
            $redirect_url_params = $is_update_mode ? 'id=' . $id_absensi : 'siswa_id=' . $siswa_id . '&tanggal=' . $tanggal_absen;
            header('Location: master_data_absensi_siswa_edit.php?' . $redirect_url_params);
            exit();
        }
        // Jika ada old_bukti_foto dan tidak ada upload baru, $bukti_foto_path akan tetap $old_bukti_foto, ini sudah benar
    } else { // Jika status Hadir, Alfa, ATAU LIBUR, pastikan keterangan dan bukti foto dihapus/dikosongkan
        // Hapus file lama jika ada dan status diubah ke Hadir/Alfa/Libur
        if (!empty($old_bukti_foto) && file_exists($target_dir . $old_bukti_foto)) {
            unlink($target_dir . $old_bukti_foto);
        }
        $keterangan = null;
        $bukti_foto_path = null;
    }

    // --- Proses INSERT atau UPDATE ke Database ---
    $success = false;
    if ($is_update_mode) {
        // Mode UPDATE: Perbarui record absensi yang sudah ada
        $stmt = $koneksi->prepare("UPDATE absensi_siswa SET status_absen = ?, keterangan = ?, bukti_foto = ? WHERE id_absensi = ?");
        if ($stmt) {
            $stmt->bind_param("sssi", $statusAbsen, $keterangan, $bukti_foto_path, $id_absensi);
            $success = $stmt->execute();
            $stmt->close();
        }
    } else {
        // Mode INSERT: Tambahkan record absensi baru
        // Pertama, cek duplikasi untuk siswa_id dan tanggal_absen (walaupun sudah ada di absensi_edit.php, validasi server-side tetap penting)
        $check_duplicate_stmt = $koneksi->prepare("SELECT id_absensi FROM absensi_siswa WHERE siswa_id = ? AND tanggal_absen = ?");
        if ($check_duplicate_stmt) {
            $check_duplicate_stmt->bind_param("is", $siswa_id, $tanggal_absen);
            $check_duplicate_stmt->execute();
            $duplicate_result = $check_duplicate_stmt->get_result();
            if ($duplicate_result->num_rows > 0) {
                $_SESSION['alert_message'] = 'Absensi untuk siswa ini pada tanggal tersebut sudah ada.';
                $_SESSION['alert_type'] = 'warning';
                $_SESSION['alert_title'] = 'Data Duplikat!';
                $check_duplicate_stmt->close();
                $koneksi->close();
                header('Location: master_data_absensi_siswa.php'); // Redirect ke daftar absen
                exit();
            }
            $check_duplicate_stmt->close();
        } else {
            error_log("Error preparing duplicate check: " . $koneksi->error);
            $_SESSION['alert_message'] = 'Terjadi kesalahan saat memeriksa duplikasi data.';
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_title'] = 'Gagal!';
            $koneksi->close();
            header('Location: master_data_absensi_siswa.php');
            exit();
        }

        $stmt = $koneksi->prepare("INSERT INTO absensi_siswa (siswa_id, tanggal_absen, status_absen, keterangan, bukti_foto, waktu_input) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("issss", $siswa_id, $tanggal_absen, $statusAbsen, $keterangan, $bukti_foto_path);
            $success = $stmt->execute();
            $stmt->close();
        }
    }

    // --- Tanggapan & Redirect ---
    if ($success) {
        $_SESSION['alert_message'] = $is_update_mode ? 'Absensi berhasil diperbarui!' : 'Absensi baru berhasil ditambahkan!';
        $_SESSION['alert_type'] = 'success';
        $_SESSION['alert_title'] = 'Berhasil!';
    } else {
        $_SESSION['alert_message'] = $is_update_mode ? 'Gagal memperbarui absensi: ' . $koneksi->error : 'Gagal menambahkan absensi baru: ' . $koneksi->error;
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_title'] = 'Gagal!';
        error_log("Database operation failed: " . $koneksi->error);
    }

    $koneksi->close();
    // Redirect kembali ke halaman daftar absensi setelah operasi selesai
    header('Location: master_data_absensi_siswa.php');
    exit();
} else {
    // Jika diakses langsung tanpa metode POST
    $_SESSION['alert_message'] = 'Akses tidak sah.';
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_title'] = 'Akses Ditolak!';
    header('Location: master_data_absensi_siswa.php');
    exit();
}