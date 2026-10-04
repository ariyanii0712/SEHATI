<?php require_once 'includes/header.php'; ?>
<script>document.getElementById('page-title').innerText = 'Manajemen Pengguna';</script>

<div class="glass-card rounded-2xl p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-slate-800">Daftar Pengguna Sistem</h3>
        <button class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primaryDark transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Pengguna
        </button>
    </div>

    <!-- Search/Filter -->
    <div class="flex gap-4 mb-6">
        <div class="flex-1 relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" placeholder="Cari nama, NIK, atau email..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg outline-none focus:border-primary text-sm">
        </div>
        <select class="border border-slate-200 rounded-lg px-4 py-2 text-sm outline-none focus:border-primary bg-white">
            <option>Semua Role</option>
            <option>Pasien</option>
            <option>Staff Faskes</option>
            <option>Admin</option>
        </select>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-y border-slate-200 text-sm text-slate-500">
                    <th class="p-4 font-semibold">Nama Lengkap</th>
                    <th class="p-4 font-semibold">NIK / Username</th>
                    <th class="p-4 font-semibold">Role</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Mock Data Array (simulating DB result)
                $mock_users = [
                    ['name' => 'Budi Santoso', 'nik' => '3578012345678901', 'role' => 'Pasien', 'status' => 'Aktif'],
                    ['name' => 'Siti Aminah', 'nik' => '3578019876543210', 'role' => 'Pasien', 'status' => 'Aktif'],
                    ['name' => 'Dr. Andi', 'nik' => 'dr_andi_mly', 'role' => 'Staff Faskes', 'status' => 'Aktif'],
                    ['name' => 'Admin Super', 'nik' => 'admin', 'role' => 'Admin', 'status' => 'Aktif'],
                ];
                
                foreach ($mock_users as $user): ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-500 text-xs">
                                <?= substr($user['name'], 0, 1) ?>
                            </div>
                            <span class="font-medium text-slate-700"><?= $user['name'] ?></span>
                        </div>
                    </td>
                    <td class="p-4 text-slate-600 text-sm"><?= $user['nik'] ?></td>
                    <td class="p-4">
                        <?php if($user['role'] == 'Pasien'): ?>
                            <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded text-xs font-semibold">Pasien</span>
                        <?php elseif($user['role'] == 'Staff Faskes'): ?>
                            <span class="px-2 py-1 bg-amber-50 text-amber-600 rounded text-xs font-semibold">Staff Faskes</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-purple-50 text-purple-600 rounded text-xs font-semibold">Admin</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 bg-emerald-50 text-emerald-600 rounded-full text-xs font-bold border border-emerald-100">
                            <i class="fa-solid fa-circle text-[8px] mr-1"></i> Aktif
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
