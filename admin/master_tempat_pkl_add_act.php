<?php
include 'partials/db.php';

$nama        = $_POST['nama_perusahaan'];
$alamat      = $_POST['alamat'];
$kontak      = $_POST['kontak'];
$kuota       = $_POST['kuota_siswa'];
$instruktur  = $_POST['nama_instruktur_lapangan'];
$jurusan_id  = $_POST['jurusan_id'];

if (!$nama || !$alamat || !$kontak || !$kuota || !$jurusan_id) {
    echo "<script>
        alert('Semua field wajib diisi!');
        window.history.back();
    </script>";
    exit;
}

$query = "INSERT INTO tempat_pkl (nama_tempat_pkl, alamat, alamat_kontak, nama_instruktur, kuota_siswa, jurusan_id)
          VALUES ('$nama', '$alamat', '$kontak', '$instruktur', '$kuota', '$jurusan_id')";

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Proses Tambah Tempat PKL</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php if ($result): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data tempat PKL berhasil ditambahkan.',
        confirmButtonText: 'OK'
    }).then(() => {
        window.location.href = 'master_tempat_pkl.php';
    });
</script>
<?php else: ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        html: 'Data gagal disimpan.<br><?= addslashes(mysqli_error($koneksi)) ?>',
        confirmButtonText: 'Kembali'
    }).then(() => {
        window.history.back();
    });
</script>
<?php endif; ?>
</body>
</html>
