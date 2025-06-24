<?php
require "partials/db.php";

//cek apakah tombol submit sudah di tekan atau belum
if ( isset($_POST["submit"]) ){
    // var_dump($_POST);
    //cek apakah berhasil atau tidak
    if ( tambahSiswa($_POST) > 0 ){
        
        echo "
        <script>
        alert('data berhasil di tambahkan!');
        document.location.href = 'master_data_siswa.php';
        </script>
        
        ";

    }else{
        echo "<script>
        alert('data gagal di tambahkan!');
        document.location.href = 'master_data_siswa.php';
        </script>";
    }






}
