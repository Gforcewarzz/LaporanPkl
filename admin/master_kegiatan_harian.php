<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Harian PKL</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5667FF;
            /* Biru Ungu Khas Material */
            --primary-dark: #4A5AEB;
            /* Sedikit lebih gelap */
            --accent-green: #4CAF50;
            /* Hijau Material */
            --accent-red: #F44336;
            /* Merah Material */
            --text-main: #212529;
            /* Hampir hitam */
            --text-secondary: #616161;
            /* Abu-abu tua */
            --bg-light: #F2F5F9;
            /* Latar belakang sangat lembut */
            --card-bg: #FFFFFF;
            --border-color: #E0E0E0;
            /* Border abu-abu terang */
            --shadow-subtle: rgba(0, 0, 0, 0.08);
            /* Bayangan sedikit lebih jelas */
            --shadow-strong: rgba(0, 0, 0, 0.15);
            /* Bayangan kuat untuk hover */
        }

        body {
            font-family: 'Poppins', sans-serif;
            /* Menggunakan Poppins, lebih elegan */
            background-color: var(--bg-light);
            color: var(--text-main);
            line-height: 1.6;
            /* Line-height lebih nyaman */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        .container-fluid {
            padding: 30px;
            /* Padding lebih besar di desktop */
        }

        /* Page Header - Tampilan yang disempurnakan */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            /* Margin lebih besar */
            padding: 35px 25px;
            /* Padding lebih besar */
            background: linear-gradient(45deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            /* Gradien diagonal */
            color: var(--card-bg);
            border-radius: 16px;
            /* Sudut lebih membulat */
            box-shadow: 0 10px 25px var(--shadow-strong);
            /* Bayangan lebih kuat */
            position: relative;
            overflow: hidden;
            animation: headerSlideIn 0.8s ease-out forwards;
            position: relative;
            /* Untuk pseudo-element background */
        }

        /* Pseudo-element untuk efek visual di header */
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><circle cx="25" cy="25" r="5" fill="rgba(255,255,255,0.1)"></circle><circle cx="75" cy="75" r="5" fill="rgba(255,255,255,0.1)"></circle></svg>') repeat;
            background-size: 20px 20px;
            opacity: 0.8;
            pointer-events: none;
        }

        @keyframes headerSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-header h2 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2.8rem;
            /* Ukuran font lebih besar */
            letter-spacing: -0.8px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
            /* Sedikit bayangan teks */
        }

        .page-header p {
            font-size: 1.1rem;
            /* Ukuran font lebih besar */
            opacity: 0.95;
            max-width: 600px;
            /* Batasi lebar deskripsi */
            margin: 0 auto;
        }

        /* Detail Peserta Section */
        .detail-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 5px 15px var(--shadow-subtle);
            padding: 30px;
            /* Padding lebih besar */
            margin-bottom: 35px;
            /* Margin lebih besar */
            border: 1px solid var(--border-color);
            animation: cardFadeIn 0.6s ease-out 0.2s forwards;
            opacity: 0;
        }

        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .detail-item {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            /* Jarak antar item lebih besar */
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-item strong {
            color: var(--text-secondary);
            min-width: 160px;
            /* Lebar label yang lebih konsisten */
            margin-right: 20px;
            /* Jarak lebih luas */
            font-weight: 500;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .detail-item span {
            color: var(--text-main);
            font-weight: 600;
            flex-grow: 1;
            font-size: 1rem;
            /* Ukuran font lebih besar */
            word-break: break-word;
        }

        /* Penyesuaian untuk Mobile Detail Peserta */
        @media (max-width: 767.98px) {
            .container-fluid {
                padding: 20px 15px;
            }

            .detail-card {
                padding: 20px;
            }

            .detail-item {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 10px;
            }

            .detail-item strong {
                min-width: unset;
                margin-bottom: 4px;
                font-size: 0.9rem;
                margin-right: 0;
            }

            .detail-item span {
                font-size: 0.95rem;
                text-align: left;
                width: 100%;
            }
        }


        /* Action Buttons Section */
        .action-buttons-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 5px 15px var(--shadow-subtle);
            padding: 25px;
            /* Padding lebih besar */
            margin-bottom: 35px;
            /* Margin lebih besar */
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            border: 1px solid var(--border-color);
            animation: cardFadeIn 0.6s ease-out 0.3s forwards;
            opacity: 0;
        }

        .action-buttons-card .btn {
            font-size: 0.95rem;
            /* Ukuran font tombol lebih besar */
            padding: 12px 20px;
            /* Padding tombol lebih besar */
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .action-buttons-card .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px var(--shadow-subtle);
        }

        .action-buttons-card .btn-secondary {
            background-color: #6C757D;
            border-color: #6C757D;
            color: white;
        }

        .action-buttons-card .btn-secondary:hover {
            background-color: #5A6268;
            border-color: #545B62;
        }

        .action-buttons-card .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .action-buttons-card .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .action-buttons-card .btn-outline-danger {
            color: var(--accent-red);
            border-color: var(--accent-red);
        }

        .action-buttons-card .btn-outline-danger:hover {
            background-color: var(--accent-red);
            color: white;
        }

        .action-buttons-card .btn-outline-success {
            color: var(--accent-green);
            border-color: var(--accent-green);
        }

        .action-buttons-card .btn-outline-success:hover {
            background-color: var(--accent-green);
            color: white;
        }

        .action-buttons-card .btn-group-left,
        .action-buttons-card .btn-group-right {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }

        .action-buttons-card .btn-group-left .btn,
        .action-buttons-card .btn-group-right .btn {
            margin-bottom: 8px;
        }

        .action-buttons-card .btn-group-left .btn:not(:last-child),
        .action-buttons-card .btn-group-right .btn:not(:last-child) {
            margin-right: 15px;
            /* Jarak kanan antar tombol lebih besar */
        }

        /* Responsif Tombol Aksi */
        @media (max-width: 767.98px) {
            .action-buttons-card {
                flex-direction: column;
                align-items: stretch;
                padding: 15px;
            }

            .action-buttons-card .btn-group-left,
            .action-buttons-card .btn-group-right {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
            }

            .action-buttons-card .btn-group-left {
                margin-bottom: 15px;
            }

            .action-buttons-card .btn-group-left .btn,
            .action-buttons-card .btn-group-right .btn {
                width: 100%;
                margin-right: 0 !important;
                margin-bottom: 10px !important;
            }

            .action-buttons-card .btn-group-left .btn:last-child,
            .action-buttons-card .btn-group-right .btn:last-child {
                margin-bottom: 0 !important;
            }
        }


        /* Daily Log Table - Perbaikan Styling */
        .log-table-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 5px 15px var(--shadow-subtle);
            padding: 30px;
            /* Padding lebih besar */
            overflow-x: auto;
            border: 1px solid var(--border-color);
            animation: cardFadeIn 0.6s ease-out 0.4s forwards;
            opacity: 0;
        }

        .log-table-card h5 {
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 25px;
            font-size: 1.3rem;
            /* Ukuran font lebih besar */
            text-align: left;
            /* Pastikan rata kiri di desktop */
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            /* Pastikan tabel mengisi lebar penuh container */
        }

        .table thead th {
            border-bottom: 3px solid var(--primary-color);
            /* Garis bawah header lebih tebal */
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
            padding: 16px 15px;
            /* Padding lebih besar */
            font-size: 0.95rem;
            /* Font lebih besar */
            background-color: #F8F8F8;
            /* Latar belakang header lebih netral */
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            vertical-align: top;
            padding: 16px 15px;
            /* Padding lebih besar */
            font-size: 0.95rem;
            /* Font lebih besar */
            color: var(--text-main);
            border-top: 1px solid var(--border-color);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(86, 103, 255, 0.07);
            /* Hover effect dengan warna primary */
        }

        .table .dropdown-toggle {
            font-size: 1.2rem;
            /* Ukuran ikon titik tiga lebih besar */
            color: var(--text-secondary);
            opacity: 0.8;
            transition: opacity 0.2s ease;
        }

        .table .dropdown-toggle:hover {
            opacity: 1;
        }

        .table .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            /* Bayangan dropdown lebih kuat */
            border: 1px solid var(--border-color);
        }

        .table .dropdown-menu .dropdown-item {
            font-size: 0.9rem;
            /* Font item dropdown lebih besar */
            padding: 10px 18px;
            /* Padding item dropdown lebih besar */
        }

        .table .dropdown-menu .dropdown-item:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Custom styling for 'Catatan' column to handle long text on desktop */
        .table tbody td[data-label="Catatan"] {
            max-width: 400px;
            /* Batasi lebar kolom catatan di desktop */
            word-wrap: break-word;
            white-space: normal;
        }


        /* Responsive Table for Small Screens (Card View) - Disempurnakan */
        @media (max-width: 767.98px) {
            .log-table-card {
                padding: 15px;
            }

            .log-table-card h5 {
                text-align: center;
                margin-bottom: 20px;
            }

            .table {
                border-radius: 10px;
            }

            .table thead {
                display: none;
            }

            .table tbody,
            .table tr,
            .table td {
                display: block;
                width: 100%;
            }

            .table tr {
                background-color: var(--card-bg);
                margin-bottom: 15px;
                border: 1px solid var(--border-color);
                border-radius: 10px;
                box-shadow: 0 3px 10px var(--shadow-subtle);
                /* Bayangan lebih halus di mobile */
                padding: 15px;
                position: relative;
            }

            .table td {
                text-align: right;
                /* Default data di kanan */
                padding-left: 15px;
                /* Padding umum */
                position: relative;
                border: none;
                border-bottom: 1px dashed var(--border-color);
                /* Garis putus-putus */
                padding-top: 10px;
                padding-bottom: 10px;
            }

            .table td:first-child {
                border-top-left-radius: 10px;
                border-top-right-radius: 10px;
            }

            .table td:last-child {
                border-bottom: 0;
            }

            .table td::before {
                content: attr(data-label);
                position: relative;
                /* Relative agar label dan data di baris terpisah */
                display: block;
                width: 100%;
                white-space: normal;
                text-align: left;
                /* Label rata kiri */
                font-weight: 600;
                color: var(--text-secondary);
                font-size: 0.85rem;
                text-transform: uppercase;
                margin-bottom: 5px;
                /* Jarak antara label dan data */
            }

            .table .dropdown {
                position: absolute;
                right: 10px;
                top: 10px;
            }

            .table .dropdown-menu {
                left: unset !important;
                right: 0 !important;
                transform: translate3d(0, 30px, 0) !important;
            }

            .table .text-center {
                text-align: right !important;
                /* Nomor tetap rata kanan di mobile */
            }
        }

        .card-footer {
            font-size: 0.85rem;
            /* Ukuran font footer lebih besar */
            color: var(--text-secondary);
            text-align: end;
            margin-top: 20px;
            /* Margin lebih besar */
            padding-top: 10px;
            /* Padding di atas teks */
            border-top: 1px solid var(--border-color);
            /* Garis di atas footer */
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="text-center mb-5 p-5 text-white rounded-4 shadow-lg position-relative overflow-hidden" style="
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); 
        font-family: 'Poppins', sans-serif;">
            <i class="fas fa-book-open fa-4x mb-3" style="opacity: 0.9;"></i>
            <h2 class="fw-bold mb-2 display-5" style="letter-spacing: -1px; text-shadow: 2px 2px 5px rgba(0,0,0,0.3);">
                Jurnal Kegiatan PKL
            </h2>
            <p class="fs-5 opacity-75 mx-auto" style="max-width: 700px; font-weight: 300;">
                Catatan harian praktik kerja lapangan Anda, didokumentasikan dengan rapi dan detail.
            </p>
        </div>

        <div class="detail-card">
            <div class="row">
                <div class="col-md-6 col-12">
                    <div class="detail-item">
                        <strong>Nama Peserta Didik:</strong>
                        <span>John Doe</span>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="detail-item">
                        <strong>Tempat PKL:</strong>
                        <span>PT. Inovasi Digital</span>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="detail-item">
                        <strong>Nama Instruktur:</strong>
                        <span>Bpk. Joni Iskandar</span>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="detail-item">
                        <strong>Nama Guru Pembimbing:</strong>
                        <span>Ibu Retno Sari</span>
                    </div>
                </div>

            </div>
        </div>

        <div class="action-buttons-card">
            <div class="btn-group-left">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <a href="master_kegiatan_harian_add.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tambah Catatan
                </a>
            </div>
            <div class="btn-group-right">
                <button type="button" class="btn btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </button>
                <button type="button" class="btn btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </button>
            </div>
        </div>

        <div class="log-table-card">
            <h5>Daftar Catatan Kegiatan Harian</h5>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="text-center">No.</th>
                        <th>Hari/Tanggal</th>
                        <th>Pekerjaan</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center" data-label="No.">1</td>
                        <td data-label="Hari/Tanggal">Senin, 17 Juni 2024</td>
                        <td data-label="Pekerjaan">Pengembangan Website (Backend)</td>
                        <td data-label="Catatan">Mempelajari dasar-dasar sintaks PHP dan framework Laravel. Melakukan
                            instalasi Composer dan konfigurasi awal project. Memulai CRUD sederhana untuk data user.
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="jurnal_edit.php?id=J001"><i
                                                class="fas fa-edit me-2"></i> Edit</a></li>
                                    <li><a class="dropdown-item" href="jurnal_delete.php?id=J001"><i
                                                class="fas fa-trash-alt me-2"></i> Hapus</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center" data-label="No.">2</td>
                        <td data-label="Hari/Tanggal">Selasa, 18 Juni 2024</td>
                        <td data-label="Pekerjaan">Desain Grafis (Promosi)</td>
                        <td data-label="Catatan">Membantu mendesain banner promosi untuk event perusahaan menggunakan
                            Adobe Illustrator. Menerapkan feedback dari tim marketing dan melakukan revisi 2 kali.
                            Finalisasi desain untuk cetak.</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="jurnal_edit.php?id=J002"><i
                                                class="fas fa-edit me-2"></i> Edit</a></li>
                                    <li><a class="dropdown-item" href="jurnal_delete.php?id=J002"><i
                                                class="fas fa-trash-alt me-2"></i> Hapus</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center" data-label="No.">3</td>
                        <td data-label="Hari/Tanggal">Rabu, 19 Juni 2024</td>
                        <td data-label="Pekerjaan">Instalasi Jaringan & Konfigurasi</td>
                        <td data-label="Catatan">Melakukan instalasi kabel LAN di ruang server baru. Membantu
                            konfigurasi dasar router MikroTik untuk pembagian bandwidth antar divisi. Menguji
                            konektivitas jaringan.</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="jurnal_edit.php?id=J003"><i
                                                class="fas fa-edit me-2"></i> Edit</a></li>
                                    <li><a class="dropdown-item" href="jurnal_delete.php?id=J003"><i
                                                class="fas fa-trash-alt me-2"></i> Hapus</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center" data-label="No.">4</td>
                        <td data-label="Hari/Tanggal">Kamis, 20 Juni 2024</td>
                        <td data-label="Pekerjaan">Perawatan Hardware</td>
                        <td data-label="Catatan">Membersihkan komponen CPU dan RAM pada 5 unit komputer kantor.
                            Melakukan pemeriksaan kondisi SSD dan HDD. Melaporkan hasil inspeksi kepada teknisi senior.
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="jurnal_edit.php?id=J004"><i
                                                class="fas fa-edit me-2"></i> Edit</a></li>
                                    <li><a class="dropdown-item" href="jurnal_delete.php?id=J004"><i
                                                class="fas fa-trash-alt me-2"></i> Hapus</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="card-footer text-muted">
                *Catatan dapat diisi detail kegiatan harian yang dilakukan.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.min.js"></script>
</body>

</html>