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

// --- AKSI SIMPAN DATA BARU ---
if (isset($_POST['simpan_tp'])) {
    $id_induk = !empty($_POST['id_induk']) ? (int)$_POST['id_induk'] : NULL;
    $kode_tp = trim($_POST['kode_tp']);
    $deskripsi_tp = trim($_POST['deskripsi_tp']);
    $jurusan_id = !empty($_POST['jurusan_id']) ? (int)$_POST['jurusan_id'] : NULL;

    if (empty($kode_tp) || empty($deskripsi_tp)) {
        $_SESSION['pesan_notifikasi'] = ['tipe' => 'error', 'judul' => 'Gagal!', 'pesan' => 'Kode dan Deskripsi tidak boleh kosong.'];
        header('Location: struktur_tp.php');
        exit();
    }
    
    try {
        $stmt = $koneksi->prepare("INSERT INTO tujuan_pembelajaran (id_induk, kode_tp, deskripsi_tp, jurusan_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $id_induk, $kode_tp, $deskripsi_tp, $jurusan_id);
        if ($stmt->execute()) {
            $_SESSION['pesan_notifikasi'] = ['tipe' => 'success', 'judul' => 'Berhasil!', 'pesan' => 'Kompetensi baru berhasil ditambahkan.'];
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
             $_SESSION['pesan_notifikasi'] = ['tipe' => 'error', 'judul' => 'Gagal!', 'pesan' => 'Kode Kompetensi "' . htmlspecialchars($kode_tp) . '" sudah ada.'];
        } else {
             $_SESSION['pesan_notifikasi'] = ['tipe' => 'error', 'judul' => 'Gagal!', 'pesan' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }
}

// --- AKSI UPDATE DATA ---
elseif (isset($_POST['update_tp'])) {
    $tp_data = $_POST['tp'];
    $koneksi->begin_transaction();
    try {
        $stmt = $koneksi->prepare("UPDATE tujuan_pembelajaran SET kode_tp = ?, deskripsi_tp = ?, jurusan_id = ? WHERE id_tp = ?");
        foreach ($tp_data as $id_tp => $data) {
            $id_tp_int = (int)$id_tp;
            $kode_tp = trim($data['kode_tp']);
            $deskripsi_tp = trim($data['deskripsi_tp']);
            $jurusan_id = !empty($data['jurusan_id']) ? (int)$data['jurusan_id'] : NULL;
            $stmt->bind_param("ssii", $kode_tp, $deskripsi_tp, $jurusan_id, $id_tp_int);
            $stmt->execute();
        }
        $stmt->close();
        $koneksi->commit();
        $_SESSION['pesan_notifikasi'] = ['tipe' => 'success', 'judul' => 'Berhasil!', 'pesan' => 'Data kompetensi telah diperbarui.'];
    } catch (mysqli_sql_exception $e) {
        $koneksi->rollback();
        $_SESSION['pesan_notifikasi'] = ['tipe' => 'error', 'judul' => 'Gagal!', 'pesan' => 'Gagal memperbarui data. Error: ' . $e->getMessage()];
    }
}

// --- AKSI HAPUS DATA ---
elseif (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id_to_delete = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id_to_delete > 0) {
        // ON DELETE CASCADE di database akan otomatis menghapus semua turunannya.
        $stmt = $koneksi->prepare("DELETE FROM tujuan_pembelajaran WHERE id_tp = ?");
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            $_SESSION['pesan_notifikasi'] = ['tipe' => 'success', 'judul' => 'Berhasil!', 'pesan' => 'Kompetensi beserta turunannya telah dihapus.'];
        } else {
            $_SESSION['pesan_notifikasi'] = ['tipe' => 'error', 'judul' => 'Gagal!', 'pesan' => 'Gagal menghapus data.'];
        }
        $stmt->close();
    }
}

$koneksi->close();
header('Location: struktur_tp.php');
exit();
?>