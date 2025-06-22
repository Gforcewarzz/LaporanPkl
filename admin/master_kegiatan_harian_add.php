<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Catatan Jurnal PKL</title>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #007BFF;
            /* Biru cerah dan energik */
            --primary-dark: #0056b3;
            /* Biru lebih tua untuk hover */
            --accent-green: #28A745;
            /* Hijau sukses */
            --accent-red: #DC3545;
            /* Merah bahaya */
            --text-dark: #343A40;
            /* Teks utama gelap */
            --text-light: #6C757D;
            /* Teks sekunder abu-abu */
            --bg-body: #F0F2F5;
            /* Latar belakang halaman abu-abu muda */
            --card-bg: #FFFFFF;
            /* Latar belakang kartu putih bersih */
            --border-light: #E9ECEF;
            /* Border sangat tipis */
            --shadow-subtle: rgba(0, 0, 0, 0.08);
            /* Bayangan tipis */
            --focus-ring: rgba(0, 123, 255, 0.25);
            /* Cincin fokus yang halus */
        }

        body {
            font-family: 'Urbanist', sans-serif;
            /* Font Urbanist yang modern dan ramping */
            background-color: var(--bg-body);
            color: var(--text-dark);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .form-container {
            background-color: var(--card-bg);
            border-radius: 16px;
            /* Sudut lebih membulat */
            box-shadow: 0 15px 30px var(--shadow-subtle);
            /* Bayangan lebih kuat tapi elegan */
            padding: 40px;
            /* Padding lebih besar */
            width: 100%;
            max-width: 600px;
            border: 1px solid var(--border-light);
            /* Border tipis */
            animation: fadeInSlideUp 0.8s ease-out forwards;
            overflow: hidden;
            /* Penting untuk efek pseudo-element */
            position: relative;
        }

        /* Pseudo-element untuk efek visual di latar belakang card */
        .form-container::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(0, 123, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            animation: bubble1 10s infinite alternate;
        }

        .form-container::after {
            content: '';
            position: absolute;
            bottom: -30px;
            right: -30px;
            width: 120px;
            height: 120px;
            background: radial-gradient(circle, rgba(0, 123, 255, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            animation: bubble2 12s infinite alternate;
        }

        @keyframes bubble1 {
            0% {
                transform: translate(0, 0);
                opacity: 0.8;
            }

            50% {
                transform: translate(10px, -10px);
                opacity: 0.6;
            }

            100% {
                transform: translate(0, 0);
                opacity: 0.8;
            }
        }

        @keyframes bubble2 {
            0% {
                transform: translate(0, 0);
                opacity: 0.7;
            }

            50% {
                transform: translate(-15px, 15px);
                opacity: 0.5;
            }

            100% {
                transform: translate(0, 0);
                opacity: 0.7;
            }
        }

        @keyframes fadeInSlideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header {
            text-align: center;
            margin-bottom: 35px;
            /* Margin lebih besar */
            position: relative;
            /* Untuk z-index agar di atas pseudo-element */
            z-index: 1;
        }

        .form-header h2 {
            font-weight: 800;
            /* Extra bold */
            color: var(--primary-blue);
            font-size: 2.2rem;
            /* Ukuran lebih besar */
            margin-bottom: 10px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-header p {
            color: var(--text-light);
            font-size: 1rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 25px;
            /* Jarak antar group lebih besar */
            position: relative;
            /* Untuk label floating */
        }

        /* Styling input dan textarea */
        .form-group .form-control {
            border-radius: 10px;
            /* Sudut input lebih membulat */
            padding: 15px 20px;
            /* Padding lebih besar */
            padding-left: 55px;
            /* Ruang untuk ikon */
            font-size: 1rem;
            border: 1px solid var(--border-light);
            box-shadow: none;
            background-color: var(--bg-body);
            /* Latar belakang input sedikit abu-abu */
            color: var(--text-dark);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px var(--focus-ring);
            /* Cincin fokus lebih tebal */
            outline: none;
            background-color: var(--card-bg);
            /* Berubah putih saat fokus */
        }

        .form-group textarea.form-control {
            min-height: 150px;
            /* Tinggi minimum textarea lebih besar */
            padding-left: 20px;
            /* Tanpa ikon di textarea */
        }

        /* Placeholder dan Label Floating */
        .form-group .form-control::placeholder {
            color: transparent;
            /* Sembunyikan placeholder asli */
        }

        .form-group label {
            position: absolute;
            left: 55px;
            /* Sejajar dengan ikon */
            top: 15px;
            /* Posisi awal label */
            font-size: 1rem;
            color: var(--text-light);
            pointer-events: none;
            transition: all 0.3s ease;
            background-color: transparent;
            /* Agar tidak menimpa background input saat hover */
            z-index: 2;
            /* Di atas input tapi di bawah ikon */
        }

        .form-group textarea+label {
            /* Untuk textarea, label tidak sejajar ikon */
            left: 20px;
            top: 15px;
        }

        .form-group .form-control:focus+label,
        .form-group .form-control:not(:placeholder-shown)+label {
            top: -10px;
            /* Naik ke atas saat fokus/terisi */
            left: 20px;
            /* Geser ke kiri atas */
            font-size: 0.75rem;
            /* Ukuran lebih kecil */
            color: var(--primary-blue);
            background-color: var(--card-bg);
            /* Latar belakang untuk label floating */
            padding: 0 5px;
            /* Padding untuk background label */
        }

        /* Penyesuaian ikon untuk floating label */
        .input-icon {
            position: absolute;
            left: 20px;
            /* Posisi ikon */
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.2rem;
            /* Ukuran ikon lebih besar */
            pointer-events: none;
            transition: color 0.3s ease;
            z-index: 1;
            /* Di bawah label floating */
        }

        .form-group .form-control:focus+label+.input-icon,
        .form-group .form-control:not(:placeholder-shown)+label+.input-icon {
            color: var(--primary-blue);
        }

        /* Button Group */
        .button-group {
            display: flex;
            justify-content: center;
            /* Tombol di tengah */
            align-items: center;
            flex-wrap: wrap;
            margin-top: 40px;
            /* Margin lebih besar */
            gap: 15px;
            /* Jarak antar tombol */
        }

        .button-group .btn {
            font-size: 1.05rem;
            /* Ukuran font tombol lebih besar */
            padding: 14px 30px;
            /* Padding lebih besar */
            border-radius: 10px;
            /* Sudut tombol lebih membulat */
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            /* Bayangan tombol */
            text-transform: uppercase;
            /* Teks uppercase */
            letter-spacing: 0.5px;
        }

        .button-group .btn:hover {
            transform: translateY(-3px);
            /* Efek angkat lebih jelas */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .button-group .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        .button-group .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .button-group .btn-outline-secondary {
            color: var(--text-light);
            border-color: var(--border-light);
            background-color: transparent;
        }

        .button-group .btn-outline-secondary:hover {
            background-color: var(--bg-body);
            border-color: var(--text-light);
            color: var(--text-dark);
        }

        /* Responsive untuk Mobile */
        @media (max-width: 575.98px) {
            body {
                padding: 15px;
            }

            .form-container {
                padding: 30px 25px;
                border-radius: 12px;
            }

            .form-header h2 {
                font-size: 1.8rem;
            }

            .form-header p {
                font-size: 0.9rem;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-group .form-control {
                padding: 12px 18px;
                padding-left: 50px;
                font-size: 0.95rem;
            }

            .form-group textarea.form-control {
                padding-left: 18px;
            }

            .form-group label {
                left: 50px;
                font-size: 0.9rem;
                top: 12px;
            }

            .form-group textarea+label {
                left: 18px;
                top: 12px;
            }

            .form-group .form-control:focus+label,
            .form-group .form-control:not(:placeholder-shown)+label {
                top: -8px;
                left: 15px;
                font-size: 0.7rem;
            }

            .input-icon {
                left: 18px;
                font-size: 1rem;
            }

            .button-group {
                flex-direction: column;
                margin-top: 30px;
                gap: 10px;
                /* Jarak antar tombol di mobile */
            }

            .button-group .btn {
                width: 100%;
                padding: 12px 20px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <div class="form-header">
            <h2>Tambah Catatan Jurnal PKL</h2>
            <p>Isi detail kegiatan harian Anda selama Praktik Kerja Lapangan.</p>
        </div>
        <form action="jurnal_save.php" method="POST">
            <div class="row">
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <input type="date" class="form-control" id="tanggal" name="tanggal" placeholder="Hari/Tanggal"
                            required>
                        <label for="tanggal">Hari/Tanggal</label>
                        <i class="fas fa-calendar-alt input-icon"></i>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" placeholder="Pekerjaan"
                            required>
                        <label for="pekerjaan">Pekerjaan</label>
                        <i class="fas fa-briefcase input-icon"></i>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <textarea class="form-control" id="catatan" name="catatan" rows="5" placeholder="Catatan Kegiatan"
                    required></textarea>
                <label for="catatan">Catatan Kegiatan</label>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan Catatan
                </button>
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="fas fa-redo me-1"></i> Reset Form
                </button>
                <a href="jurnal_harian.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Batal
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.min.js"></script>
</body>

</html>