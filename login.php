<?php
session_start();

if (isset($_SESSION['siswa']) && $_SESSION['siswa'] === 'login') {
    // Jika sudah login, arahkan langsung ke dashboard atau halaman lain
    header("Location: admin/master_kegiatan_harian.php");
    exit;
}
?>

<?php include 'admin/partials/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Form</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
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

    .form-group input {
        width: 100%;
        padding: 0.9rem 0.9rem 0.9rem 2.5rem;
        border: 1.5px solid #ddd;
        border-radius: 12px;
        outline: none;
        transition: border-color 0.3s;
        font-size: 1rem;
        background-color: #f9f9f9;
    }

    .form-group input:focus {
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

    @media (max-width: 480px) {
        .login-container {
            padding: 1.5rem 1rem;
            border-radius: 20px;
        }

        .form-group input {
            padding-left: 2.3rem;
        }
    }
    </style>
</head>

<body>
        <div class="login-container">
        <h2><i class="fas fa-user-graduate"></i> Login Siswa</h2>
        <form action="login_act.php" method="POST" autocomplete="off">
            <div class="form-group">
            <label for="nisn">NISN</label>
            <div class="input-icon">
                <i class="fas fa-id-card"></i>
                <input type="text" name="nisn" id="nisn" required placeholder="Masukkan NISN Anda" />
                <div id="nisnList" style="position: absolute; background: white; border: 1px solid #ccc; z-index: 1000;"></div>
            </div>
            </div>
            <div class="form-group">
            <label for="password">Password</label>
            <div class="input-icon">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" required placeholder="Masukkan Password" />
            </div>
            </div>
            <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Masuk</button>
        </form>
        <div class="extra-links">
            <p><i class="fas fa-info-circle"></i> Ada kendala? Hubungi admin</p>
        </div>
        </div>

                <script>

        </script>
</body>

</html>