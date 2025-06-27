<?php
session_start();

// Cek apakah salah satu sesi login admin, guru, atau siswa sudah ada
// Jika sudah login sebagai salah satu role, arahkan ke dashboard yang sesuai
if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'login') {
    header("Location: admin/dashboard_admin.php"); // Contoh dashboard admin
    exit;
} elseif (isset($_SESSION['guru_pendamping']) && $_SESSION['guru_pendamping'] === 'login') {
    header("Location: guru/dashboard_guru.php"); // Contoh dashboard guru pendamping
    exit;
} elseif (isset($_SESSION['siswa']) && $_SESSION['siswa'] === 'login') {
    header("Location: admin/master_kegiatan_harian.php"); // Dashboard siswa
    exit;
}
?>

<?php include 'admin/partials/db.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Petugas/Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        /* CSS yang sama seperti sebelumnya */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            background: linear-gradient(135deg, #6e73fe, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .login-container {
            background: #ffffff;
            padding: 2rem;
            border-radius: 25px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.8s ease forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.3rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #444;
            font-weight: 500;
        }

        .form-group .input-icon {
            position: relative;
        }

        .form-group .input-icon i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #6e73fe;
        }

        .form-group input,
        .form-group select {
            /* Tambahkan seleksi untuk select */
            width: 100%;
            padding: 0.9rem 0.9rem 0.9rem 2.5rem;
            /* Sesuaikan padding untuk select jika perlu */
            border: 1.5px solid #ddd;
            border-radius: 12px;
            outline: none;
            transition: border-color 0.3s;
            font-size: 1rem;
            background-color: #f9f9f9;
            -webkit-appearance: none;
            /* Hilangkan gaya default browser untuk select */
            -moz-appearance: none;
            appearance: none;
        }

        .form-group select {
            padding-right: 2.5rem;
            /* Ruang untuk ikon dropdown */
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%236e73fe"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.2em;
        }


        .form-group input:focus,
        .form-group select:focus {
            /* Tambahkan seleksi untuk select focus */
            border-color: #6e73fe;
            background-color: #fff;
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background: #6e73fe;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background 0.3s ease-in-out;
        }

        .login-btn:hover {
            background: #5a61e0;
        }

        .extra-links {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            color: #555;
        }

        .extra-links i {
            margin-right: 6px;
            color: #6e73fe;
        }

        .extra-links a {
            color: #6e73fe;
            text-decoration: none;
            transition: color 0.2s;
            font-weight: 500;
        }

        .extra-links a:hover {
            text-decoration: underline;
            color: #444;
        }

        /* Responsif */
        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem 1rem;
                border-radius: 20px;
            }

            .form-group input,
            .form-group select {
                padding-left: 2.3rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2><i class="fas fa-user-shield"></i> Login Petugas/Admin</h2>
        <form action="login_petugas_act.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" id="username" required placeholder="Masukkan Username Anda" />
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" required placeholder="Masukkan Password" />
                </div>
            </div>
            <div class="form-group">
                <label for="role">Masuk Sebagai:</label>
                <div class="input-icon">
                    <i class="fas fa-user-tag" style="left: 15px;"></i> <select name="role" id="role" required>
                        <option value="">-- Pilih Peran --</option>
                        <option value="admin">Admin</option>
                        <option value="guru_pendamping">Guru Pendamping</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Masuk</button>
        </form>
        <div class="extra-links">
            <p><i class="fas fa-question-circle"></i> Lupa password? Hubungi Administrator.</p>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</body>

</html>