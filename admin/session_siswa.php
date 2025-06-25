<?php
session_start();
if(isset($_SESSION['siswa']) == 'login'){
    header("Location: master_kegiatan_harian.php");
    exit;
}
