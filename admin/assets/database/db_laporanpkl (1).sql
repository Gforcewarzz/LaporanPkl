-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 29 Jun 2025 pada 04.46
-- Versi server: 8.0.30
-- Versi PHP: 8.3.12

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
-- Struktur dari tabel `admin`
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
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama_admin`, `email`, `created_at`) VALUES
(1, 'warzz', '$2y$10$CplBa3GrE2/h6P.p.FuuvOUIAyg5Fp3efRObKGOCUtYrq6c6VgD0a', 'warzz', 'rizzlonely811@gmail.com', '2025-06-27 12:55:36'),
(2, 'Easy', '$2y$10$EpuLETFAayHoS6qjjZgFAeRSXIEj3.km5Vk.fB8qdE356ZLQdHun.', 'Easy bree', 'easyy@gmail.com', '2025-06-28 01:10:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru_pembimbing`
--

CREATE TABLE `guru_pembimbing` (
  `id_pembimbing` int NOT NULL,
  `nama_pembimbing` varchar(100) NOT NULL,
  `nip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `guru_pembimbing`
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
-- Struktur dari tabel `jurnal_harian`
--

CREATE TABLE `jurnal_harian` (
  `id_jurnal_harian` int NOT NULL,
  `tanggal` date NOT NULL,
  `pekerjaan` text,
  `catatan` text,
  `siswa_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `jurnal_harian`
--

INSERT INTO `jurnal_harian` (`id_jurnal_harian`, `tanggal`, `pekerjaan`, `catatan`, `siswa_id`) VALUES
(1, '2025-06-25', 'ngoding sampai sukses', 'pala gua muter coo', 1),
(5, '2025-06-28', 'Mantapp', 'Demi 5 btc', 1),
(6, '2025-06-28', 'Ngelas', 'Ngepas kapal laud tegar siregar', 30),
(8, '2025-06-28', 'Hzbz', 'Hahz', 1),
(10, '2025-06-28', 'Bzkaka', 'BzjJz', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurnal_kegiatan`
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

--
-- Dumping data untuk tabel `jurnal_kegiatan`
--

INSERT INTO `jurnal_kegiatan` (`id_jurnal_kegiatan`, `nama_pekerjaan`, `perencanaan_kegiatan`, `pelaksanaan_kegiatan`, `catatan_instruktur`, `gambar`, `siswa_id`, `tanggal_laporan`) VALUES
(5, 'projek lumpuh', 'ngoding pagi malem', 'membuat web pkl', 'gasss terus', 'proyek_edit_685f25dfcef983.70444904.jpg', 1, '2025-06-28 06:14:39'),
(10, 'Yyv', 'F', 'Ychccg', 'Vttct', 'proyek_685fd2f4b53976.39966015.png', 1, '2025-06-28 18:33:08'),
(12, 'Baksnajznnajajansjana', 'Bzianzjzjzhhzjzj\r\nJzjzjsjzbzbjzjzjzjzjzjzbznannaoaisjsjja\r\nJaoanskalakjxgjanajxikamahz\r\nHzjansbbzhhs', 'Hskalalksjxhahahahkakakakajajajajhxhaodhsiansbsuxkakxb, hjabsbshhsbdnskskskksbxjaiahsbbajzbzhhxhdhdbbbxnanjanxjahsnxnjsbabsbixbaha sjzbbsbshs sbzbjzbzjabzb', 'Jajzjzhxhxbajiajabzbzbsjakakakkakajahsbbajananabbshahakakldkxjjbsbsb hsisknabzhananajiakzbjs khshxhaoakns', 'proyek_685fda713e6272.50506851.jpg', 1, '2025-06-28 19:05:05'),
(13, 'Hsusjsj', 'Bxhhzbzbzbz', 'Hsnsns', 'Jajajsj', 'proyek_685fdb820924b6.83589264.png', 2, '2025-06-28 19:09:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurusan`
--

CREATE TABLE `jurusan` (
  `id_jurusan` int NOT NULL,
  `nama_jurusan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `jurusan`
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
-- Struktur dari tabel `siswa`
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
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `password`, `nama_siswa`, `no_induk`, `nisn`, `jenis_kelamin`, `kelas`, `status`, `jurusan_id`, `pembimbing_id`, `tempat_pkl_id`) VALUES
(1, '$2y$10$rRK7jFEMEOCYmPJllBYGWOET3o3SlDzrwSXxhlX2auvhCw.vBu1Oa', 'CANDRA', 'TP001', '00700001', 'Perempuan', 'XI TP 1', 'Aktif', 4, 1, 154),
(2, '$2y$10$Kkl9xsAdrfdA/XPzbXgBJeyO89fiYO7XP4/zm8FNVHKoVUNBJGor.', 'KRISNA', 'TP002', '00700002', 'Laki-laki', 'XI TP 2', 'Aktif', 4, 2, 156),
(3, '$2y$10$/Id2VBH0XGf/u76grPZ6U.NMZFGN28gakKA0lDKseAto/Ac2rt29q', 'ARMAN', 'TP003', '00700003', 'Laki-laki', 'XII TP 1', 'Aktif', 4, 3, 158),
(4, '$2y$10$6I0qkMwe0.XVkbwCnyqDIOT7nZj.WRLCfZ0tHRwso0EWeFfWcpIlC', 'RENO AJI SAPUTRA', 'TP004', '00700004', 'Laki-laki', 'XII TP 2', 'Aktif', 4, 4, 160),
(5, '$2y$10$m6kUW2rxLjh0RwbT5egE6OIy6.w7YIGo/1VdmAAVqumBb/DFNQfiG', 'RAFEL ADITYA', 'TP005', '00700005', 'Laki-laki', 'XI TP 1', 'Aktif', 4, 5, 162),
(6, '$2y$10$CCGZBueJVuzUsHijK4JpSeYl/ZDL5kgBwNX0icqkviOvqNBRCDifO', 'RAMA', 'TP006', '00700006', 'Laki-laki', 'XI TP 2', 'Aktif', 4, 6, 164),
(7, '$2y$10$0tvhUK/eI35pLwyuw95wDO/85fMU312nCp6vzinQTo6iCiN3ftp56', 'TRIAN ALAMSYAH', 'TP007', '00700007', 'Laki-laki', 'XII TP 1', 'Aktif', 4, 7, 166),
(8, '$2y$10$mL5vQSRyyeBlSrP.Z1ZK3uJaK5a3goAAR1d29Wjf5kAKXvMl0zF6C', 'MUHAMMAD NUR FARHANUDIN', 'TP008', '00700008', 'Laki-laki', 'XII TP 2', 'Aktif', 4, 8, 168),
(9, '$2y$10$HINM2YoJdofSdbqP.DhaNut3itHuMG6MqWDii2f1b40cg6OZetvPS', 'WENDI SYAHPUTRA', 'TP009', '00700009', 'Laki-laki', 'XI TP 1', 'Aktif', 4, 9, 170),
(10, '$2y$10$bgJgWdfu0z6NNa6/3ZTt6O0qUTRfOObie8.6xAVk1r9wPmjtSNNiW', 'AHMAD MAULANA', 'TP010', '00700010', 'Laki-laki', 'XI TP 2', 'Aktif', 4, 10, 172),
(11, '$2y$10$6q8Kg520SQM5wyBlNjDapuDu7wZrA7Vso2YyU/0qPLqY7d/HnWnna', 'AKIM DARMAWAN', 'TP011', '00700011', 'Laki-laki', 'XII TP 1', 'Aktif', 4, 1, 174),
(12, '$2y$10$f/Q0yGszmOyw5i0frx0yBeXj2B0Bu/8mvRgGmeLdba/8cf8NiyOo6', 'AHMAD DENIS', 'TP012', '00700012', 'Laki-laki', 'XII TP 2', 'Aktif', 4, 2, 176),
(13, '$2y$10$bMYMdocziS1BHZ3pjGw/R.y.Idq2nz0cavneMPhX7pdEZCYTT3Bwi', 'WANDI ADITIA', 'TP013', '00700013', 'Laki-laki', 'XI TP 1', 'Aktif', 4, 3, 178),
(14, '$2y$10$1Wnboumc00Oj2DYJ.1d2m.h/GhafsXyiHU.4uLN7FT2Xwq43ANwva', 'ARDI APRILIA PUTRA', 'TP014', '00700014', 'Laki-laki', 'XI TP 2', 'Aktif', 4, 4, 180),
(15, '$2y$10$Sb1VA/3yGvDaEXexEAV2Q.5VgLJ6NJuwRMZRjxaROeNhUdlaNYKQ6', 'DAVIT AGUNG SUSANTO', 'TP015', '00700015', 'Laki-laki', 'XII TP 1', 'Aktif', 4, 5, 182),
(16, '$2y$10$1H/U6c3CyQJ1j1q2CbQPBu/XJhMdKw3NH/FV9bzhQyTauwPZTGDpS', 'DHAFFA KHAERURIZKY AGHNIYANSYAH', 'TP016', '00700016', 'Laki-laki', 'XII TP 2', 'Aktif', 4, 6, 184),
(17, '$2y$10$p7zB1FqsegsHxrQCy7Jc1eZcBUawVUt8blGd6nJuE6exwyqHLIje.', 'MUFLIH NUR FATHAN HIDAYAT', 'TP017', '00700017', 'Laki-laki', 'XI TP 1', 'Aktif', 4, 7, 186),
(18, '$2y$10$1dZmFAa1MLWtqneSEUTj0etH4gnEXNRuFFzdjKy1VyWgK2e2Oa27a', 'MUHAMMAD RAYHAN ADITYA', 'TP018', '00700018', 'Laki-laki', 'XI TP 2', 'Aktif', 4, 8, 188),
(19, '$2y$10$bS4.ma66ERbke65A3ILJbOg.A8zh97JY5eOzmc46IVoY.AorOTQjC', 'AGIEL YUSUF HAMDANI', 'TP019', '00700019', 'Laki-laki', 'XII TP 1', 'Aktif', 4, 9, 190),
(20, '$2y$10$91ndN2.kE0SJFAqfeh4U1.xb.DsOAvXq8p3TUfZB5eiMe4WhUbV5K', 'AKBAR MAULANA SAPUTRA', 'TP020', '00700020', 'Laki-laki', 'XII TP 2', 'Aktif', 4, 10, 192),
(21, '$2y$10$Lr8WeYVQRzWbLe.T2rfWseSws6wXgRki8TOZUtpFefYucGGiUOeC.', 'HAPIZ ASKIATUR RAMADAN', 'TP021', '00700021', 'Laki-laki', 'XI TP 1', 'Aktif', 4, 1, 194),
(22, '$2y$10$rEyiEgBY3S5kQCZr31a4N..lN9QccDtY4/Vqabq4cbuXDAZdEAKGO', 'MUHAMMAD PAJAR', 'TP022', '00700022', 'Laki-laki', 'XI TP 2', 'Aktif', 4, 2, 196),
(23, '$2y$10$zp4.xV4kCYL7BGmy2.2EEOzLQH570jTD0E2MCPz86F5snALGzQ91.', 'ADITIA REZA SAPUTRA', 'TP023', '00700023', 'Laki-laki', 'XII TP 1', 'Aktif', 4, 3, 198),
(24, '$2y$10$vYYhD.vVQDYG8aKYuzaHnOYHHUGikLzz7Z65IjPjyfmDeY55yvob2', 'DANI KURNIAWAN', 'TP024', '00700024', 'Laki-laki', 'XII TP 2', 'Aktif', 4, 4, 200),
(25, '$2y$10$twrYOQpl7ObYR5smni0pJ.EWBig1IhSTjPd20/oWc0K5slLK8zNsm', 'WISNU SUKMA WIJAYA', 'TP025', '00700025', 'Laki-laki', 'XI TP 1', 'Aktif', 4, 5, 199),
(26, '$2y$10$EWyUcmTONwN7Dwo0K1LNXOqNutTqJdWHt7lJgnQAeaSdQvwEQAVJy', 'RAKA ABDILLAH', 'TP026', '00700026', 'Laki-laki', 'XI TP 2', 'Aktif', 4, 6, 197),
(27, '$2y$10$ExU83BbUl8ZWm3xMAwBYV.riCa.GdHqb7tMirDci.RME/LSn5Q.Ru', 'RAEHAN FERDIANSYAH', 'TP027', '00700027', 'Laki-laki', 'XII TP 1', 'Aktif', 4, 7, 195),
(28, '$2y$10$Zm19zw/wURsuwcKCqJJvr.TZOB5r7rp.kryGPUU/7G3j/HI.NUvQG', 'FEBYAN KURNIAWAN', 'TP028', '00700028', 'Laki-laki', 'XII TP 2', 'Aktif', 4, 8, 193),
(29, '$2y$10$qsnTutNEtA8ugbjjLTG7ZOHq1Y.wnN19RBNHdBgfpTvt8LSdmGa0u', 'GEVI FANIANSYAH', 'TP029', '00700029', 'Laki-laki', 'XI TP 1', 'Aktif', 4, 9, 191),
(30, '$2y$10$URflrKDBdFj5ZxEVvXzKj.iFUSKoGCxXYv0APK7TsV0.orYuD8.Pq', 'SAEFUL AKBAR', 'TP030', '00700030', 'Laki-laki', 'XI TP 2', 'Aktif', 4, 10, 189),
(31, '$2y$10$kBv7HP7uG59OFoAGyOHbcebnKnXGX0ZWuRtTNulNEP8HXf0tR7aUO', 'RIO MAHENDRA', 'TP031', '00700031', 'Laki-laki', 'XII TP 1', 'Aktif', 4, 1, 187),
(36, '$2y$10$Jo8eWXj053UiVT5PLI7eyeOUhCLMKRHafBhyy241Ryl71VHZUBBdi', 'joko anwar', '21332132', '32131312323', 'Laki-laki', 'XII FI 1', 'Aktif', 8, 1, 160),
(38, '$2y$10$U80rdvrateqLJE9bRoUGEe.gG5HLFQi.SJs3FozpxKOGMAdLHnvSq', 'ANWAR HIDAYAT', '213321324', '007000077', 'Laki-laki', 'XII RPL 2', 'Tidak Aktif', 1, 1, 158),
(39, '$2y$10$tSmOW7r7ecN/bsFqdKjlDOjc2mLYfp.tSq31qkL3ux3.EQR.zo6/G', 'ghhh', '4544', '46444', 'Laki-laki', 'XII RPL 2', 'Aktif', 2, 1, 155);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tempat_pkl`
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
-- Dumping data untuk tabel `tempat_pkl`
--

INSERT INTO `tempat_pkl` (`id_tempat_pkl`, `nama_tempat_pkl`, `alamat`, `nama_instruktur`, `alamat_kontak`, `kuota_siswa`, `jurusan_id`) VALUES
(154, 'BPN Sumedang', '', '', '', 9, 1),
(155, 'Disdukcapil Sumedang', '', '', '', 3, 1),
(156, 'POLRES Subang', '', '', '', 2, 1),
(157, 'Dinas Pertanian Subang', '', '', '', 3, 1),
(158, 'PT. TELKOM Haurgeulis', '', '', '', 2, 1),
(159, 'DISDUKCAPIL Subang', '', '', '', 5, 1),
(160, 'Disnakertrans ESDM Subang', '', '', '', 4, 1),
(161, 'Universitas Subang', '', '', '', 6, 1),
(162, 'HEC HAURGEULIS (Haurgeulis Education Centre)', '', '', '', 4, 1),
(163, 'LKP Execom Haurgeulis', '', '', '', 10, 1),
(164, 'Puja Photocopy Gantar', '', '', '', 4, 2),
(165, 'VJ Studio Bongas - Indramayu', '', '', '', 2, 2),
(166, 'Happy Digital Printing Gabuswetan', '', '', '', 4, 2),
(167, 'Auni Digital Printing Anjatan', '', '', '', 6, 2),
(168, 'Ruang Photo Haurgeulis', '', '', '', 2, 2),
(169, 'Kencana Putra Digital Printing - Subang', '', '', '', 4, 2),
(170, 'Prime Studio Subang', '', '', '', 8, 2),
(171, 'Abadi Photoworks Subang', '', '', '', 4, 2),
(172, 'Bestro Project Sumedang', '', '', '', 2, 2),
(173, 'Kuens Photo Studio Haurgeulis', '', '', '', 2, 2),
(174, 'Percetakan Dea Grafika Subang', '', '', '', 3, 2),
(175, 'Perdana Photo Lab dan Studio Subang', '', '', '', 4, 2),
(176, 'Pubdok Photografi Sumedang', '', '', '', 3, 2),
(177, 'Dizah Printing Bongas', '', '', '', 3, 2),
(178, 'CV. Sumber Mandiri Printshop Subang', '', '', '', 2, 2),
(179, 'Nusa Edu Bandung', '', '', '', 2, 2),
(180, 'CIHO Printshop Sumedang', '', '', '', 6, 2),
(181, 'Bayu Copy Digital Gantar', '', '', '', 3, 2),
(182, 'BENGKEL EAS ERIK AUTO SERVICE', '', '', '', 5, 3),
(183, 'PT INDOMOBIL TRADA INTERNASIONAL KARAWANG', '', '', '', 5, 3),
(184, 'MUKTI AGUNG', '', '', '', 6, 3),
(185, 'BENGKEL MOBIL ATIM', '', '', '', 3, 3),
(186, 'GLOBAL MOTOR', '', '', '', 4, 3),
(187, 'BENGKEL MOBIL A 8', '', '', '', 2, 3),
(188, 'BENGKEL MOBIL AN MOTOR', '', '', '', 6, 3),
(189, 'BENGKEL ROBI MOTOR', '', '', '', 4, 3),
(190, 'GLORI MOTOR', '', '', '', 4, 3),
(191, 'MEKAR MUDA MOTOR', '', '', '', 4, 3),
(192, 'BENGKEL JEMBAR MOTOR', '', '', '', 6, 3),
(193, 'BENGKEL ANUGRAH WIJAYA MOTOR', '', '', '', 5, 3),
(194, 'PRIMA MOTOR', '', '', '', 4, 3),
(195, 'SLAMET JAYA ABADI MOTOR', '', '', '', 2, 3),
(196, 'PT SUN STAR PRIMA MOTOR MITSUBISHI', '', '', '', 2, 3),
(197, 'PT CINTA DAMAI PUTRA BAHAGIA', '', '', '', 2, 3),
(198, 'PT CITRA ASRI BUANA', '', '', '', 2, 3),
(199, 'JASUTRA MOTOR', '', '', '', 2, 3),
(200, 'ZIA MOTOR', '', '', '', 4, 3),
(201, 'BENGKEL FAMILY MANDIRI', '', '', '', 1, 3),
(202, 'BENGKEL KIARA AUTO', '', '', '', 1, 3),
(203, 'LUPIN MOTOR', '', '', '', 2, 3),
(204, 'RALLY VARIASI', '', '', '', 1, 3),
(205, 'PT ASAHI INDONESIA', '', '', '', 3, 3),
(206, 'BENGKEL MOBIL RAHEL MOTOR', '', '', '', 1, 3),
(207, 'DAYAT MOTOR', '', '', '', 5, 3),
(208, 'AL-IANDA', '', '', '', 2, 3),
(209, 'BENGKEL MOBIL MANG ATEP', '', '', '', 3, 3),
(210, 'MIADI MOTOR', '', '', '', 5, 3),
(211, 'AZMI CAR', '', '', '', 4, 3),
(212, 'JAJA LAS', '', '', '', 2, 4),
(213, 'BM STAR', '', '', '', 4, 4),
(214, 'IFAN JAYA LAS', '', '', '', 3, 4),
(215, 'AL IANDA MOTOR', '', '', '', 2, 4),
(216, 'MEKARJAYA', '', '', '', 2, 4),
(217, 'ACHENK KARYA ABADI', '', '', '', 2, 4),
(218, 'CONDONG TEKNIK', '', '', '', 2, 4),
(219, 'Q-BAL', '', '', '', 3, 4),
(220, 'WANAKAYA LAS', '', '', '', 2, 4),
(221, 'PT.SANOH INDONESIA', '', '', '', 2, 4),
(222, 'CV.MULTI GUNA UTAMA', '', '', '', 4, 4),
(223, 'RAKA MANDIRI', '', '', '', 3, 4),
(224, 'LAFI PUSKESAD', '', '', '', 24, 5),
(225, 'RS. BHAYANGKARA INDRAMAYU', '', '', '', 6, 5),
(226, 'PUSKESMAS HAURGEULIS', '', '', '', 3, 5),
(227, 'DAYAT MOTOR', '', '', '', 5, 6),
(228, 'AL IANDA MOTOR', '', '', '', 2, 6),
(229, 'CV. ARSIFAN PROJECT', '', '', '', 10, 7),
(230, 'CV. PERMATA KONSULTAN', '', '', '', 4, 7),
(231, 'CV. DESIMA', '', '', '', 3, 7),
(232, 'PT. DAE ARSITEK', '', '', '', 4, 7),
(233, 'Prop2GO', '', '', '', 3, 7),
(234, 'CV. ANGNGA STUDIO', '', '', '', 3, 7),
(235, 'Skala Ruang', '', '', '', 3, 7),
(236, 'PT. Prisma Cahaya Persada', '', '', '', 3, 7),
(237, 'PT. ARIENDO ARCHITECTURE WORKSHOP', '', '', '', 2, 7),
(238, 'PT. BANGUN INDAH PERKASA SENTOSA', '', '', '', 4, 7),
(239, 'PT. ANDRAXI MANDIRI INDONESIA', '', '', '', 3, 7),
(240, 'DINAS PERUMAHAN KAWASAN PERMUKIMAN DAN PERTANAHAN KAB. SUMEDANG', '', '', '', 5, 7),
(241, 'CV. Areta Jaya', '', '', '', 1, 7),
(242, 'PT. SAMUDRA JAYA KONSULTAN', '', '', '', 4, 7),
(243, 'CV. Dazi Architektur', '', '', '', 4, 7),
(244, 'QINTANI BETON UTAMA', '', '', '', 3, 7),
(245, 'DJONG DESIGN', '', '', '', 3, 7),
(246, 'PT. Nusa Raya Cipta', '', '', '', 2, 7),
(247, 'BANK BJB KCP HAURGEULIS', '', '', '', 3, 8),
(248, 'TRIDJAYA MOTOR CIPANCUH', '', '', '', 3, 8),
(249, 'KSP ANUGERAH REJEKI (GANTAR)', '', '', '', 2, 8),
(250, 'Notaris Makhrom Ismail, SH., M.Kn', '', '', '', 2, 8),
(251, 'BANK BPR PK BONGAS', '', '', '', 4, 8),
(252, 'BKPSDM KAB. SUBANG', '', '', '', 5, 8),
(253, 'PT Shinta Indah Jaya Factory', '', '', '', 3, 8),
(254, 'Koperasi Nusa Gantar', '', '', '', 2, 8),
(255, 'PRIMKOPOL POLRES SUBANG', '', '', '', 5, 8),
(256, 'KSP Maler Group Anjatan', '', '', '', 2, 8),
(257, 'FIF Group Haurgeulis', '', '', '', 2, 8),
(258, 'FIF Group Gantar', '', '', '', 3, 8),
(259, 'PT. PLN (Persero) ULP Haurgeulis', '', '', '', 2, 8),
(260, 'PENGADILAN NEGERI SUBANG KELAS 1B', '', '', '', 4, 8),
(261, 'CV. TRIDJAYA ANUGERAH SUKSES', '', '', '', 3, 8),
(262, 'UPK DAPM GANTAR AGUNG', '', '', '', 6, 8),
(263, 'Kantor POS Anjatan', '', '', '', 4, 8),
(264, 'KPP Pratama Subang', '', '', '', 2, 8),
(265, 'BANK BJB KCP TERISI', '', '', '', 3, 8),
(266, 'PUSAT PENGELOLA PENDAPATAN DAERAH WILAYAH KAB. INDRAMAYU II HAURGEULIS', '', '', '', 2, 8),
(267, 'Bank BIMJ / BPR Cabang Kroya', '', '', '', 3, 8),
(268, 'Kantor Pemerintah Daerah Subang', '', '', '', 8, 8),
(269, 'Kantor POS Haurgeulis', '', '', '', 4, 8),
(270, 'KPPS BMT NU Umat Sejahtera Haurgeulis', '', '', '', 3, 8),
(271, 'PT Pegadaian Cabang Anjatan', '', '', '', 3, 8),
(272, 'Notaris Pendi Paundrakarna Gantar', '', '', '', 2, 8),
(273, 'DEASSY SUKSES MULTI USAHA', '', '', '', 2, 8),
(274, 'PERUM PERHUTANI', '', '', '', 2, 8),
(275, 'Pt sejahtera indah', 'cirebon utara', 'bapak yono', '02343433', 21, 8),
(277, 'PT SAGARAA', 'Jl surenn', 'ANWAR H', 'coba@gamil.com', 4, 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD KEY `created_at` (`created_at`);

--
-- Indeks untuk tabel `guru_pembimbing`
--
ALTER TABLE `guru_pembimbing`
  ADD PRIMARY KEY (`id_pembimbing`),
  ADD UNIQUE KEY `username` (`nip`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `nama_pembimbing` (`nama_pembimbing`);

--
-- Indeks untuk tabel `jurnal_harian`
--
ALTER TABLE `jurnal_harian`
  ADD PRIMARY KEY (`id_jurnal_harian`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `siswa_id_2` (`siswa_id`,`tanggal`),
  ADD KEY `siswa_id_3` (`siswa_id`),
  ADD KEY `tanggal` (`tanggal`);

--
-- Indeks untuk tabel `jurnal_kegiatan`
--
ALTER TABLE `jurnal_kegiatan`
  ADD PRIMARY KEY (`id_jurnal_kegiatan`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `siswa_id_2` (`siswa_id`),
  ADD KEY `tanggal_laporan` (`tanggal_laporan`),
  ADD KEY `siswa_id_3` (`siswa_id`,`tanggal_laporan`);

--
-- Indeks untuk tabel `jurusan`
--
ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`id_jurusan`);

--
-- Indeks untuk tabel `siswa`
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
-- Indeks untuk tabel `tempat_pkl`
--
ALTER TABLE `tempat_pkl`
  ADD PRIMARY KEY (`id_tempat_pkl`),
  ADD KEY `jurusan_id` (`jurusan_id`),
  ADD KEY `jurusan_id_2` (`jurusan_id`),
  ADD KEY `nama_tempat_pkl` (`nama_tempat_pkl`),
  ADD KEY `nama_instruktur` (`nama_instruktur`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `guru_pembimbing`
--
ALTER TABLE `guru_pembimbing`
  MODIFY `id_pembimbing` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `jurnal_harian`
--
ALTER TABLE `jurnal_harian`
  MODIFY `id_jurnal_harian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `jurnal_kegiatan`
--
ALTER TABLE `jurnal_kegiatan`
  MODIFY `id_jurnal_kegiatan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id_jurusan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `tempat_pkl`
--
ALTER TABLE `tempat_pkl`
  MODIFY `id_tempat_pkl` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=278;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `jurnal_harian`
--
ALTER TABLE `jurnal_harian`
  ADD CONSTRAINT `fk_jurnal_harian_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jurnal_harian_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jurnal_kegiatan`
--
ALTER TABLE `jurnal_kegiatan`
  ADD CONSTRAINT `fk_jurnal_kegiatan_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jurnal_kegiatan_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `fk_siswa_jurusan` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_siswa_pembimbing` FOREIGN KEY (`pembimbing_id`) REFERENCES `guru_pembimbing` (`id_pembimbing`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_siswa_tempat_pkl` FOREIGN KEY (`tempat_pkl_id`) REFERENCES `tempat_pkl` (`id_tempat_pkl`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`pembimbing_id`) REFERENCES `guru_pembimbing` (`id_pembimbing`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_3` FOREIGN KEY (`tempat_pkl_id`) REFERENCES `tempat_pkl` (`id_tempat_pkl`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tempat_pkl`
--
ALTER TABLE `tempat_pkl`
  ADD CONSTRAINT `fk_tempat_pkl_jurusan` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `tempat_pkl_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
