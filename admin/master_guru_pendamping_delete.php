<?php

session_start();
// 1. Aturan utama: Cek apakah pengguna yang mengakses BUKAN seorang ADMIN.
if (!isset($_SESSION['admin_status_login']) || $_SESSION['admin_status_login'] !== 'logged_in') {

  // 2. Jika bukan admin, cek apakah dia adalah SISWA.
  if (isset($_SESSION['siswa_status_login']) && $_SESSION['siswa_status_login'] === 'logged_in') {
      // Jika benar siswa, kembalikan ke halaman siswa.
      header('Location: master_kegiatan_harian.php');
      exit();
  }
  // 3. TAMBAHAN: Jika bukan siswa, cek apakah dia adalah GURU.
  elseif (isset($_SESSION['guru_pendamping_status_login']) && $_SESSION['guru_pendamping_status_login'] === 'logged_in') {
      // Jika benar guru, kembalikan ke halaman guru.
      header('Location: ../../halaman_guru.php'); //belum di atur
      exit();
  }
  // 4. Jika bukan salah satu dari role di atas (admin, siswa, guru),
  // artinya pengguna belum login sama sekali. Arahkan ke halaman login.
  else {
      header('Location: ../login.php');
      exit();
  }
}

// 5. Jika lolos semua pemeriksaan di atas, maka dia adalah ADMIN yang sah.
// Tampilkan semua konten halaman ini.
include 'partials/db.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Cek apakah data ada
    $check = mysqli_query($koneksi, "SELECT * FROM guru_pembimbing WHERE id_pembimbing = '$id'");
    if (mysqli_num_rows($check) === 0) {
        echo "<script>
                alert('Data guru tidak ditemukan!');
                window.location.href = 'master_guru_pendamping.php';
              </script>";
        exit;
    }

    // Lanjutkan delete
    $delete = mysqli_query($koneksi, "DELETE FROM guru_pembimbing WHERE id_pembimbing = '$id'");

    if ($delete) {
        echo "<script>
                alert('Data guru berhasil dihapus.');
                window.location.href = 'master_guru_pendamping.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data guru.');
                window.history.back();
              </script>";
    }
} else {
    echo "<script>
            alert('ID guru tidak ditemukan.');
            window.location.href = 'master_guru_pendamping.php';
          </script>";
}
?>
