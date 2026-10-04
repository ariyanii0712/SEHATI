<?php require_once 'includes/header.php'; ?>
<script>document.getElementById('page-title').innerText = 'Manajemen Faskes';</script>

<div class="glass-card rounded-2xl p-6 relative">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-slate-800">Daftar Fasilitas Kesehatan</h3>
        <button onclick="openModal('add')" class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primaryDark transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Faskes
        </button>
    </div>

    <!-- Search/Filter -->
    <form method="GET" action="facilities.php" class="flex flex-wrap md:flex-nowrap gap-4 mb-6">
        <div class="flex-1 relative min-w-[200px]">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" placeholder="Cari nama faskes..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm">
        </div>
        
        <?php
        require_once '../config/database.php';
        ?>
        <select name="wilayah" onchange="this.form.submit()" class="px-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm bg-white">
            <option value="">Semua Wilayah</option>
            <?php
            $w_res = $conn->query("SELECT DISTINCT wilayah FROM faskes WHERE wilayah IS NOT NULL AND wilayah != '' ORDER BY wilayah ASC");
            if ($w_res) {
                while($w = $w_res->fetch_assoc()):
                    $sel = (isset($_GET['wilayah']) && $_GET['wilayah'] == $w['wilayah']) ? 'selected' : '';
            ?>
                <option value="<?= htmlspecialchars($w['wilayah']) ?>" <?= $sel ?>><?= htmlspecialchars($w['wilayah']) ?></option>
            <?php endwhile; } ?>
        </select>
        
        <select name="sort" onchange="this.form.submit()" class="px-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm bg-white">
            <option value="ASC" <?= (isset($_GET['sort']) && $_GET['sort'] == 'ASC') ? 'selected' : '' ?>>A - Z</option>
            <option value="DESC" <?= (isset($_GET['sort']) && $_GET['sort'] == 'DESC') ? 'selected' : '' ?>>Z - A</option>
        </select>
        
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition-colors text-sm font-bold">Terapkan</button>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-y border-slate-200 text-sm text-slate-500">
                    <th class="p-4 font-semibold">Nama Faskes</th>
                    <th class="p-4 font-semibold">Tipe</th>
                    <th class="p-4 font-semibold">Alamat</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once '../config/database.php';
                
                // Ambil daftar semua poli untuk modal
                $all_polis = [];
                $poli_res = $conn->query("SELECT * FROM poli ORDER BY nama_poli ASC");
                if ($poli_res) {
                    while($p = $poli_res->fetch_assoc()) {
                        $all_polis[] = $p;
                    }
                }

                $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
                $wilayah = isset($_GET['wilayah']) ? $conn->real_escape_string($_GET['wilayah']) : '';
                $sort = isset($_GET['sort']) && $_GET['sort'] == 'DESC' ? 'DESC' : 'ASC';

                $where_clauses = [];
                if ($search) $where_clauses[] = "f.nama LIKE '%$search%'";
                if ($wilayah) $where_clauses[] = "f.wilayah = '$wilayah'";
                
                $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

                $query = "SELECT f.*, GROUP_CONCAT(fl.poli_id) as poli_ids 
                          FROM faskes f 
                          LEFT JOIN faskes_layanan fl ON f.id = fl.faskes_id 
                          $where_sql
                          GROUP BY f.id 
                          ORDER BY f.nama $sort";
                $result = $conn->query($query);
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): 
                        // Parse poli_ids to array
                        $row['poli_ids'] = $row['poli_ids'] ? explode(',', $row['poli_ids']) : [];
                        // escape for js 
                        // escape for js
                        $jsonData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                    <td class="p-4 font-medium text-slate-700">
                        <div class="flex items-center gap-3">
                            <?php if(!empty($row['image_url'])): ?>
                                <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="img" class="w-10 h-10 rounded-md object-cover bg-slate-200">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-md bg-slate-200 flex items-center justify-center text-slate-400"><i class="fa-solid fa-hospital"></i></div>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($row['nama']) ?></span>
                        </div>
                    </td>
                    <td class="p-4 text-slate-600 text-sm"><?= htmlspecialchars($row['kategori']) ?></td>
                    <td class="p-4 text-slate-600 text-sm"><?= htmlspecialchars($row['alamat']) ?></td>
                    <td class="p-4">
                        <span class="px-2 py-1 <?= $row['status'] == 'Aktif' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100' ?> rounded-full text-xs font-bold border">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <button onclick="openModal('edit', <?= $jsonData ?>)" class="text-slate-400 hover:text-primary transition-colors p-1"><i class="fa-solid fa-pen-to-square"></i></button>
                        <form action="../ajax/admin_faskes.php" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus faskes ini?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="current_search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            <input type="hidden" name="current_wilayah" value="<?= isset($_GET['wilayah']) ? htmlspecialchars($_GET['wilayah']) : '' ?>">
                            <input type="hidden" name="current_sort" value="<?= isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : 'ASC' ?>">
                            <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors p-1 ml-1"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                <tr class="border-b border-slate-100">
                    <td colspan="5" class="p-4 text-center text-slate-500 text-sm">Tidak ada data faskes.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Background -->
<div id="faskesModal" class="fixed inset-0 z-50 bg-slate-900/50 hidden flex items-center justify-center p-4">
    <!-- Modal Content -->
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden shadow-xl">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Tambah Faskes</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-red-500"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <div class="p-6 overflow-y-auto">
            <form id="faskesForm" action="../ajax/admin_faskes.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="formId" value="">
                
                <!-- Persist filters -->
                <input type="hidden" name="current_search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <input type="hidden" name="current_wilayah" value="<?= isset($_GET['wilayah']) ? htmlspecialchars($_GET['wilayah']) : '' ?>">
                <input type="hidden" name="current_sort" value="<?= isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : 'ASC' ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Faskes *</label>
                        <input type="text" name="nama" id="formNama" required class="w-full px-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori *</label>
                        <select name="kategori" id="formKategori" required class="w-full px-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm">
                            <option value="Puskesmas">Puskesmas</option>
                            <option value="Klinik">Klinik</option>
                            <option value="RSUD">RSUD</option>
                            <option value="Rumah Sakit">Rumah Sakit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Status</label>
                        <select name="status" id="formStatus" class="w-full px-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Telepon</label>
                        <input type="text" name="telepon" id="formTelepon" class="w-full px-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Wilayah</label>
                        <input type="text" name="wilayah" id="formWilayah" class="w-full px-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                        <textarea name="alamat" id="formAlamat" rows="2" class="w-full px-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Jam Pelayanan</label>
                        <input type="text" name="jam_pelayanan" id="formJam" placeholder="Senin-Jumat 08:00 - 15:00" class="w-full px-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Foto Faskes</label>
                        <input type="file" name="image" accept=".jpg, .jpeg, .png" class="w-full px-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm bg-slate-50 text-slate-500 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primaryDark">
                        <p class="text-xs text-slate-400 mt-1">Pilih file untuk mengubah foto</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi Tambahan</label>
                        <textarea name="deskripsi" id="formDeskripsi" rows="3" class="w-full px-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Poli / Layanan yang Tersedia</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <?php foreach($all_polis as $poli): ?>
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="polis[]" value="<?= $poli['id'] ?>" class="form-checkbox-poli w-4 h-4 text-primary rounded border-slate-300">
                                <?= htmlspecialchars($poli['nama_poli']) ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-bold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-primary rounded-lg hover:bg-primaryDark transition-colors">Simpan Faskes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('faskesModal');
    const form = document.getElementById('faskesForm');
    const modalTitle = document.getElementById('modalTitle');
    
    function openModal(type, data = null) {
        modal.classList.remove('hidden');
        // Reset checkboxes
        document.querySelectorAll('.form-checkbox-poli').forEach(cb => cb.checked = false);

        if (type === 'add') {
            modalTitle.innerText = 'Tambah Faskes';
            document.getElementById('formAction').value = 'create';
            form.reset();
            document.getElementById('formId').value = '';
        } else if (type === 'edit' && data) {
            modalTitle.innerText = 'Edit Faskes';
            document.getElementById('formAction').value = 'update';
            document.getElementById('formId').value = data.id;
            
            document.getElementById('formNama').value = data.nama || '';
            document.getElementById('formKategori').value = data.kategori || 'Puskesmas';
            document.getElementById('formStatus').value = data.status || 'Aktif';
            document.getElementById('formTelepon').value = data.telepon || '';
            document.getElementById('formWilayah').value = data.wilayah || '';
            document.getElementById('formAlamat').value = data.alamat || '';
            document.getElementById('formJam').value = data.jam_pelayanan || '';
            document.getElementById('formDeskripsi').value = data.deskripsi || '';

            if (data.poli_ids && data.poli_ids.length > 0) {
                data.poli_ids.forEach(id => {
                    const cb = document.querySelector(`.form-checkbox-poli[value="${id}"]`);
                    if (cb) cb.checked = true;
                });
            }
        }
    }
    
    function closeModal() {
        modal.classList.add('hidden');
    }
</script>

<?php require_once 'includes/footer.php'; ?>
