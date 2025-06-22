<!DOCTYPE html>
<html lang="id" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="./assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin E-Jurnal PKL</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <link rel="stylesheet" href="./assets/css/partials.css" />


</head>

<body
    style="font-family: 'Inter', sans-serif; background-color: #f8f9fa; color: #343a40; line-height: 1.5; overflow-x: hidden;">
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './partials/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './partials/navbar.php'; ?>
                <div class="content-wrapper" style="padding: 1.5rem;">
                    <div class="container-xxl flex-grow-1 container-p-y"
                        style="padding-top: 1.5rem; padding-bottom: 1.5rem; padding-left: 1.5rem; padding-right: 1.5rem;">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100"
                                    style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.04); border: 1px solid #e9ecef; transition: transform 0.15s ease-out, box-shadow 0.15s ease-out;">
                                    <div class="card-body d-flex flex-column align-items-start" style="padding: 20px;">
                                        <div class="avatar flex-shrink-0 mb-3"
                                            style="background-color: #007bff; width: 48px; height: 48px; display: flex; justify-content: center; align-items: center; border-radius: 50%; font-size: 1.5rem; color: white;">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <span class="fw-semibold d-block mb-1"
                                            style="font-size: 0.95rem; color: #6c757d; font-weight: 500;">Total
                                            Siswa</span>
                                        <h3 class="fw-bold mb-0"
                                            style="font-size: 1.8rem; color: #343a40; margin-top: 5px; line-height: 1.2;">
                                            125</h3>
                                        <small class="text-muted"
                                            style="font-size: 0.8rem; color: #6c757d !important;">Siswa aktif
                                            terdaftar</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100"
                                    style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.04); border: 1px solid #e9ecef; transition: transform 0.15s ease-out, box-shadow 0.15s ease-out;">
                                    <div class="card-body d-flex flex-column align-items-start" style="padding: 20px;">
                                        <div class="avatar flex-shrink-0 mb-3"
                                            style="background-color: #28a745; width: 48px; height: 48px; display: flex; justify-content: center; align-items: center; border-radius: 50%; font-size: 1.5rem; color: white;">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <span class="fw-semibold d-block mb-1"
                                            style="font-size: 0.95rem; color: #6c757d; font-weight: 500;">Total Guru
                                            Pembimbing</span>
                                        <h3 class="fw-bold mb-0"
                                            style="font-size: 1.8rem; color: #343a40; margin-top: 5px; line-height: 1.2;">
                                            50</h3>
                                        <small class="text-muted"
                                            style="font-size: 0.8rem; color: #6c757d !important;">Guru pembimbing yang
                                            tersedia</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12 mb-4">
                                <div class="card h-100"
                                    style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.04); border: 1px solid #e9ecef; transition: transform 0.15s ease-out, box-shadow 0.15s ease-out;">
                                    <div class="card-body d-flex flex-column align-items-start" style="padding: 20px;">
                                        <div class="avatar flex-shrink-0 mb-3"
                                            style="background-color: #fd7e14; width: 48px; height: 48px; display: flex; justify-content: center; align-items: center; border-radius: 50%; font-size: 1.5rem; color: white;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <span class="fw-semibold d-block mb-1"
                                            style="font-size: 0.95rem; color: #6c757d; font-weight: 500;">Jumlah Tempat
                                            PKL</span>
                                        <h3 class="fw-bold mb-0"
                                            style="font-size: 1.8rem; color: #343a40; margin-top: 5px; line-height: 1.2;">
                                            30</h3>
                                        <small class="text-muted"
                                            style="font-size: 0.8rem; color: #6c757d !important;">Mitra perusahaan
                                            aktif</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card"
                                    style="border-radius: 12px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04); border: 1px solid #e9ecef; padding: 25px; margin-bottom: 25px;">
                                    <h5
                                        style="font-size: 1.1rem; font-weight: 600; color: #343a40; margin-bottom: 20px;">
                                        Akses Cepat</h5>
                                    <div
                                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 12px;">
                                        <a href="master_data_siswa.php"
                                            style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
                                            <i class="fas fa-users"
                                                style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
                                            <span style="font-size: 0.8rem; font-weight: 500; text-align: center;">Data
                                                Siswa</span>
                                        </a>
                                        <a href="master_data_guru.php"
                                            style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
                                            <i class="fas fa-user-tie"
                                                style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
                                            <span style="font-size: 0.8rem; font-weight: 500; text-align: center;">Data
                                                Guru</span>
                                        </a>
                                        <a href="master_data_tempat_pkl.php"
                                            style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
                                            <i class="fas fa-city"
                                                style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
                                            <span style="font-size: 0.8rem; font-weight: 500; text-align: center;">Data
                                                Tempat PKL</span>
                                        </a>
                                        <a href="jurnal_harian.php"
                                            style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
                                            <i class="fas fa-book"
                                                style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
                                            <span
                                                style="font-size: 0.8rem; font-weight: 500; text-align: center;">Jurnal
                                                Harian</span>
                                        </a>
                                        <a href="penilaian_pkl.php"
                                            style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
                                            <i class="fas fa-star"
                                                style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
                                            <span
                                                style="font-size: 0.8rem; font-weight: 500; text-align: center;">Penilaian</span>
                                        </a>
                                        <a href="absensi_pkl.php"
                                            style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
                                            <i class="fas fa-calendar-alt"
                                                style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
                                            <span
                                                style="font-size: 0.8rem; font-weight: 500; text-align: center;">Absensi</span>
                                        </a>
                                        <a href="laporan_pkl.php"
                                            style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
                                            <i class="fas fa-file-alt"
                                                style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
                                            <span
                                                style="font-size: 0.8rem; font-weight: 500; text-align: center;">Laporan</span>
                                        </a>
                                        <?php // if ($userRole == 'Super Admin'): 
                                        ?>
                                        <a href="master_data_pengguna.php"
                                            style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
                                            <i class="fas fa-user-shield"
                                                style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
                                            <span
                                                style="font-size: 0.8rem; font-weight: 500; text-align: center;">Pengguna</span>
                                        </a>
                                        <?php // endif; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="layout-overlay layout-menu-toggle"></div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html> <a href="jurnal_harian.php"
    style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
    <i class="fas fa-book" style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
    <span style="font-size: 0.8rem; font-weight: 500; text-align: center;">Jurnal
        Harian</span>
</a>
<a href="penilaian_pkl.php"
    style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
    <i class="fas fa-star" style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
    <span style="font-size: 0.8rem; font-weight: 500; text-align: center;">Penilaian</span>
</a>
<a href="absensi_pkl.php"
    style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
    <i class="fas fa-calendar-alt" style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
    <span style="font-size: 0.8rem; font-weight: 500; text-align: center;">Absensi</span>
</a>
<a href="laporan_pkl.php"
    style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
    <i class="fas fa-file-alt" style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
    <span style="font-size: 0.8rem; font-weight: 500; text-align: center;">Laporan</span>
</a>
<?php // if ($userRole == 'Super Admin'): 
?>
<a href="master_data_pengguna.php"
    style="display: flex; flex-direction: column; align-items: center; padding: 12px; border-radius: 8px; border: 1px solid #e9ecef; background-color: #f8f9fa; text-decoration: none; color: #343a40; transition: all 0.15s ease;">
    <i class="fas fa-user-shield" style="font-size: 1.8rem; margin-bottom: 8px; color: #007bff;"></i>
    <span style="font-size: 0.8rem; font-weight: 500; text-align: center;">Pengguna</span>
</a>
<?php // endif; 
?>
</div>
</div>
</div>
</div>
</div>

<div class="layout-overlay layout-menu-toggle"></div>
</div>
</div>
</div>
</div>

<?php include 'partials/script.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>