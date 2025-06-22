<?php
// Pastikan sesi sudah dimulai di awal file
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

// Ambil data dari sesi, atau gunakan nilai default jika sesi belum aktif atau data tidak ada
// $userName = $_SESSION['nama'] ?? 'Administrator'; // Nilai default jika sesi tidak aktif/data tidak ada
// $userRole = $_SESSION['role'] ?? 'Super Admin';   // Nilai default jika sesi tidak aktif/data tidak ada
// $userAvatar = './assets/img/avatars/1.png'; // Path avatar default (contoh)
// Anda bisa menambahkan logika untuk avatar berdasarkan role atau user ID
// Misal: if ($userRole == 'Siswa') $userAvatar = './assets/img/avatars/siswa.png';

// --- DATA STATIS UNTUK PENGUJIAN TAMPILAN (Hapus atau komentari ini saat sesi aktif) ---
$userName = 'Anjay Mabar';
$userRole = 'Admin';
$userAvatar = './assets/img/avatars/1.png'; // Ganti dengan path avatar default Anda
// --- AKHIR DATA STATIS ---

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
                    <img src="<?= htmlspecialchars($userAvatar); ?>" alt="User Avatar" class="user-avatar">
                    <div class="user-info d-none d-md-block"> <span
                            class="user-name"><?= htmlspecialchars($userName); ?></span>
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
                        <a class="dropdown-item" href="javascript:void(0);" id="logoutBtn">
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
    document.getElementById("logoutBtn").addEventListener("click", function(e) {
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
                // Di sini Anda bisa arahkan ke halaman logout statis atau simulasi logout
                alert("Simulasi Logout: Anda telah keluar.");
                // Hapus baris alert di atas dan uncomment baris di bawah ini saat sesi PHP aktif
                // window.location.href = "../logout.php"; 
            }
        });
    });
</script>