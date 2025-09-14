document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search_siswa");
  const tableBody = document.getElementById("siswa-table-body");
  const loadingIndicator = document.getElementById("loading-indicator");
  const emptyState = document.getElementById("empty-state");

  let searchTimeout;

  // Fungsi untuk mengambil data siswa dari server
  const fetchSiswa = async (searchTerm = "") => {
    loadingIndicator.style.display = "block";
    emptyState.style.display = "none";
    tableBody.innerHTML = "";

    try {
      const response = await fetch(
        `get_siswa.php?search=${encodeURIComponent(searchTerm)}`
      );
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      const students = await response.json();
      renderTable(students);
    } catch (error) {
      console.error("Fetch error:", error);
      tableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">Gagal memuat data siswa.</td></tr>`;
    } finally {
      loadingIndicator.style.display = "none";
    }
  };

  // Fungsi untuk menampilkan data siswa ke dalam tabel
  const renderTable = (students) => {
    if (students.length === 0 && searchInput.value.trim() !== "") {
      emptyState.style.display = "block";
      emptyState.querySelector("p").textContent =
        "Siswa tidak ditemukan dengan kata kunci tersebut.";
    } else if (students.length === 0) {
      emptyState.style.display = "block";
      emptyState.querySelector("p").textContent =
        "Belum ada siswa yang bisa ditampilkan.";
    } else {
      emptyState.style.display = "none";
      students.forEach((siswa) => {
        const sudahDinilai = siswa.jumlah_nilai > 0;
        const statusBadge = sudahDinilai
          ? `<span class="badge bg-label-success">Sudah Dinilai</span>`
          : `<span class="badge bg-label-secondary">Belum Dinilai</span>`;

        // Tombol "Nilai" sekarang adalah link <a> yang mengarah ke halaman form baru
        const actionButton = `
                    <a href="form_penilaian_detail.php?id_siswa=${siswa.id_siswa}" class="btn btn-sm btn-primary">
                        <i class="bx bx-edit-alt me-1"></i> Nilai
                    </a>
                `;

        const row = document.createElement("tr");
        row.innerHTML = `
                    <td>
                        <i class="bx bxs-user me-2 text-primary"></i><strong>${escapeHTML(
                          siswa.nama_siswa
                        )}</strong>
                    </td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">${actionButton}</td>
                `;
        tableBody.appendChild(row);
      });
    }
  };

  // Event listener untuk input pencarian dengan "debounce"
  searchInput.addEventListener("keyup", (e) => {
    clearTimeout(searchTimeout);
    const searchTerm = e.target.value;
    searchTimeout = setTimeout(() => {
      fetchSiswa(searchTerm);
    }, 350); // Menunggu 350ms setelah user berhenti mengetik
  });

  // Fungsi helper untuk keamanan (mencegah XSS)
  const escapeHTML = (str) => {
    return str.replace(
      /[&<>'"]/g,
      (tag) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          "'": "&#39;",
          '"': "&quot;",
        }[tag] || tag)
    );
  };

  // Panggil fungsi untuk memuat daftar siswa awal saat halaman dibuka
  fetchSiswa();
});
