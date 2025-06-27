<?php
session_start();
if (!isset($_SESSION['id_siswa'])) {
    header("Location: ../login.php");
    exit;
}
