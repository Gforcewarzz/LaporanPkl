-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 12 Jul 2025 pada 11.10
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
-- Struktur dari tabel `absensi_siswa`
--

CREATE TABLE `absensi_siswa` (
  `id_absensi` int NOT NULL,
  `siswa_id` int NOT NULL,
  `tanggal_absen` date NOT NULL,
  `status_absen` enum('Hadir','Sakit','Izin','Alfa','Libur') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `keterangan` text,
  `bukti_foto` varchar(255) DEFAULT NULL,
  `waktu_input` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `absensi_siswa`
--

INSERT INTO `absensi_siswa` (`id_absensi`, `siswa_id`, `tanggal_absen`, `status_absen`, `keterangan`, `bukti_foto`, `waktu_input`) VALUES
(3, 403, '2025-07-12', 'Sakit', 'sakit dadaku , ku melupa rindu', 'bukti_6871c2abb9700_img_20180417_090504_4481160940215.jpg', '2025-07-12 09:04:27'),
(6, 393, '2025-07-12', 'Sakit', 'sadkwd', 'bukti_6871e0f747d4e_Gambar WhatsApp 2025-07-10 pukul 19.10.13_b3e05812.jpg', '2025-07-12 11:13:43'),
(7, 379, '2025-07-12', 'Hadir', NULL, NULL, '2025-07-12 12:06:25'),
(8, 378, '2025-07-12', 'Hadir', NULL, NULL, '2025-07-12 12:08:44'),
(9, 354, '2025-07-12', 'Hadir', NULL, NULL, '2025-07-12 12:12:02'),
(10, 404, '2025-07-12', 'Libur', NULL, NULL, '2025-07-12 12:37:30'),
(11, 382, '2025-07-12', 'Libur', NULL, NULL, '2025-07-12 17:24:57'),
(12, 371, '2025-07-12', 'Hadir', NULL, NULL, '2025-07-12 17:28:14'),
(13, 335, '2025-07-12', 'Libur', NULL, NULL, '2025-07-12 17:28:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_admin` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama_admin`, `email`, `created_at`) VALUES
(3, 'Admin01', '$2y$10$L4dkmiURidr.3ByYlqzrQuqUuY1EYrDwwlTvaWqaif.qgKyPKYezu', 'Admin01', '', '2025-06-30 16:13:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru_pembimbing`
--

CREATE TABLE `guru_pembimbing` (
  `id_pembimbing` int NOT NULL,
  `nama_pembimbing` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nip` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `guru_pembimbing`
--

INSERT INTO `guru_pembimbing` (`id_pembimbing`, `nama_pembimbing`, `nip`, `jenis_kelamin`, `password`) VALUES
(1, 'Ahmad Fauzi', '198112001001', 'Laki-laki', '$2y$10$yq5CNAsYYGXiKdCwgZfy.esEeq/lt4eNsUBBrSTxrYVy0nrMbR5Fq'),
(2, 'Nani Yuningsih, S. Farm.', '198212002002', 'Perempuan', '$2y$10$J0Tfn0yiqkoKkcIOpj95EeEIcNriwl75ZzNonHl9ovZY0VebkzZxy'),
(3, 'Budi Santoso', '198312003003', 'Laki-laki', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(4, 'Dewi Lestari', '198412004004', 'Perempuan', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(5, 'Joko Prasetyo', '198512005005', 'Laki-laki', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(6, 'Apt. Sally Oktavia Sagita Ningsih, S. Farm.', '198612006006', 'Perempuan', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(7, 'Heri Wijaya', '198712007007', 'Laki-laki', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(8, 'Lina Marlina', '198812008008', 'Perempuan', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(9, 'Andi Saputra', '198912009009', 'Laki-laki', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(10, 'Desi Ratnasari', '19810120100010', 'Perempuan', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'),
(13, 'Ibrahim Zaenal', '198008191826171', 'Laki-laki', '$2y$10$biJMwaSVowgIQgskMvqnSOhUmf2AetJcZkLfbKELU81CYFvJaZBva'),
(16, 'SARWO EDI, S.St', '9090909090', 'Laki-laki', '$2y$10$8ZF/xQiOMV.tu//UF4DxS.aynRykYRY93QxBwQpkautvkSGVrtHau'),
(17, 'ABDU ROHIM, S.Kom', '7812313222', 'Laki-laki', '$2y$10$BQrXoiabS1Eeuh7niwWNSOEbh.G7bfp3EzujQXFP4eJHC1PhEi8K6'),
(18, 'ABD.AZIZ REGUNA,S.Pd', '9632203234', 'Laki-laki', '$2y$10$ULCtt/LsMKqcpAc.JFKUxuruwEwABwElX1ry.cOfjtstM9fVmgwXa'),
(19, 'RUSLAN FIRMANSYAH,S/Pd', '1981120010012', 'Laki-laki', '$2y$10$n9Izq4gmLHQrbXV.mUyUu.5J9EnGoHDnpPSwRxshPk1giG8pUtDmS'),
(20, 'NUROHIM, S.T', '19811200100199', 'Laki-laki', '$2y$10$DmspDeJDfp4YMyP6kwy5deS.aephZsNNQoUCE5RUXSOTtVB.RrM1G'),
(21, 'DUDI  AMARUDIN, S.T', '19811200100198', 'Laki-laki', '$2y$10$qo2RChee7n8X69jJEEQxyeeTqP2ISYUicsZkUugWpWp8uICXeVhkO'),
(22, 'WAWAN, S.T.', '19811200100808', 'Laki-laki', '$2y$10$uMPPQyYhsKbjZ4.BHdLDxeNDp3eMBfF6Mi5B/55B2JELMbGfpTNpK'),
(23, 'WAHIB MUDHOFIR, S.Kom.', '1981120010019080', 'Laki-laki', '$2y$10$RmSm5ohIHCGxKj2DcOHXoeUSndskAaRR8d.ARWpp/2kr0zogONfHC'),
(24, 'AHMAD NURSOHE, S.Kom.', '19811200100821', 'Laki-laki', '$2y$10$etPA8Ip7JPNbe.OUvrE7buR98W4fxR0u2vfxulDJB5vegjLA6l92C'),
(25, 'WANA, S.T.', '1981120010019089', 'Laki-laki', '$2y$10$zKyCGMvNJIZcXzZeKyDXHeDAef15Hgjle8icAoq2HMWS8lvPzlodG'),
(26, 'RUDI HASAN ASURO, S.Kom.', '198112001008092', 'Laki-laki', '$2y$10$dFeqe8AaYxz88sFEdjoLhufvU9kaSyfDtmnv5nbv2wRgHG7Bdq9cy');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurnal_harian`
--

CREATE TABLE `jurnal_harian` (
  `id_jurnal_harian` int NOT NULL,
  `tanggal` date NOT NULL,
  `pekerjaan` text COLLATE utf8mb4_general_ci,
  `catatan` text COLLATE utf8mb4_general_ci,
  `siswa_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jurnal_harian`
--

INSERT INTO `jurnal_harian` (`id_jurnal_harian`, `tanggal`, `pekerjaan`, `catatan`, `siswa_id`) VALUES
(13, '2025-07-01', 'perkenalan diri, berkeliling gedung dan penempatan/pembagian tugas', 'karena masi awal jadi hanya perkenalan diri, pembagian tugas dan berkeliling gedung untuk mengetahui denah gedungnya.', 188),
(14, '2025-07-02', 'pelayanan keuangan, belajar bagaimana merekap pembayaran dan menginfut pembayaran serta belajar ngeprint kwitansi hasil pembayaran', 'lumayan rumit untuk menginfut maka dari itu sementara membantu untuk mempoto kwitansi hasil pembayaran ketika yang jaga sedang istirahat lalu kirim ke ibu syefira dan ibu syefira yang menginfut.', 188),
(15, '2025-07-01', 'Memindahkan file ke Gdrive', 'mengalami salah input folder drivenya', 183),
(16, '2025-07-02', 'membantu tim umum mengeprint surat ', 'pada saat menggunting surat tidak rapih', 183),
(17, '2025-07-01', 'menyusun berkas sesuai dari tanggal,arahan dari bagian capil(arsip) ke komputer (digital) ,mengurut akta kelahiran dari taun 24-25 dan kutipan akta  kematian ', 'mengalami kesulitan saat mencari data', 184),
(18, '2025-07-02', 'mengscan data artip ', 'lupa ngesave file dokumennya', 184),
(20, '2025-07-01', '1. Membantu tim bagian hukum untuk mencari salah satu berkas yang nyasar di lemari arsip untuk di pindahkan ke tempat yang benar.\r\n2. Mengedit data arsipan pada bagian penempatan dilemari arsip menggunakan website SIPP.\r\n3. Mengambil berkas di ruangan bagian panitera hukum lalu meminta tanda tangan ke panitera muda pidana kemudian mengantarkan berkas yang sudah di tanda tangan ke ruangan \r\narsip.', 'Saya melalukan ini berdua dengan salah satu teman saya.', 182),
(21, '2025-07-01', ' pertama diruangan bidang Capil itu mencoba membantu untuk mengintri atau memasukkan data ,dari data manual ke data digital ', 'mengalami kesulitan /lupa saat mau menyimpan file ke folder yg tersedia ', 139),
(22, '2025-07-02', 'membantu tim di bagian bidang arsip paris.\r\ncontoh menyimpan data arsip manual ke arsip digital.\r\n', 'kendala nya tadi keyboard nya aga susah di pencet dan aga ruangan nya aga sumpek', 155),
(23, '2025-07-02', 'yg kedua bagian ruangan arsip paris, membantu tim menyimpan arsip manual ke arsip digital', 'mengalami kesulitan saat menyimpan bagian dokumennya ', 139),
(24, '2025-07-01', 'ruangan bidang Capil.\r\nMengintri atau memasukan data ,dari data manual ke data digital.', 'menghadapi kendala \r\n1. PC tidak dapat berfungsi dengan baik, sehingga mempengaruhi kinerja bagian lain.\r\n2. PC berjalan sangat lambat, sehingga memakan waktu lebih lama untuk melakukan tugas.\r\n3. scanner tidak dapat memindai dokumen dengan baik, sehingga mempengaruhi kualitas hasil scan.', 155),
(25, '2025-07-01', 'pengenalan koor atau penanggung jawab siswa pkl dari smkn 1 gantar, pembagian tempat pada setiap atau masing-masing siswanya, berkeliling universitas subang dan pengenalan nama-nama gedungnya (room tour).\r\n', 'mengenal orang baru atau penanggung jawab siswa pklnya, mengetahui tempat atau bidangnya masing-masing, mengenal gedung-gedung yang ada di universitas subang.', 125),
(26, '2025-07-02', '1. Melanjutkan tugas kemarin yaitu mengedit data rekapan di ruangan arsip.\r\n2. Mengambil berkas di ruangan panitraan hukum untuk di bawa ke ruangan arsip.', 'Tidak jauh beda dengan kemarin.', 182),
(27, '2025-07-02', 'merekap data mahasiswa jurusan atau bidang komputer angkatan tahun 2023, menyeken hard file menjadi soft file dengan format file pdf, memfotocopy berkas dalam jumlah yang bayak.', 'senang karna mendapat pengalaman baru dengan dipenuhi orang-orang yang baik', 125),
(29, '2025-07-01', 'Memperkenalkan diri serta mencatat hal-hal penting yang disampaikan oleh atasan terkait jam kerja, seragam, dan penggunaan atribut', 'hari pertama diawali dengan perkenalan staf pegawai univ. Subang, pembagian tugas, serta perkenalan lingkungan dan fakultas yang ada', 191),
(30, '2025-07-02', 'Mendata surat keluar yang akan diberikan kepada beberapa pihak yang dituju', 'mendata surat keluar di buku kendali surat keluar dan tidak ada hambatan mengenai pekerjaan ini', 191),
(31, '2025-07-01', '-', 'Melakukan pengenalan lingkungan kerja (tidak langsung bekerja)', 166),
(32, '2025-07-02', 'melakukan aktivasi pada ms word  dan juga melakukan perakitan', '-', 166),
(33, '2025-07-01', '-', 'perkenalan diri dan pembagian bidang', 177),
(34, '2025-07-03', 'Membatu apoteker mengambil obat sesuai resep yang di minta oleh pasien ', 'Menghadapi pasien yang mengantri untuk mengambil obat ', 217),
(35, '2025-07-01', 'hari pertama perkenalan RS Bhayangkara,dan ikut upacara, pas hari pertama diajak keliling2 dari mulai ruang igd, poliklinik,tempat pengambilan faksin, kita dikasi tau tentangg ruangan tersebut buat apa aja dan di jelaskan sama ibu win,pas hari pertama uda ditugaskan untuk pkl, hari pertama aku dapet sip siang dari jam 2siang sampe jam 9 malem,disana ditugaskan untuk mengambilkan obat²an atau obat injeksi. tugasnnya ada bagian didepan untuk mengantarkan resep ke ruang pengambilan obat,teruss saya ambil resepnnya kemudian dicari obat apa aja yang mau di berikan kepada pasien.', 'tidak ada kendala apapun,semuannya alhamdulilah berjalan dengan lancar', 197),
(36, '2025-07-03', 'Saya selama disini membantu memanggil pasien untuk memeriksa gigi atau mencabut gigi ', 'Ngada catatan tambahan ', 212),
(37, '2025-07-03', 'membantu tim kia dalam membuat triwulan ibu hamil,persalinan dan bayi baru lahir', 'selama di kia saya hanya membantu bantu membuat triwulan', 219),
(38, '2025-07-03', 'saya melayani resep dan memberikan obat kepada pasien', 'tidak ada ', 216),
(39, '2025-07-02', 'Membantu pak reno menginstall ulang laptop dan stanbay di lpm', '-', 177),
(40, '2025-07-03', 'mengscan data artip  dan mengsteples in berkas nya lagi ', 'tidak,perkerjaanya enak,seruu  mines nya sakit pinggang sama sakit tangan nya', 184),
(41, '2025-07-03', 'membantu tim di bagian bidang arsip.\r\ncontoh menyimpan data arsip manual ke arsip digital.', 'kendala nya itu sakit pinggang sama tangan pegel.', 155),
(42, '2025-07-03', 'membantu tim dibagian bidang arsip contohnya kaya menyimpan arsip manual ke arsip digital', 'minus sakitt pinggang +tangan pegel ', 139),
(43, '2025-07-03', 'pelayanan keuangan', 'banyak berdiam diri karena sekarang sedang PMB (penerimaan mahasiswi baru) dan hanya 2 orang yang melakukan pembayaran.', 188),
(44, '2025-07-03', '1. Belajar dan membantu dalam mempersiapkan Rapat para Rektor dengan para dekan seperti mengabsen kehadiran dan memberikan konsumsi yang sudah tersedia.\r\n2. Menyiapkan Surat keluar dan mendata surat keluar ke dalam Buku Kendali Surat Keluar.\r\n3. Memasukkan surat serta brosur universitas ke dalam map untuk diberikan kepada pihak pihak yang akan dituju.', 'Belajar banyak mengenai surat-surat terlebih lagi dalam mendata surat keluar, dan belajar bagaimana mempersiapkan rapat dengan baik.', 191),
(45, '2025-07-03', 'melayani pasien yang ingin menebus obat', 'tidak ada', 227),
(46, '2025-07-03', 'membantu menyalan dan mematikan infokus , dan membantu ngeprint', '-', 177),
(47, '2025-07-01', '- ', 'memperkenalkan diri, membagi individu di suatu bidang secara menyeluruh, pengenalan tempat/gedung di Universitas Subang', 171),
(48, '2025-07-02', 'mengaktifasi ms office word, membongkar dan merakit PC', 'membersihkan personal computer', 171),
(49, '2025-07-03', 'ngebooting atau mengkloningkan data SSD dari komputer 1 ke yang lain', '-', 171),
(51, '2025-07-01', 'Membantu rekan sesama peserta PKL dalam melakukan pencatatan data surat keluar secara manual pada buku agenda di Subbagian Kepegawaian. Pekerjaan meliputi pengisian nomor surat, tanggal, perihal, dan instansi tujuan.', 'Pada hari pertama, saya belum menerima penugasan di Subbagian PTIP ( Perencanaan, Teknologi Informasi, dan Pelaporan ) sebagai tempat utama PKL, sehingga sementara waktu membantu kegiatan administrasi di bagian Kepegawaian.', 154),
(52, '2025-07-02', 'Mulai bertugas di Subbagian PTIP dengan membantu menandai dokumen menggunakan stiker notes pada bagian yang memerlukan tanda tangan, guna memudahkan pegawai saat proses penandatanganan.', 'Menandai dokumen dilakukan untuk mempercepat proses penandatanganan, agar pegawai tidak perlu mencari halaman yang harus ditandatangani satu per satu.', 154),
(53, '2025-07-03', 'Membuat desain grafis untuk media publikasi Pengadilan Negeri Subang, termasuk poster peringatan 17 Agustus dan Selain itu, saya juga merancang ulang tampilan feed Instagram sebagai etalase digital kegiatan-kegiatan (giat) resmi lembaga.', 'Dalam proses pembuatan desain, desain dibuat menggunakan platform Canva ,saya menyesuaikan setiap elemen seperti logo, foto pimpinan, dan warna latar agar sesuai dengan karakter resmi lembaga. Penempatan elemen disusun secara proporsional untuk menjaga estetika sekaligus memastikan informasi kegiatan dapat tersampaikan secara jelas dan formal melalui media sosial.', 154),
(54, '2025-07-03', 'Staf pada bagian Panitera Muda Perdata Pengadilan Negeri Subang bertugas mencatat data perkara perdata yang masuk, baik berupa gugatan maupun permohonan. Pencatatan meliputi identitas penggugat dan tergugat, nama hakim yang menangani, jadwal persidangan, serta perkembangan proses perkara. Seluruh data dicatat secara akurat dalam sistem informasi dan dokumen administrasi resmi pengadilan untuk mendukung kelancaran proses persidangan.', 'Pekerjaan ini memerlukan ketelitian, tanggung jawab, kedisiplinan, serta kemampuan menjaga kerahasiaan dan integritas  perkara.', 150),
(55, '2025-07-03', '1. Mengedit data arsipan \r\n2. Mengambil berkas hasil sidang dan meminta tanda tangan ke panitera muda pidana dan panitera muda perdata ', 'Sama seperti pekerjaan kemarin ', 182),
(56, '2025-07-04', 'Mencatatat pasien yang akan di periksa dan membersihkan alat yang akan di gunakan', 'Ngga ada', 212),
(57, '2025-07-04', 'masih sama,membantu bikin data triwulan dikomputer dan membantu ibu bidan jikalau beliau kesusahan', 'tidak ada', 219),
(58, '2025-07-04', 'meracik resep atau melayani pasien', 'tidak ada', 227),
(60, '2025-07-04', 'menggunting obatt dan memasukan ke plastik/etiket obat, dan diberikan kepada pasien', 'tidak ada', 216),
(61, '2025-07-04', 'Membantu apoteker mengambil obat,dan alat kesehatan sesuai dengan resep untuk diberikan kepada pasien', 'Menghadapi pasien yang mengantri untuk mengambil obat ', 202),
(63, '2025-07-02', 'Sebagai staf pada bagian Panitera Muda Perdata, bertugas mencatat dan mendata perkara perdata yang masuk, baik berupa gugatan maupun permohonan. Tugas lainnya meliputi pengantaran berkas ke ruang hukum serta melakukan pefotokopian dokumen sesuai arahan dari atasan', 'Pencatatan dilakukan secara manual maupun melalui sistem informasi perkara untuk menunjang kelancaran proses administrasi peradilan.', 150),
(64, '2025-07-04', 'mengscan data artip dan menyimpan data  artip manual ke artip digital', 'berjalan dengan baik ,mines nya seperti biasa sakit pinggang sama leher ', 184),
(65, '2025-07-04', 'masih membantu tim di bagian bidang arsip.\r\ncontoh menyimpan data arsip manual ke arsip digital.', 'uda lumayan ngerasa nyaman sih tapi mines nya aga pegel doang', 155),
(66, '2025-07-04', 'pelayanan keuangan, melayani pembayaran kuliah mahasiswi, mengarahkan mahasiswi untuk menulis NPM nya di kwitansi pembayaran lalu di poto untuk di input oleh bu syefira ', 'hanya beberapa mahasiswi yang datang.', 188),
(67, '2025-07-04', '1. Mengantar kertas ke ruang surat menyurat.\r\n2. Menyusun nota pembelian untuk keperluan SPJ (Surat Pertanggungjawaban)', 'tidak ada', 191),
(68, '2025-07-03', 'Hari pertama saya pkl di tanggal 3 Juli 2025,saya kebagian sif pagi, pagi jam 07:00 saya mengikuti apel rutin dari Rumah sakit, setelah mengikuti apel pagi, lanjut bertugas, tugas saya yang pertama yaitu mengambil obat dari resep yang sudah disediakan, terus saya ditugaskan untuk meracik obat. ', 'Alhamdulillah baik. ', 204),
(69, '2025-07-04', 'Membantu Nulisin arsip surat,mengantarkan arsip untuk di mintai nomer surat,menginstall aplikasi scaner ', '-', 177),
(70, '2025-07-02', '1. nyari resep yang sudah di anterin sma temen saya,\r\n2. mengantarkan obat syrup ke pk1 atau puri kencana\r\n 4. mengantarkan resep.', 'tidak ada kendala bekerjasama dengan baik', 197),
(71, '2025-07-04', 'Hari kedua saya yaitu di hari Kamis saya masuk siff ps (pagi siang) di jam 09:00 s/d 04:00, waktu pagi saya ditugaskan untuk meracik obat, setelah itu saya ditugaskan untuk  mengambil obat sesuai resep yg dibagikan. ', 'Mulai mengerti.. ', 204),
(73, '2025-07-03', 'mengcloning data ssd', 'pada setiap komputer, ssd dicopot dan dipasang ke sebuah komputer untuk dilakukan pengcloningan', 166),
(74, '2025-07-04', 'melanjutkan mengclone data ssd', '-', 166),
(75, '2025-07-04', 'masih membantu tim menyimpan data arsip manual ke arsip digital', 'sakit pinggang +tangan cape ngetik', 139),
(76, '2025-07-05', 'membantu tim kia dalam membuat data triwulan', 'tidak ada catatan tambahan', 219),
(77, '2025-07-05', 'meracik obat', 'tidak ada', 227),
(78, '2025-07-05', 'menggunting obat, memasukan kedalam etiket dan diberikan kepada pasien', 'tidak ada', 216),
(79, '2025-07-05', 'Memebersihkan alat sambil menunggu pasien ', 'Ngada', 212),
(80, '2025-07-05', '\r\nTugas kegiatan nya:\r\n1. absen masuk\r\n2. mengisi ulang obat injeksi dan tablet/ syrup\r\n3. mengantarkan resep ke temen\r\n4. mengambil obat sesuai resepnya\r\n5. absen pulang', 'semuanya aman dan baik.', 197),
(81, '2025-07-05', 'Membantu apoteker mengambil obat,dan alat kesehatan.\r\nMengantarkan obat dan alat kesehatan yang di butuhkan pasien kepada perawat', 'Kendala pada Hari Sabtu Resep lebih sedikit karena poliklinik tutup ', 202),
(82, '2025-07-05', '1.menyiapkan alat yang dibutuhkan. \r\n2.menyiapkan obat, sesuai dengan resep yang dibutuhkan. \r\n3.mengartarkan obat keruangan', 'melakukan kalibrasi alat dan bahan yang sesuai', 204),
(84, '2025-07-05', 'pelayanan keuangan, membantu pembayaran seperti biasa, dan belajar mengriset data mahasiswi baru, serta belajar mengarahkan mahasiswi baru untuk membuka web kampus ( SIAKAD ) untuk digunakan saat mulai berkuliah nanti, serta mengarahkan dan menjelaskaan kegunaannya, seperti menjelaskan tentang history pembayaran (bayaran yg sudah di bayar) dan tagihan pembayaran (tagihan/pembayaran yg belum di bayar).', 'kendala : menerima pemembayar biaya sidang yg harusnya bayar ke fakultas ', 188),
(85, '2025-07-04', 'melanjutkan ngebooting atau mengkloningkan data SSD dari komputer 1 ke yang lain seperti sebelumnya', '-', 171),
(87, '2025-07-01', 'menyiapkan alkes dan mengambil obat sesuai resep yang di berikan ', 'melakukan kalibrasi alat dan bahan yang sesuai ', 196),
(88, '2025-07-02', 'membuat kapsul', 'melakukan kalibrasi alat dan bahan yang sesuai ', 196),
(89, '2025-07-03', 'menerima resep di meja depan ', 'melakukan kalibrasi alat dan bahan yang sesuai ', 196),
(90, '2025-07-06', 'menerima resep dari meja depan dan mengambil obat', 'melakukan kalibrasi alat dan bahan yang sesuai ', 196),
(91, '2025-07-05', 'Memberikan konsumsi untuk para hadirin seminar', 'menghadapi kendala saat membagikan konsumsi karena keadaan yang begitu ramai dan sedikit tidak kondusif', 191),
(92, '2025-07-06', 'Mengambil obat dan alat kesehatan Restock obat \r\n', 'Menghadapi pasien yang ingin mengambil obat dan alat kesehatan ', 202),
(93, '2025-07-01', 'Mengambil obat dan alat kesehatan, \r\nMengantarkan obat dan alkes keruang ICU, menunggu pasien mengambil obat ', 'Menghadapi pasien ', 202),
(94, '2025-07-04', 'Membantu tim bagian hukum mengarsip berkas', 'Menjadi tau cara mengarsip berkas', 182),
(95, '2025-07-07', 'Membatu Memangil pasien untuk di periksa giginya ', 'Ngada', 212),
(96, '2025-07-04', 'Membantu proses scanning di Subbagian Kepegawaian dan mengetik ulang berkas sidang lanjutan di bagian Panitera, lalu menyusun pertanyaan dalam bentuk tabel.', 'Mempelajari proses digitalisasi arsip kepegawaian dan membantu merapikan dokumen persidangan dengan menyusun tanya-jawab hakim dan pihak terkait dalam format tabel agar lebih sistematis.', 154),
(97, '2025-07-03', 'menghitung atk', '-', 183),
(98, '2025-07-07', 'membantu tim kia dalam membuat catatan ibu hamil', 'tidak ada', 219),
(99, '2025-07-06', '1. membantu mengambilkan obat dan ngeristok obat pct infus dan omeprazole injeksi', 'baik', 197),
(100, '2025-07-06', '1. membantu mengambilkan obat dan ngeristok obat pct infus dan omeprazole injeksi', 'baik', 197),
(101, '2025-07-04', 'menulis slip gaji', '-', 183),
(102, '2025-07-01', 'Membantu menyelesaikan perkara di ruang sidang', 'meminta tanda tangan hakim dengan menelusuri kantor', 134),
(103, '2025-07-02', 'Mengetik berita acara sidang perkara pidana nomor 86 atas nama NURIMAN terdakwa', 'menggunakan komputer di ruang pidana ', 134),
(104, '2025-07-03', 'Mengetik berita acara sidang perkara pidana nomor 86 atas nama NURIMAN terdakwa. ', 'membantu membuat berkas berkas saksi di ruang pidana', 134),
(105, '2025-07-04', 'mencatat dan menyalin pertanyaan dengan jawaban dari hardware menjadi software', 'menggunakan word', 134),
(106, '2025-07-07', 'meracik obat', 'tidak ada', 227),
(107, '2025-07-07', 'membantuu apoteker melayankan resep kepada pasien', 'tidak ada', 216),
(108, '2025-07-02', 'Membantu apoteker membuat capsul.', 'Menghadapi rasa deg-degan saat membuat capsul.\r\n', 217),
(109, '2025-07-03', 'Membantu menata obat sesuai tempatnya.', 'Harus Dengan cara yang berhati hati takut ada obat yang jatoh.', 217),
(110, '2025-07-04', 'Di ajari oleh senior tentang nama obat serta kegunaannya.', 'Harus mengingat obat yang telah kita pelajari .', 217),
(111, '2025-07-07', 'masih sama di ruangan bidang arsip Paris,membantu tim bidang menyimpan data arsip manual ke data arsip digital ', 'minus cape ngetik ful dari pagi Ampe sore', 139),
(112, '2025-07-07', 'Menyiapkan obat dan alat kesehatan untuk diberikan kepada pasien.seperti, Etabion, amlodipine,furosemid,ceftriaxon spuit,dll', 'Menghadapi resep yang ingin di berikan kepada pasien ', 202),
(113, '2025-07-07', '• Apel\r\n• Acara pembukaan PKPA/PKL\r\n• Tour guide (Pengenalan lingkungan secara observasi per gedung)\r\n• Perkenalan\r\n• Mendengarkan materi dari Mayor CKM Karyono\r\n• Pembagian kelompok ', 'Mengisi formulir registrasi dan pembagian baju', 223),
(114, '2025-07-07', '•Apel pagi\r\n•Acara pembukaan PKPA/PKL\r\n•tour guide(pengenalan lingkungan secara observasi pergedung) \r\n•Perkenalan\r\n•pembagian kelompok dan mendengarkan pemateri dari mayor CKM Karyono\r\n•perkenalan\r\n•pembagian kelompok dan mendengarkan pemateri dari Mayor CKM Karyono', 'mengisi formulir registrasi dan pembagian baju', 209),
(115, '2025-07-07', '•apel acara pembukaan pkl dan PIPA(untk yang prospesi)\r\n• tour guide, ( pengenalan lingkungan secara observasi per-gedung)\r\n•perkenalan \r\n •pembagian kelompok \r\n•mendengarkan pemateri dari mayor CKM karyono', 'mengisi formulir registrasi dan pembagian baju', 199),
(116, '2025-07-07', '• apel pagi \r\n• acara pembukaan PKPA/PKL\r\n• tour guide (pengenalan lingkungan secara observasi per gedung) \r\n• perkenalan\r\n• pembagian kelompok \r\n• mendengarkan pemateri dari mayor CKM karyono', 'mengisi formulir registrasi dan pembagian baju', 228),
(117, '2025-07-07', '•Apel acara pembukaan pkl\r\n•tour guide(pengenalan lingkungan secara observasi per gedung) \r\n•perkenalan \r\n•mendengar pemateri dari CKM karyono', 'Mengisi registrasi dan pembagian baju', 214),
(118, '2025-07-07', '. apel acara pembukaan pkl dan PIPA ( untuk yang profesi) \r\n. tour guide (pengenalan lingkungan secara observasi per gedung) \r\n. perkenalan\r\n. pembagian kelompok \r\n. mendengarkan pemateri dari mayor CKM karyono ', 'mengisi formulir registrasi dan pembagian baju ', 218),
(120, '2025-07-07', '•apel acara pembukaan pkl dan PIPA(untuk yang profesi) \r\n•tour guide (pengenalan lingkungan secara observasi per gedung) \r\n•perkenalan \r\n•pembagian kelompok\r\n•mendengarkan pemateri dari mayor CKM karyono', 'Mengisi formulir registrasi dan pembagian baju', 220),
(121, '2025-07-07', '1. mengantarkan obat lanzoprazole, kegunaannya:mengobati nyeri ulu hati, tukak lambung.\r\n2. mengisi obat metformin kegunaannya:mengendalikan kadar gula darah pada penderita diabetes tipe 2.\r\n3. mengambil obat        -L-Bio kegunaanya: menjaga keseimbangan bakteri baik dalam saluran pencernaan. -obat interzinc syrup kegunaanya:mengganti cairan tubuh dan mencegah dehidrasi pada anak', 'baik', 197),
(123, '2025-07-07', 'Pengenalan lingkungan Lapi AD', '1. apel pagi pembukaan pkl \r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 213),
(124, '2025-07-07', 'Perkenalan ruang lingkup LAFI PUSKESAD ', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan LAFI AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 221),
(125, '2025-07-07', 'pengenalan pemateri, melihat ruanganan ruangan produksi ', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 201),
(126, '2025-07-07', 'Pengenalan pemateri &amp; pembekelan', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 205),
(127, '2025-07-07', 'pengenlan lingkungan lembaga farmasi ad ', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 195),
(128, '2025-07-07', 'pengenalan lingkungan lagi ad', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 198),
(129, '2025-07-07', 'Pengenalan lingkungan LAFI AD', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan per-gedung \r\n5. pemateri,mayor karyono menjelaskan tentang pengenalan struktur Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 211),
(130, '2025-07-07', 'pengenalan lingkungan LAFI AD', '1. apel pagi pembukaan pkl dan PKPA (untk yang profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan LAFI AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 203),
(131, '2025-07-07', 'Pengenalan lingkungan LAFIAD', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 208),
(132, '2025-07-07', 'Pengenalan lingkungan puskesad ', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 210),
(133, '2025-07-07', 'Pengenalan lingkungan Lafi AD', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 207),
(134, '2025-07-07', 'PENGENALAN LUNGKUNGAN LAFI AD', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 215),
(135, '2025-07-07', 'pengenalan lingkungan industri dan pengenalan pemateri', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 225),
(136, '2025-07-07', 'Pengenalan lingkungan LAFI AD ', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 206),
(137, '2025-07-07', 'Pengenalan lingkungan LAFI AD', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 224),
(138, '2025-07-07', 'pengenalan lingkungan lembaga farmasi puskesad', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI.', 194),
(139, '2025-07-07', 'mengscan data artip dan memberi cap stempel pada berkas nya yang sudah di scan', 'semuanya aman terkendali', 184),
(140, '2025-07-07', '1. ruangan bidang Capil.\r\nMengintri atau memasukan data ,dari data manual ke data digital.\r\n2. membantu tim di bagian bidang arsip.\r\ncontoh menyimpan data arsip manual ke arsip digital.\r\n3. menginstal apk untuk mengedit\r\n4. mengedit file yang terpisah menggunakan apk untuk di simpan ke folder\r\n', 'ngga ada sih, cuman ya pegel kaki karna mondar mandir turun tangga', 155),
(141, '2025-07-07', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lapi AD serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 'saat mengerjakan pre test lafi', 222),
(142, '2025-07-07', 'Membantu mengantarkan infokus keruang rapat, membantu menyalakan dan mematikan infokus, dan membantu membereskan kertas-kertas', '-', 177),
(143, '2025-07-07', '1. apel pagi pembukaan pkl dan PKPA (untk. yg profesi)\r\n2. pembekalan awal\r\n3. pembagian kelompok selama masa pkl\r\n4. pengenalan lingkungan secara observasi per-gedung dan di jelaskan secara rinci\r\n5. pemateri oleh mayor karyono menjelaskan tentang pengenalan lingkungan Lafi puskesad serta pengenalan awal tentang pangkat pangkat yang ada di TNI', 'kendala dalam PreTes PKPA/PKL Lafipuskesad ', 200),
(144, '2025-07-07', 'mengscan hardfile ke softfile', '-', 183),
(145, '2025-07-07', 'Mendata surat keluar serta surat yang masuk ke buku kendali ', 'Sudah terbiasa dengan mencatat dan mengurus soal seperti ini', 191),
(147, '2025-07-07', 'Membantu proses scanning dokumen kepegawaian di Subbagian Kepegawaian dan mengetik ulang berkas sidang lanjutan di bagian Panitera, lalu menyusun pertanyaan dalam bentuk tabel.', 'Mempelajari proses digitalisasi arsip kepegawaian dan membantu merapikan dokumen persidangan dengan menyusun tanya-jawab hakim dan pihak terkait dalam format tabel agar lebih sistematis.', 154),
(148, '2025-07-07', 'pelayanan keuangan, membantu pembayaran seperti biasa, serta membantu mengriset data mahasiswi baru agar bisa login ke akun SIAKAD dan mengarahkan serta menjelaskan mahasiswi tentang penggunaan akun SIAKAD', 'kendala : seketika lupa akan pembayaran biaya awal mahasiswi baru apakah setengahnya boleh di transfer atau cash', 188),
(149, '2025-07-08', 'pelayanan keuangan, membantu pembayaran seperti biasa dan membantu mengarahkan mahasiswi yg membayar aksel secara cash/tunai untuk bayar ke bank lalu konfirmasi kesini, serta membantu mengriset data mahasiswi baru kembali dan mengarahkan dan menjelaskan \r\npenggunaan SIAKAD kembali.', 'kendala : gemeter di saat menjelaskan penggunaan SIAKAD (belum terbiasa)', 188),
(150, '2025-07-08', 'membantu tim kia dalam mencatat imunisasi anak dan ibu hamil', 'tidak ada catatan tambahan', 219),
(151, '2025-07-08', 'Belajar Installasi PHP Laravel ', 'Didampingi oleh pembimbing dari Departement IT', 148),
(152, '2025-07-08', '1. absen\r\n2. mengambil kan obat cetirizine injeksi\r\n   kegunaanya :meredakan gejala akibat reaksi alergi, seperti mata berair, \r\n3. mengantarkan resep \r\nmengisi ulang obat ketorolac  injeksi yang suda habis\r\nkegunaanya :meredakan nyeri sedang dan berat dalam jangka pendek pada orang yang berusia minimal 17 tahun.', 'tidak ada kendala, baik.', 197),
(153, '2025-07-08', 'membantu melayankan resep', 'tidak ada', 216),
(154, '2025-07-08', 'Membantu menyala dan mematikan infocus,membantu memberskan/merapihkan lampiran,membatu ngeprint dan membantu mengantarkan laporan ke ruang lain', '-', 177),
(155, '2025-07-08', 'membantu tim di bagian bidang arsip.\r\ncontoh menyimpan data arsip manual ke arsip digital.', 'ada kendala di pengisian data, dan mengganti no registrasi nya, selain itu baik baik saja', 155),
(156, '2025-07-08', 'Belajar Installasi PHP Laravel', 'Didampingi oleh pembimbing dari Departemen IT', 149),
(157, '2025-07-08', 'masih tetep di bagian bidang arsip membantu tim untuk menyimpan data arsip manual ke data arsip digital ', 'pas bagian no register nya salah jadi kita harus ngulang lagi dari awal ', 139),
(158, '2025-07-08', 'mengscan data artip dan memberi cap stempel pada berkas nya yang sudah di scan', 'melakukan pengscan an secara double atau meng scan 2 berkas sekaligus akhirnya  harus mengulang lgi', 184),
(159, '2025-07-08', 'tidak ada materi karena akan ada audit dari BPOM ', 'jogging pagi', 228),
(160, '2025-07-08', 'tidak ada materi,karena akan ada audit dari BPOM ', 'jogging pagi', 209),
(161, '2025-07-08', 'Tidak ada materi karena akan ada audit dari BPOM', 'Jongging pagi ', 223),
(162, '2025-07-08', 'kebugaran jasmani', 'jalan santai mengelilingi lafi ad puskesad ', 194),
(163, '2025-07-08', 'Kebugaran jasmani olahraga', 'Jalan santai mengelilingi area Lafi Puskesad', 205),
(164, '2025-07-08', 'kebugaran jasmani ', 'jalan santai mengelilingin lafi puskesad', 201),
(165, '2025-07-08', 'KEBUGARAN JASMANAI', 'mengelilingi lafi puskesad', 215),
(166, '2025-07-08', 'Kebugaran jasmani ', '1. Apel pembekalan untuk kegiatan jalan santai.\r\n2. Jalan santai mengelilingi LAFI PUSKESAD \r\n', 221),
(167, '2025-07-08', 'Olahraga pagi kebugaran jasmani dan Belajar Catatan Pengelolaan Bets (CPOB)', '1. apel\r\n2. pembekalan\r\n3. jalan santai\r\n\r\njalan santai mengelilingi kompleks lafi puskesad selamaa 3 putaran\r\n\r\ncatatan pengelolaan bets yang di tulis secara tabel dengan lengkap dan terinci, memberikan kita space untuk mempelajari dan menganalisis tentang pencatatan bets sekala industri ', 213),
(168, '2025-07-08', 'tidak ada materi karena akan ada audit dari BPOM', 'jogging pagi', 199),
(169, '2025-07-08', 'kebugaran jasmani', 'jalann santaii mengelilingi lagi puskesad', 225),
(170, '2025-07-08', 'kebugaran jasmani', 'jalan santai mengelilingi lafi ad', 198),
(171, '2025-07-08', 'Kebugaran jasmani ', 'Jalan santai mengelilingi LAFI AD', 206),
(172, '2025-07-08', 'Kebugaran Jasmani', 'Jalan Santai Mengelilingi LAFI AD ', 203),
(173, '2025-07-08', 'kebugaran jasmani ', 'jalan santai mengelilingi lafi puskesad', 211),
(174, '2025-07-08', 'kebugaran jasmani ', 'jalan santai mengelilingi lafi ad', 224),
(175, '2025-07-08', 'Kebugaran Jasmani Dan Rohani', 'Jalan santai mengelilingi Lafi AD', 207),
(176, '2025-07-08', 'Kebugaran jasmani ', 'Mengelilingi LafiAD', 208),
(177, '2025-07-08', 'tidak ada materi karena akan ada audit BPOM', 'jogging pagi', 220),
(178, '2025-07-08', 'KEBUGARAN JASMANI', '1. Melaksanakan apel pagi bagi seluruh peserta pkl/pkpa periode juli tahun 2025\r\n2. Jalan santai mengelilingi Lembaga Farmasi PUSKESAD ', 195),
(179, '2025-07-08', 'Kebugaran jasmani', 'Mengelilingi LAFIAD PUSKESAD\r\n', 210),
(180, '2025-07-07', 'Mengedit data arsipan ', 'Mengedit di bagian penempatan di rak ', 182),
(181, '2025-07-08', 'Mengedit data arsipan \r\nDan membantu menyiapkan barang untuk di ruangan hukum', 'Masih sama kaya kemarin ', 182),
(182, '2025-07-08', 'tidak ada materi karena akan ada audit BPOM', 'jogging pagi', 218),
(183, '2025-07-08', '1. Lari pagi\r\n2. Pengarahan\r\n3. Membantu memindahkan barang dari gudang pengemasan ke gudang transit\r\n4. membantu memindahkan 4 batch amoxicilin ke gudang bekkes', 'kendala kekurangan personil ', 200),
(184, '2025-07-08', 'meracik obat', 'tidak ada', 227),
(185, '2025-07-08', 'Memebersihkan alat alat yang akan di gunakan ', 'Ngada', 212),
(186, '2025-07-09', 'Membatu Memebersihkan alat yang akan di gunakan ', 'Ngada', 212),
(187, '2025-07-08', 'Tidak ada materi karena ada audit dari BPOM', 'Joging pagi', 214),
(188, '2025-07-01', 'Pada hari pertama, kegiatan diawali dengan perkenalan lingkungan kerja dan pembagian tugas. Saya ditempatkan pada bagian Panitera Muda Perdata di Pengadilan Negeri Subang. Setelah penempatan, saya mulai beradaptasi dengan lingkungan tempat pkl serta memperhatikan proses administrasi perkara perdata, termasuk pencatatan gugatan, permohonan, serta jadwal persidangan.\r\n\r\n', '\r\nSelama proses adaptasi, saya mulai memahami sistem kerja di bagian Panitera Muda Perdata, seperti alur masuknya perkara, pencatatan data pihak berperkara, dan pentingnya ketelitian dalam administrasi.\r\n\r\n', 150),
(189, '2025-07-09', 'memberikan resep kepada pasienn', 'tidak ada', 216),
(190, '2025-07-04', 'Kegiatan diawali dengan olahraga pagi bersama seluruh pegawai dan peserta pkl . Setelah itu, saya kembali melanjutkan tugas di bagian Panitera Muda Perdata dengan mencatat data pada register induk perkara, meliputi perkara gugatan, gugatan sederhana, dan permohonan. Selain itu, saya juga membantu tugas-tugas lain seperti mengantarkan berkas ke bagian hukum untuk ditandatangani sesuai arahan dari staf, baik ibu maupun bapak pegawai.', 'Melalui kegiatan ini, saya mulai terbiasa dengan jenis-jenis perkara perdata serta alur administrasi yang melibatkan koordinasi antar bagian di lingkungan Pengadilan Negeri Subang', 150),
(191, '2025-07-09', 'memahami konsep digunakannya laravel', 'gaada catatan tambahan', 148),
(192, '2025-07-09', '1. Memahami konsep digunakannya laravel\r\n2. Mencoba membuat tampilan website menggunakan laravel ', 'Menghadapi kendala saat run server', 149),
(193, '2025-07-09', 'masih dibagian bidang arsip Paris membantu tim untuk menyimpan data arsip manual ke data arsip digital ', 'tida ada kendala', 139),
(194, '2025-07-09', 'diliburkan karena ada pemeriksaan dari BPOM ', 'tidak boleh keluar dari mess', 228),
(195, '2025-07-09', 'membantu tim kia / layanan inu hamil dan nifas', 'tidak ada ', 219),
(196, '2025-07-09', 'Diliburkan karena ada pemeriksaan dari bpom', 'Tidak boleh keluar dari mess', 209),
(197, '2025-07-09', 'Diliburkan karena ada pemeriksaan dari BPOM', 'Tidak boleh keluar dari mess', 223),
(198, '2025-07-09', 'Di liburkan karena ada pemeriksaan dari BPOM', 'tidak boleh keluar dari mess', 199),
(199, '2025-07-09', 'di libur kan karena ada pemeriksaan dari BPOM', 'tidak boleh keluar dari mess', 218),
(200, '2025-07-09', 'di liburkan karena ada pemeriksaan dari BPOM', 'tidak boleh keluar dari mess', 220),
(201, '2025-07-09', 'Diliburkan karna ada pemeriksaan dari bpom', 'Tidak boleh keluar dari mess', 214),
(202, '2025-07-09', 'mengscan data artip dan memberi cap stempel pada berkas nya yang sudah di scan', 'aman terkendali,asikk banyak santai nya karna pengerjaan nya bisa gantiannn  ', 184),
(203, '2025-07-09', 'membantu tim di bagian bidang arsip.\r\ncontoh menyimpan data arsip manual ke arsip digital.', 'tidak ada kendala, semua berjalan dengan baik', 155),
(204, '2025-07-09', 'meracik obat', 'tidak ada', 227),
(205, '2025-07-08', 'mengecek kembali data rekening koran', 'tidak ada', 191),
(206, '2025-07-09', '1. Mengantar surat ke ruangan staff universitas subang\r\n2. mendata surat keluar ke buku kendali', 'menghadapi kendala untuk mencari staff yang dituju karena belum kenal', 191),
(207, '2025-07-09', '1. Membantu mengecek kelengkapan data arsipan \r\n2. Memfotcopy kan berkas \r\n3. Membantu mencari berkas di rak arsip', 'Banyak pekerjaan baru di hari ini', 182),
(208, '2025-07-09', 'pelayanan keuangan, membantu pembayaran mahasiswi seperti biasa dan mengarahkan, menjelaskan kegunaan SIAKAD seperti biasa.', 'tidak ada ', 188),
(223, '2025-07-05', 'membantu pembimbing membawa komputer', '-', 166),
(224, '2025-07-07', 'membuat ip dibeberapa komputer yang masih belum ada ip nya', '-', 166),
(225, '2025-07-05', 'membantu pembimbing membawa personal computer', '-', 171),
(226, '2025-07-08', 'merakit komputer dan mengistal ulang', 'mengganti psu yang lemah dan menambahkan ssd ', 166),
(227, '2025-07-08', 'membongkar dan merakit, menginstal ulang pc yang rusak', 'mengganti psu yang lemah dan menambahkan ssd kedalam pc', 171),
(228, '2025-07-10', 'Membatu membersihkan alat alat ', 'Ngada ', 212),
(229, '2025-07-10', 'membantu tim kia / pelayanan ibu hamil dan nifas', 'tidak ada', 219),
(230, '2025-07-09', '1. absen\r\n2. mengambil alkes spuit 3 sama spuit 10 \r\n        kegunaanya spuit 3: untuk memberikan obat atau cairan dengan dosis yang tepat serta mengambil sampel cairan tubuh untuk keperluan diagnostik berbentuk suntikan ukuran 3 ml.\r\n        kegunaanya spuit 10: untuk menyuntikkan cairan obat atau cairan medis lainnya ke dalam tubuh pasien dalam jumlah yang relatif besar ukuran 10 ml berbentuk suntikan\r\n        kegunaanya underpat: digunakan untuk orang yang mengalami masalah dalam mengontrol berkemih sehingga tidak mengotori matras/kasur.\r\n   3. meracik obat Amlodipine.       \r\nmenjadi serbuk\r\nkegunaanya Amlodipine: untuk mengobati tekanan darah tinggi (hipertensi). jantung kekurangan oksigen', 'baik', 197),
(231, '2025-07-10', 'Mengambil obat dan alkes. ', 'Baik', 204),
(232, '2025-07-06', 'Restok, menyiapkan obat dan alkes. ', 'Alhamdulillah baik', 204),
(233, '2025-07-07', '1.Menyiapkan obat dan alkes. \r\n2.mengantarkan obat di ruang IGD dan puri kencana 2.', 'Alhamdulillah baik', 204),
(234, '2025-07-09', 'membantu keberlangsungan acara FGD', '-', 171),
(235, '2025-07-10', 'memelihara lab komputer', '-', 171),
(236, '2025-07-10', 'membuat CRUD dengan laravel dan menggunakan database', 'masi belum bisa memahami ketika membuat crud dgn database', 148),
(237, '2025-07-10', 'Membuat CRUD dengan laravel menggunakan database', 'Belum paham ketika ditanya tentang bagaimana alur web itu berjalan', 149),
(238, '2025-07-10', 'membantu memberikan resep obat kepada pasien', 'tidak adaa', 216),
(239, '2025-07-10', 'melayani pasien', 'tidak ada', 227),
(240, '2025-07-10', 'diliburkan karena ada pemeriksaan dari BPOM ', 'tidak boleh keluar dari mess', 228),
(241, '2025-07-10', 'Diliburkan karena ada pemeriksaan dari BPOM', 'Tidak boleh keluar dari mess', 223),
(242, '2025-07-10', 'Diliburkan karena ada pemeriksaan dari BPOM', 'Tidak boleh keluar dari mess', 209),
(243, '2025-07-10', 'diliburkan karena ada pemeriksaan dari BPOM', 'tidak boleh keluar dari mess', 218),
(244, '2025-07-10', 'Di liburkan karena ada pemeriksaan dari BPOM', 'Tidak boleh keluar dari mess', 199),
(245, '2025-07-10', 'diliburkan karena ada pemeriksaan dari BPOM', 'tidak boleh keluar dari mess', 220),
(246, '2025-07-10', 'Diliburkan karena ada pemeriksaan dari BPOM', 'Tidak boleh keluar dari mess', 214),
(247, '2025-07-10', 'Masi dibagian bidang arsip Paris membantu tim untuk menyimpan data arsip manual ke data arsip digital ', 'gda kendala apa apaa', 139),
(248, '2025-07-10', 'membantu tim di bagian bidang arsip paris.\r\ncontoh menyimpan data arsip manual ke arsip digital.', 'Alhamdulillah semuanya berjalan lancar.', 155),
(249, '2025-07-10', '1. Menscan berkas perdata\r\n2.Mengecek dan memastikan berkas lengkap atau tidak\r\n3. Meminta tanda tangan ke panitera muda hukum, pidana, perdata.', 'Banyak ilmu baru di hari ini', 182),
(251, '2025-07-10', 'mengscan data artip dan memberi cap stempel pada berkas nya yang sudah di scan', 'salah saat memasukan nama data ', 184),
(252, '2025-07-11', 'Membatu membersihkan alat yang akan di gunakan', 'Ngada ', 212);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurnal_kegiatan`
--

CREATE TABLE `jurnal_kegiatan` (
  `id_jurnal_kegiatan` int NOT NULL,
  `nama_pekerjaan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `perencanaan_kegiatan` text COLLATE utf8mb4_general_ci,
  `pelaksanaan_kegiatan` text COLLATE utf8mb4_general_ci,
  `catatan_instruktur` text COLLATE utf8mb4_general_ci,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `siswa_id` int DEFAULT NULL,
  `tanggal_laporan` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jurnal_kegiatan`
--

INSERT INTO `jurnal_kegiatan` (`id_jurnal_kegiatan`, `nama_pekerjaan`, `perencanaan_kegiatan`, `pelaksanaan_kegiatan`, `catatan_instruktur`, `gambar`, `siswa_id`, `tanggal_laporan`) VALUES
(17, 'Mendata surat keluar', '1. Menerima surat yang akan keluar untuk diberikan kepada pihak yang dituju.\r\n2. Mencari Buku Kendali Surat Keluar', '1. Mendata surat keluar mengenai nomor surat, tanggal masuk dan tanggal keluar surat tersebut, perihal isi surat, pihak yang mengirim, serta pihak yang dituju.\r\n2. berhasil mendata surat keluar tanpa ada kesalahan.', '', 'proyek_68654a231fb891.27685592.jpg', 191, '2025-07-02 15:02:59'),
(18, 'pelayanan ibu hamil', 'menyalakan komputer sebelum digunakan untuk membuat data triwulan', '1. membuat data triwulan ibu hamil\r\n2. membuat data triwulan persalinan\r\n3. membuat data triwulan anak bayi baru lahir', 'pembimbing : ibu titien', 'proyek_686626126436a3.12655916.jpg', 219, '2025-07-03 06:41:22'),
(19, 'ruang obat', 'membereskan obat obat', 'berhasil melakukan pelayanan resep', 'bu suherni', 'proyek_68662ab89c6116.07692700.jpg', 216, '2025-07-03 07:01:12'),
(20, 'Memangil pasien untuk di periksa dan mencabut gigi', 'Membersihkan alat alat dan Memangil pasien untuk di periksa', 'Diem sambil menunggu pasien selanjutnya', 'Pembimbing riya Cahya samudra', 'proyek_68662baf1935d8.40571786.jpeg', 212, '2025-07-03 07:05:19'),
(21, 'mempersiapkan Rapat rektor bersama para dekan', '1. Menyiapkan absensi kehadiran para dekan\r\n2. membantu membawa konsumsi ke depan ruangan rapat\r\n3. menyusun konsumsi agar mudah diambil dan diberikan', '1. Membantu para dekan untuk pengisian absensi\r\n2. para dekan mendapat konsumsi tanpa ada yang terlewat\r\n3. Rapat berjalan dengan lancar tanpa ada hambatan', '', 'proyek_68666c95c046a2.00299078.jpg', 191, '2025-07-03 11:42:13'),
(22, 'Menyusun surat serta brosur universitas ke dalam map', '1. menyiapkan surat pernyataan, map, brosur universitas, dan amplop coklat\r\n2. menyiapkan penjepit kertas', '1. memasukkan brosur, surat pernyataan, dan amplop ke dalam map\r\n2. lalu jepit kertas agar terjepit dengan map-nya\r\n3. surat dan brosur tersusun dengan rapih dalam map tanpa ada kesalahan', '', 'proyek_68666dd1cf9a40.32824073.jpg', 191, '2025-07-03 11:47:29'),
(24, 'melayani pasien', 'menata obat obat ke tempat yang benar', 'membaca resep dan meracik obat', '', 'proyek_68667c60c43ef9.52100784.jpg', 227, '2025-07-03 12:49:36'),
(25, 'Memangil pasien dan mencatat nama pasien yang akan di periksa', 'Membersihkan alat alat dan mempersiapkan kan alat untuk di gunakan', 'Memangil pasien dan mencatat pasien yang akan di periksa', 'Pembimbing riya Cahya samudra', 'proyek_68675d1bd3b0d4.11333642.jpeg', 212, '2025-07-04 04:48:27'),
(26, 'pelayanan ibu hamil dan nifas', '1. membantu membersihkan puskemas bersama ibu bidan yang sudah ada\r\n2. setelah selesai saya mengikuti apel pagi', '1. melanjutkan membuat data triwulan dan membantu ibu bidan jikalau beliau sedang kesusahan', 'pembimbing : ibu titien', 'proyek_68676adc45bd55.62142526.jpg', 219, '2025-07-04 05:47:08'),
(27, 'meracik obat', 'melayani pasien', 'meracik obat dan membaca resep', '', 'proyek_edit_68676e952c6729.42001605.jpg', 227, '2025-07-04 05:59:56'),
(28, 'ruang obatt', 'menyiapkan obat dan membersihkan meja terlebih dahulu', 'berhasil dalam melakukan pelayanan resep', 'bu suherni', 'proyek_68676e8c5c5ee2.42454968.jpg', 216, '2025-07-04 06:02:52'),
(29, 'Menyusun Nota Pembelian', '1. Menyiapkan Nota pembelian\r\n2. Menyiapkan map', '1. Menempelkan nota ke kertas A4, lalu jepit dan masukkan ke dalam map\r\n2. nota tersusun dengan rapih dan sesuai tanggal pembeliannya', '', 'proyek_6867d905960b67.52662461.jpg', 191, '2025-07-04 13:37:09'),
(30, 'pelayanan ibu hamil dan nifas', '1. membantu membersihkan ruangan \r\n2. setelah itu melaksanakan apel setiap pagi', '1. memahami cara menggunakan tensi darah\r\n2. membantu ibu bidan jika beliau kesulitan\r\n3. melanjutkan membuat triwulan', 'pembimbing : ibu titien', 'proyek_686895b3a88325.74433708.jpg', 219, '2025-07-05 03:02:11'),
(31, 'meracik obat', 'meracik obat', 'melayani pasien dan meracik obat', '', 'proyek_68689daf53a6a5.09356361.jpg', 227, '2025-07-05 03:36:15'),
(32, 'ruang obat', 'menyiapkan etiket dan spidol, membersihkan meja dan membereskan obat', 'berhasil memberikan obat kepada pasien', 'bu suherni', 'proyek_68689e443164e2.91254335.jpg', 216, '2025-07-05 03:38:44'),
(33, 'Memangil pasien untuk di periksa', 'Membersih kan alat dan menunggu pasien yang akan di periksa', 'Diem sambil menunggu pasien dan mencatatat sudah di periksa', 'Pembimbing riya Cahya samudra', 'proyek_6868ad3084a970.97632080.jpeg', 212, '2025-07-05 04:42:24'),
(34, 'Mengambil obat dan alat kesehatan untuk pasien', 'Meracik obat tablet', 'Mengambil obat dan alat kesehatan \r\nSeperti Paracetamol infus,ambroxol,spuit,omeprazole INJ dll', 'Proges cukup baik', 'proyek_6868dce0e41a91.36370973.jpg', 202, '2025-07-05 08:05:52'),
(35, 'Mengambil obat dan alat kesehatan.', '1.menyiapkan alat yang dibutuhkan. \r\n2.menyiapkan obat, sesuai dengan resep yang dibutuhkan. \r\n3.mengartarkan obat keruangan', '1.melakukan kalibrasi alat dan bahan yang sesuai', 'Mengerjakan sesuai arahan dan menyiapkan obat dengan baik', 'proyek_686926cc532ef6.33037123.jpg', 204, '2025-07-05 13:21:16'),
(36, 'Mengambil obat dan alat kesehatan untuk pasien', '1. Menyetok obat di gudang', 'Mengambil obat dan alkes,seperti Paracetamol infus,ambroxol,spuit,omeprazole inj,dll', 'Progres cukup baik', 'proyek_6869cd06dcccd1.09393553.jpg', 217, '2025-07-06 01:10:30'),
(37, 'mengantarkan resep', '1. mengantarkan resep', '1. mengambil obat dan alat kesehatan seperti spuit 3, ranitidin injk', 'baik', 'proyek_686a1f2670fbf0.28984140.jpg', 197, '2025-07-06 07:00:54'),
(38, 'Mengambil obat dan alat kesehatan.', 'Menyiapkan peralatan dan bahan yang dibutuhkan, mengantarkan obat.', 'Menyiapkan alat dan bahan yang sesuai.', 'Progres sangat baik', 'proyek_686a5e15865ca0.05792875.jpg', 204, '2025-07-06 11:29:25'),
(39, 'Mengambil obat dan alat kesehatan', 'Mengantarkan obat', 'Mengambil obat dan alat kesehatan, restock. seperti amlodipine ,calcifar, lisinopril,infusan Rl ,spuit omeprazole INJ dll', 'Progres sangat baik', 'proyek_686a5ec3a49a12.64504156.jpg', 202, '2025-07-06 11:32:19'),
(40, 'menunggu resep di meja depan', '1.menyiapkan peralatan dan bahan yang dibutuhkan \r\n2.membaca instruktur kerja atau prosedur standar \r\n3.berkoordinasi dengan supervisor atau rekan kerja', 'melakukan kalibrasi alat dan bahan yang sesuai', 'baik', 'proyek_686a7b9c9f0564.69351462.jpg', 196, '2025-07-06 13:35:24'),
(41, 'pelayanan ibu hamil dan nifas', 'membersihkan ruangan dan menyiapkan atau menyalakan komputer yg sudah tersedia diruangan', 'membantu membuat catatan ibu hamil\r\ndan membantu ibu bidan bila beliau sedang kesusahan', 'pembimbing : ibu titien', 'proyek_686b3f51c9eb37.28560805.jpg', 219, '2025-07-07 03:30:25'),
(42, 'Membersihkan alat alat', 'Membersihkan alat dan menyiapkan alat', 'Memangil pasien dan mencatat yang udah di periksa', 'Pembingbing ria cahya samudra', 'proyek_686b4ea9423900.29544380.jpeg', 212, '2025-07-07 04:35:53'),
(43, 'meracik obat', 'melayani pasien dan meracik obat', 'melayani pasien', '', 'proyek_686b7f4332cf12.33798515.jpg', 227, '2025-07-07 08:03:15'),
(44, 'meracik', 'mengepus pelayanan resep dikomputer', 'berhasil memberikan resep obat kepada pasien', 'bu suherni', 'proyek_686b7fe0a29149.64969323.jpg', 216, '2025-07-07 08:05:52'),
(45, 'Meng ngprint resep', 'Mengecek ulang resep tersebut,lalu di print', 'Mengambil obat dan alkes sesuai yang di inginkan oleh pasien.', 'Progres sangat baik.', 'proyek_686b85b74e5b20.02364193.jpg', 217, '2025-07-07 08:30:47'),
(46, 'Mengambil obat dan alat kesehatan untuk pasien', 'Meracik dan mengantarkan obat, alat kesehatan ke pasien', 'Restock obat dan mengambil obat seperti Interzinc syr,\r\nRanitidin inj,\r\nEtabion,\r\nAmlodipine 10mg,\r\nClopidogrel 75mg,\r\nFurosemid 40mg,\r\nCalcifar tab,\r\nPregabalin 75 mg,\r\nCurcuma Forte.', 'Progres cukup baik', 'proyek_686bc4fbbebbb6.96370813.jpg', 202, '2025-07-07 13:00:43'),
(47, 'mengambil obat', '1. membuat kapsul', '1. mengambil obat ranitidin, piracetam,interzinc syrup L-Bio, curcuma forte tab,ranitidin, ceftriaxone, metronidazole infus.\r\n2. mengantarkan obat lanzoprazole\r\n3. mengisi obat metformin', 'Cukup baik, belajar lagi ya', 'proyek_686bcc13b2e3f9.76088929.jpg', 197, '2025-07-07 13:30:59'),
(48, 'merestock obat', 'meracik kapsul, menyerahkan nomor antrian', 'melakukan kalibrasi alat dan bahan yang sesuai', 'progres ini cukup baik', 'proyek_686bcca9a9f686.01540096.jpg', 196, '2025-07-07 13:33:29'),
(49, 'mengerjakan per test lafi', '1.bersiap untuk pergi ke lafi\r\n2.apel pembukaan\r\n3.pembekalan awal', 'pengenalan lingkungan.cv lafi.qbagi kelompok', '', 'proyek_686bd9ec1c9bb9.38905518.jpg', 222, '2025-07-07 14:30:04'),
(50, 'Pembekalan dan Pengenalan area Lafi puskesad', '1. Bersiap Pergi ke kantor pusat lafi\r\n2. Apel pagi\r\n3. Pembekalan dan Pengenalan area Lafi', '1. Apel pagi di lanjut pembekalan\r\n2. observasi area yang ada di lafi', '', 'proyek_686be061907c24.30534947.jpg', 200, '2025-07-07 14:57:37'),
(51, 'Mengambil obat dan alat kesehatan.', '1.menyiapkan peralatan kesehatan yang dibutuhkan. \r\n2.mengambil obat dari resep yang sudah disediakan. \r\n3.mengantarkan obat di ruang IGD dan puri kencana 2.', 'Melakukan kegiatan yang sesuai dengan arahan pembimbing.', 'Menyiapkan obat dari IGD dan merapikan resep', 'proyek_686c59fee0a840.24038193.jpg', 204, '2025-07-07 23:36:30'),
(52, 'Memangil pasien untuk di periksa', 'Menyiapkan alat yang akan di gunakan', 'Mecatatat pasien yang sudah di periksa', 'Pembingbing ria Cahya samudra', 'proyek_686c9fd9d48866.88191227.jpeg', 212, '2025-07-08 04:34:33'),
(53, 'pelayanan ibu hamil dan nifas', '1. membersihkan ruangan dan menyiapkan alat alat yang dibutuhkan', '1. mencatat nama nama bayi yang di imunisasi dan ibu hamil\r\n2. mengentri nama nama bayi yang di imunisasi dan ibu hamil', 'pembimbing : ibu titien', 'proyek_686ccd6d3e02f8.83144302.jpg', 219, '2025-07-08 07:49:01'),
(54, 'Belajar PHP Laravel Dasar', 'Kegiatan : \r\n1. Tahap Installasi PHP Laravel \r\n2. Belajar Struktur Dasar PHP Laravel\r\n3. Belajar Menggunkana Laragon\r\n4. Belajar Menggunakan Heidi SQL (database)', '1. Mampu memahami konsep dasar dari PHP Laravel\r\n2. Bisa menjalankan Aplikasi Berbasis Laravel', 'Pendampingan proses belajar PHP Laravel dasar', 'proyek_686ccd9a6b8c03.67671985.png', 148, '2025-07-08 07:49:46'),
(55, 'ruang obat', 'membersihkan meja dan peralatan utk meracik obat', 'berhasil memberikan resep obat kepada pasien', 'bu suherni', 'proyek_686cee21299eb0.34292602.jpg', 216, '2025-07-08 10:08:33'),
(56, 'Belajar PHP Laravel Dasar', 'Kegiatan : \r\n1. Tahap installasi PHP Laravel\r\n2. Belajar Struktur Dasar PHP Laravel\r\n3. Belajar Menggunakan Laragon\r\n4. Belajar Menggunakan Heidi SQL ( database )', '1. Mampu memahami konsep dasar dari PHP Laravel\r\n2. Bisa menjalankan Aplikasi Berbasis Laravel', 'Pendampingan proses belajar PHP Laravel dasar', 'proyek_686d069385cdd4.72620714.png', 149, '2025-07-08 11:52:51'),
(57, 'Kebugaran jasmani', '1. Apel pembekalan untuk kegiatan kebugaran jasmani \r\n2. Jalan santai mengelilingi LAFI PUSKESAD', '1. Melakukan kegiatan jalan santai', '', 'proyek_686d1a2f03f883.03308069.jpg', 221, '2025-07-08 13:16:31'),
(58, 'mengambil alkes', '1. membuat obat kapsul', '1. absen\r\n2. mengambil obat cetirizine\r\n3. mengantarkan resep\r\n4. mengisi obat ketorolac injeksi', 'cukup baik untuk pengambilan obat&alkes', 'proyek_686d1aedb66964.80327710.jpg', 197, '2025-07-08 13:19:41'),
(59, 'menyiapkan alkes dan mengambil obat pct Amoxilin etabion dll', 'meracik kapsul merestock obat', 'melakukan kalibrasi alat dan bahan yang sesuai', 'cukup baik untuk pengambilan obat & alkes', 'proyek_686d1c02482129.96927368.jpg', 196, '2025-07-08 13:24:18'),
(60, 'Membantu memindahkan 59 batch Paracetamol ke gudang transit', '1. Lari pagi/jalan santai\r\n2. Membantu memindahkan 59 batch Paracetamol\r\n3. Membantu memindahkan 4 batch amoxicilin', '1. Lari pagi/jalan santai 3 putaran\r\n2. Membantu memindahkan 59 batch Paracetamol ke gudang transit 85%\r\n3. Membantu memindahkan 4 batch amoxicilin ke gudang bekkes 15%', '', 'proyek_686d5a698973d7.59439502.jpg', 200, '2025-07-08 17:50:33'),
(61, 'meracik obat', 'melayani pasien', 'meracik obat dan melayani pasien', '', 'proyek_686da9778ba856.80099841.jpg', 227, '2025-07-08 23:27:51'),
(62, 'Memangil pasien untuk di cabut giginya', 'Menyiapkan alat yang akan di gunakan dan Memangil pasien untuk di periksa', 'Menunggu pasien sambil membantu mencatat', 'Pembingbing ria Cahya samudra', 'proyek_686df9948f15d4.78546201.jpeg', 212, '2025-07-09 05:09:40'),
(63, 'ruang obat', 'menyalakan komputer, dan meng epus data pasien', 'berhasil memberikan resep obat kepada pasien', 'bu suherni', 'proyek_686e1720998780.23303330.jpg', 216, '2025-07-09 07:15:44'),
(64, 'pelayanan ibu hamil dan nifas', 'menyiapkan alat alat yang dibutuhkan dan menyiapkan buku catatan registrasi ibu hamil', 'mempelajari gimana cara berkerja dibidang kia dengan seksama,dan mempelajari cara kerja ny alat tensi darah', 'pembimbing : ibu titien', 'proyek_686e53e12c4219.58715070.jpg', 219, '2025-07-09 11:34:57'),
(65, 'meracik kapsul, menyiapkan alkes, dan mengambil obat', 'menerima resep di depan, merestock obat', 'melakukan kalibrasi alat dan bahan yang sesuai', 'cukup baik', 'proyek_686e662b4cb160.90356968.jpg', 196, '2025-07-09 12:52:59'),
(66, 'melayani pasien', 'meracik obat', 'melayani pasien dan meracik obat', '', 'proyek_686e77b20eb251.38795939.jpg', 227, '2025-07-09 14:07:46'),
(67, 'mengambil obat', '1. meracik obat kapsul', '1. absen\r\n2. mengambil alkes spuit 3 sama spuit 10 underpat\r\n3. meracik obat Amlodipine menjadi serbuk', 'baik', 'proyek_686e83a2d04cf9.20025617.jpg', 197, '2025-07-09 14:58:42'),
(68, 'Mengecek kembali data rekening koran', '1. menyiapkan data data rekening koran\r\n2. menyiapkan stabilo berwarna', 'mencari nominal credit sesuai perintah dari pembimbing lalu diwarnai dengan stabilo', '', 'proyek_686e932a0cd048.35661454.jpg', 191, '2025-07-09 16:04:58'),
(69, 'uninstal xampp dan menginstal kembali xampp', 'memahami konsep digunakaannya laravel', 'menginstal xampp\r\nmengintal composer', 'belum bisa memahami konsep dasarnya dan masi ragu untuk melakukan sesuatu', 'proyek_686f0da7485a26.10441272.png', 148, '2025-07-10 00:47:35'),
(70, 'uninstall xampp lama dan menginstal xampp versi terbaru', '1. Mengikuti instruksi dari pembimbing', '1. Berhasil menginstal xampp\r\n2. Berhasil menginstal composer', 'belum memahami konsep dasarnya dan terlalu ragu untuk mencoba sesuatu', 'proyek_686f0e2ebc1f30.92765956.jpg', 149, '2025-07-10 00:49:50'),
(78, 'pelayanan ibu hamil dan nifas', 'mengikuti apel setiap pagi dan membersihkan ruangan', 'mempelajari gimana cara nya memeriksa ibu hamil dengan baik dan mencatat nama nama ibu hamil di buku registrasi serta meng e-Pus nama ibu hamil dikomputer', 'pembimbing : ibu titien', 'proyek_686f697c3d6946.61982139.jpg', 219, '2025-07-10 07:19:24'),
(79, 'Menyiapkan obat alkes.', 'Menyiapkan alat kesehatan dan obat yang dibutuhkan.', 'Sesuai arahan Pembimbing', 'Sesuai dengan baik.', 'proyek_686f7a3b2b9b07.21696496.jpg', 204, '2025-07-10 08:30:51'),
(80, 'Membuat CRUD dgn laravel', '1. Menginstal composer\r\n2. Membuat tampilan web sederhana\r\n3. Membuaat CRUD dgn laravel', '1. Menginstal composer\r\n2. Membuat tampilan web sederhana\r\n3. Membuaat CRUD dgn laravel', 'proses belajar CRUD laravel perlu pendalaman lagi.', 'proyek_686f89ca3c1d93.30106739.png', 148, '2025-07-10 09:37:14'),
(81, 'ruang obat', 'menata obat yg dr gudang, utk mengisi etalase di apotek nya', 'berhasil memberikan resep kepada pasien', 'bu suherni', 'proyek_686f99493a0f70.51635803.jpg', 216, '2025-07-10 10:43:21'),
(82, 'melayani pasien', 'meracik obat', 'melayani pasien dan meracik obat', '', 'proyek_686f9c087f7025.20847343.jpg', 227, '2025-07-10 10:55:04'),
(83, 'Memangil pasien unutuk di periksa', 'Menyiapkan alat untuk di gunakan', 'Menunggu pasien sambil membersihkan atau mencuci alat', 'Pembimbing ria Cahya samudra', 'proyek_686f9d46b1cd77.21416394.jpeg', 212, '2025-07-10 11:00:22'),
(84, 'Membuat CRUD di laravel', '1. Menginstal composer \r\n2. Membuat tampilan halaman web sederhana \r\n3. Membuat CRUD di laravel', 'Berhasil membuat CRUD di laravel dan mengkoneksikannya di database', 'Perlu dikembangkan lagi', 'proyek_686fcf844cf9d1.41964776.jpg', 149, '2025-07-10 14:34:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurusan`
--

CREATE TABLE `jurusan` (
  `id_jurusan` int NOT NULL,
  `nama_jurusan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `password` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_siswa` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `no_induk` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nisn` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') COLLATE utf8mb4_general_ci NOT NULL,
  `kelas` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif','Selesai') COLLATE utf8mb4_general_ci NOT NULL,
  `jurusan_id` int DEFAULT NULL,
  `pembimbing_id` int DEFAULT NULL,
  `tempat_pkl_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `password`, `nama_siswa`, `no_induk`, `nisn`, `jenis_kelamin`, `kelas`, `status`, `jurusan_id`, `pembimbing_id`, `tempat_pkl_id`) VALUES
(124, '$2y$10$0cHxj/jTXsSvjG6CTcM7p.1lZC1dCUjlP9KcHuaOCCXip8Etb.Ese', 'AJI DEWANGGA', '2324-10-024', '0075545180', 'Laki-laki', 'XII RPL 1', 'Aktif', 1, 23, 303),
(125, '$2y$10$4FdksMEsAHldoZpZJX2/Z.5FI6dAXnFoLsYJ77PBkK5ixjK151Zfq', 'AMANAH FITRIYANI', '2324-10-045', '0071526017', 'Perempuan', 'XII RPL 1', 'Aktif', 1, NULL, 377),
(126, '$2y$10$RhFXfnHH2gVVbUikPX1dp.oPyAdz3BrJJhuOlrV1KOFlw/uxYChCe', 'ANGGIA RISA AULIA', '2324.10.061', '0088709687', 'Perempuan', 'XII RPL 1', 'Aktif', 1, NULL, 294),
(127, '$2y$10$Q9nzC.J3osmSp4wpLmTHbux.vRSzlT.HFomHa4u9chB2Nh8QtxWwO', 'ANJAS APRILIYANTO', '2324-10-066', '0083460119', 'Laki-laki', 'XII RPL 1', 'Aktif', 1, 24, 304),
(128, '$2y$10$t0k4KEmh8rMzsxHh0go9MuRgXshV1wsnzHKFBLW.w6EIh1Xl2yM2.', 'CAHYA MAULANA JAN', '2324-10-089', '0087843835', 'Laki-laki', 'XII RPL 1', 'Aktif', 1, 22, 298),
(129, '$2y$10$jJfBceP7WCxONYjVHt6dgOcevgClVHOjjd3oH5jX4YYz8wdD6UH.G', 'DEA LIANA', '2324-10-106', '0076561544', 'Perempuan', 'XII RPL 1', 'Aktif', 1, NULL, 299),
(130, '$2y$10$JBJTqBUlUVTzH3UtxM0QpOAce3ZrGrlct3d647EFNLYhyzevOgEri', 'DENIS MAULANA', '2324-10-123', '0082048462', 'Laki-laki', 'XII RPL 1', 'Aktif', 1, 23, 365),
(131, '$2y$10$WyOEWam3lm9eEO6pgpop3uMC/Ie4u9NE8Lh0/UpKddgtg4Tt7Tk.q', 'DHONY FUJIANTO', '2324-10-134', '0078169129', 'Laki-laki', 'XII RPL 1', 'Aktif', 1, 22, 298),
(132, '$2y$10$CI0Moetn1ZZ94j6XFfYwVucfE/6UTmQBGz1uRNwxB/ep/wLMZaKvi', 'DIANA PUSPITA SARI', '2324-10-137', '0087727454', 'Perempuan', 'XII RPL 1', 'Aktif', 1, NULL, 294),
(133, '$2y$10$J.4XGkmoNmCn0RkWb/4f1umPShqTdBzEk9QJq7FhAohChXuWM90aW', 'DINA EGISTIN', '2324-10-144', '0076095295', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 25, 301),
(134, '$2y$10$0oKQVQ.B3L37Ai0zRhB5PeT1tIUENWDLEHK6q9oxNJofyCSBTD.0W', 'DINA JULIANTI', '2324-10-148', '0086159629', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 24, 300),
(135, '$2y$10$45XkqbwBxflr1V9YqhV4POpMyPcetrCb16xh9Wi10LTWAPjUjcVtW', 'DWIKY RIADY JULIANSYAH', '2324-10-156', '3089253203', 'Laki-laki', 'XII RPL 1', 'Aktif', 1, 23, 303),
(136, '$2y$10$Fq6TQfnLBNkPjD/8QvDNp.LFakoSXmomyaXxH1YooTRaO09q9U0/e', 'ELANTINA', '2324-10-164', '0084455569', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 23, 365),
(137, '$2y$10$NqqCkfXc9M7B0lqLh.7qWOKheQ39j.xsfS82lcGG1LPXiGLOB2hh2', 'FARHAN GHIFAARI NAFIS', '2324-10-180', '0075158451', 'Laki-laki', 'XII RPL 1', 'Aktif', 1, 23, 365),
(138, '$2y$10$sK9J7Iq83BmM5sd44mkHF.QNFZER2Uo58IGOPca/Q8sQay4ChY9cS', 'ICHA LIVINA MILANASYA', '2324-10-218', '0084016085', 'Perempuan', 'XII RPL 1', 'Aktif', 1, NULL, 299),
(139, '$2y$10$3gLynVZAzRvLvdKhrJaTVuCtaxxPlaWs4rtmstMu6QnKZ/ge3xGee', 'KEZIA LAURA', '2324-10-249', '0082793229', 'Perempuan', 'XII RPL 1', 'Aktif', 1, NULL, NULL),
(140, '$2y$10$lXJ4YMu68eje.t7r06DeZuD6LFP0ftgXKORpghyfB32gDKHM8EDMu', 'LARASATI', '2324-10-257', '0085741983', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 25, 301),
(141, '$2y$10$6zUKNBePcGc23Mzwc8nO.OhqiHH7zCAZQT5CeMDLD8qBWuXlSTpg2', 'MEGA SEPRIYANI', '2324.10.275', '0072602669', 'Perempuan', 'XII RPL 1', 'Aktif', 1, NULL, 299),
(142, '$2y$10$zDeMnsPI5Zpw1KqJrfcm0.pF0FUqB/o085MWlC03IM.4f7ITr.7OC', 'MUHAMAD FADEL ALEXANDER', '2324-10-301', '0075020719', 'Laki-laki', 'XII RPL 1', 'Aktif', 1, 24, 304),
(143, '$2y$10$UvJILyd7zoWOwXqbunOs9.4be7Ca7tWpyxpM6P8dado41U3bRXjf2', 'MULYANA AMIR HAMZAH', '2324-10-315', '0078618338', 'Laki-laki', 'XII RPL 1', 'Aktif', 1, NULL, 377),
(144, '$2y$10$jBn5Y2O98NyqgPjRfheeT.5vl3NqUL797hanCygoe8sEQsRlagyvK', 'NAILA RAMDINI', '2324-10-318', '0073671414', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 23, 365),
(145, '$2y$10$x5W31FWe3aIjFS2rcx9t8.PckSg4OJn./8mn539x7GvHdjl12tm1S', 'REZHA ADITYA PRATAMA', '2324-10-399', '0084546604', 'Laki-laki', 'XII RPL 1', 'Aktif', 1, 23, 365),
(146, '$2y$10$SwDI/b0wTfQTsyzBXKBXK.5HQZhDRHrvci1Vw3QW9RHzJ7vpGOeee', 'RIRIN DWI SETIAWATI', '2324-10-406', '0088442349', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 24, 305),
(147, '$2y$10$tx95AzFpwrx7NYROiOkSye.b/68yI7vVx26oU8cmgMKOcLXZWj2yi', 'RITA AMELIA', '2324-10-412', '0077788600', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 23, 365),
(148, '$2y$10$4g4YUERexs1p3liz4rdU2uDskSjFSus8J8pRnzqMkFCjMGmW7kfES', 'SALMA FITRIA RAHMADANI', '2324-10-431', '0086097517', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 4, 309),
(149, '$2y$10$tzOkJ2Eq0jjwf/Ff5MuP5.P9.HqLzqOklKRCF.WUoWFVCXhgr5/i.', 'SALSA YULIANA DEWI', '2324-10-434', '0083100128', 'Perempuan', 'XII RPL 1', 'Aktif', 1, NULL, 309),
(150, '$2y$10$PGObYtcPIwWFYdhbpVzaSONeNsnZtMwlo3rRER.62H8GHzeDiYxI.', 'SHENDY CAYLA ANDINI', '2324-10-446', '0081286104', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 24, 300),
(151, '$2y$10$TfMzWQPDH/GmH4WYUQmsG.KL5G6nclrLsm3szyAnstubktIGovBGK', 'SITI NURAJIZAH', '2324-10-458', '0088486758', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 23, 365),
(152, '$2y$10$4edODDgWQo7hlTaYIDM0/.HKaJaIe3b1IseFLUIEHwydqwcwTHr3C', 'SRI RAHAYU', '2324-10-465', '0075736641', 'Perempuan', 'XII RPL 1', 'Aktif', 1, NULL, 299),
(153, '$2y$10$ye2wHzf8p4sY6o0NNrQPuOKkyEINhnXYqzlKYd/tciBf6gJivwkUO', 'SUSAN PUTRI NURBATIN', '2324-10-470', '0087783273', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 23, 365),
(154, '$2y$10$TtqIi6jltFnOuX.Jep.Lp.ez66OZN.A9lXlhPa6VGG5OMIhSYABMG', 'TETI AMELIA', '2324-10-478', '0083024984', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 24, 300),
(155, '$2y$10$T40ZGACYYV6q6uemYx1MieQ.cGbrLLPwWAtfuRH2VLFlFrqjLm9Ea', 'TRIYANI', '2324-10-492', '0086929952', 'Perempuan', 'XII RPL 1', 'Aktif', 1, NULL, NULL),
(156, '$2y$10$IJabiuYV.l3pyHYmQgWaTuIjYVZjvL88ITkq71m3sezUXxBsFPTaO', 'WAFIQ USWATUN NIDA', '2324-10-507', '0088580483', 'Perempuan', 'XII RPL 1', 'Aktif', 1, 23, 365),
(157, '$2y$10$6Z1y0sOY.0rSOyS3jQHZseAnRPN9rVRTYcPIPgoA0S426P5HdFiS6', 'AISYAH PEBRIYANI', '2324-10-023', '0085067884', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 24, 296),
(158, '$2y$10$WxLiRpdQKaanxHP0d8n..OONBV0sA2GJezOH6ZD.o25OCCcwODANO', 'ALDIYANSYAH', '2324-10-031', '0083674313', 'Laki-laki', 'XII RPL 2', 'Aktif', 1, NULL, 294),
(159, '$2y$10$hGZrYcSh5y9G7A918hLEX.vEhaQzzkWwHGPsDV3NH7R7KVDAROniG', 'ANDINI NOFIYANI', '2324-10-058', '0088265038', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 25, 301),
(160, '$2y$10$fIdBQjD1z3m1nWQRXaaahuzXXsUafTiOBpyAnFH9zPgM//X/71hYO', 'ANIS RAHAYU', '2324-10-063', '0089311202', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 22, 298),
(161, '$2y$10$5zGpMp7tFp9PJLi3y3RP4OSjchsbvg0nCdg1WVeA3I1F.ITeFYKzW', 'AYU WANDIRAH', '2324-10-082', '0089527836', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 24, 296),
(162, '$2y$10$XmpmN2hJTDcnBQ/Pkw7kmOI1vpsFjTLTdcr7mJUI4GL8ca/4E0W7i', 'CICA', '2324-10-096', '0082752317', 'Perempuan', 'XII RPL 2', 'Aktif', 1, NULL, 294),
(163, '$2y$10$82Dg8vA776zQbjYUn/Nyt.p1bxHdIHKmVqONgbQSMFxgaFwIU6xNa', 'DANI RIZWAN', '2324-10-102', '0089256011', 'Laki-laki', 'XII RPL 2', 'Aktif', 1, NULL, 302),
(164, '$2y$10$unhX04DKflA2CqSZph3Mseg8rHsEi9LL5CZcjg7ULIyuXH8Ue/OVK', 'DEWI ROHAYANTI', '2324-10-131', '0087261958', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 25, 301),
(165, '$2y$10$9bP0vYgNWQzvwqdinTWi4.zCCLxEo.sZ55991v0UhO6fjCP.7ibEe', 'DIAN AYU AGUSTINA', '2324-10-136', '0078445277', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 24, 296),
(166, '$2y$10$DqqtQcy8GWcWrSaZGWYIfeDwIBwFKe4a/XNmad4iE3PsYRwn8hQmi', 'DIANA RIZKI', '2324-10-138', '3089514774', 'Perempuan', 'XII RPL 2', 'Aktif', 1, NULL, 377),
(167, '$2y$10$2vVq9zoHJXPcjSPXozn3R.bkLkP/CIWAe2i8rdZk4j1fACKCZ0FF2', 'DINDA LESTARI', '2324-10-146', '0072503251', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 24, 296),
(168, '$2y$10$IaP945Bh7PWKQPRxUSKI6ugHXiOvQieuVoF6ZLrbhTT0mCLDIsiBK', 'DITA LAURA', '2324-10-149', '0087121834', 'Perempuan', 'XII RPL 2', 'Aktif', 1, NULL, 294),
(169, '$2y$10$H88vaXZDu8l6Kv3EFPGR9OhCSHUjPHqxhpqeCN1NwqNzORPvl7lzi', 'EGI FERDIANSAH', '2324-10-160', '0084079953', 'Laki-laki', 'XII RPL 2', 'Aktif', 1, NULL, 302),
(170, '$2y$10$hohzR5mvM5Y8efTTpsXmQe1gQqP38jM/bValajXKpBKqauw0NE8ou', 'ELSA FITRIANI', '2324-10-167', '0083597912', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 25, 407),
(171, '$2y$10$5LABA9wiI6ooDMcgXOccuOgwubaXCaXA8GfxRMX12JwgO00LBXHsS', 'IKHLAS AMINUDDIN', '2324-10-219', '0083378474', 'Laki-laki', 'XII RPL 2', 'Aktif', 1, NULL, 377),
(172, '$2y$10$faFA14IYY5pOCX6AsZ2mfO0zSiNJ5LKIRM.18ExVNOhyby/oK/kC6', 'INDRI RISMAYANTI', '2324-10-224', '0076886099', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 24, 406),
(173, '$2y$10$qKzkX.w4woF3bfqi05MUP.CTgzeL8FYLGcOJWUepIiFZCIvAXEF.G', 'KAYLA DWI ANGGITA SYAHARANI PUTRI', '2324-10-243', '0086082915', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 24, 296),
(174, '$2y$10$6ratjnUhm0grv31v/qJqaO4mDPvkceYIRxah/ZO.zJ0lkIUnGTmau', 'KHAIRIZA RIHHADATUL `AISY', '2324-10-252', '0083126729', 'Perempuan', 'XII RPL 2', 'Aktif', 1, NULL, 307),
(175, '$2y$10$Nn9K0DBLx9XQ7URp.6oF1eOxARvM4/kj6A/bvEPUYQCBw1KFzzFbC', 'LAURA ADE LINE', '2324-10-258', '0082931681', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 25, 301),
(176, '$2y$10$lyPAsTN//Z/zdRmW1yUB5e83YHcvV.n3cr4BbYGdyYhJoCfh1xS0e', 'MELY AMELIA', '2324-10-278', '0074342164', 'Perempuan', 'XII RPL 2', 'Aktif', 1, NULL, 294),
(177, '$2y$10$7KL99K3ZxgGstU77sUl2uONld0gpV6NPE8d.XIINCDKCyGi1r6gTi', 'MUHAMAD HADI KUSUMA', '2324-10-304', '0074617652', 'Laki-laki', 'XII RPL 2', 'Aktif', 1, NULL, 377),
(178, '$2y$10$FAX.94IunskM.ZoKppTNgO33F.EwykKfboELoW0lT1AHyz97FGxu6', 'NADIA PUTRI SALSABILA', '2324-10-316', '0082259034', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 22, 306),
(179, '$2y$10$2k3wxuMPSZwNpwkEMD2lPepZgG84s5qsrafRzjGAmYvWhc5.pAM7u', 'NAZWA NOVITA', '2324-10-326', '0077346420', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 22, 298),
(180, '$2y$10$syfhL.Mb6eT6.o4DyCDuaOg7rZzy8Kw9T917jfo1V7qsyeJpaqQai', 'PUTRI MELY NATASYA', '2324-10-359', '0054622493', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 24, 305),
(181, '$2y$10$2sU3W4rOtvdOxNtgRB3X6.sCWDMEFiGj7mMqH1QnGBYsbJ8HoD6L6', 'RAEHAN FAUJIYANSAH', '2324-10-362', '0078339374', 'Laki-laki', 'XII RPL 2', 'Aktif', 1, NULL, 294),
(182, '$2y$10$OIsJcn/EXnFCDcNqXwGs8ehC.l9oxqxCLCQc5m62/Y4XpfsfwK3tC', 'RAYHAN ADITIYAN', '2324.10.374', '0085240704', 'Laki-laki', 'XII RPL 2', 'Aktif', 1, 24, 300),
(183, '$2y$10$cGWR6Kk6eGjzRDrk1w3rq.UkrpmauEO2JzRSHGMJ2tjTgpYHq1.Yu', 'RIZAKY AMAR', '2324-10-416', '0087664800', 'Laki-laki', 'XII RPL 2', 'Aktif', 1, 24, 300),
(184, '$2y$10$fiykY.qhF8S9b3GZPtXDKuBQaIuvCWwhMqSTAo333Fo9dR5oUS1zq', 'SALSA NABILA', '2324-10-433', '0081380774', 'Perempuan', 'XII RPL 2', 'Aktif', 1, NULL, NULL),
(185, '$2y$10$ruZH6SbG/TMmJb/OtNgF.uw.PX5MotBk/nCE6Ns0GEJpeic9GxqES', 'SASKIA OKTAVIANI', '2324-10-438', '0079159104', 'Perempuan', 'XII RPL 2', 'Aktif', 1, 22, 306),
(186, '$2y$10$NqznX2GbQlTGpxMzon4Oh.jC0b.JvMdB9YtLJiZ7gL7f830Uu2fVi', 'SITI ABIDAH', '2324-10-454', '0071317214', 'Perempuan', 'XII RPL 2', 'Aktif', 1, NULL, 294),
(187, '$2y$10$4tS6NI/VYbOyim5YKq8LZuhzSdsPaCzNUBIgBdBEgJI1wsknADl6.', 'SRI APRILIA NURAENI', '2324-10-464', '0088367876', 'Perempuan', 'XII RPL 2', 'Aktif', 1, NULL, 294),
(188, '$2y$10$d7qqEMEC2H470Gal1DI/DOC1UMB0yV/Y67N9YsNO9Ffa3qWv424Rq', 'SUCI RIANTI RAMADHANI', '2324-10-467', '0079913112', 'Perempuan', 'XII RPL 2', 'Aktif', 1, NULL, 377),
(189, '$2y$10$7MTU1WtilLsmwvZ9uX33AuHnZ1fdpAHp33Jd6dsu3hivrKJKceqKS', 'TARPIN', '2324-10-477', '0082922302', 'Laki-laki', 'XII RPL 2', 'Aktif', 1, NULL, 302),
(190, '$2y$10$gOBHmRwiXY.pdsEBCOUiaOwtyfPqFD/fOtkMCUNRfF3PuEF1Lo.Du', 'TIA NURANISA', '2324-10-481', '0078885964', 'Perempuan', 'XII RPL 2', 'Aktif', 1, NULL, NULL),
(191, '$2y$10$GQDxq3EFanCCvJjOqS2y8.qYtYmap4gRQYWaFddN0gheJdoqYVQ7W', 'VALENT', '2324-10-496', '0085303875', 'Perempuan', 'XII RPL 2', 'Aktif', 1, NULL, 377),
(192, '$2y$10$wRZbxoPh3svPsm49/5Bk0eUQmgvKhyQhZIxGkAMmA2.J5v2bLsZ0a', 'ZULFIKAR MUHAMAD ARIF', '2324-10-539', '0089148462', 'Laki-laki', 'XII RPL 2', 'Aktif', 1, 25, 408),
(194, '$2y$10$23JrCVSZt2cuEo6LqQZ7R.myWTWtLQ8haXuIpuJeWuLFl9HfYDoO6', 'ADINDA SALSABILA', '2324.10.009', '0078661556', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(195, '$2y$10$ST3q8cDh8rwF6oaBvOCKo.CcNDKWaoqx5C1Z1yIvT935D0JRwbYXW', 'NURUL HIDAYAH', '2324.10.341', '0082499469', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(196, '$2y$10$SW1oGeQmoSGyIVV8RlIY9e4uCei3fTezmkFoh2cfbkFB0pYWVT6em', 'OLIVIANI', '2324.10.343', '0086206712', 'Perempuan', 'XII FI', 'Aktif', 5, 6, 402),
(197, '$2y$10$rLfC5EFT/a0IeD6BMnhLg.ji.JrLGjnTie7ItEtD4B0dVLT0O5fNK', 'PUJA SUGIARTI', '2324.10.353', '0085961693', 'Perempuan', 'XII FI', 'Aktif', 5, 6, 402),
(198, '$2y$10$YVsZzw1a6epGJ7Yo5g3mm.YTuQBc4LVf9yL8z/UngxlswdHSX6KU2', 'PUTRI INDRIANI', '2324.10.358', '0085834970', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(199, '$2y$10$dZO0AfNPQlmQytAQIc1sUuJOuYuRZ1.WkkFHyOUqj6AGSdyufaAkq', 'RESIH', '2324.10.388', '0087196136', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(200, '$2y$10$q2HpR1xHDDqpK7iN7MfQVeoM6fwvCisX9t.5gHHiOf7.6IfstJfHq', 'REYHAN YUSUF AL HISAM', '2324.10.397', '0087297002', 'Laki-laki', 'XII FI', 'Aktif', 5, 2, 401),
(201, '$2y$10$uTt6dTjYaQNlBzoGIiMdXeVtfrWYsV9NwluEGvBQdHn/1cAjwBNkO', 'RIRI JULIANTI PERMATA', '2324.10.405', '3088517771', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(202, '$2y$10$5Sh/Fy8W8CscaMUPpGlSSuZYzcbMRNRenzzTJa1V336IB0RB7kPPC', 'SUSILAWATI', '2324.10.472', '0081807810', 'Perempuan', 'XII FI', 'Aktif', 5, 6, 402),
(203, '$2y$10$oGitVobfKn3qmJcoJtEG2.b3RddhTqBZ8fX7G0.495E0HJekt6Rqu', 'THIARA AL QAEZZA', '2324.10.479', '0075195752', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(204, '$2y$10$HWm49cqO6LzEAzKZsvZgvu4MCVF5bkwPqls.N1fQGbp2Dz/4j4Mxi', 'VERA ILHAMI', '2324.10.498', '007003977', 'Perempuan', 'XII FI', 'Aktif', 5, 6, 402),
(205, '$2y$10$6Yp5K7DU.ADdebnb7UeGzud/2TCsXYLIaMN2lo4Ro.hOLzNv4WnbK', 'VIANA JINGGA APRILLITA', '2324.10.499', '0089466694', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(206, '$2y$10$STeKJOrGFqL6kOu.fSmfLe.S9./Wxhri0q29Qhhq0bHBWsMEMsise', 'VICKY WIDIA PUTRI JENILIA', '2324.10.501', '3083298322', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(207, '$2y$10$s0y6WWosA0oq7ICnzhThWus3pjO5T260VITVyLTEB6l.X./G/fwGa', 'VIKA FITRIA YANA', '2324.10.502', '0075577900', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(208, '$2y$10$FxNhREyB4gluM4.HIy4ny.HltwAdNj9xVOpROCEmKHQ3Gz.H2JwA2', 'VINA ROSITA', '2324.10.504', '0085568574', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(209, '$2y$10$2cD93.rJRNKOcndYSeYkuefU6T4OEuYH7Xw.ScQhA3q/UMTCHgr/K', 'WINASARIH', '2324.10.517', '0074071518', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(210, '$2y$10$a7qtj24AtMehXNgGa7c8EOLYFwkceX7Vs3GMnvCrz.McqnbWJj1NO', 'YOLANDA FITRI OCTA RAMADANI', '2324.10.525', '0076047919', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(211, '$2y$10$xtSTWe8IToEF3iYBNNTWMOVLsVQFkkpw8qUzY.Jq.t3ClGjy16iFK', 'YUNITA', '2324.10.528', '0084467708', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(212, '$2y$10$iD.DvOBtJDmlZzZPuaLrReJE8iuMD2YvNdHalJ3vxd8neS5zNf3Na', 'ZASKIA MEILAND ARDILLA', '2324.10.538', '0087706203', 'Perempuan', 'XII FI', 'Aktif', 5, 6, 403),
(213, '$2y$10$YDVSczP2LIDVHBYZQQCIyubaR1PkBKQSjDnC1m0Ovk3rW3RwprjBW', 'ANBIANKA DEVARINZYA ALIFFA', '2324.10.053', '0082847980', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(214, '$2y$10$yZJKm0zBVBvww0.8/rYCDOxC9bktlDzAe3KiGVkadRY2pm4hredDu', 'ANGGI PUTRI AGUSTIN', '2324.10.060', '008969757', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(215, '$2y$10$NiN4zfTh7sRItaekwVnvNuBZh.xfDLsROr4xAzcslBlHRaTJ9yO3a', 'ANISSA SHABILLA EKA RIZKITA', '2324.10.065', '0089599151', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(216, '$2y$10$BDr5PFVaJd0TViZ6od3igeWW.nyOpKSdxQelDwRgdUBXwSHVTmqj.', 'DESTIA CHUSNUL JANNAH', '2324.10.125', '0078156454', 'Perempuan', 'XII FI', 'Aktif', 5, 6, 403),
(217, '$2y$10$Jt9an4UItREqZtOD6uQlyuDUOBc.s4RlCN4dJQbHPykDer.b/g1Ay', 'DHEA KIRANA', '2324.10.133', '0087440964', 'Perempuan', 'XII FI', 'Aktif', 5, 6, 402),
(218, '$2y$10$mvZw4i4wM1IQMOgZ2lDq5OwkbtvP/T..5W.ePMKqwSOleksPfLUTC', 'DWI ANGRAENI', '2324.10.151', '0088323903', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(219, '$2y$10$71z6Fehl1b9pNhAMMB./zuq/dUllvX1izjmKXyBsAZndg/re8UtEW', 'DZIHNI RAMADHANI', '2324.10.158', '0079573554', 'Perempuan', 'XII FI', 'Aktif', 5, 6, 403),
(220, '$2y$10$0eLhLwp1DRVxYPaNEZTiJOhzyXy4IVCcvl8tNUKsD13S1e3pFSIYu', 'ELDIA INDRIANA', '2324.10.165', '0088697830', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(221, '$2y$10$LxnvgN3fJ/E85GJko1.pT.fv1ROX0mpfyxS1GfrooYB9seZ4kPcb2', 'ERHISCHA MYNA QUEEN', '2324.10.170', '0084328692', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(222, '$2y$10$q.2.xDaiS9go0hwQgMS2SeKSEL8DX0sv2ErvULsJLx/8cQtpVn6B.', 'ERIK TEGUH MAULANA', '2324.10.171', '0089703690', 'Laki-laki', 'XII FI', 'Aktif', 5, 2, 401),
(223, '$2y$10$ZCz7VMPGcsqUW.GYjo4PzOs4BDddA3.o4bStkyhjzIxNfhgTO8Sf6', 'GHINA TRIWAHYUNI', '2324.10.196', '0088361474', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(224, '$2y$10$7uEYYTGSXs5dQhf7ODarHulMwOpuoQ1x9TZSJu5hBzWOvltCogUDa', 'HELEN HERLIANA', '2324.10.208', '0082603227', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(225, '$2y$10$AVXiAP.NLFJ9KDLSuR7IZ.pD.kf/ipmtW6upnRt6F2ak1bljd0YqO', 'JAMILAH PRISILAWATI', '2324.10.231', '0087047057', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(227, '$2y$10$zEyaNEfU9VvOrH7cIeb2BOWBVKsOxhnZjQumU1izcSIHy/G4u4r/y', 'LINTANG MAULIDA AZZAHRA', '2324.10.261', '0087539500', 'Perempuan', 'XII FI', 'Aktif', 5, 6, 403),
(228, '$2y$10$8tXYYUv.QeA.ly5QuKnb7eqKoRwL.ZAWhrPrt7cdJ0hvnH1w2yXQu', 'MELLY NUR AZIZAH', '2324.10.277', '0082451827', 'Perempuan', 'XII FI', 'Aktif', 5, 2, 401),
(231, '$2y$10$KfArhcyAvXxxFyuEHV/7Y.Y08dKR59sDQz46BZtsv49RxkH7D/4da', 'NUR SABILAH', '84435937', '0084435937', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 16, 363),
(232, '$2y$10$vt3aX8DxrfTCaxgKg1N2te59ZBm6JpJJdjGH9gMXJ3Up7KZjdBE8m', 'SESILIA RASTAFIRILYA WULANDARI', '76355021', '0076355021', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 16, 363),
(233, '$2y$10$xG.YNpIv655f5wJ7pSI83eltn0ZTvooMcnmCHBC6hc8yPp.LmQLGO', 'SINTA NURMALASARI', '0079312963', '0079312963', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 16, 363),
(234, '$2y$10$aoEzETOk/KQqkgknATwF.e5dVQGQSSMEu2tIZA3mzjiRrhNHvQs3W', 'ABDUL MUHAIMIN', '2324.10.002', '0089881952', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 19, 337),
(235, '$2y$10$.KV5duz7fMqHc6f4xw2GyuhUk5nI5BKfI/TEZNxR7b4v.0ylunh1C', 'AHMAD SYARIFUDIN', '2324.10.021', '0086731532', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 19, 338),
(236, '$2y$10$gxf4.ZfatY9UiiNHwuipjOB8JAnFr9ARl9nq813s1gVjoKhS8xixW', 'AJI MAULANA', '2324.10.025', '0089010680', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 311),
(237, '$2y$10$2/al2kvNsFnilFKpMYAoo.kO.W0C3JGwpJbahWGT6beyP8t9OFMu6', 'ALDO', '2324.10.032', '0078333880', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 310),
(238, '$2y$10$Sd9w6nrIKfYphKp.fCjUROeYfgQK07h4fldm/kc4mJpdvJL7..cP6', 'ALIF TIO RAMDHANI', '2324.10.038', '0083589951', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 311),
(239, '$2y$10$vPQFlmSrFqOSFWZZdsPVseXv6LV8iQhMta/o6tZ21nNmr8DF7ulq.', 'ALYA', '2324.10.043', '3086706180', 'Perempuan', 'XII TKR 1', 'Aktif', 3, 20, 313),
(240, '$2y$10$ev9atIN2oCFUJ3cag9v3M.RqaU5ADN.rsUdSFkwryvaEXap.8HR1u', 'ANAS KHOIRUDDIN', '2324.10.052', '3080751893', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 19, 335),
(241, '$2y$10$T0KpnORua5q3zubijkH7uOiFZUUaV0OHAS.GrSmYwrC.4SZLf.tym', 'ANTON ADHA', '2324.10.067', '0069424311', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 314),
(242, '$2y$10$m6RhtllniHJNXKp7IBx0DOoxzNmbf5lLmQ9q9AY3yCrzL/wCGuFaK', 'DIMAS FIRMANA', '2324.10.143', '0086958594', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 311),
(243, '$2y$10$jZFX061gBNQy7kltMm5uEOknFetPbYtRP5zp5DqadqoVOfDU1sCdS', 'ELFAN DWI ARDIANSYAH', '2324.10.166', '0075391756', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 19, 335),
(244, '$2y$10$O/1y8Au85lShMJQ0Vxbs6unbWDGT23dvm4qz70GmePe1WgJuOJ5IO', 'EVWAN HERLAMBANG', '2324.10.177', '0085303145', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 310),
(245, '$2y$10$ZTRAfKo9e4COAAjdu5mOLuIqyLTwL5h4P3s3L81WFle9LipyEWgsq', 'FIQHI FATURRAHMAN', '2324.10.190', '0082687734', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 19, 335),
(246, '$2y$10$JY909bXssyvIinKCTGSnTeHV8mbxAi1rGs02vrWzSLvIhdSsspmva', 'GITA LAURA', '2324.10.201', '0074415499', 'Perempuan', 'XII TKR 1', 'Aktif', 3, 19, 333),
(247, '$2y$10$obye9FftYu5PhXB1R9kBzeLp7FRfR.tZiWn8ggMEUti9Re/rPjkiq', 'HERLINI', '2324.10.212', '0081608494', 'Perempuan', 'XII TKR 1', 'Aktif', 3, 19, 333),
(248, '$2y$10$JZ9fCYgCpOtyCsHQm8E2s.DbJ5.tluI85LW.gp5kxK.z9a2.60LrK', 'KEVIN INDRA WIJAYA', '2324.10.247', '0083133006', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 19, 338),
(249, '$2y$10$ultvgliqQ/132/Q3MhkKi.pMTJNO/O.ULiQ05A0wHB82fPmsbjK7i', 'M. WAHYUDI WIDIYANTO', '2324.10.265', '0088150323', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 19, 338),
(250, '$2y$10$gI4JdRTDNTKO6mYDRlg.Pe3uCGqWJ4zJek7AIYzif5hMCeLJ1XLKq', 'MOHAMAD ANDIKA ALFARABI', '2324.10.281', '0062202577', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 19, 338),
(251, '$2y$10$JGeiGMby6xQOSUfyRIbAfetREdx7dOBl0KvZ9qObc1t58UMDeKwry', 'MUHAMAD BERKAH RAMDANI', '2324.10.289', '0077012821', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 19, 310),
(252, '$2y$10$mrUY5dFSbMhhSZr9SF.4.OzIbB52F8qTjd5nfwuiHMc0laZScm68C', 'MUHAMMAD EGA RADEWA', '2324.10.300', '0076290920', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 315),
(253, '$2y$10$ecs6SfieNGZmi6xM28IZEedXQD.V.GhvpyIXi0WHd5wcpIiS0Be4W', 'MUHAMMAD ILHAM FIRDAUS', '2324.10.306', '0087100658', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 21, 319),
(254, '$2y$10$yEbo2oIniKK8TzLxCa.6e.9AhXmAaNqZv7bfP/cTkFO/hNmjJVLf2', 'NISAH ANGGRAENI', '2324.10.331', '0074380457', 'Perempuan', 'XII TKR 1', 'Aktif', 3, 20, 313),
(255, '$2y$10$EzzRY0Ky9FS47r/BsThl3OkmBEZGrgOR5aFCkGLKR5Mm.Jxn73lxu', 'NOVA NOVITA', '2324.10.336', '0077003416', 'Perempuan', 'XII TKR 1', 'Aktif', 3, 19, 333),
(256, '$2y$10$dqg9.Cjatm1caeenJbNLVue0oHVJSRvF.q7UB.LsvTvUvk2GzZ96C', 'PRAMANA HALIM', '2324.10.352', '3070427407', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 310),
(257, '$2y$10$A.nQWY87EbQSGqP38BanuusS3tcIzFS3OnK2Rdrg2p5UP1JnxVB4S', 'RENDI PRATAMA', '2324.10.380', '0083575242', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 312),
(258, '$2y$10$s9PTSAkldx2K5/6iGZ8OZOU2iVF7u485dhSCaedtYUFDjMB464GKa', 'REVAL ALGI TRI ALVARIZKI', '2324.10.392', '0085645349', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 319),
(259, '$2y$10$xBZ6.8yob8IGALZXxup24.TQc1JRV14uLGTMTJyaW69cgVFo5Kx3q', 'ROHEDI EKA PRAYOGA', '2324.10.421', '0072671511', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 21, 319),
(260, '$2y$10$S.11/e/2lL6lGvgsQjOUlOkVl1HXmtXp4mxTO1ggeJIjrAzkj5qhm', 'SARIP MAULANA IBRAHIM', '2324.10.436', '0077753531', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 19, 335),
(261, '$2y$10$niY86cTm.IQArG32c9cQUO3NTOeO6mG8qKBROqh4Z6zu9x3cOd2wC', 'SOFYAN ALFARISIH', '2324.10.461', '0074308725', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 317),
(262, '$2y$10$E4GqTr.G0JoldXr/oI8nye9Sc7qIn/qoLb4Fj.ZjfJ3TSO4epeo7.', 'UMAR SAEFUDIN', '2324.10.494', '0072969423', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 312),
(263, '$2y$10$q6P/iEY1n/Ne04mLGzC9tObTThGr.zIwTO.AlbHTEzMO7lyiNFMqK', 'WANDI', '2324.10.511', '0083474042', 'Laki-laki', 'XII TKR 1', 'Aktif', 3, 20, 315),
(264, '$2y$10$rpwCwLc13OcNSrEGmuswSuOL/.BxLsk8i7XqnejS3MHFMgVLsj1Re', 'YOGI ADITIA PUTRA', '2324.10.524', '0084369928', '', 'XII TKR 1', 'Aktif', 3, 19, 335),
(265, '$2y$10$fuYBBDXlUQe0JeWU7guuqetxGUS2Dmg7lq4k6zZcrY0f6uF8gEKYG', 'ABDUL ROHIM', '2324.10.003', '0085497769', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 318),
(266, '$2y$10$Xnt9bVsIOM/iXu6qttF7gevKwObL0fRCQhyB8Gxy/JFeBYfTMv.1e', 'ADLLY DWI PERMANA', '2324.10.013', '0082202822', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 312),
(267, '$2y$10$czaZqTSoHy9RuCSD.A/dyOIPk/FpXYSV76ec5p2EUCw3uKVZOURRO', 'AKBAR NUR AIDIL FIKRI', '2324.10.027', '0079498720', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 21, 320),
(268, '$2y$10$D2fV8u9lYu6LUr0fnl2DpOHVcELDHIepgfB9gIJ6l4wWXC5s53CMq', 'ALEX ZUBAIDI', '2324.10.034', '0071261655', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 21, 325),
(269, '$2y$10$TCJqx8OMP75QKMrXdF0FM.6IeDYtoC0W99YRlcsoZMWuNBP7ABLiu', 'ALVHINE MAHARDIKA', '2324.10.042', '0078089271', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 318),
(270, '$2y$10$fdwrsn/vbR9hJNyndsytB.F4ILjZ4ArO5jI/9HtHU.zZHuauKDZD2', 'ALYIS CORNELIUS', '2324.10.044', '0081737028', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 312),
(271, '$2y$10$8x.z.Gf74EaL9inYnfyeuO5IXZ0H/SfdbkQX23cNWJQ5ymsWG1hOS', 'ANDIKA DWI ARDIANSYAH', '2324.10.055', '0078685686', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 316),
(272, '$2y$10$4ubmvSfyWGUpcO19g2DEUediOsKtHpM9Sck/VbKdm8qpc5RXlW6QO', 'BRYANT MUHAMAD YUSUP', '2324.10.086', '0083195127', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 314),
(273, '$2y$10$JMBy6C89y9I/RJPjK2VDZ.B/pDTc8Rd6yGuO8tKIvsiN4nIr3IC6i', 'DANI IKSAN REZA', '2324.10.099', '0085483255', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 312),
(274, '$2y$10$liDjhgV8aSv.4mg7q.bqEeACzIwHUHva13gyfB7UPsrxx/9p82m.2', 'DENDRA ALFARIZKI', '2324.10.120', '0081621785', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 21, 322),
(275, '$2y$10$UrwvLM7v/2Pe.Tc5VLoVCewDhUpAmq.wWfZkTcJWmXwypTzivq3pS', 'DONI FIRMANSYAH', '2324.10.150', '0073638429', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 21, 320),
(276, '$2y$10$WY9cqoqfAtIuTGXAap2u2u8qRX1EldxwInJY4uHafy3rNNdf9XUFa', 'ERWIN', '2324.10.173', '0083349824', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 21, 328),
(277, '$2y$10$yYsW/lFpwCC8eptVJkD/pu514m2mU8G1xp4f76TFxH9jsHxBw.ub.', 'FARIQ HIDAYAT', '2324.10.182', '0074118950', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 314),
(278, '$2y$10$6juDVS3WehQMCLoobZLYb.dceDc/IGwW62LCKQZCcEvpAnCY5ZQYK', 'FITRAH ABDUL BADAR', '2324.10.191', '0082150407', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 317),
(279, '$2y$10$y3o4LU6Y4MOwZSfFv9.VE.C6ZPJ.9cBfRFHVX0p4rkdIlgeR8j.AO', 'FITROH ABDUL SABIL', '2324.10.193', '0089728887', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 317),
(280, '$2y$10$ofzT6JhzJrlZJYpeN4Pqn.2HxLRKHPhLoVoE1zyMa5uGVfztNrQoK', 'HILMI MUHAMMAD PAKIH', '2324.10.215', '0078410241', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 21, 322),
(281, '$2y$10$I45o1tKkKsZssfmXfaQya.0itu0gCVcCeoaHR1JpNslE2Mevh4Cfq', 'JAENAL PURWANTO', '2324.10.230', '0086767990', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 312),
(282, '$2y$10$queEWM3rgjSIZQeyfM/Fuu4SQVwP4aGIg6Iq/jNsxdKMHEzp/0tRC', 'KARNA', '2324.10.238', '0084455775', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 19, 337),
(283, '$2y$10$YVP.dCf6hHVRlylhYQBQ.u/yzv2QK8rNCByEGf/8Z/hGqY9Kl.jqq', 'KHAERUL ANWAR', '2324.10.250', '0082604038', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 314),
(284, '$2y$10$.LLj/jg2B.xuW7z6dE1.XOknGmR83EFd9SvO5YwnXky0Q8O0hVJxK', 'MAULANA KIKI', '2324.10.271', '0076396420', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 318),
(285, '$2y$10$afpK1tKRD0rU1ph.ymI3u.uVLi1d7xbf/.UAINYzL7yNeEqye3zae', 'MUHAMMAD ALDI MARDIANSYA', '2324.10.286', '0086058853', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 19, 336),
(286, '$2y$10$C/zvDfgYbXzOoTcMIGNYp.fj9WhdPh1KxkBPj.2XuPG7l4VPO2Pz6', 'MUHAMMAD FAKHRI SUHERI', '2324.10.302', '0082376533', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 317),
(287, '$2y$10$LXYjyu/L9Yz623BA7VXlNe0wcdoZBlYah/EoBtJAVJBdmxArPFKfG', 'MUHAMMAD RAHMADANNY', '2324.10.309', '0071255669', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 21, 319),
(288, '$2y$10$CrsHi3rbfkkN/58jgyzXo.IKi7I1bFFzD05i4HB7cGHYqonYXJ3cC', 'MUHHAMAD RIZKY FAUZI', '2324.10.314', '0083661144', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 19, 335),
(289, '$2y$10$WDoVQlu4qrKp6Iq47dyuGutZrHXDbv94z0v927hJIGPKfGD04D7R6', 'NANDIKA KASWARA', '2324.10.322', '0086927192', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 19, 339),
(290, '$2y$10$HMMJv6dFTIKIq4PuOVFCsePUlSJuBxBRUWboTwlPFqJtpGqjhzESq', 'RADIT MAULANA', '2324.10.361', '0089877647', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 21, 316),
(291, '$2y$10$oxRjVmv7HmBbbDqow6A86uTNV.bND6rTPnCpfqRyc/x83J7LrS05u', 'RENDI PUTRA', '2324.10.381', '0084201617', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 21, 322),
(292, '$2y$10$154iYMmBP50QeDTL56tcG.MJhA8Un62Pa9iij61Utf6v6Vin5a1uC', 'REYHAN ALDESFRI', '2324.10.396', '0078866007', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 316),
(293, '$2y$10$8HDtr7CCOgG0LhmOUoyEve7ybQosJX6tGoor1LQ9omwXeFqoAwyj.', 'RIZKY IBNU AZIZ', '2324.10.418', '0089914633', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 21, 320),
(294, '$2y$10$YzNU1soepNf.aUAmj3sPkeys6RDmZ4NwjrCGK2MT3dNyAAM6JyW6.', 'RYAN RAMDANI SETIAWAN', '2324.10.425', '0076099692', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 311),
(295, '$2y$10$OVjFthmgtwFl7hEJLxrfluQH6kyc7aeOWH5lJwsB2TyQKFOE4Aql6', 'SATRIO', '2324.10.439', '0077385391', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 316),
(296, '$2y$10$SQbcX2sn9jLM228KrstfKe6vasZoOqagtly2a6bcil9F95uc.Yxoq', 'SOHARI', '2324.10.462', '0083498715', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 316),
(297, '$2y$10$kf/CXGGWKhhCG.q1NqoqBeQR19xLCMqL28PzpNGJ8KlhpgRwOn2km', 'SYAHRA EKA PUTRA', '2324.10.475', '3060053745', 'Perempuan', 'XII TKR 2', 'Aktif', 3, 19, 337),
(298, '$2y$10$zzPerx2kB8j/Zk7.wDaKAOdn2RwvFZl9K7APZ1CjgBY5jDgX727iy', 'VICKY ABDILLAH', '2324.10.500', '0086560078', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 20, 316),
(299, '$2y$10$YxD1h6krNS4lrAbQ15cVlOOWvwwQcDAlUscWiFfTCQF1K1jGnaWpi', 'WIDONI HENDRIAWAN', '2324.10.515', '0076762702', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 21, 320),
(300, '$2y$10$e2EDzSYLOmlUKH2i/47tbOl1KU.ldYvN.zSoniQPEm3VIUn5tdXg2', 'YUDA ANANDA', '2324.10.526', '0082888956', 'Laki-laki', 'XII TKR 2', 'Aktif', 3, 19, 336),
(301, '$2y$10$DLmaF3wTt/NkVr8kQ7yGD.MOSqFPuoRNPl2sZjm10t3xdzvhUiT3a', 'ADI RAHIMAN', '2324.10.005', '0072461940', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 19, 339),
(302, '$2y$10$SgvVAnHI//cEPP8amskdyeeuioT/ijMqWUB8U0ocKNCn/ReiJlTPa', 'ADITYA PEBRIAN', '2324.10.012', '0082287058', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 321),
(303, '$2y$10$Qg.F3pV2TNMuo1grM/XeWepdVfZOcbnhsjuIDB8o7pt186xqxxfTe', 'ALFIAN DARMAWAN', '2324.10.036', '0071721382', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 19, 330),
(304, '$2y$10$tttpe1aEIyc5wN0JlFvrd.q1VPtMrOpNmnGctR7rQHgs0vL8Bhp4W', 'ANDIKA SAPUTRA', '2324.10.056', '0089004559', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 324),
(305, '$2y$10$ToPZdhdMDAGDE.FE5XWTh.FmYdfAgsLUmYch45UucngyIi3uGM.J6', 'CANDRA ADITIA', '2324.10.092', '0082462910', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 322),
(306, '$2y$10$75Dk2AFArwrN1AS2vA9Zx.HWqbuz2WHfsElS6OO8/gDUkGgt5b3OC', 'DANI MAULANA', '2324.10.101', '0088004093', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 19, 339),
(307, '$2y$10$Fz5lJQrls3SZf3VQ5Af7HuZH2JQuC9jR.DGdXSlLpi.sI6yLZhDMK', 'DENIS', '2324.10.122', '0083698544', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 20, 311),
(308, '$2y$10$9gUc8YsBYmdWFh27Mapjqu4Zj8Q501WKkcH8rHX8xQogOThlTmkDO', 'DYAS SAPUTRA', '2324.10.157', '0072763978', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 321),
(309, '$2y$10$ucee9mKtnRha2JwOQUPFCe3eFsBMfnZzw9eHaZ/X3NeHFyrk8xZn6', 'ERWIN', '2324.10.174', '0082960891', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 328),
(310, '$2y$10$vRa0EQYADX6b332mQWqbzOxomKrxxyMqhoDsNIW7f5FBcuJ0qp3sC', 'FARKHAN', '2324.10.183', '0084327534', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 19, 332),
(311, '$2y$10$p5qVeNmJVMRA3Sw03Hf6ze/p3fPX6gJJSJwrdz8ImkjvPtztLrLfG', 'GILANG ZULKARNAIN TRI MUKTI', '2324.10.198', '0071017262', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 321),
(312, '$2y$10$9/jZkXrWXjrmxCacxx7i5.b6SXkyaTIETTaVOD.290eGtcKN54P4C', 'HILMAN MAAS ALFAUZI', '2324.10.214', '0074379142', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 20, 312),
(313, '$2y$10$k./Wc14MpVnk6ZZRzIdbl.7z.5Pow0wjW/LE2hvFfm8Koa5O76Xmy', 'IBRAHIM ZUKRUF AL HAYDEN', '2324.10.216', '0084775222', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 19, 330),
(314, '$2y$10$bkwkxSSzZEb/ZIWXaJXSLOI/eKR7j0FHoqkkuNNCDRYuIa4VNEKv.', 'IXAL ADY WIBOWO', '2324.10.229', '0078943496', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 19, 330),
(315, '$2y$10$9nMOELQslVNwsRpqBUx2LetTi0IW0ka9PQAukk6dznY6FMBn.eijC', 'KARMIN SOEKARTAM', '2324.10.237', '0071628029', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 20, 311),
(316, '$2y$10$uudKRs0E8H87jaozXqeyMeslIOAOpqN.jsfM4hKdSz8QUxOxbsycW', 'MAULANA MALIK IBRAHIM', '2324.10.272', '0076624938', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 320),
(317, '$2y$10$pTG/z7aXbbIyp3K4Xplx0uE1s.ulzICCFUzWOzq58TWdmHCoC57eW', 'MUHAMAD ARDIANSYAH', '2324.10.287', '0089838330', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 19, 331),
(318, '$2y$10$AtTfRVuzl3OlxWTpiIFm/edLEY/KDURy762u2ByXw9gKVjfHf3YIy', 'MUHAMAD FARID ALFARISI', '2324.10.292', '0074592913', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 328),
(319, '$2y$10$SrCQR67sXktmK.nFE1YUaudL8LGoLt7vkhSiyoWwbmCZiRQnWSFhC', 'MUHAMMAD FARIEL IRAWAN', '2324.10.303', '0086615586', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 20, 318),
(320, '$2y$10$0zSv68t2FbbX9TWiztbsheNRECwymtHHr2bipE4UPlYE75gzoKvIC', 'MUHAMMAD RIPAN ADITIA', '2324.10.311', '0075107728', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 327),
(321, '$2y$10$0SrjkRBVkJ5RS5f6JGzt4.vs/5L1ADRloKgqXdAb712nmRlh.YjZG', 'MUHAMMAD SOLEHUDIN', '2324.10.313', '0083615164', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 19, 334),
(322, '$2y$10$.QosFvOl1.sIpV289QMRS.ov5ZvNRLxABnjxyl1qHSY0vkK11VRCu', 'NENDI', '2324.10.327', '0082382944', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 325),
(323, '$2y$10$wlVRM25nswr5olfVRvPeMegV7cBXoUyWrZDBsxDnxoV7qUimkPAlu', 'RAEHAN SETIAWAN', '2324.10.364', '0074336177', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 19, 331),
(324, '$2y$10$TVRUKVZtK8c4x6ZxYlfSBO0/imzoSEVgrRyR1wYoYBv.HXZqNISSe', 'RENO ANDIKA', '2324.10.386', '0084290300', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 20, 313),
(325, '$2y$10$zrudON6FZcMKeoS0gIele.bMWPAP9XXl4hHPYUFfrBHVgAmDHE89m', 'RENOVA ADIANTO HAKIM', '2324.10.387', '0079049391', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 328),
(326, '$2y$10$6d6tTxcHualyoe38E1V7NeWhtk40x1t4XONBMOy/0zytNX4Al9/OW', 'RIAN HIDAYAT', '2324.10.400', '0082266595', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 327),
(327, '$2y$10$pJlf28OvVlj0nrCbITPBrOProyVsx8Gs.ZghrSMhAyoUjU.ZpKHS.', 'RIZKY RHAMADHAN', '2324.10.419', '0072060976', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 324),
(328, '$2y$10$PO4A7qTBbZww0hHdkpE2kOtkLlvEaPcO79RK3gIQFRbcQSoqTAdQi', 'SAKIN', '2324.10.428', '0076801414', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 328),
(329, '$2y$10$SXxpsI2VAZ6bv5otm63ZruPm2PyCAk21tgZIOwTbsHSir6ip/ZKyq', 'SELLO SEPTIANO', '2324.10.441', '0081006460', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 323),
(330, '$2y$10$YzNU1soepNf.aUAmj3sPkeys6RDmZ4NwjrCGK2MT3dNyAAM6JyW6.', 'SURO SIDIK', '2324.10.468', '0089576002', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 19, 329),
(331, '$2y$10$iZRwXlptLnLWgvdR/f3tXeVShdJyEYHmwqksCW5akK/PGCZdDAZyi', 'TONI GUNAWAN', '2324.10.489', '0089735165', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 320),
(332, '$2y$10$94GkdXfwbEd7CnDHSruVAOJNt.4Bvpcr2s5BAy7yGK1lcSaTxkRrq', 'VIKY WILYANTO', '2324.10.503', '0088135878', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 21, 323),
(333, '$2y$10$hgmqO30VF8fxd6LjXX/AcOqVahhwbGvuvTCUs2IrnXBDNkf.kalGq', 'YUSUP SUHENDRA', '2324.10.531', '0071063661', 'Laki-laki', 'XII TKR 3', 'Aktif', 3, 19, 339),
(334, '$2y$10$S/M/jMBHO0W0OCu10KGWUO2x0Rk.bR5qDyvvc00GZtBsZiF49tCt.', 'UMMURAHMAH FAUZIAH', '77454374', '0077454374', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 16, 363),
(335, '$2y$10$RAmIIMAz5dK6kd4BLuUIMeQnfdHd3XC3AUraphNDwiBdgnVzXRSgW', 'DEWI RAHMAH SAFITRI', '77507511', '0077507511', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 17, 354),
(336, '$2y$10$DUhuTCAj0ZxtnYZRUe4Dwei5P1ndFeR.wKwHPBY7Sk/YGfMV3KX.K', 'REVA AYU NIRMALA', '82912722', '0082912722', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 17, 354),
(337, '$2y$10$k6B3ozxjdoNrj6o..xdQgeet0931LjyzxxoHIULYtIeEV0L3NNCHK', 'DWI ASTUTI', '81561810', '0081561810', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 17, 354),
(338, '$2y$10$GvUciTeKVvgsTOChuUgi3.MTKdY67bCD46jk7.UWyKKexucV1YqAq', 'FRISKA HERLIANA PUTRI', '97945223', '0097945223', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 17, 354),
(339, '$2y$10$j7SlFajjeTkpobZhkgYMj.uomjhMC/K2pjhwUHDIpQHOyJk3i8LgC', 'RAHAYU JULIYANTI', '83095491', '0083095491', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 17, 354),
(340, '$2y$10$Hof/FBHkBxDsFfKWdpvYTOCa8SUwB87jeW4REiA/rjML1yYyrO7GG', 'SAHARA', '89900410', '0089900410', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 17, 354),
(341, '$2y$10$xdzFD9LPwC1FwunbSUePGOwT4cKBQ/s7iAjk1/hJiq8nSnmNO7Mz.', 'SAFITRIA NINGSIH', '89727109', '0089727109', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 17, 360),
(342, '$2y$10$gHp6BO0KF4o3u8cyCSmQZOQ8NJE1EGzf7yUQ27BI0zSxZVJVrmj0e', 'SITI MUDZAKIROH', '88363040', '0088363040', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 17, 360),
(343, '$2y$10$LXzdywgkC.FY3Dj9qrfg9eU0KswG5AQHTLVuoP0cm4yqVCZ44YD9m', 'KAYLA ZAHARA ANASTASYA', '87661416', '0087661416', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 17, 360),
(344, '$2y$10$GkOeTirEmY2act2iCGf1.OgVhowntb6a5H3ggXFMyZ2POhrnq6eqC', 'RIYANA RAHMATINA', '81375039', '0081375039', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 18, 364),
(345, '$2y$10$LyD4xYvUZXJ/MyjjS3h6HuMvG61olfUNDxlNc7NUJnIAcSerFvs3O', 'ROSIYANAH', '73164531', '0073164531', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 18, 364),
(346, '$2y$10$7XPMwxaAlPkqen1QEZxVX..PFYiLOa9F3y/vc/5dq2iLsBBFLJI/6', 'MEILISSA ANGGRAINI SOEDJANA', '87817622', '0087817622', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 18, 353),
(347, '$2y$10$yK9SDUXvAFSmLW0wn9XwHOKw8mg3OAxkf3K3Q/DesMuo7Ck/p6jW2', 'NANDA DIYAZ SOVIE', '87394332', '0087394332', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 18, 353),
(348, '$2y$10$By03/gbZe89GzoscivX34uqKOspzX0jFuZ1WrpGeCuVFimehGx54q', 'NITA DONITA', '87740139', '0087740139', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 18, 353),
(349, '$2y$10$t2tTwfP89EriWBSa83F.BeA2e0A9dsWPK6Gx9YuY.qVhrQzoKfQJm', 'MUHAMAD RENDI', '79025630', '0079025630', 'Laki-laki', 'XII DKV 2', 'Aktif', 2, 18, 353),
(350, '$2y$10$TrSg9bIem8MSUmkpvYj2AuKMhZwW9o4TBF4MvPaz65tNNb2/QtkSy', 'NONO SUBASTIAN', '84373256', '0084373256', 'Laki-laki', 'XII DKV 2', 'Aktif', 2, 18, 353),
(351, '$2y$10$UebopmAM6HD4oM8NiHyA7O9niVUweWg5SyKx/KW4w9w8gl1xAS6N2', 'ZULMI ALFARIZI', '83099015', '0083099015', 'Laki-laki', 'XII DKV 2', 'Aktif', 2, 18, 353),
(352, '$2y$10$30ayFrH3LtAc8A1qnGcWnuAUH0dWOT8HKFgmnl4YYwWXeOhXTGNjq', 'DELLA RISMA EFRILLIA', '83674369', '0083674369', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 16, 369),
(353, '$2y$10$LQCTAPcLd4FCPOCYN3b2pumSmvlosO7ghD7Vuef5z9mKT7h36LNrC', 'REFA APRIL LIANA', '87412707', '0087412707', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 16, 369),
(354, '$2y$10$uAJXxTj.AJnzGYapoDiopew2zpIB3T0r1wMGEH0LYdbMjXRfZk9Be', 'ASIYAH INDRIYANI', '82246687', '0082246687', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 17, 368),
(355, '$2y$10$N7pTVV3.wowAxX8L65kCHOroNWIluMAydg5ORTN3ZHfxJBwySHipW', 'PITRI NURCAHYA', '75866975', '0075866975', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 17, 368),
(356, '$2y$10$BNb4DoEdBjiLlUH6P/sff.loz8ePdVfu0HLE3Mv79BpC.BQfXW8uu', 'RATNASARI', '83492735', '0083492735', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 17, 368),
(357, '$2y$10$L0iTgA6fc5m9DQSREw8/Z.PsmqcnvolQemdTsFer.vf1k7n/VRRuG', 'DINDA DEWI', '79480408', '0079480408', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 17, 356),
(358, '$2y$10$62oZgW6Mlz71lCQN60DQheMhve4QDij5bLeT8jLJoGI3exJ5w6FgC', 'NOVA ARIYANTI', '73167741', '0073167741', 'Perempuan', 'XII RPL 1', 'Aktif', 2, 17, 356),
(359, '$2y$10$80DumFp6D8hxVF7hbzXUh.GSizJdQ1S9.W2XvHWJUVy353Ple5AxG', 'PERAWATI', '83334845', '0083334845', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 17, 356),
(360, '$2y$10$Nkba3X9r3fMfUuiruq.p2uU55sKoJTroxBg2R23T6j6gu5P0DGNNG', 'YARAH RIFANA', '77476193', '0077476193', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 17, 356),
(361, '$2y$10$ukGekwwKVC0UeNrgEbSmuurrEo/OI7hryFMBZseKoj5zZYB4jmbfu', 'AMELIA PUTRI LESTARI', '76512176', '0076512176', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 17, 358),
(362, '$2y$10$Ic3L82Z.UQNbhqH5W2hoQOBt4uElGfwj5m4AAAUmUNlqWDPhmuMIO', 'ANISAH ALFATONAH', '81755014', '0081755014', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 17, 358),
(363, '$2y$10$5Hs62ewRNI.pQ9YR2cD/O.9Ho/9BhojMC.6KvKxvstduhE7f95j06', 'DEDE MILA', '86950503', '0086950503', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 17, 358),
(364, '$2y$10$dij4qWMuQANmoKRnmpA0mebt8vPYihVcNzHxDHXuVQtA1MHTCqVri', 'SISI ANASTASYA MUZAHIDAH', '84229338', '0084229338', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 17, 358),
(365, '$2y$10$xs6Muzz32w5gp.dXOhwlKesb54V5t9YhjLlfvF8IOXXkWwzluuzL6', 'ADITYA KHOLIFATURROHMAN', '75260124', '0075260124', 'Laki-laki', 'XII DKV 2', 'Aktif', 2, 16, 357),
(366, '$2y$10$9iXcgZZtbXeDyJGFkUBpouVKHb0dZVPofdEx.TTj7LuhTgjjtY046', 'DETIAN ALFARIZI', '82919903', '0082919903', 'Laki-laki', 'XII DKV 2', 'Aktif', 2, 16, 357),
(367, '$2y$10$juLiATf5DQhUH.36jFMO9.M69w6W1m/QciQs..Nux3qO01FBdL2oO', 'INDRA DJATI SYAHDAN', '82515304', '0082515304', 'Laki-laki', 'XII DKV 2', 'Aktif', 2, 16, 357),
(368, '$2y$10$d2pimt/diPHdRgoYeZojSuJ1SXrqqB8dPfXBcyzudfSqTabGaYlSO', 'WAHYUDIN', '87485019', '0087485019', 'Laki-laki', 'XII DKV 2', 'Aktif', 2, 16, 357),
(369, '$2y$10$Z8MKxMshw8xGK8vL9B/FBunVLcOhk600AVvLPPfRJG1uOlI9aIFHO', 'MUHAMAD RAPIQ HABIBI', '82374311', '0082374311', 'Laki-laki', 'XII DKV 1', 'Aktif', 2, 16, 370),
(370, '$2y$10$epqONmZ/8Gvt/xSzKwl6j.cjIklkVT8SxxCRATLOqIbSvi6Ced1e6', 'MUHAMMAD AZIS SAEPUDIN', '84240288', '0084240288', 'Laki-laki', 'XII DKV 1', 'Aktif', 2, 16, 370),
(371, '$2y$10$s0FiebGPCSxAUZ40vW5Xtewjx30SspdqP3v60X3MF6/tDiRQ1G076', 'DEN BAGUS NARIZKI', '79140579', '0079140579', 'Laki-laki', 'XII DKV 1', 'Aktif', 2, 16, 295),
(372, '$2y$10$2H.noXfZL/i.HgHNM8WzNe5uO9kvr7gKD0wNzAGqkEOtXBtDw9kKe', 'DIDI ALFAZRI', '76267371', '0076267371', 'Laki-laki', 'XII DKV 1', 'Aktif', 2, 16, 295),
(373, '$2y$10$9LSyvEE1XPqAXM1Z5fcfE.bH3OtZRTvY95Gdm26MjGVTKtb5Adg.a', 'FABIAN OCTORA', '78402537', '0078402537', 'Laki-laki', 'XII DKV 1', 'Aktif', 2, 18, 371),
(374, '$2y$10$LoWk4CV.o/GOK9Xb5qXx5.Byz.wuUQslkBk30nurQs3n9m2TkOete', 'RENO', '79597279', '0079597279', 'Laki-laki', 'XII DKV 2', 'Aktif', 2, 18, 371),
(375, '$2y$10$LMb/nN1XhdSW43RCERlnfedb72gcTGFtp6HSuDoRPYbOKbzpixoJS', 'MARSYA ARDIANSYAH', '83506939', '0083506939', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 18, 366),
(376, '$2y$10$aQZKpSxqIesC.Q7Ao106quKzaJ3MR1G32XXDgwy6a0DJmH8VgS0Y6', 'TIARA SULISTIAWATI', '82726548', '0082726548', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 18, 366),
(377, '$2y$10$vehckD9mdBnCflsfUf9HEOny9nX0RcBBWV1qQ8RmNJY1amSI6aGOO', 'TORIKOH', '77722414', '0077722414', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 18, 366),
(378, '$2y$10$R01RappsP3Ra/i95dldJsecqaqFR4vKiV8e0SmC75WcqBrJNug/VO', 'ADINDA SAFA\'ATUN HASANAH', '72832483', '0072832483', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 16, 359),
(379, '$2y$10$DEd3z..C0nt9jGzV/F7yP.csEmMmazGh96DHcNrs/05UmVLyQVYVi', 'ANIRA NATASYA', '82943356', '0082943356', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 16, 359),
(380, '$2y$10$.etGGpUpUz.q/6QEcKGnPO6kkaH1cD4QU3wI7EjBh4CCLFWpQn1fu', 'DEA SAPITRI', '88909682', '0088909682', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 16, 359),
(381, '$2y$10$R0qp0pa9eh5kqgjIUfs.A.WJ50fEXzIiHQx7EgFdEMgAx.VlsThhG', 'NAISYAH', '81989403', '0081989403', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 16, 359),
(382, '$2y$10$ZzN0dAWVtgVel/N39w95tu8wWdvViY7ASc/45Ee1tozu6mersrT8u', 'AULIA ZAHRA SALSA BILA', '87018795', '0087018795', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 18, 352),
(383, '$2y$10$3k4ovehMezKMYGeCwckFKuNUhJbybkkLAoHs.R7oDkn1XvirqAnP6', 'HANI LIA APRILIYANTI', '87778171', '0087778171', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 18, 352),
(384, '$2y$10$tPHMvJA7KCtc5sq2VVCeBuBKiNfKDZnB3NgyYCZHknfpqvTlWek9y', 'JELITA MUTIA NINGSIH', '83951875', '0083951875', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 18, 352),
(385, '$2y$10$CzIU5cXXlR4p3aQfyQE0S.xv82CVbSsoG4lmK3aL1WIvalncEL.92', 'MEGA JULIA WATI', '89423600', '0089423600', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 18, 352),
(386, '$2y$10$M69PzWZHRNwhW.psPO245OF/k8UgN9qwTuNGT186Jghl/7hcvsfJ.', 'NAZWA APRILIA PUTRI', '87772071', '0087772071', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 18, 352),
(387, '$2y$10$nEwVSOPm430Px4iweF4ZLOn/e4R5dv3kzPW5YPWYZxNJwfxqelOPm', 'NURUS SYARIPAH', '85018711', '0085018711', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 18, 352),
(388, '$2y$10$./BBhORY7OrkbKZmSmeYPOrvzMzOz3nVbQnzfrJBFBZIdlAp30WGS', 'RIRIN HERLINDA', '85015634', '0085015634', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 18, 352),
(389, '$2y$10$7hp4ROZFiackngqP4UbfH.bX5NxRdght8UJN2gawp0LKrN3dwSy.a', 'SIFA NURIPIANTI', '83627657', '0083627657', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 18, 352),
(390, '$2y$10$U5qeDwP/O1gALsgKler7n.QJ/gEhQ4WJaWo.fy77IySXmLK0.PG7K', 'MARSYALINA DWI KAMIDYA PUTRI', '81972114', '0081972114', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 18, 367),
(391, '$2y$10$rZreV7GN8qaEmeox2rB0r.kObmRg3VqqDYiHLsESnipRNPwriHHzG', 'TIARA', '72893918', '0072893918', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 18, 367),
(392, '$2y$10$.t5pjJ6tbEdD2iMVJYXlfetZvy9BL7eloAKaQQWnMLVh7R2OHRaxi', 'TITA PUSPITA RAMDHANI', '82942561', '0082942561', 'Perempuan', 'XII DKV 1', 'Aktif', 2, 18, 367),
(393, '$2y$10$tvN1EZNt5Wsn33nefE/Nyey98aiCJ8pBsua7YD3xcqGVDDes7x0GC', 'ANANDA NUR ROHMAN', '88653226', '0088653226', 'Laki-laki', 'XII DKV 1', 'Aktif', 2, 17, 355),
(394, '$2y$10$.fFtb2Q2JWgQ20Q6V0.6eO9RKFcm6rWzisSDqCwQjww7WVvFvTyt6', 'JANWARUDIN', '81257107', '0081257107', 'Laki-laki', 'XII DKV 1', 'Aktif', 2, 17, 355),
(395, '$2y$10$E10u.w5kufTaKwGztlhbre0MLj5tmQU9bMAoxNFeKYNRsD9OlhOeG', 'KEVIN APRILIO', '84356894', '0084356894', 'Laki-laki', 'XII DKV 1', 'Aktif', 2, 17, 355),
(396, '$2y$10$mUtRIG6SnG6z4tLoFvE1GO6wLlPuUkUHxVIhnXEZeJoX2lE7qx0n.', 'KHAERUL ARDIANSYAH', '87795275', '0087795275', 'Laki-laki', 'XII DKV 1', 'Aktif', 2, 17, 355),
(397, '$2y$10$vbwna8yN8B6.B0SxcUt6z.WbLML77F6Wk1.VRJsWa4QujBPnrTX8m', 'MIA FEBRIYANTI', '82124362', '0082124362', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 16, 362),
(398, '$2y$10$rrGbz/ZIvyVbu8wJrv/kDOygcN.BUcyBMcRhazkrbb077pVlstevO', 'NOVI ANDINI', '138705291', '00138705291', 'Perempuan', 'XII DKV 2', 'Aktif', 2, 16, 362),
(399, '$2y$10$ZGJ0pmhCtLUTAzYw9Up9ZukrHNCEGCqUnLeOn398Qc3eFJf2BPnJa', 'PIRLO HIDAYAT', '67550883', '0067550883', 'Laki-laki', 'XII DKV 2', 'Aktif', 2, 17, 361),
(400, '$2y$10$ftfqGMZqwgkkRw95k7TBAuFRAN2DNhUE.I.2xiauXBcBsh95ovyOO', 'SOPIYAN', '88323462', '0088323462', 'Laki-laki', 'XII DKV 2', 'Aktif', 2, 17, 361),
(403, '$2y$10$D5b0bbXHu.NUrZj724Hw..zFmeazoWrrpB4s4irLZxPYqVeIfaaLG', 'Warzz', '222', '132132', 'Laki-laki', 'XII DKV 2', 'Aktif', 2, 8, 300),
(404, '$2y$10$gMWC6IzFJKZWjydQZp5ZBO.AKx2cAl8yCUEODKd7/4ETGjaY0y7s6', 'EASY', '2222', '1321322', 'Laki-laki', 'XII DPIB 2', 'Aktif', 7, 2, 300);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tempat_pkl`
--

CREATE TABLE `tempat_pkl` (
  `id_tempat_pkl` int NOT NULL,
  `nama_tempat_pkl` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci NOT NULL,
  `nama_instruktur` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_kontak` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kuota_siswa` int DEFAULT '0',
  `jurusan_id` int NOT NULL,
  `sabtu_masuk` enum('Ya','Tidak') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Tidak' COMMENT 'Apakah siswa di tempat PKL ini masuk hari Sabtu?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tempat_pkl`
--

INSERT INTO `tempat_pkl` (`id_tempat_pkl`, `nama_tempat_pkl`, `alamat`, `nama_instruktur`, `alamat_kontak`, `kuota_siswa`, `jurusan_id`, `sabtu_masuk`) VALUES
(294, 'BPN Sumedang', '', '', '', 7, 1, 'Tidak'),
(295, 'LKP Execom Haurgeulis', '', '', '', 2, 1, 'Tidak'),
(296, 'Disdukcapil Subang', '', '', '', 7, 1, 'Tidak'),
(297, 'Universitas Subang', '', '', '', 5, 1, 'Tidak'),
(298, 'Haurgeulis Education Center (HEC) Haurgeulis', '', '', '', 4, 1, 'Tidak'),
(299, 'PTPN Ciater', '', '', '', 4, 1, 'Tidak'),
(300, 'Pengadilan Negeri Subang', '', '', '', 4, 1, 'Tidak'),
(301, 'Disnakertrans ESDM Subang', '', '', '', 4, 1, 'Tidak'),
(302, 'Dinas Pertanian Subang', '', '', '', 3, 1, 'Tidak'),
(303, 'SDN Cadasngampar', '', '', '', 2, 1, 'Tidak'),
(304, 'PT. TELKOM Haurgeulis', '', '', '', 2, 1, 'Tidak'),
(305, 'POLRES Subang', '', '', '', 2, 1, 'Tidak'),
(306, 'Syafa Husada Klinik Gantar', '', '', '', 2, 1, 'Tidak'),
(307, 'Badan Kesatuan Bangsa dan Politik Bekasi', '', '', '', 1, 1, 'Tidak'),
(308, 'PT. MEDCO AMERA JAKARTA', '', '', '', 1, 1, 'Tidak'),
(309, 'PT. MEDKO AMPERA JAKARTA', '', '', '', 1, 1, 'Tidak'),
(310, 'BENGKEL EAS ERIK AUTO SERVICE', '', '', '', 5, 3, 'Tidak'),
(311, 'PT INDOMOBIL TRADA INTERNASIONAL KARAWANG', '', '', '', 5, 3, 'Tidak'),
(312, 'MUKTI AGUNG', '', '', '', 6, 3, 'Tidak'),
(313, 'BENGKEL MOBIL ATIM', '', '', '', 3, 3, 'Tidak'),
(314, 'GLOBAL MOTOR', '', '', '', 4, 3, 'Tidak'),
(315, 'BENGKEL MOBIL A 8', '', '', '', 2, 3, 'Tidak'),
(316, 'BENGKEL MOBIL AN MOTOR', '', '', '', 6, 3, 'Tidak'),
(317, 'BENGKEL ROBI MOTOR', '', '', '', 4, 3, 'Tidak'),
(318, 'GLORI MOTOR', '', '', '', 4, 3, 'Tidak'),
(319, 'MEKAR MUDA MOTOR', '', '', '', 4, 3, 'Tidak'),
(320, 'BENGKEL JEMBAR MOTOR', '', '', '', 6, 3, 'Tidak'),
(321, 'BENGKEL ANUGRAH WIJAYA MOTOR', '', '', '', 5, 3, 'Tidak'),
(322, 'PRIMA MOTOR', '', '', '', 4, 3, 'Tidak'),
(323, 'SLAMET JAYA ABADI MOTOR', '', '', '', 2, 3, 'Tidak'),
(324, 'PT SUN STAR PRIMA MOTOR MITSUBISHI', '', '', '', 2, 3, 'Tidak'),
(325, 'PT CINTA DAMAI PUTRA BAHAGIA', '', '', '', 2, 3, 'Tidak'),
(326, 'PT CITRA ASRI BUANA', '', '', '', 2, 3, 'Tidak'),
(327, 'JASUTRA MOTOR', '', '', '', 2, 3, 'Tidak'),
(328, 'ZIA MOTOR', '', '', '', 4, 3, 'Tidak'),
(329, 'BENGKEL FAMILY MANDIRI', '', '', '', 1, 3, 'Tidak'),
(330, 'BENGKEL KIARA AUTO', '', '', '', 1, 3, 'Tidak'),
(331, 'LUPIN MOTOR', '', '', '', 2, 3, 'Tidak'),
(332, 'RALLY VARIASI', '', '', '', 1, 3, 'Tidak'),
(333, 'PT ASAHI INDONESIA', '', '', '', 3, 3, 'Tidak'),
(334, 'BENGKEL MOBIL RAHEL MOTOR', '', '', '', 1, 3, 'Tidak'),
(335, 'DAYAT MOTOR', '', '', '', 5, 3, 'Tidak'),
(336, 'AL-IANDA', '', '', '', 2, 3, 'Tidak'),
(337, 'BENGKEL MOBIL MANG ATEP', '', '', '', 3, 3, 'Tidak'),
(338, 'MIADI MOTOR', 'JL. Raya Cimalaka No.138 Sumedang', '-', '-', 10, 3, 'Tidak'),
(339, 'AZMI CAR', '', '', '', 4, 3, 'Tidak'),
(340, 'JAJA LAS', 'BLOK MANDIRANCAN RT 21 RW 06 DESA HAURGEULIS KEC.HAURGEULIS KAB.INDRAMAYU', 'SARJA', '', 2, 4, 'Tidak'),
(341, 'BM STAR', 'BJL.RAYA HAURGEULIS-PATROL DESA KEDUNGWUNGU KEC.ANJATAN KAB.INDRAMAYU', 'SUGIONO', '', 4, 4, 'Tidak'),
(342, 'IFAN JAYA LAS', 'BLOK PILANG DESA BALERAJA KEC.GANTAR KAB.INDRAMAYU', 'ARIFIN', '', 3, 4, 'Tidak'),
(343, 'AL IANDA MOTOR', 'BLOK KUBANGJATI DESA BALERAJA KEC.GANTAR KAB.INDRAMAYU', 'HENDRA', '', 2, 4, 'Tidak'),
(344, 'MEKARJAYA', 'JL.ARIEF RAHMAN HAKIM NO.18 PINTU UYUH SUBANG', 'H.ENGKOS KOSASIH', '', 2, 4, 'Tidak'),
(345, 'ACHENK KARYA ABADI', 'JL.KH DEWANTARA RT 36 RW 18 KELURAHAN DANGDEUR KEC.SUBANG', 'AAH ROHENDI', '', 2, 4, 'Tidak'),
(346, 'CONDONG TEKNIK', 'JL.SUNAN GUNUNG JATI BLOK SANJULARA DESA ASTANA CIREBON', 'ZAENUL ARIFIN', '', 2, 4, 'Tidak'),
(347, 'Q-BAL', 'JL ARIEF RAHMAN HAKIM SUBANG', 'ELIH MUSLIH', '', 3, 4, 'Tidak'),
(348, 'WANAKAYA LAS', 'WANAKAYA BLOK 1 JL CIPUNAGARA HAURGEULIS', 'YAYAT SUPIYATNA', '', 2, 4, 'Tidak'),
(349, 'PT.SANOH INDONESIA', 'JL INTI II NO.10 BLOK C-4,SUKRESMI CIKARANG SELATAN KAB.BEKASI', 'MISBAHUL MUNIR', '', 2, 4, 'Tidak'),
(350, 'CV.MULTI GUNA UTAMA', 'JL.RAYA PARUNG RT 04 RW 02 KELURAHAN PARUNG SUBANG', 'ADE KOSASIH', '', 4, 4, 'Tidak'),
(351, 'RAKA MANDIRI', 'JL. DI PANJAITAN, SOKLAT SUBANG', 'HERMAWAN', '', 3, 4, 'Tidak'),
(352, 'Prime Studio Subang', '', '', '', 5, 2, 'Tidak'),
(353, 'CIHO Printshop Sumedang', '', '', '', 5, 2, 'Tidak'),
(354, 'Auni Digital Printing Anjatan', '', '', '', 4, 2, 'Tidak'),
(355, 'Puja Photocopy Gantar', '', '', '', 3, 2, 'Tidak'),
(356, 'Happy Digital Printing Gabuswetan', '', '', '', 3, 2, 'Tidak'),
(357, 'Kencana Putra Digital Printing - Subang', '', '', '', 3, 2, 'Tidak'),
(358, 'HEC HAURGEULIS (Haurgeulis Education Centre)', '', '', '', 3, 2, 'Tidak'),
(359, 'Perdana Photo Lab dan Studio Subang', '', '', '', 3, 2, 'Tidak'),
(360, 'Bayu Copy Digital Gantar', '', '', '', 3, 2, 'Tidak'),
(361, 'VJ Studio Bongas - Indramayu', '', '', '', 2, 2, 'Tidak'),
(362, 'Ruang Photo Haurgeulis', '', '', '', 2, 2, 'Tidak'),
(363, 'Abadi Photoworks Subang', '', '', '', 2, 2, 'Tidak'),
(364, 'Bestro Project Sumedang', '', '', '', 2, 2, 'Tidak'),
(365, 'LKP Execom Haurgeulis', '', '', '', 10, 2, 'Tidak'),
(366, 'Percetakan Dea Grafika Subang', '', '', '', 2, 2, 'Tidak'),
(367, 'Pubdok Photografi Sumedang', '', '', '', 2, 2, 'Tidak'),
(368, 'Dizah Printing Bongas', '', '', '', 2, 2, 'Tidak'),
(369, 'CV. Sumber Mandiri Printshop Subang', '', '', '', 2, 2, 'Tidak'),
(370, 'Kuens Photo Studio Haurgeulis', '', '', '', 1, 2, 'Tidak'),
(371, 'Nusa Edu Bandung', '', '', '', 1, 2, 'Tidak'),
(372, 'PRIMKOPOL POLRES SUBANG', '', '', '', 8, 8, 'Tidak'),
(373, 'CV. TRIDAYA ANUGERAH SUKSES', '', '', '', 5, 8, 'Tidak'),
(374, 'KPP Pratama Subang', '', '', '', 5, 8, 'Tidak'),
(375, 'KANTOR POS Anjatan', '', '', '', 5, 8, 'Tidak'),
(376, 'UPK DAPM GANTAR AGUNG', '', '', '', 4, 8, 'Tidak'),
(377, 'Universitas Subang', '', '', '', 4, 8, 'Tidak'),
(378, 'PENGADILAN NEGERI SUBANG KELAS 1B', '', '', '', 4, 8, 'Tidak'),
(379, 'BANK BPR PK BONGAS', '', '', '', 4, 8, 'Tidak'),
(380, 'KSP MALER Group Anjatan', '', '', '', 4, 8, 'Tidak'),
(381, 'FIF Group Gantar', '', '', '', 4, 8, 'Tidak'),
(382, 'Kantor Pemerintah Daerah Subang', '', '', '', 3, 8, 'Tidak'),
(383, 'DEASSY SUKSES MULTI USAHA', '', '', '', 3, 8, 'Tidak'),
(384, 'KSPPS BMT NU Umat Sejahtera Haurgeulis', '', '', '', 3, 8, 'Tidak'),
(385, 'BANK BJB KCP HAURGEULIS', '', '', '', 3, 8, 'Tidak'),
(386, 'FIF Group Haurgeulis', '', '', '', 3, 8, 'Tidak'),
(387, 'BANK BJB KCP HAURGEULIS', '', '', '', 2, 8, 'Tidak'),
(388, 'PUSAT PENGELOLA PENDAPATAN DAERAH WILAYAH KAB. INDRAMAYU II HAURGEULIS', '', '', '', 2, 8, 'Tidak'),
(389, 'Notaris Makhrom Ismail, SH., M.Kn.', '', '', '', 2, 8, 'Tidak'),
(390, 'Kantor Kecamatan Haurgeulis', '', '', '', 2, 8, 'Tidak'),
(391, 'Kantor POS Haurgeulis', '', '', '', 2, 8, 'Tidak'),
(392, 'KSPPS BMT AN-NAJAH HAURGEULIS', '', '', '', 1, 8, 'Tidak'),
(393, 'TRIDAYA MOTOR CIDANCUN', '', '', '', 1, 8, 'Tidak'),
(394, 'PT. Shinta Indah Jaya Factory', '', '', '', 1, 8, 'Tidak'),
(395, 'DEKOPINDA KAB.SUBANG', '', '', '', 1, 8, 'Tidak'),
(396, 'DEKPSDM KAB. SUBANG', '', '', '', 1, 8, 'Tidak'),
(397, 'Bank CIMB / BPR Cabang Kroya', '', '', '', 1, 8, 'Tidak'),
(398, 'Notaris Pendil Paundrakarna Gantar', '', '', '', 1, 8, 'Tidak'),
(399, 'PT Pegadaian Cabang Anjatan', '', '', '', 1, 8, 'Tidak'),
(400, 'UPK PNPM Haurgeulis', '', '', '', 1, 8, 'Tidak'),
(401, 'LAFI PUSKESAD', 'Jl. Gudang Utara No. 25-26, Bandung - Jawa Barat 40113', '', '', 24, 5, 'Tidak'),
(402, 'RS. BHAYANGKARA INDRAMAYU', 'Jl. Raya Pantura Km.73-75, Kec. Losarang-Indramayu 45253', '', '', 6, 5, 'Tidak'),
(403, 'PUSKESMAS HAURGEULIS', 'Jl. Siliwangi No.61, Kec. Haurgeulis-Indramayu 45264', '', '', 5, 5, 'Tidak'),
(404, 'BPN Subang', '  ', ' ', '   ', 2, 1, 'Tidak'),
(406, 'PT PLN (PERSERO) HAURGEULIS ', 'Jl. Jenderal Sudirman, Cipancuh Kec. Haurgeulis Kab. Indramayu, 45264', 'NULL', 'NULL', 1, 1, 'Tidak'),
(407, 'Kantor Pertanahan Kab. Sumedang - ATR/BPN', 'Jl. Pangeran Kornel No.264, Pasanggrahan Baru, Kec. Sumedang Sel., Kab. Sumedang, 45311', 'NULL', 'NULL', 1, 1, 'Tidak'),
(408, 'Kantor Pertanahan Kab. Subang', 'Jl. Mayjen Sutoyo Siswomiharjo No.44, Karanganyar, Kec. Subang, Kab. Subang, 41211', 'NULL', 'NULL', 1, 1, 'Tidak');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi_siswa`
--
ALTER TABLE `absensi_siswa`
  ADD PRIMARY KEY (`id_absensi`),
  ADD UNIQUE KEY `idx_siswa_tanggal_unique` (`siswa_id`,`tanggal_absen`);

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
-- AUTO_INCREMENT untuk tabel `absensi_siswa`
--
ALTER TABLE `absensi_siswa`
  MODIFY `id_absensi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `guru_pembimbing`
--
ALTER TABLE `guru_pembimbing`
  MODIFY `id_pembimbing` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `jurnal_harian`
--
ALTER TABLE `jurnal_harian`
  MODIFY `id_jurnal_harian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT untuk tabel `jurnal_kegiatan`
--
ALTER TABLE `jurnal_kegiatan`
  MODIFY `id_jurnal_kegiatan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT untuk tabel `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id_jurusan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=405;

--
-- AUTO_INCREMENT untuk tabel `tempat_pkl`
--
ALTER TABLE `tempat_pkl`
  MODIFY `id_tempat_pkl` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=409;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi_siswa`
--
ALTER TABLE `absensi_siswa`
  ADD CONSTRAINT `absensi_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_siswa_jurusan` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_siswa_pembimbing` FOREIGN KEY (`pembimbing_id`) REFERENCES `guru_pembimbing` (`id_pembimbing`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_siswa_tempat_pkl` FOREIGN KEY (`tempat_pkl_id`) REFERENCES `tempat_pkl` (`id_tempat_pkl`) ON UPDATE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`pembimbing_id`) REFERENCES `guru_pembimbing` (`id_pembimbing`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_3` FOREIGN KEY (`tempat_pkl_id`) REFERENCES `tempat_pkl` (`id_tempat_pkl`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tempat_pkl`
--
ALTER TABLE `tempat_pkl`
  ADD CONSTRAINT `fk_tempat_pkl_jurusan` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tempat_pkl_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id_jurusan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
