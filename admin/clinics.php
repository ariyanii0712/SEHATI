<?php require_once 'includes/header.php'; ?>
<script>document.getElementById('page-title').innerText = 'Manajemen Klinik / Poli';</script>

<div class="glass-card rounded-2xl p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-slate-800">Daftar Klinik</h3>
        <button class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primaryDark transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Data
        </button>
    </div>

    <!-- Search/Filter -->
    <div class="flex gap-4 mb-6">
        <div class="flex-1 relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" placeholder="Cari nama faskes..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm">
        </div>
    </div>

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
                // Mock Data Array
                $mock_data = [
                    ['name' => 'Puskesmas Mulyorejo', 'type' => 'Puskesmas', 'address' => 'Jl. Mulyorejo Utara No. 2', 'status' => 'Aktif'],
                    ['name' => 'RSUD Dr. Soewandhi', 'type' => 'Rumah Sakit', 'address' => 'Jl. Tambah Rejo No. 45', 'status' => 'Aktif'],
                ];
                
                foreach ($mock_data as $row): ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                    <td class="p-4 font-medium text-slate-700"><?= $row['name'] ?></td>
                    <td class="p-4 text-slate-600 text-sm"><?= $row['type'] ?></td>
                    <td class="p-4 text-slate-600 text-sm"><?= $row['address'] ?></td>
                    <td class="p-4">
                        <span class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded-full text-xs font-bold border border-emerald-100">
                            Aktif
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <button class="text-slate-400 hover:text-primary transition-colors p-1"><i class="fa-solid fa-pen-to-square"></i></button>
                        <button class="text-slate-400 hover:text-red-500 transition-colors p-1 ml-1"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

