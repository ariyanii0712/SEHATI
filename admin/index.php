<?php require_once 'includes/header.php'; 
require_once '../config/database.php';

// Get total patients
$q_patients = $conn->query("SELECT COUNT(*) as total FROM patients");
$total_patients = $q_patients->fetch_assoc()['total'];

// Get registrations today
$today = date('Y-m-d');
$q_registrations = $conn->query("SELECT COUNT(*) as total FROM pendaftaran WHERE DATE(waktu_daftar) = '$today' OR tanggal_kunjungan = '$today'");
$total_registrations = $q_registrations->fetch_assoc()['total'];

// Get active queues
$q_queues = $conn->query("SELECT COUNT(*) as total FROM pendaftaran WHERE status IN ('menunggu', 'diperiksa')");
$total_queues = $q_queues->fetch_assoc()['total'];

// Get active facilities
$q_faskes = $conn->query("SELECT COUNT(*) as total FROM faskes WHERE status = 'Aktif'");
$total_faskes = $q_faskes->fetch_assoc()['total'];

?>
<script>document.getElementById('page-title').innerText = 'Dashboard & Statistik';</script>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stat Cards -->
    <div class="glass-card rounded-2xl p-5 border-l-4 border-l-primary">
        <p class="text-sm font-medium text-slate-500">Total Pasien</p>
        <h3 class="text-3xl font-bold text-slate-800 mt-2"><?= number_format($total_patients, 0, ',', '.') ?></h3>
        <p class="text-xs text-slate-400 font-medium mt-2">Terdaftar di sistem</p>
    </div>
    <div class="glass-card rounded-2xl p-5 border-l-4 border-l-emerald-500">
        <p class="text-sm font-medium text-slate-500">Pendaftaran Hari Ini</p>
        <h3 class="text-3xl font-bold text-slate-800 mt-2"><?= number_format($total_registrations, 0, ',', '.') ?></h3>
        <p class="text-xs text-slate-400 font-medium mt-2">Registrasi / Jadwal</p>
    </div>
    <div class="glass-card rounded-2xl p-5 border-l-4 border-l-amber-500">
        <p class="text-sm font-medium text-slate-500">Antrean Aktif</p>
        <h3 class="text-3xl font-bold text-slate-800 mt-2"><?= number_format($total_queues, 0, ',', '.') ?></h3>
        <p class="text-xs text-slate-400 font-medium mt-2">Menunggu atau Diperiksa</p>
    </div>
    <div class="glass-card rounded-2xl p-5 border-l-4 border-l-purple-500">
        <p class="text-sm font-medium text-slate-500">Faskes Aktif</p>
        <h3 class="text-3xl font-bold text-slate-800 mt-2"><?= number_format($total_faskes, 0, ',', '.') ?></h3>
        <p class="text-xs text-slate-400 font-medium mt-2">Puskesmas, RS, Klinik</p>
    </div>
</div>

<!-- Mock Chart Area & Recent Table -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 glass-card rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Tren Pendaftaran Mingguan</h3>
        <div class="w-full h-64 bg-slate-100 rounded-lg flex items-center justify-center border border-slate-200">
            <p class="text-slate-400 font-medium"><i class="fa-solid fa-chart-line text-2xl mb-2 block text-center"></i> Chart Area (Placeholder)</p>
        </div>
    </div>
    
    <div class="glass-card rounded-2xl p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Layanan Terpopuler</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-primary flex items-center justify-center"><i class="fa-solid fa-stethoscope"></i></div>
                    <div><p class="font-bold text-sm">Poli Umum</p><p class="text-xs text-slate-500">Pemeriksaan rutin</p></div>
                </div>
                <span class="font-bold text-slate-700">45%</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-tooth"></i></div>
                    <div><p class="font-bold text-sm">Poli Gigi</p><p class="text-xs text-slate-500">Perawatan gigi</p></div>
                </div>
                <span class="font-bold text-slate-700">25%</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-baby"></i></div>
                    <div><p class="font-bold text-sm">Poli Anak</p><p class="text-xs text-slate-500">Kesehatan anak</p></div>
                </div>
                <span class="font-bold text-slate-700">18%</span>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
