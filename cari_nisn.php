<?php
include 'admin/partials/db.php';

if (isset($_POST['query'])) {
    $search = mysqli_real_escape_string($koneksi, $_POST['query']);
    $result = mysqli_query($koneksi, "SELECT nisn FROM siswa WHERE nisn LIKE '%$search%' LIMIT 10");

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='nisn-item' style='padding: 8px; cursor: pointer;'>{$row['nisn']}</div>";
        }
    } else {
        echo "<div style='padding: 8px; color: gray;'>Tidak ditemukan</div>";
    }
}
?>
