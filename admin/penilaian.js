document.addEventListener('DOMContentLoaded', function () {
    const selectSiswa = document.getElementById('pilih_siswa');
    const containerKompetensi = document.getElementById('container-kompetensi');
    const submitWrapper = document.getElementById('submit-wrapper');

    selectSiswa.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const jurusanId = selectedOption.dataset.jurusanId;
        const siswaId = this.value; // Ambil ID siswa yang dipilih

        containerKompetensi.innerHTML = `
            <div class="text-center text-muted p-4 border rounded-3 bg-light">
                <i class="bx bx-loader-alt bx-spin fs-1 mb-3"></i>
                <p class="mb-0">Memuat daftar kompetensi dan nilai sebelumnya...</p>
            </div>`;
        submitWrapper.style.display = 'none';

        if (jurusanId && jurusanId !== '0' && siswaId) {
            // --- PERUBAHAN DI SINI: Kirim siswa_id ke API ---
            fetch(`api_get_tp.php?jurusan_id=${jurusanId}&siswa_id=${siswaId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    containerKompetensi.innerHTML = '';
                    // Kirim data nilai ke fungsi render
                    renderTPForm('', data.semua_tp, data.tp_anak, data.leaf_node_ids, data.nilai_siswa);
                    
                    submitWrapper.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    containerKompetensi.innerHTML = `<div class="alert alert-danger text-center">Gagal memuat data. Silakan coba lagi.</div>`;
                });
        }
    });

    // --- PERUBAHAN DI SINI: Tambahkan parameter 'nilai_siswa' ---
    function renderTPForm(id_induk, semua_tp, tp_anak, leaf_node_ids, nilai_siswa, parentElement = containerKompetensi) {
        if (!tp_anak[id_induk]) {
            return;
        }

        const ul = document.createElement('ul');
        ul.style.listStyleType = 'none';
        ul.style.paddingLeft = '20px';

        tp_anak[id_induk].forEach(id_tp => {
            const item = semua_tp[id_tp];
            const is_leaf_node = leaf_node_ids.includes(parseInt(id_tp));

            const li = document.createElement('li');
            li.className = is_leaf_node ? 'leaf-item' : 'parent-item';
            // ... (kode styling <li> lainnya tetap sama)
            li.style.padding = '10px';
            li.style.marginBottom = '8px';
            li.style.borderLeft = is_leaf_node ? '3px solid #3498db' : '3px solid #e0e0e0';
            if(is_leaf_node){
                li.style.display = 'flex';
                li.style.justifyContent = 'space-between';
                li.style.alignItems = 'center';
            }


            const deskripsiDiv = document.createElement('div');
            // ... (kode styling deskripsiDiv lainnya tetap sama)
            deskripsiDiv.className = `deskripsi ${is_leaf_node ? 'leaf' : 'induk'}`;
            deskripsiDiv.innerHTML = `
                <span class="kode_tp" style="font-weight: bold; margin-right: 15px; color: #34495e; background-color: #ecf0f1; padding: 4px 8px; border-radius: 4px; min-width: 50px; text-align: center; flex-shrink: 0;">${item.kode_tp}</span>
                <span class="teks_tp" style="${is_leaf_node ? 'color: #555;' : 'font-weight: bold; color: #2c3e50;'}">${item.deskripsi_tp}</span>
            `;

            li.appendChild(deskripsiDiv);

            if (is_leaf_node) {
                // --- PERUBAHAN DI SINI: Cek nilai yang ada dan isikan ke input ---
                const nilai_sebelumnya = nilai_siswa[id_tp] ? parseFloat(nilai_siswa[id_tp]) : '';
                const inputWrapper = document.createElement('div');
                inputWrapper.className = 'input-wrapper';
                inputWrapper.style.paddingLeft = '15px';
                inputWrapper.innerHTML = `<input type="number" name="nilai[${id_tp}]" class="form-control" min="0" max="100" placeholder="0-100" value="${nilai_sebelumnya}" required style="width: 80px; text-align: center;">`;
                li.appendChild(inputWrapper);
            } else {
                // Kirim juga 'nilai_siswa' ke panggilan rekursif selanjutnya
                renderTPForm(id_tp, semua_tp, tp_anak, leaf_node_ids, nilai_siswa, li);
            }
            
            parentElement.appendChild(li);
        });
    }
});