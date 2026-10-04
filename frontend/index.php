<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';

$active_queue = null;
$history = [];
$upcoming = null;

if (isset($_SESSION['user_id'])) {
    $user_id = $conn->real_escape_string($_SESSION['user_id']);
    $today = date('Y-m-d');
    
    // Active Queue Today
    $q_active = "SELECT p.*, f.nama as faskes_nama, po.nama_poli, pat.nama_lengkap as patient_name, jp.waktu_selesai 
                 FROM pendaftaran p
                 JOIN faskes f ON p.faskes_id = f.id
                 JOIN poli po ON p.poli_id = po.id
                 LEFT JOIN patients pat ON p.patient_id = pat.id
                 LEFT JOIN jadwal_poli jp ON p.faskes_id = jp.faskes_id AND p.poli_id = jp.poli_id AND p.tanggal_kunjungan = jp.tanggal AND p.waktu_kunjungan = jp.waktu_mulai
                 WHERE p.user_id = '$user_id' 
                 AND p.tanggal_kunjungan = '$today'
                 AND p.status IN ('terjadwal', 'menunggu', 'diperiksa')
                 ORDER BY p.waktu_daftar ASC";
    $res_active = $conn->query($q_active);
    $active_queue = null;
    $now = date('H:i:s');
    if ($res_active) {
        while ($row = $res_active->fetch_assoc()) {
            if ($row['status'] === 'terjadwal') {
                $waktu_batas = !empty($row['waktu_selesai']) ? $row['waktu_selesai'] : $row['waktu_kunjungan'];
                if ($waktu_batas < $now) {
                    continue; // Skip this, it's terlewat
                }
            }
            $active_queue = $row;
            break;
        }
    }

    // Recent History (max 3)
    $q_hist = "SELECT p.*, f.nama as faskes_nama, po.nama_poli 
               FROM pendaftaran p
               JOIN faskes f ON p.faskes_id = f.id
               JOIN poli po ON p.poli_id = po.id
               WHERE p.user_id = '$user_id' 
               AND p.status IN ('selesai', 'batal')
               ORDER BY p.tanggal_kunjungan DESC, p.waktu_daftar DESC LIMIT 3";
    $res_hist = $conn->query($q_hist);
    if ($res_hist) {
        while($row = $res_hist->fetch_assoc()) {
            $history[] = $row;
        }
    }

    // Upcoming Reminder (future dates)
    $q_upcoming = "SELECT p.*, f.nama as faskes_nama, po.nama_poli,
                   DATEDIFF(p.tanggal_kunjungan, '$today') as days_left
                   FROM pendaftaran p
                   JOIN faskes f ON p.faskes_id = f.id
                   JOIN poli po ON p.poli_id = po.id
                   WHERE p.user_id = '$user_id' 
                   AND p.tanggal_kunjungan > '$today'
                   AND p.status IN ('terjadwal', 'menunggu')
                   ORDER BY p.tanggal_kunjungan ASC LIMIT 1";
    $res_up = $conn->query($q_upcoming);
    if ($res_up && $res_up->num_rows > 0) {
        $upcoming = $res_up->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sehati - Portal Pasien</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#413074',
                        primaryDark: '#2c1e54',
                        secondary: '#f6f9f9',
                        accent: '#f59e0b', // Amber 500
                        success: '#10b981', // Emerald 500
                        warning: '#f59e0b',
                        danger: '#ef4444'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f6f9f9; }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="text-slate-800 pb-20 md:pb-0">

    <!-- Splash Screen -->
    <div id="splash-screen" class="fixed inset-0 bg-white z-[9999] flex flex-col items-center justify-center transition-opacity duration-500">
        <img src="/sehati/frontend/logo.png" alt="SEHATI Logo" class="h-28 mb-6 animate-bounce">
        <h1 class="text-primary text-3xl font-bold mb-2 tracking-wide">SEHATI</h1>
        <p class="text-slate-500 text-sm md:text-base font-medium text-center px-6">Sistem Elektronik Kesehatan Terintegrasi</p>
    </div>
    
    <script>
        // Splash screen fade out logic
        document.addEventListener('DOMContentLoaded', () => {
            const splash = document.getElementById('splash-screen');
            if (splash) {
                if (sessionStorage.getItem('splashShown')) {
                    splash.style.display = 'none';
                } else {
                    sessionStorage.setItem('splashShown', 'true');
                    setTimeout(() => {
                        splash.style.opacity = '0';
                        setTimeout(() => {
                            splash.style.display = 'none';
                        }, 500); 
                    }, 1800); 
                }
            }
        });
    </script>


    <!-- Mobile Top Bar -->
    <header class="md:hidden bg-primary text-white p-4 sticky top-0 z-50 shadow-md">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <img src="/sehati/frontend/logo.png" alt="SEHATI Logo" class="h-8 brightness-0 invert"> SEHATI
        </div>
    </header>

    <!-- Desktop Sidebar & Layout Wrapper -->
    <div class="flex min-h-screen">
        <!-- Desktop Sidebar -->
        <aside class="hidden md:flex flex-col w-64 bg-[#413074] text-white sticky top-0 h-screen shadow-xl z-10 shrink-0">
            <div class="p-6 pb-2">
                <h1 class="text-2xl font-bold flex items-center gap-2 text-white">
                    <img src="/sehati/frontend/logo.png" alt="SEHATI Logo" class="h-8 brightness-0 invert"> SEHATI
                </h1>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2 custom-scrollbar overflow-y-auto">
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-gradient-to-r from-[#A57BD7] to-[#A57BD7]/60 shadow-md text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10'; ?> rounded-xl transition-all">
                    <i class="fa-solid fa-house w-6 text-center"></i> Beranda
                </a>
                <a href="find.php" class="flex items-center gap-3 px-4 py-3 <?php echo basename($_SERVER['PHP_SELF']) == 'find.php' ? 'bg-gradient-to-r from-[#A57BD7] to-[#A57BD7]/60 shadow-md text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10'; ?> rounded-xl transition-all">
                    <i class="fa-solid fa-stethoscope w-6 text-center"></i> Cari Layanan & Faskes
                </a>
                <a href="jadwal.php" class="flex items-center gap-3 px-4 py-3 <?php echo basename($_SERVER['PHP_SELF']) == 'jadwal.php' ? 'bg-gradient-to-r from-[#A57BD7] to-[#A57BD7]/60 shadow-md text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10'; ?> rounded-xl transition-all">
                    <i class="fa-regular fa-calendar-check w-6 text-center"></i> Jadwal Saya
                </a>
                <a href="tarif.php" class="flex items-center gap-3 px-4 py-3 <?php echo basename($_SERVER['PHP_SELF']) == 'tarif.php' ? 'bg-gradient-to-r from-[#A57BD7] to-[#A57BD7]/60 shadow-md text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10'; ?> rounded-xl transition-all">
                    <i class="fa-solid fa-receipt w-6 text-center"></i> Info Tarif
                </a>
                <a href="tentang.php" class="flex items-center gap-3 px-4 py-3 <?php echo basename($_SERVER['PHP_SELF']) == 'tentang.php' ? 'bg-gradient-to-r from-[#A57BD7] to-[#A57BD7]/60 shadow-md text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10'; ?> rounded-xl transition-all">
                    <i class="fa-solid fa-circle-info w-6 text-center"></i> Tentang Sehati
                </a>
            </nav>
            
            <div class="px-6 pb-6 mt-auto">
                <div class="mb-6 relative">
                    <div class="relative w-14 h-14 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-heart text-[#A57BD7] text-5xl"></i>
                        <i class="fa-solid fa-plus absolute top-0 left-0 text-white text-sm drop-shadow-md"></i>
                        <i class="fa-solid fa-hand-holding-heart absolute text-[#413074] text-xl"></i>
                    </div>
                    <p class="font-bold text-[15px] leading-tight text-white mb-2">Sehat hari ini,<br><span class="font-normal text-slate-300">lebih baik esok</span></p>
                    <div class="w-10 h-1 bg-[#0fb7b8] rounded-full"></div>
                </div>
                
                <?php if (!isset($_SESSION["user_id"])): ?>
                <!-- Logged Out State -->
                <div class="space-y-3">
                    <a href="login.php" class="block w-full py-3 px-4 bg-[#A57BD7] text-white text-center text-sm font-bold rounded-xl hover:bg-[#9162c9] transition-colors shadow-sm">
                        Login
                    </a>
                    <a href="register.php" class="block w-full py-3 px-4 bg-transparent border border-white/30 text-white text-center text-sm font-bold rounded-xl hover:bg-white/10 transition-colors">
                        Registrasi
                    </a>
                </div>
                <?php else: 
                    $userName = $_SESSION["user_nama"];
                    $words = preg_split("/\s+/", trim($userName));
                    $formattedName = $userName;
                    if (count($words) > 3) {
                        $formattedName = implode(" ", array_slice($words, 0, 3)) . " ";
                        for ($i = 3; $i < count($words); $i++) {
                            $formattedName .= strtoupper(substr($words[$i], 0, 1)) . ". ";
                        }
                    }
                    $formattedName = trim($formattedName);
                ?>
                <!-- Logged In State -->
                <a href="profile.php" class="flex items-center gap-3 p-3 bg-white/10 hover:bg-white/20 transition-colors rounded-xl cursor-pointer">
                    <div class="w-10 h-10 rounded-full bg-[#A57BD7] text-white flex items-center justify-center shrink-0 border-2 border-white/20 shadow-sm">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-bold text-white truncate"><?php echo htmlspecialchars($formattedName); ?></p>
                        <p class="text-[11px] text-slate-300">Lihat Profil</p>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 w-full max-w-5xl mx-auto p-4 md:p-8 relative">
            
            <!-- Greeting -->
            <div class="mb-6 mt-2 md:mt-0">
                <h2 class="text-2xl md:text-3xl font-bold text-[#413074]">Selamat datang, <?php echo isset($formattedName) ? htmlspecialchars($formattedName) : 'Pengunjung'; ?> 👋</h2>
                <p class="text-slate-500 text-sm md:text-base mt-1">Semoga Anda selalu dalam keadaan sehat.</p>
            </div>

            <!-- Search Bar Shortcut -->
            <form action="find.php" method="GET" class="mb-8 relative hover-lift">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-[#413074] text-lg"></i>
                </div>
                <input type="text" name="q" placeholder="Cari layanan, poli, atau fasilitas kesehatan..." class="block w-full pl-12 pr-12 py-4 bg-white border border-slate-200 rounded-2xl text-slate-700 focus:outline-none focus:border-[#A57BD7] focus:ring-1 focus:ring-[#A57BD7] shadow-sm transition-colors placeholder-slate-400">
                <button type="submit" class="absolute inset-y-0 right-2 my-auto h-10 w-10 bg-[#413074] text-white rounded-xl flex items-center justify-center hover:bg-[#A57BD7] transition-colors">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <!-- Active Queue -->
            <div class="mb-8">
                <?php if ($active_queue): 
                    $aq_status = strtolower($active_queue['status']);
                    $header_text = 'ANTREAN AKTIF';
                    $btn_text = 'Lihat Status';
                    $status_display = strtoupper($aq_status);
                    
                    if ($aq_status === 'terjadwal') {
                        $header_text = 'JADWAL HARI INI';
                        $btn_text = 'Lihat Jadwal';
                        $status_display = 'TERJADWAL';
                    } elseif ($aq_status === 'menunggu') {
                        $status_display = 'MENUNGGU';
                    } elseif ($aq_status === 'diperiksa') {
                        $status_display = 'SEDANG DIPERIKSA';
                    }
                    
                    $fTime = date('H.i', strtotime($active_queue['waktu_kunjungan'])) . ' WIB';
                    $patientName = !empty($active_queue['patient_name']) ? $active_queue['patient_name'] : ($active_queue['nama'] ?? 'Pasien');
                ?>
                <div class="bg-white border border-slate-100 rounded-2xl p-5 md:p-6 flex flex-col md:flex-row gap-4 items-center justify-between shadow-sm hover-lift relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#A57BD7]/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                    <div class="flex items-center gap-4 w-full md:w-auto relative z-10">
                        <div class="w-14 h-14 rounded-full bg-[#413074] text-white flex items-center justify-center text-2xl shrink-0 shadow-md">
                            <i class="fa-solid fa-ticket"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#0fb7b8] uppercase tracking-wider mb-1"><?= $header_text ?></p>
                            <h3 class="font-bold text-[#413074] text-lg leading-tight"><?= htmlspecialchars($active_queue['faskes_nama']) ?></h3>
                            <p class="text-primary font-medium text-sm mb-1"><?= htmlspecialchars($active_queue['nama_poli']) ?></p>
                            
                            <div class="text-xs text-slate-500 mt-2 space-y-1">
                                <p><i class="fa-solid fa-user w-4 text-center"></i> Untuk: <span class="font-semibold text-slate-700"><?= htmlspecialchars($patientName) ?></span></p>
                                <p><i class="fa-regular fa-clock w-4 text-center"></i> <span class="font-semibold text-slate-700"><?= $fTime ?></span></p>
                            </div>
                            
                            <p class="text-sm text-slate-500 mt-2">No. Antrean <span class="font-bold text-lg text-[#413074] px-2 py-0.5 bg-[#A57BD7]/20 rounded ml-1">#<?= htmlspecialchars($active_queue['nomor_antrean']) ?></span></p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2 w-full md:w-auto relative z-10">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-[-4px]">STATUS:</p>
                        <div class="flex items-center gap-2 text-sm font-semibold text-emerald-600 mb-2 md:mb-0">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <?= $status_display ?>
                        </div>
                        <a href="ticket.php?id=<?= $active_queue['id'] ?>" class="w-full md:w-auto px-6 py-2.5 bg-[#413074] text-white text-sm font-bold rounded-xl text-center hover:bg-[#A57BD7] transition-colors shadow-sm">
                            <?= $btn_text ?> <i class="fa-solid fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white border border-slate-100 rounded-2xl p-6 md:p-8 flex flex-col items-center justify-center text-center shadow-sm">
                    <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-[#A57BD7] text-3xl mb-4">
                        <i class="fa-regular fa-calendar-xmark"></i>
                    </div>
                    <h3 class="font-bold text-[#413074] text-lg">Tidak ada antrean aktif</h3>
                    <p class="text-slate-500 text-sm mt-1 mb-5">Anda belum memiliki antrean untuk hari ini.</p>
                    <a href="find.php" class="px-6 py-2.5 bg-[#413074] text-white text-sm font-bold rounded-xl hover:bg-[#A57BD7] transition-colors shadow-sm">
                        Cari Layanan <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Layanan Utama -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-1 bg-[#0fb7b8] rounded-full"></div>
                    <h3 class="text-lg font-bold text-[#413074]">Layanan Utama</h3>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="find.php" class="bg-white rounded-2xl p-5 hover-lift flex flex-col items-start gap-3 h-auto group border border-slate-100 hover:border-[#A57BD7] transition-all shadow-sm relative overflow-hidden">
                        <div class="w-10 h-10 rounded-full bg-[#A57BD7]/10 text-[#413074] flex items-center justify-center text-lg group-hover:bg-[#413074] group-hover:text-white transition-colors">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-[#413074] text-sm md:text-base leading-tight mb-1">Cari Layanan<br>& Faskes</span>
                            <span class="text-xs text-slate-400">Temukan layanan terdekat.</span>
                        </div>
                        <i class="fa-solid fa-arrow-right absolute bottom-4 right-4 text-[#A57BD7] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </a>

                    <a href="jadwal.php" class="bg-white rounded-2xl p-5 hover-lift flex flex-col items-start gap-3 h-auto group border border-slate-100 hover:border-[#A57BD7] transition-all shadow-sm relative overflow-hidden">
                        <div class="w-10 h-10 rounded-full bg-[#A57BD7]/10 text-[#413074] flex items-center justify-center text-lg group-hover:bg-[#413074] group-hover:text-white transition-colors">
                            <i class="fa-regular fa-calendar-check"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-[#413074] text-sm md:text-base leading-tight mb-1">Jadwal<br>Saya</span>
                            <span class="text-xs text-slate-400">Kelola jadwal pemeriksaan.</span>
                        </div>
                        <i class="fa-solid fa-arrow-right absolute bottom-4 right-4 text-[#A57BD7] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </a>

                    <a href="keluarga.php" class="bg-white rounded-2xl p-5 hover-lift flex flex-col items-start gap-3 h-auto group border border-slate-100 hover:border-[#A57BD7] transition-all shadow-sm relative overflow-hidden">
                        <div class="w-10 h-10 rounded-full bg-[#A57BD7]/10 text-[#413074] flex items-center justify-center text-lg group-hover:bg-[#413074] group-hover:text-white transition-colors">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-[#413074] text-sm md:text-base leading-tight mb-1">Daftar<br>Keluarga</span>
                            <span class="text-xs text-slate-400">Kelola profil anggota.</span>
                        </div>
                        <i class="fa-solid fa-arrow-right absolute bottom-4 right-4 text-[#A57BD7] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </a>
                    
                    <a href="find.php" class="bg-white rounded-2xl p-5 hover-lift flex flex-col items-start gap-3 h-auto group border border-slate-100 hover:border-[#A57BD7] transition-all shadow-sm relative overflow-hidden">
                        <div class="w-10 h-10 rounded-full bg-[#A57BD7]/10 text-[#413074] flex items-center justify-center text-lg group-hover:bg-[#413074] group-hover:text-white transition-colors">
                            <i class="fa-solid fa-ticket-simple"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-[#413074] text-sm md:text-base leading-tight mb-1">Daftar<br>Berobat</span>
                            <span class="text-xs text-slate-400">Registrasi cepat layanan.</span>
                        </div>
                        <i class="fa-solid fa-arrow-right absolute bottom-4 right-4 text-[#A57BD7] opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </a>
                </div>
            </div>

            <!-- Riwayat Terakhir -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-1 bg-[#0fb7b8] rounded-full"></div>
                        <h3 class="text-lg font-bold text-[#413074]">Riwayat Terakhir</h3>
                    </div>
                    <a href="jadwal.php" class="text-sm font-bold text-[#A57BD7] hover:text-[#413074] transition-colors">Lihat Semua <i class="fa-solid fa-arrow-right text-xs ml-1"></i></a>
                </div>
                
                <?php if (count($history) > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach($history as $h): 
                        $status_class = strtolower($h['status']) === 'selesai' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100';
                        $icon = 'fa-stethoscope';
                        if(stripos($h['nama_poli'], 'gigi') !== false) $icon = 'fa-tooth';
                        if(stripos($h['nama_poli'], 'mata') !== false) $icon = 'fa-eye';
                        if(stripos($h['nama_poli'], 'anak') !== false) $icon = 'fa-child';
                    ?>
                    <div class="bg-white rounded-xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between hover-lift">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-[#F6F9F9] flex items-center justify-center text-[#413074] shrink-0">
                                <i class="fa-solid <?= $icon ?>"></i>
                            </div>
                            <div class="overflow-hidden">
                                <h4 class="font-bold text-[#413074] text-sm truncate"><?= htmlspecialchars($h['nama_poli']) ?></h4>
                                <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($h['faskes_nama']) ?></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-auto pt-3 border-t border-slate-50">
                            <p class="text-[11px] font-medium text-slate-400"><?= date('d F Y', strtotime($h['tanggal_kunjungan'])) ?> • <?= htmlspecialchars($h['nama_pasien'] ?? 'Pasien') ?></p>
                            <span class="px-2 py-0.5 border rounded-md text-[10px] font-bold uppercase <?= $status_class ?>"><?= htmlspecialchars($h['status']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-xl p-6 border border-slate-100 text-center shadow-sm">
                    <p class="text-slate-500 text-sm">Belum ada riwayat kunjungan medis.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pengingat Jadwal (If Any) -->
            <?php if ($upcoming): ?>
            <div class="bg-gradient-to-r from-blue-50 to-[#F6F9F9] border border-blue-100 rounded-2xl p-5 mb-8 flex items-center justify-between shadow-sm hover-lift">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 text-[#0fb7b8] text-2xl flex items-center justify-center animate-bounce">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-[#413074] text-sm">Pengingat Jadwal</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Anda memiliki jadwal <span class="font-bold text-slate-700"><?= htmlspecialchars($upcoming['nama_poli']) ?></span> dalam <?= $upcoming['days_left'] ?> hari ke depan.</p>
                    </div>
                </div>
                <a href="jadwal.php" class="px-4 py-2 bg-white border border-slate-200 text-[#413074] text-xs font-bold rounded-lg hover:bg-slate-50 transition-colors whitespace-nowrap shadow-sm">
                    Lihat Jadwal <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>
            <?php endif; ?>

        </main>
    </div>

        <!-- Mobile Bottom Nav -->
    <nav class="md:hidden fixed bottom-0 w-full bg-white border-t border-slate-200 flex justify-around items-center pb-safe pt-2 pb-2 z-50 px-2 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <a href="index.php" class="flex flex-col items-center p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
            <i class="fa-solid fa-house text-xl mb-1"></i>
            <span class="text-[10px] font-semibold">Beranda</span>
        </a>
        <a href="find.php" class="flex flex-col items-center p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'find.php' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
            <i class="fa-solid fa-stethoscope text-xl mb-1"></i>
            <span class="text-[10px] font-semibold">Cari</span>
        </a>
        <a href="jadwal.php" class="flex flex-col items-center p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'jadwal.php' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
            <i class="fa-regular fa-calendar-check text-xl mb-1"></i>
            <span class="text-[10px] font-semibold">Jadwal</span>
        </a>
        <a href="tarif.php" class="flex flex-col items-center p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'tarif.php' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
            <i class="fa-solid fa-receipt text-xl mb-1"></i>
            <span class="text-[10px] font-semibold">Tarif</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
            <i class="fa-regular fa-user text-xl mb-1"></i>
            <span class="text-[10px] font-semibold">Profil</span>
        </a>
    </nav>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 16px); }
    </style>
    
</body>
</html>




