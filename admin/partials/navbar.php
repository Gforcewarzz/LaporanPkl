<?php
// Asumsi session_start() sudah dipanggil di partials/head.php atau file induk utama

$userName = 'Guest';
$userRole = 'Pengunjung';
$userAvatar = 'assets/img/avatars/1.png'; // Menggunakan avatar default dari aset

// Cek jika ada pengguna yang login menggunakan sesi universal
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    $userName = $_SESSION['user_name'] ?? 'Pengguna Tidak Dikenal';

    switch ($_SESSION['user_role']) {
        case 'siswa':
            $userRole = 'Siswa PKL';
            $userAvatar = 'assets/img/avatars/1.png'; // Avatar default untuk siswa
            break;
        case 'guru_pendamping':
            $userRole = 'Guru Pembimbing';
            $userAvatar = 'assets/img/avatars/1.png'; // Avatar default untuk guru
            break;
        case 'admin':
            $userRole = 'Administrator';
            $userAvatar = 'assets/img/avatars/1.png'; // Avatar default untuk admin
            break;
        default:
            $userRole = 'Pengguna'; // Fallback jika peran tidak dikenali
            $userAvatar = 'assets/img/avatars/1.png'; // Avatar default
            break;
    }
}
?>

<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center" href="javascript:void(0);"
                    data-bs-toggle="dropdown">
                    <div class="avatar avatar-online me-2">
                        <img src="<?= htmlspecialchars($userAvatar); ?>" alt="User Avatar"
                            class="w-px-40 h-auto rounded-circle">
                    </div>
                    <div class="user-info d-none d-md-block">
                        <span class="user-name fw-semibold d-block"><?= htmlspecialchars($userName); ?></span>
                        <small class="user-role"><?= htmlspecialchars($userRole); ?></small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="<?= htmlspecialchars($userAvatar); ?>" alt="User Avatar"
                                            class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block"><?= htmlspecialchars($userName); ?></span>
                                    <small class="text-muted"><?= htmlspecialchars($userRole); ?></small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="../ganti_password_dispatcher.php">
                            <i class="bx bx-cog me-2"></i> <span class="align-middle">Ganti Password</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" id="logoutButton">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById("logoutButton");

    if (logoutBtn) {
        logoutBtn.addEventListener("click", function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Konfirmasi Logout',
                text: "Apakah Anda yakin ingin keluar dari sesi ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../logout.php";
                }
            });
        });
    } else {
        console.error("Elemen dengan ID 'logoutButton' tidak ditemukan.");
    }
});
</script>