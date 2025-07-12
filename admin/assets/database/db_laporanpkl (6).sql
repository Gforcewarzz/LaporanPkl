-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 30, 2025 at 02:02 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_laporanpkl`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_admin` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama_admin`, `email`, `created_at`) VALUES
(1, 'warzz', '$2y$10$CplBa3GrE2/h6P.p.FuuvOUIAyg5Fp3efRObKGOCUtYrq6c6VgD0a', 'warzz', 'rizzlonely811@gmail.com', '2025-06-27 12:55:36'),
(2, 'Easy', '$2y$10$EpuLETFAayHoS6qjjZgFAeRSXIEj3.km5Vk.fB8qdE356ZLQdHun.', 'Easy bree', 'easyy@gmail.com', '2025-06-28 01:10:26');

-- --------------------------------------------------------

--
-- Table structure for table `guru_pembimbing`
--

CREATE TABLE `guru_pembimbing` (
  `id_pembimbing` int NOT NULL,
  `nama_pembimbing` varchar(100) NOT NULL,
  `nip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `guru_pembimbing`
--

INSERT INTO `guru_pembimbing` (`id_pembimbing`, `nama_pembimbing`, `nip`, `jenis_kelamin`, `password`) VALUES
(1, 'Ahmad Fauzi', '198112001001', 'Laki-laki', '$2y$10$yq5CNAsYYGXiKdCwgZfy.esEeq/lt4eNsUBBrSTxrYVy0nrMbR5Fq'),
(2, 'Siti Aminah', '198212002002', 'Perempuan', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(3, 'Budi Santoso', '198312003003', 'Laki-laki', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(4, 'Dewi Lestari', '198412004004', 'Perempuan', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(5, 'Joko Prasetyo', '198512005005', 'Laki-laki', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(6, 'Rina Kurnia', '198612006006', 'Perempuan', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(7, 'Heri Wijaya', '198712007007', 'Laki-laki', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(8, 'Lina Marlina', '198812008008', 'Perempuan', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(9, 'Andi Saputra', '198912009009', 'Laki-laki', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(10, 'Desi Ratnasari', '19810120100010', 'Perempuan', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(13, 'Ibrahim Zaenal', '198008191826171', 'Laki-laki', '$2y$10$biJMwaSVowgIQgskMvqnSOhUmf2AetJcZkLfbKELU81CYFvJaZBva');

-- --------------------------------------------------------

--
-- Table structure for table `jurnal_harian`
--

CREATE TABLE `jurnal_harian` (
  `id_jurnal_harian` int NOT NULL,
  `tanggal` date NOT NULL,
  `pekerjaan` text,
  `catatan` text,
  `siswa_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jurnal_kegiatan`
--

CREATE TABLE `jurnal_kegiatan` (
  `id_jurnal_kegiatan` int NOT NULL,
  `nama_pekerjaan` varchar(100) DEFAULT NULL,
  `perencanaan_kegiatan` text,
  `pelaksanaan_kegiatan` text,
  `catatan_instruktur` text,
  `gambar` varchar(255) DEFAULT NULL,
  `siswa_id` int DEFAULT NULL,
  `tanggal_laporan` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jurusan`
--

CREATE TABLE `jurusan` (
  `id_jurusan` int NOT NULL,
  `nama_jurusan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jurusan`
--

INSERT INTO `jurusan` (`id_jurusan`, `nama_jurusan`) VALUES
(1, 'Rekayasa Perangkat Lunak'),
(2, 'Desain Komunikasi Visual '),
(3, 'Teknik Kendaraan Ringan'),
(4, 'Teknik Pengelasan'),
(5, 'Farmasi Industri'),
(6, 'Teknik Bodi Otomotif'),
(7, 'Desain Permodelan Informasi Bangunan'),
(8, 'Akuntansi Keuangan Lembaga');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int NOT NULL,
  `password` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `no_induk` varchar(20) NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `kelas` varchar(10) DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif','Selesai') NOT NULL,
  `jurusan_id` int DEFAULT NULL,
  `pembimbing_id` int DEFAULT NULL,
  `tempat_pkl_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `password`, `nama_siswa`, `no_induk`, `nisn`, `jenis_kelamin`, `kelas`, `status`, `jurusan_id`, `pembimbing_id`, `tempat_pkl_id`) VALUES
(124, '$2y$10$0cHxj/jTXsSvjG6CTcM7p.1lZC1dCUjlP9KcHuaOCCXip8Etb.Ese', 'AJI DEWANGGA', '2324-10-024', '0075545180', 'Laki-laki', 'XI RPL 1', 'Aktif', 1, NULL, 303),
(125, '$2y$10$4FdksMEsAHldoZpZJX2/Z.5FI6dAXnFoLsYJ77PBkK5ixjK151Zfq', 'AMANAH FITRIYANI', '2324-10-045', '0071526017', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 377),
(126, '$2y$10$RhFXfnHH2gVVbUikPX1dp.oPyAdz3BrJJhuOlrV1KOFlw/uxYChCe', 'ANGGIA RISA AULIA', '2324.10.061', '0088709687', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 294),
(127, '$2y$10$Q9nzC.J3osmSp4wpLmTHbux.vRSzlT.HFomHa4u9chB2Nh8QtxWwO', 'ANJAS APRILIYANTO', '2324-10-066', '0083460119', 'Laki-laki', 'XI RPL 1', 'Aktif', 1, NULL, 304),
(128, '$2y$10$t0k4KEmh8rMzsxHh0go9MuRgXshV1wsnzHKFBLW.w6EIh1Xl2yM2.', 'CAHYA MAULANA JAN', '2324-10-089', '0087843835', 'Laki-laki', 'XI RPL 1', 'Aktif', 1, NULL, 298),
(129, '$2y$10$jJfBceP7WCxONYjVHt6dgOcevgClVHOjjd3oH5jX4YYz8wdD6UH.G', 'DEA LIANA', '2324-10-106', '0076561544', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 299),
(130, '$2y$10$JBJTqBUlUVTzH3UtxM0QpOAce3ZrGrlct3d647EFNLYhyzevOgEri', 'DENIS MAULANA', '2324-10-123', '0082048462', 'Laki-laki', 'XI RPL 1', 'Aktif', 1, NULL, 365),
(131, '$2y$10$WyOEWam3lm9eEO6pgpop3uMC/Ie4u9NE8Lh0/UpKddgtg4Tt7Tk.q', 'DHONY FUJIANTO', '2324-10-134', '0078169129', 'Laki-laki', 'XI RPL 1', 'Aktif', 1, NULL, 298),
(132, '$2y$10$CI0Moetn1ZZ94j6XFfYwVucfE/6UTmQBGz1uRNwxB/ep/wLMZaKvi', 'DIANA PUSPITA SARI', '2324-10-137', '0087727454', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 294),
(133, '$2y$10$J.4XGkmoNmCn0RkWb/4f1umPShqTdBzEk9QJq7FhAohChXuWM90aW', 'DINA EGISTIN', '2324-10-144', '0076095295', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 365),
(134, '$2y$10$0oKQVQ.B3L37Ai0zRhB5PeT1tIUENWDLEHK6q9oxNJofyCSBTD.0W', 'DINA JULIANTI', '2324-10-148', '0086159629', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 300),
(135, '$2y$10$45XkqbwBxflr1V9YqhV4POpMyPcetrCb16xh9Wi10LTWAPjUjcVtW', 'DWIKY RIADY JULIANSYAH', '2324-10-156', '3089253203', 'Laki-laki', 'XI RPL 1', 'Aktif', 1, NULL, 303),
(136, '$2y$10$Fq6TQfnLBNkPjD/8QvDNp.LFakoSXmomyaXxH1YooTRaO09q9U0/e', 'ELANTINA', '2324-10-164', '0084455569', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 365),
(137, '$2y$10$NqqCkfXc9M7B0lqLh.7qWOKheQ39j.xsfS82lcGG1LPXiGLOB2hh2', 'FARHAN GHIFAARI NAFIS', '2324-10-180', '0075158451', 'Laki-laki', 'XI RPL 1', 'Aktif', 1, NULL, 365),
(138, '$2y$10$sK9J7Iq83BmM5sd44mkHF.QNFZER2Uo58IGOPca/Q8sQay4ChY9cS', 'ICHA LIVINA MILANASYA', '2324-10-218', '0084016085', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 299),
(139, '$2y$10$3gLynVZAzRvLvdKhrJaTVuCtaxxPlaWs4rtmstMu6QnKZ/ge3xGee', 'KEZIA LAURA', '2324-10-249', '0082793229', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, NULL),
(140, '$2y$10$lXJ4YMu68eje.t7r06DeZuD6LFP0ftgXKORpghyfB32gDKHM8EDMu', 'LARASATI', '2324-10-257', '0085741983', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 301),
(141, '$2y$10$6zUKNBePcGc23Mzwc8nO.OhqiHH7zCAZQT5CeMDLD8qBWuXlSTpg2', 'MEGA SEPRIYANI', '2324.10.275', '0072602669', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 299),
(142, '$2y$10$0mX8sa3AczyqO.AoOEqxMuHRGDHSfIj4jG2fmQT5DYAHFzgrs.oOa', 'MUHAMAD FADEL ALEXANDER', '2324-10-301', '0075020719', 'Laki-laki', 'XI RPL 1', 'Aktif', 1, NULL, 304),
(143, '$2y$10$UvJILyd7zoWOwXqbunOs9.4be7Ca7tWpyxpM6P8dado41U3bRXjf2', 'MULYANA AMIR HAMZAH', '2324-10-315', '0078618338', 'Laki-laki', 'XI RPL 1', 'Aktif', 1, NULL, 377),
(144, '$2y$10$jBn5Y2O98NyqgPjRfheeT.5vl3NqUL797hanCygoe8sEQsRlagyvK', 'NAILA RAMDINI', '2324-10-318', '0073671414', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 365),
(145, '$2y$10$x5W31FWe3aIjFS2rcx9t8.PckSg4OJn./8mn539x7GvHdjl12tm1S', 'REZHA ADITYA PRATAMA', '2324-10-399', '0084546604', 'Laki-laki', 'XI RPL 1', 'Aktif', 1, NULL, 365),
(146, '$2y$10$SwDI/b0wTfQTsyzBXKBXK.5HQZhDRHrvci1Vw3QW9RHzJ7vpGOeee', 'RIRIN DWI SETIAWATI', '2324-10-406', '0088442349', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 305),
(147, '$2y$10$tx95AzFpwrx7NYROiOkSye.b/68yI7vVx26oU8cmgMKOcLXZWj2yi', 'RITA AMELIA', '2324-10-412', '0077788600', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 365),
(148, '$2y$10$4g4YUERexs1p3liz4rdU2uDskSjFSus8J8pRnzqMkFCjMGmW7kfES', 'SALMA FITRIA RAHMADANI', '2324-10-431', '0086097517', 'Laki-laki', 'XI RPL 1', 'Aktif', 1, NULL, 309),
(149, '$2y$10$tzOkJ2Eq0jjwf/Ff5MuP5.P9.HqLzqOklKRCF.WUoWFVCXhgr5/i.', 'SALSA YULIANA DEWI', '2324-10-434', '0083100128', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 309),
(150, '$2y$10$PGObYtcPIwWFYdhbpVzaSONeNsnZtMwlo3rRER.62H8GHzeDiYxI.', 'SHENDY CAYLA ANDINI', '2324-10-446', '0081286104', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 300),
(151, '$2y$10$TfMzWQPDH/GmH4WYUQmsG.KL5G6nclrLsm3szyAnstubktIGovBGK', 'SITI NURAJIZAH', '2324-10-458', '0088486758', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 365),
(152, '$2y$10$4edODDgWQo7hlTaYIDM0/.HKaJaIe3b1IseFLUIEHwydqwcwTHr3C', 'SRI RAHAYU', '2324-10-465', '0075736641', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 299),
(153, '$2y$10$ye2wHzf8p4sY6o0NNrQPuOKkyEINhnXYqzlKYd/tciBf6gJivwkUO', 'SUSAN PUTRI NURBATIN', '2324-10-470', '0087783273', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 365),
(154, '$2y$10$TtqIi6jltFnOuX.Jep.Lp.ez66OZN.A9lXlhPa6VGG5OMIhSYABMG', 'TETI AMELIA', '2324-10-478', '0083024984', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 300),
(155, '$2y$10$T40ZGACYYV6q6uemYx1MieQ.cGbrLLPwWAtfuRH2VLFlFrqjLm9Ea', 'TRIYANI', '2324-10-492', '0086929952', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, NULL),
(156, '$2y$10$IJabiuYV.l3pyHYmQgWaTuIjYVZjvL88ITkq71m3sezUXxBsFPTaO', 'WAFIQ USWATUN NIDA', '2324-10-507', '0088580483', 'Perempuan', 'XI RPL 1', 'Aktif', 1, NULL, 365),
(157, '$2y$10$6Z1y0sOY.0rSOyS3jQHZseAnRPN9rVRTYcPIPgoA0S426P5HdFiS6', 'AISYAH PEBRIYANI', '2324-10-023', '0085067884', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 296),
(158, '$2y$10$WxLiRpdQKaanxHP0d8n..OONBV0sA2GJezOH6ZD.o25OCCcwODANO', 'ALDIYANSYAH', '2324-10-031', '0083674313', 'Laki-laki', 'XI RPL 2', 'Aktif', 1, NULL, 294),
(159, '$2y$10$hGZrYcSh5y9G7A918hLEX.vEhaQzzkWwHGPsDV3NH7R7KVDAROniG', 'ANDINI NOFIYANI', '2324-10-058', '0088265038', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 301),
(160, '$2y$10$fIdBQjD1z3m1nWQRXaaahuzXXsUafTiOBpyAnFH9zPgM//X/71hYO', 'ANIS RAHAYU', '2324-10-063', '0089311202', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 298),
(161, '$2y$10$5zGpMp7tFp9PJLi3y3RP4OSjchsbvg0nCdg1WVeA3I1F.ITeFYKzW', 'AYU WANDIRAH', '2324-10-082', '0089527836', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 296),
(162, '$2y$10$XmpmN2hJTDcnBQ/Pkw7kmOI1vpsFjTLTdcr7mJUI4GL8ca/4E0W7i', 'CICA', '2324-10-096', '0082752317', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 294),
(163, '$2y$10$82Dg8vA776zQbjYUn/Nyt.p1bxHdIHKmVqONgbQSMFxgaFwIU6xNa', 'DANI RIZWAN', '2324-10-102', '0089256011', 'Laki-laki', 'XI RPL 2', 'Aktif', 1, NULL, 302),
(164, '$2y$10$unhX04DKflA2CqSZph3Mseg8rHsEi9LL5CZcjg7ULIyuXH8Ue/OVK', 'DEWI ROHAYANTI', '2324-10-131', '0087261958', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 301),
(165, '$2y$10$9bP0vYgNWQzvwqdinTWi4.zCCLxEo.sZ55991v0UhO6fjCP.7ibEe', 'DIAN AYU AGUSTINA', '2324-10-136', '0078445277', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 296),
(166, '$2y$10$DqqtQcy8GWcWrSaZGWYIfeDwIBwFKe4a/XNmad4iE3PsYRwn8hQmi', 'DIANA RIZKI', '2324-10-138', '3089514774', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 377),
(167, '$2y$10$2vVq9zoHJXPcjSPXozn3R.bkLkP/CIWAe2i8rdZk4j1fACKCZ0FF2', 'DINDA LESTARI', '2324-10-146', '0072503251', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 296),
(168, '$2y$10$IaP945Bh7PWKQPRxUSKI6ugHXiOvQieuVoF6ZLrbhTT0mCLDIsiBK', 'DITA LAURA', '2324-10-149', '0087121834', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 294),
(169, '$2y$10$H88vaXZDu8l6Kv3EFPGR9OhCSHUjPHqxhpqeCN1NwqNzORPvl7lzi', 'EGI FERDIANSAH', '2324-10-160', '0084079953', 'Laki-laki', 'XI RPL 2', 'Aktif', 1, NULL, 302),
(170, '$2y$10$hohzR5mvM5Y8efTTpsXmQe1gQqP38jM/bValajXKpBKqauw0NE8ou', 'ELSA FITRIANI', '2324-10-167', '0083597912', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 294),
(171, '$2y$10$5LABA9wiI6ooDMcgXOccuOgwubaXCaXA8GfxRMX12JwgO00LBXHsS', 'IKHLAS AMINUDDIN', '2324-10-219', '0083378474', 'Laki-laki', 'XI RPL 2', 'Aktif', 1, NULL, 377),
(172, '$2y$10$faFA14IYY5pOCX6AsZ2mfO0zSiNJ5LKIRM.18ExVNOhyby/oK/kC6', 'INDRI RISMAYANTI', '2324-10-224', '0076886099', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, NULL),
(173, '$2y$10$qKzkX.w4woF3bfqi05MUP.CTgzeL8FYLGcOJWUepIiFZCIvAXEF.G', 'KAYLA DWI ANGGITA SYAHARANI PUTRI', '2324-10-243', '0086082915', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 296),
(174, '$2y$10$6ratjnUhm0grv31v/qJqaO4mDPvkceYIRxah/ZO.zJ0lkIUnGTmau', 'KHAIRIZA RIHHADATUL `AISY', '2324-10-252', '0083126729', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 307),
(175, '$2y$10$Nn9K0DBLx9XQ7URp.6oF1eOxARvM4/kj6A/bvEPUYQCBw1KFzzFbC', 'LAURA ADE LINE', '2324-10-258', '0082931681', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 301),
(176, '$2y$10$lyPAsTN//Z/zdRmW1yUB5e83YHcvV.n3cr4BbYGdyYhJoCfh1xS0e', 'MELY AMELIA', '2324-10-278', '0074342164', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 294),
(177, '$2y$10$7KL99K3ZxgGstU77sUl2uONld0gpV6NPE8d.XIINCDKCyGi1r6gTi', 'MUHAMAD HADI KUSUMA', '2324-10-304', '0074617652', 'Laki-laki', 'XI RPL 2', 'Aktif', 1, NULL, 377),
(178, '$2y$10$FAX.94IunskM.ZoKppTNgO33F.EwykKfboELoW0lT1AHyz97FGxu6', 'NADIA PUTRI SALSABILA', '2324-10-316', '0082259034', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 306),
(179, '$2y$10$2k3wxuMPSZwNpwkEMD2lPepZgG84s5qsrafRzjGAmYvWhc5.pAM7u', 'NAZWA NOVITA', '2324-10-326', '0077346420', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 298),
(180, '$2y$10$syfhL.Mb6eT6.o4DyCDuaOg7rZzy8Kw9T917jfo1V7qsyeJpaqQai', 'PUTRI MELY NATASYA', '2324-10-359', '0054622493', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 305),
(181, '$2y$10$2sU3W4rOtvdOxNtgRB3X6.sCWDMEFiGj7mMqH1QnGBYsbJ8HoD6L6', 'RAEHAN FAUJIYANSAH', '2324-10-362', '0078339374', 'Laki-laki', 'XI RPL 2', 'Aktif', 1, NULL, 294),
(182, '$2y$10$OIsJcn/EXnFCDcNqXwGs8ehC.l9oxqxCLCQc5m62/Y4XpfsfwK3tC', 'RAYHAN ADITIYAN', '2324.10.374', '0085240704', 'Laki-laki', 'XI RPL 2', 'Aktif', 1, NULL, 300),
(183, '$2y$10$cGWR6Kk6eGjzRDrk1w3rq.UkrpmauEO2JzRSHGMJ2tjTgpYHq1.Yu', 'RIZAKY AMAR', '2324-10-416', '0087664800', 'Laki-laki', 'XI RPL 2', 'Aktif', 1, NULL, 300),
(184, '$2y$10$fiykY.qhF8S9b3GZPtXDKuBQaIuvCWwhMqSTAo333Fo9dR5oUS1zq', 'SALSA NABILA', '2324-10-433', '0081380774', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, NULL),
(185, '$2y$10$ruZH6SbG/TMmJb/OtNgF.uw.PX5MotBk/nCE6Ns0GEJpeic9GxqES', 'SASKIA OKTAVIANI', '2324-10-438', '0079159104', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 306),
(186, '$2y$10$NqznX2GbQlTGpxMzon4Oh.jC0b.JvMdB9YtLJiZ7gL7f830Uu2fVi', 'SITI ABIDAH', '2324-10-454', '0071317214', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 294),
(187, '$2y$10$4tS6NI/VYbOyim5YKq8LZuhzSdsPaCzNUBIgBdBEgJI1wsknADl6.', 'SRI APRILIA NURAENI', '2324-10-464', '0088367876', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 294),
(188, '$2y$10$W7WqUUVqWqZEQEjdOvmdPO1y6N7TGluLXpo6.tS9D0g067zbAVxOi', 'SUCI RIANTI RAMADHANI', '2324-10-467', '0079913112', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 377),
(189, '$2y$10$7MTU1WtilLsmwvZ9uX33AuHnZ1fdpAHp33Jd6dsu3hivrKJKceqKS', 'TARPIN', '2324-10-477', '0082922302', 'Laki-laki', 'XI RPL 2', 'Aktif', 1, NULL, 302),
(190, '$2y$10$gOBHmRwiXY.pdsEBCOUiaOwtyfPqFD/fOtkMCUNRfF3PuEF1Lo.Du', 'TIA NURANISA', '2324-10-481', '0078885964', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, NULL),
(191, '$2y$10$GQDxq3EFanCCvJjOqS2y8.qYtYmap4gRQYWaFddN0gheJdoqYVQ7W', 'VALENT', '2324-10-496', '0085303875', 'Perempuan', 'XI RPL 2', 'Aktif', 1, NULL, 377),
(192, '$2y$10$wRZbxoPh3svPsm49/5Bk0eUQmgvKhyQhZIxGkAMmA2.J5v2bLsZ0a', 'ZULFIKAR MUHAMAD ARIF', '2324-10-539', '0089148462', 'Laki-laki', 'XI RPL 2', 'Aktif', 1, NULL, 404);

-- --------------------------------------------------------

--
-- Table structure for table `tempat_pkl`
--

CREATE TABLE `tempat_pkl` (
  `id_tempat_pkl` int NOT NULL,
  `nama_tempat_pkl` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `nama_instruktur` varchar(100) DEFAULT NULL,
  `alamat_kontak` varchar(100) DEFAULT NULL,
  `kuota_siswa` int DEFAULT '0',
  `jurusan_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tempat_pkl`
--

INSERT INTO `tempat_pkl` (`id_tempat_pkl`, `nama_tempat_pkl`, `alamat`, `nama_instruktur`, `alamat_kontak`, `kuota_siswa`, `jurusan_id`) VALUES
(294, 'BPN Sumedang', '', '', '', 7, 1),
(295, 'LKP Execom Haurgeulis', '', '', '', 7, 1),
(296, 'Disdukcapil Subang', '', '', '', 7, 1),
(297, 'Universitas Subang', '', '', '', 5, 1),
(298, 'Haurgeulis Education Center (HEC) Haurgeulis', '', '', '', 4, 1),
(299, 'PTPN Ciater', '', '', '', 4, 1),
(300, 'Pengadilan Negeri Subang', '', '', '', 4, 1),
(301, 'Disnakertrans ESDM Subang', '', '', '', 4, 1),
(302, 'Dinas Pertanian Subang', '', '', '', 3, 1),
(303, 'SDN Cadasngampar', '', '', '', 2, 1),
(304, 'PT. TELKOM Haurgeulis', '', '', '', 2, 1),
(305, 'POLRES Subang', '', '', '', 2, 1),
(306, 'Syafa Husada Klinik Gantar', '', '', '', 2, 1),
(307, 'Badan Kesatuan Bangsa dan Politik Bekasi', '', '', '', 1, 1),
(308, 'PT. MEDCO AMERA JAKARTA', '', '', '', 1, 1),
(309, 'PT. MEDKO AMPERA JAKARTA', '', '', '', 1, 1),
(310, 'BENGKEL EAS ERIK AUTO SERVICE', '', '', '', 5, 3),
(311, 'PT INDOMOBIL TRADA INTERNASIONAL KARAWANG', '', '', '', 5, 3),
(312, 'MUKTI AGUNG', '', '', '', 6, 3),
(313, 'BENGKEL MOBIL ATIM', '', '', '', 3, 3),
(314, 'GLOBAL MOTOR', '', '', '', 4, 3),
(315, 'BENGKEL MOBIL A 8', '', '', '', 2, 3),
(316, 'BENGKEL MOBIL AN MOTOR', '', '', '', 6, 3),
(317, 'BENGKEL ROBI MOTOR', '', '', '', 4, 3),
(318, 'GLORI MOTOR', '', '', '', 4, 3),
(319, 'MEKAR MUDA MOTOR', '', '', '', 4, 3),
(320, 'BENGKEL JEMBAR MOTOR', '', '', '', 6, 3),
(321, 'BENGKEL ANUGRAH WIJAYA MOTOR', '', '', '', 5, 3),
(322, 'PRIMA MOTOR', '', '', '', 4, 3),
(323, 'SLAMET JAYA ABADI MOTOR', '', '', '', 2, 3),
(324, 'PT SUN STAR PRIMA MOTOR MITSUBISHI', '', '', '', 2, 3),
(325, 'PT CINTA DAMAI PUTRA BAHAGIA', '', '', '', 2, 3),
(326, 'PT CITRA ASRI BUANA', '', '', '', 2, 3),
(327, 'JASUTRA MOTOR', '', '', '', 2, 3),
(328, 'ZIA MOTOR', '', '', '', 4, 3),
(329, 'BENGKEL FAMILY MANDIRI', '', '', '', 1, 3),
(330, 'BENGKEL KIARA AUTO', '', '', '', 1, 3),
(331, 'LUPIN MOTOR', '', '', '', 2, 3),
(332, 'RALLY VARIASI', '', '', '', 1, 3),
(333, 'PT ASAHI INDONESIA', '', '', '', 3, 3),
(334, 'BENGKEL MOBIL RAHEL MOTOR', '', '', '', 1, 3),
(335, 'DAYAT MOTOR', '', '', '', 5, 3),
(336, 'AL-IANDA', '', '', '', 2, 3),
(337, 'BENGKEL MOBIL MANG ATEP', '', '', '', 3, 3),
(338, 'MIADI MOTOR', '', '', '', 5, 3),
(339, 'AZMI CAR', '', '', '', 4, 3),
(340, 'JAJA LAS', 'BLOK MANDIRANCAN RT 21 RW 06 DESA HAURGEULIS KEC.HAURGEULIS KAB.INDRAMAYU', 'SARJA', '', 2, 4),
(341, 'BM STAR', 'BJL.RAYA HAURGEULIS-PATROL DESA KEDUNGWUNGU KEC.ANJATAN KAB.INDRAMAYU', 'SUGIONO', '', 4, 4),
(342, 'IFAN JAYA LAS', 'BLOK PILANG DESA BALERAJA KEC.GANTAR KAB.INDRAMAYU', 'ARIFIN', '', 3, 4),
(343, 'AL IANDA MOTOR', 'BLOK KUBANGJATI DESA BALERAJA KEC.GANTAR KAB.INDRAMAYU', 'HENDRA', '', 2, 4),
(344, 'MEKARJAYA', 'JL.ARIEF RAHMAN HAKIM NO.18 PINTU UYUH SUBANG', 'H.ENGKOS KOSASIH', '', 2, 4),
(345, 'ACHENK KARYA ABADI', 'JL.KH DEWANTARA RT 36 RW 18 KELURAHAN DANGDEUR KEC.SUBANG', 'AAH ROHENDI', '', 2, 4),
(346, 'CONDONG TEKNIK', 'JL.SUNAN GUNUNG JATI BLOK SANJULARA DESA ASTANA CIREBON', 'ZAENUL ARIFIN', '', 2, 4),
(347, 'Q-BAL', 'JL ARIEF RAHMAN HAKIM SUBANG', 'ELIH MUSLIH', '', 3, 4),
(348, 'WANAKAYA LAS', 'WANAKAYA BLOK 1 JL CIPUNAGARA HAURGEULIS', 'YAYAT SUPIYATNA', '', 2, 4),
(349, 'PT.SANOH INDONESIA', 'JL INTI II NO.10 BLOK C-4,SUKRESMI CIKARANG SELATAN KAB.BEKASI', 'MISBAHUL MUNIR', '', 2, 4),
(350, 'CV.MULTI GUNA UTAMA', 'JL.RAYA PARUNG RT 04 RW 02 KELURAHAN PARUNG SUBANG', 'ADE KOSASIH', '', 4, 4),
(351, 'RAKA MANDIRI', 'JL. DI PANJAITAN, SOKLAT SUBANG', 'HERMAWAN', '', 3, 4),
(352, 'Prime Studio Subang', '', '', '', 5, 2),
(353, 'CIHO Printshop Sumedang', '', '', '', 5, 2),
(354, 'Auni Digital Printing Anjatan', '', '', '', 4, 2),
(355, 'Puja Photocopy Gantar', '', '', '', 3, 2),
(356, 'Happy Digital Printing Gabuswetan', '', '', '', 3, 2),
(357, 'Kencana Putra Digital Printing - Subang', '', '', '', 3, 2),
(358, 'HEC HAURGEULIS (Haurgeulis Education Centre)', '', '', '', 3, 2),
(359, 'Perdana Photo Lab dan Studio Subang', '', '', '', 3, 2),
(360, 'Bayu Copy Digital Gantar', '', '', '', 3, 2),
(361, 'VJ Studio Bongas - Indramayu', '', '', '', 2, 2),
(362, 'Ruang Photo Haurgeulis', '', '', '', 2, 2),
(363, 'Abadi Photoworks Subang', '', '', '', 2, 2),
(364, 'Bestro Project Sumedang', '', '', '', 2, 2),
(365, 'LKP Execom Haurgeulis', '', '', '', 2, 2),
(366, 'Percetakan Dea Grafika Subang', '', '', '', 2, 2),
(367, 'Pubdok Photografi Sumedang', '', '', '', 2, 2),
(368, 'Dizah Printing Bongas', '', '', '', 2, 2),
(369, 'CV. Sumber Mandiri Printshop Subang', '', '', '', 2, 2),
(370, 'Kuens Photo Studio Haurgeulis', '', '', '', 1, 2),
(371, 'Nusa Edu Bandung', '', '', '', 1, 2),
(372, 'PRIMKOPOL POLRES SUBANG', '', '', '', 8, 8),
(373, 'CV. TRIDAYA ANUGERAH SUKSES', '', '', '', 5, 8),
(374, 'KPP Pratama Subang', '', '', '', 5, 8),
(375, 'KANTOR POS Anjatan', '', '', '', 5, 8),
(376, 'UPK DAPM GANTAR AGUNG', '', '', '', 4, 8),
(377, 'Universitas Subang', '', '', '', 4, 8),
(378, 'PENGADILAN NEGERI SUBANG KELAS 1B', '', '', '', 4, 8),
(379, 'BANK BPR PK BONGAS', '', '', '', 4, 8),
(380, 'KSP MALER Group Anjatan', '', '', '', 4, 8),
(381, 'FIF Group Gantar', '', '', '', 4, 8),
(382, 'Kantor Pemerintah Daerah Subang', '', '', '', 3, 8),
(383, 'DEASSY SUKSES MULTI USAHA', '', '', '', 3, 8),
(384, 'KSPPS BMT NU Umat Sejahtera Haurgeulis', '', '', '', 3, 8),
(385, 'BANK BJB KCP HAURGEULIS', '', '', '', 3, 8),
(386, 'FIF Group Haurgeulis', '', '', '', 3, 8),
(387, 'BANK BJB KCP HAURGEULIS', '', '', '', 2, 8),
(388, 'PUSAT PENGELOLA PENDAPATAN DAERAH WILAYAH KAB. INDRAMAYU II HAURGEULIS', '', '', '', 2, 8),
(389, 'Notaris Makhrom Ismail, SH., M.Kn.', '', '', '', 2, 8),
(390, 'Kantor Kecamatan Haurgeulis', '', '', '', 2, 8),
(391, 'Kantor POS Haurgeulis', '', '', '', 2, 8),
(392, 'KSPPS BMT AN-NAJAH HAURGEULIS', '', '', '', 1, 8),
(393, 'TRIDAYA MOTOR CIDANCUN', '', '', '', 1, 8),
(394, 'PT. Shinta Indah Jaya Factory', '', '', '', 1, 8),
(395, 'DEKOPINDA KAB.SUBANG', '', '', '', 1, 8),
(396, 'DEKPSDM KAB. SUBANG', '', '', '', 1, 8),
(397, 'Bank CIMB / BPR Cabang Kroya', '', '', '', 1, 8),
(398, 'Notaris Pendil Paundrakarna Gantar', '', '', '', 1, 8),
(399, 'PT Pegadaian Cabang Anjatan', '', '', '', 1, 8),
(400, 'UPK PNPM Haurgeulis', '', '', '', 1, 8),
(401, 'LAFI PUSKESAD', 'Jl. Gudang Utara No. 25-26, Bandung - Jawa Barat 40113', '', '', 24, 5),
(402, 'RS. BHAYANGKARA INDRAMAYU', 'Jl. Raya Pantura Km.73-75, Kec. Losarang-Indramayu 45253', '', '', 6, 5),
(403, 'PUSKESMAS HAURGEULIS', 'Jl. Siliwangi No.61, Kec. Haurgeulis-Indramayu 45264', '', '', 5, 5),
(404, 'BPN Subang', '  ', ' ', '   ', 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `guru_pembimbing`
--
ALTER TABLE `guru_pembimbing`
  ADD PRIMARY KEY (`id_pembimbing`),
  ADD UNIQUE KEY `username` (`nip`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `nama_pembimbing` (`nama_pembimbing`);

--
-- Indexes for table `jurnal_harian`
--
ALTER TABLE `jurnal_harian`
  ADD PRIMARY KEY (`id_jurnal_harian`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `siswa_id_2` (`siswa_id`,`tanggal`),
  ADD KEY `siswa_id_3` (`siswa_id`),
  ADD KEY `tanggal` (`tanggal`);

--
-- Indexes for table `jurnal_kegiatan`
--
ALTER TABLE `jurnal_kegiatan`
  ADD PRIMARY KEY (`id_jurnal_kegiatan`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `siswa_id_2` (`siswa_id`),
  ADD KEY `tanggal_laporan` (`tanggal_laporan`),
  ADD KEY `siswa_id_3` (`siswa_id`,`tanggal_laporan`);

--
-- Indexes for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`id_jurusan`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `no_induk` (`no_induk`),
  ADD UNIQUE KEY `nisn` (`nisn`),
  ADD UNIQUE KEY `no_induk_2` (`no_induk`),
  ADD UNIQUE KEY `nisn_2` (`nisn`),
  ADD KEY `jurusan_id` (`jurusan_id`),
  ADD KEY `pembimbing_id` (`pembimbing_id`),
  ADD KEY `tempat_pkl_id` (`tempat_pkl_id`),
  ADD KEY `jurusan_id_2` (`jurusan_id`),
  ADD KEY `pembimbing_id_2` (`pembimbing_id`),
  ADD KEY `tempat_pkl_id_2` (`tempat_pkl_id`),
  ADD KEY `nama_siswa` (`nama_siswa`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `tempat_pkl`
--
ALTER TABLE `tempat_pkl`
  ADD PRIMARY KEY (`id_tempat_pkl`),
  ADD KEY `jurusan_id` (`jurusan_id`),
  ADD KEY `jurusan_id_2` (`jurusan_id`),
  ADD KEY `nama_tempat_pkl` (`nama_tempat_pkl`),
  ADD KEY `nama_instruktur` (`nama_instruktur`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `guru_pembimbing`
--
ALTER TABLE `guru_pembimbing`
  MODIFY `id_pembimbing` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `jurnal_harian`
--
ALTER TABLE `jurnal_harian`
  MODIFY `id_jurnal_harian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `jurnal_kegiatan`
--
ALTER TABLE `jurnal_kegiatan`
  MODIFY `id_jurnal_kegiatan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id_jurusan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT for table `tempat_pkl`
--
ALTER TABLE `tempat_pkl`
  MODIFY `id_tempat_pkl` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=405;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jurnal_harian`
--
ALTER TABLE `jurnal_harian`
  ADD CONSTRAINT `fk_jurnal_harian_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jurnal_harian_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jurnal_kegiatan`
--
ALTER TABLE `jurnal_kegiatan`
  ADD CONSTRAINT `fk_jurnal_kegiatan_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jurnal_kegiatan_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `fk_siswa_jurusan` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_siswa_pembimbing` FOREIGN KEY (`pembimbing_id`) REFERENCES `guru_pembimbing` (`id_pembimbing`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_siswa_tempat_pkl` FOREIGN KEY (`tempat_pkl_id`) REFERENCES `tempat_pkl` (`id_tempat_pkl`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`pembimbing_id`) REFERENCES `guru_pembimbing` (`id_pembimbing`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_3` FOREIGN KEY (`tempat_pkl_id`) REFERENCES `tempat_pkl` (`id_tempat_pkl`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tempat_pkl`
--
ALTER TABLE `tempat_pkl`
  ADD CONSTRAINT `fk_tempat_pkl_jurusan` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `tempat_pkl_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
