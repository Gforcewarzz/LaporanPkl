<?php
include 'partials/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data utama
    $id_siswa      = mysqli_real_escape_string($koneksi, $_POST['id_siswa']);
    $nama_siswa    = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $nisn          = mysqli_real_escape_string($koneksi, $_POST['nisn']);
    $no_induk      = mysqli_real_escape_string($koneksi, $_POST['no_induk']);
    $kelas         = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $status        = mysqli_real_escape_string($koneksi, $_POST['status']);

    // Ambil nama dari datalist
    $jurusan_nama     = mysqli_real_escape_string($koneksi, $_POST['jurusan_nama']);
    $guru_nama        = mysqli_real_escape_string($koneksi, $_POST['guru_nama']);
    $tempat_pkl_nama  = mysqli_real_escape_string($koneksi, $_POST['tempat_pkl_nama']);

    // Cari ID berdasarkan nama
    $jurusan_id = null;
    $q_jurusan = mysqli_query($koneksi, "SELECT id_jurusan FROM jurusan WHERE nama_jurusan = '$jurusan_nama' LIMIT 1");
    if ($row = mysqli_fetch_assoc($q_jurusan)) {
        $jurusan_id = $row['id_jurusan'];
    }

    $pembimbing_id = null;
    $q_guru = mysqli_query($koneksi, "SELECT id_pembimbing FROM guru_pembimbing WHERE nama_pembimbing = '$guru_nama' LIMIT 1");
    if ($row = mysqli_fetch_assoc($q_guru)) {
        $pembimbing_id = $row['id_pembimbing'];
    }

    $tempat_pkl_id = null;
    $q_tempat = mysqli_query($koneksi, "SELECT id_tempat_pkl FROM tempat_pkl WHERE nama_tempat_pkl = '$tempat_pkl_nama' LIMIT 1");
    if ($row = mysqli_fetch_assoc($q_tempat)) {
        $tempat_pkl_id = $row['id_tempat_pkl'];
    }

    // Validasi: apakah semua ID ditemukan?
    if (!$jurusan_id || !$pembimbing_id || !$tempat_pkl_id) {
        echo "<script>
            alert('Data Jurusan, Guru Pendamping, atau Tempat PKL tidak valid. Silakan pilih dari saran yang tersedia.');
            window.history.back();
        </script>";
        exit;
    }

    // Jalankan update
    $update = mysqli_query($koneksi, "UPDATE siswa SET
        nama_siswa     = '$nama_siswa',
        jenis_kelamin  = '$jenis_kelamin',
        nisn           = '$nisn',
        no_induk       = '$no_induk',
        kelas          = '$kelas',
        status         = '$status',
        jurusan_id     = '$jurusan_id',
        pembimbing_id  = '$pembimbing_id',
        tempat_pkl_id  = '$tempat_pkl_id'
        WHERE id_siswa = '$id_siswa'
    ");

    if ($update) {
        echo "<script>
            alert('Data siswa berhasil diperbarui.');
            window.location.href = 'master_data_siswa.php';
        </script>";
    } else {
        echo "<script>
            alert('Terjadi kesalahan saat memperbarui data.');
            window.history.back();
        </script>";
    }
} else {
    // Akses langsung tanpa POST
    header('Location: master_data_siswa.php');
    exit;
}
?>
