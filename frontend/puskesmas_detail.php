<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
require_once '../config/helpers.php';

$faskes_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($faskes_id === 0) {
    header("Location: find.php");
    exit;
}

// Fetch Faskes
$sql = "SELECT * FROM faskes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $faskes_id);
$stmt->execute();
$faskes = $stmt->get_result()->fetch_assoc();

if (!$faskes) {
    header("Location: find.php");
    exit;
}

// Fetch Polis
$sql_poli = "SELECT p.id, p.nama_poli, p.deskripsi FROM poli p JOIN faskes_layanan fl ON p.id = fl.poli_id WHERE fl.faskes_id = ?";
$stmt_poli = $conn->prepare($sql_poli);
$stmt_poli->bind_param("i", $faskes_id);
$stmt_poli->execute();
$res_poli = $stmt_poli->get_result();
$polis = [];
while($p = $res_poli->fetch_assoc()) {
    $polis[] = $p;
}

$imageUrl = isset($faskes['image_url']) && $faskes['image_url'] ? $faskes['image_url'] : 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?q=80&w=600&auto=format&fit=crop';

// Mock Data for Advanced Features

$mockPuskesmas = [
    'fasilitas_umum' => [
        ['icon' => 'fa-wheelchair', 'nama' => 'Akses Difabel'],
        ['icon' => 'fa-square-parking', 'nama' => 'Area Parkir'],
        ['icon' => 'fa-restroom', 'nama' => 'Toilet Bersih'],
        ['icon' => 'fa-mosque', 'nama' => 'Mushola'],
        ['icon' => 'fa-couch', 'nama' => 'Ruang Tunggu'],
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Faskes - <?php echo htmlspecialchars($faskes['nama']); ?></title>
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
                        accent: '#0fb7b8',
                        accentDark: '#00b8c9',
                        highlight: '#A57BD7',
                        success: '#10b981',
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
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .poli-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .poli-card:hover {
            border-color: #A57BD7;
            transform: translateY(-2px);
        }
        .poli-card.selected {
            border-color: #413074;
            background-color: #f0f4ff; /* Slight tint */
            box-shadow: 0 0 0 2px #413074;
        }
        /* Custom Scrollbar for Tabs */
        .tab-scroll::-webkit-scrollbar { height: 4px; }
        .tab-scroll::-webkit-scrollbar-track { background: transparent; }
        .tab-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="text-slate-800 pb-20 md:pb-0">

    <!-- Mobile Top Bar -->
    <header class="md:hidden bg-primary text-white p-4 sticky top-0 z-50 shadow-md">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <img src="/sehati/frontend/logo.png" alt="SEHATI Logo" class="h-8 brightness-0 invert"> SEHATI
            </h1>
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
                <a href="find.php" class="flex items-center gap-3 px-4 py-3 <?php echo in_array(basename($_SERVER['PHP_SELF']), ['find.php', 'rs_detail.php', 'puskesmas_detail.php']) ? 'bg-gradient-to-r from-[#A57BD7] to-[#A57BD7]/60 shadow-md text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10'; ?> rounded-xl transition-all">
                    <i class="fa-solid fa-stethoscope w-6 text-center"></i> Cari Layanan & Faskes
                </a>
                <a href="jadwal.php" class="flex items-center gap-3 px-4 py-3 <?php echo basename($_SERVER['PHP_SELF']) == 'jadwal.php' ? 'bg-gradient-to-r from-[#A57BD7] to-[#A57BD7]/60 shadow-md text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10'; ?> rounded-xl transition-all">
                    <i class="fa-regular fa-calendar-check w-6 text-center"></i> Jadwal Saya
                </a>
                <a href="tarif.php" class="flex items-center gap-3 px-4 py-3 <?php echo basename($_SERVER['PHP_SELF']) == 'tarif.php' ? 'bg-gradient-to-r from-[#A57BD7] to-[#A57BD7]/60 shadow-md text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/10'; ?> rounded-xl transition-all">
                    <i class="fa-solid fa-receipt w-6 text-center"></i> Info Tarif dan Pembiayaan
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
        <main class="flex-1 w-full max-w-6xl mx-auto p-4 md:p-6 lg:p-8">
            
            <!-- Breadcrumb / Back -->
            <a href="find.php" class="inline-flex items-center text-primary font-medium hover:underline mb-6 text-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Cari Faskes
            </a>

            <!-- Hero Image Banner -->
            <div class="w-full h-[200px] md:h-[300px] rounded-2xl overflow-hidden mb-6 relative shadow-sm border border-slate-200">
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($faskes['nama']); ?>" class="w-full h-full object-cover object-center">
            </div>

            <!-- Header Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl shrink-0">
                            <i class="fa-solid fa-house-medical"></i>
                        </div>
                        <div>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-2 bg-emerald-100 text-emerald-700">
                                🟢 <?php echo htmlspecialchars($faskes['kategori']); ?>
                            </span>
                            <h1 class="text-2xl md:text-3xl font-bold text-slate-800 mb-1"><?php echo htmlspecialchars($faskes['nama']); ?></h1>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 w-full md:w-auto">
                        <?php 
                            $mapsQuery = str_replace('.', '', $faskes['nama']) . ' Surabaya';
                            $mapsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($mapsQuery);
                        ?>
                        <a href="<?php echo $mapsUrl; ?>" target="_blank" class="px-6 py-2.5 bg-white border border-slate-300 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-colors text-center shadow-sm">
                            <i class="fa-solid fa-map-location-dot mr-1"></i> Lihat di Peta
                        </a>
                    </div>
                </div>
            </div>

            <?php
            $masterLayanan = [
                'UMUM' => ['icon' => 'fa-stethoscope', 'color' => 'blue', 'tentang' => 'Pelayanan pemeriksaan kesehatan umum dan penanganan awal terhadap berbagai keluhan kesehatan.', 'yang_dilayani' => ['Pemeriksaan kondisi kesehatan umum', 'Keluhan kesehatan sehari-hari', 'Pemeriksaan awal', 'Penanganan awal sesuai kondisi', 'Rujukan apabila diperlukan']],
                'GIGI' => ['icon' => 'fa-tooth', 'color' => 'teal', 'tentang' => 'Pelayanan pemeriksaan dan perawatan kesehatan gigi dan mulut.', 'yang_dilayani' => ['Pemeriksaan kesehatan gigi dan mulut', 'Keluhan gigi berlubang', 'Keluhan gusi dan mulut', 'Perawatan kesehatan gigi dasar', 'Edukasi kesehatan dan kebersihan gigi']],
                'ANAK' => ['icon' => 'fa-child', 'color' => 'rose', 'tentang' => 'Pelayanan kesehatan bagi bayi, anak, dan pasien usia anak sesuai layanan yang tersedia di Puskesmas.', 'yang_dilayani' => ['Pemeriksaan kesehatan anak', 'Keluhan kesehatan pada anak', 'Pemantauan kesehatan anak', 'Edukasi kesehatan anak']],
                'KIA' => ['icon' => 'fa-person-breastfeeding', 'color' => 'fuchsia', 'tentang' => 'Pelayanan kesehatan ibu dan anak sesuai layanan yang tersedia di Puskesmas.', 'yang_dilayani' => ['Pemeriksaan kesehatan ibu', 'Pemantauan kesehatan ibu dan anak', 'Konseling kesehatan ibu dan anak', 'Pelayanan sesuai kebutuhan ibu dan anak']],
                'LANSIA' => ['icon' => 'fa-person-cane', 'color' => 'emerald', 'tentang' => 'Pelayanan kesehatan yang ditujukan bagi masyarakat lanjut usia.', 'yang_dilayani' => ['Pemeriksaan kesehatan lansia', 'Pemantauan kondisi kesehatan', 'Konsultasi keluhan kesehatan', 'Pemantauan faktor risiko penyakit']],
                'PSIKOLOGI' => ['icon' => 'fa-brain', 'color' => 'pink', 'tentang' => 'Pelayanan konsultasi dan dukungan psikologis sesuai layanan yang tersedia di Puskesmas.', 'yang_dilayani' => ['Konsultasi psikologis', 'Konseling', 'Keluhan terkait kondisi emosional dan psikologis', 'Skrining/asesmen sesuai layanan yang tersedia']],
                'GIZI' => ['icon' => 'fa-apple-whole', 'color' => 'amber', 'tentang' => 'Pelayanan yang berkaitan dengan penilaian dan pengelolaan kebutuhan gizi.', 'yang_dilayani' => ['Konsultasi gizi', 'Penilaian status gizi', 'Edukasi pola makan', 'Konseling kebutuhan gizi']],
                'PARU' => ['icon' => 'fa-lungs', 'color' => 'sky', 'tentang' => 'Pelayanan pemeriksaan dan penanganan keluhan yang berkaitan dengan sistem pernapasan.', 'yang_dilayani' => ['Pemeriksaan keluhan pernapasan', 'Konsultasi kesehatan paru', 'Penilaian awal gangguan pernapasan']],
                'VCT' => ['icon' => 'fa-vial-virus', 'color' => 'red', 'tentang' => 'Pelayanan konseling dan pemeriksaan HIV secara sukarela sesuai prosedur layanan.', 'yang_dilayani' => ['Konseling sebelum pemeriksaan', 'Pemeriksaan HIV sesuai prosedur', 'Konseling setelah pemeriksaan', 'Edukasi terkait HIV']],
                'PKPR' => ['icon' => 'fa-users', 'color' => 'indigo', 'tentang' => 'Pelayanan kesehatan yang ditujukan bagi remaja.', 'yang_dilayani' => ['Konsultasi kesehatan remaja', 'Edukasi kesehatan remaja', 'Konseling', 'Skrining sesuai layanan yang tersedia']],
                'P2M' => ['icon' => 'fa-shield-virus', 'color' => 'orange', 'tentang' => 'Layanan yang mendukung pencegahan dan pengendalian penyakit menular.', 'yang_dilayani' => ['Edukasi pencegahan penyakit menular', 'Pemantauan dan pengendalian penyakit menular', 'Konsultasi terkait risiko penularan', 'Tindak lanjut sesuai program Puskesmas']],
                'SANITASI' => ['icon' => 'fa-faucet-drip', 'color' => 'cyan', 'tentang' => 'Pelayanan yang berkaitan dengan kesehatan lingkungan dan sanitasi.', 'yang_dilayani' => ['Konsultasi kesehatan lingkungan', 'Edukasi sanitasi', 'Penilaian faktor lingkungan yang dapat memengaruhi kesehatan', 'Edukasi higiene dan sanitasi']],
                'PENGOBATAN TRADISIONAL' => ['icon' => 'fa-leaf', 'color' => 'lime', 'tentang' => 'Pelayanan terkait pemanfaatan pengobatan tradisional sesuai layanan yang tersedia di Puskesmas.', 'yang_dilayani' => ['Konsultasi pemanfaatan pengobatan tradisional', 'Edukasi penggunaan yang aman', 'Pelayanan sesuai jenis layanan yang tersedia']],
                'PKG' => ['icon' => 'fa-file-medical', 'color' => 'purple', 'tentang' => 'Layanan pemeriksaan kesehatan gratis untuk mendukung deteksi dan pemantauan kondisi kesehatan masyarakat sesuai ketentuan program yang berlaku.', 'yang_dilayani' => ['Pemeriksaan kesehatan', 'Skrining kesehatan sesuai program', 'Identifikasi faktor risiko', 'Edukasi kesehatan', 'Tindak lanjut sesuai hasil pemeriksaan']],
                'MEDICAL CHECK UP' => ['icon' => 'fa-notes-medical', 'color' => 'teal', 'tentang' => 'Pelayanan pemeriksaan kesehatan untuk mengetahui kondisi kesehatan secara menyeluruh sesuai jenis pemeriksaan yang tersedia.', 'yang_dilayani' => ['Pemeriksaan kesehatan', 'Skrining kesehatan', 'Pemeriksaan sesuai paket/jenis layanan yang tersedia']],
                'HAMIL' => ['icon' => 'fa-person-pregnant', 'color' => 'fuchsia', 'tentang' => 'Pelayanan kesehatan yang berkaitan dengan pemeriksaan dan pemantauan kehamilan sesuai layanan yang tersedia.', 'yang_dilayani' => ['Pemeriksaan kehamilan', 'Pemantauan kondisi ibu', 'Edukasi kesehatan kehamilan']],
                'TUMBUH KEMBANG' => ['icon' => 'fa-child-reaching', 'color' => 'blue', 'tentang' => 'Pelayanan pemantauan tumbuh kembang anak sesuai layanan yang tersedia.', 'yang_dilayani' => ['Pemantauan pertumbuhan anak', 'Pemantauan perkembangan anak', 'Edukasi kepada orang tua/wali', 'Tindak lanjut sesuai hasil pemantauan']],
                'REHAB MEDIC FISIOTHERAPY' => ['icon' => 'fa-person-walking-with-cane', 'color' => 'emerald', 'tentang' => 'Pelayanan rehabilitasi untuk membantu pemulihan dan mempertahankan fungsi tubuh sesuai layanan yang tersedia.', 'yang_dilayani' => ['Evaluasi kondisi yang membutuhkan rehabilitasi', 'Program latihan/terapi sesuai layanan', 'Pemantauan perkembangan pemulihan']],
                'STD' => ['icon' => 'fa-viruses', 'color' => 'rose', 'tentang' => 'Pelayanan yang berkaitan dengan pemeriksaan dan penanganan penyakit menular seksual sesuai layanan yang tersedia.', 'yang_dilayani' => ['Konsultasi', 'Pemeriksaan sesuai indikasi', 'Edukasi pencegahan penularan', 'Tindak lanjut sesuai layanan']],
                'PELAYANAN ANAK BERKEBUTUHAN KHUSUS (ABK)' => ['icon' => 'fa-hands-holding-child', 'color' => 'sky', 'tentang' => 'Pelayanan yang mendukung kebutuhan kesehatan anak berkebutuhan khusus sesuai layanan yang tersedia.', 'yang_dilayani' => ['Konsultasi kesehatan anak', 'Pemantauan kebutuhan kesehatan', 'Edukasi kepada orang tua/wali', 'Tindak lanjut sesuai kebutuhan']],
                'DEGENERATIF' => ['icon' => 'fa-crutch', 'color' => 'amber', 'tentang' => 'Pelayanan yang berkaitan dengan pemantauan dan pengelolaan kondisi penyakit degeneratif sesuai layanan yang tersedia.', 'yang_dilayani' => ['Pemeriksaan dan pemantauan kondisi kesehatan', 'Edukasi kesehatan', 'Pemantauan faktor risiko']],
                'PALIATIF' => ['icon' => 'fa-bed-pulse', 'color' => 'pink', 'tentang' => 'Pelayanan yang berfokus pada dukungan dan peningkatan kualitas hidup pasien dengan kondisi tertentu sesuai layanan yang tersedia.', 'yang_dilayani' => ['Dukungan sesuai kebutuhan pasien', 'Pemantauan kondisi', 'Edukasi dan dukungan bagi keluarga', 'Tindak lanjut sesuai layanan']],
                'LOKET' => ['icon' => 'fa-clipboard-user', 'color' => 'slate', 'tentang' => 'Layanan administrasi untuk membantu kebutuhan pendaftaran dan proses administrasi pelayanan di Puskesmas.', 'yang_dilayani' => ['Pendaftaran', 'Proses administrasi pelayanan'], 'is_admin' => true]
            ];

            $layananMedis = [];
            $layananAdmin = [];
            
            foreach ($polis as $p) {
                $namaUpper = strtoupper(trim($p['nama_poli']));
                $masterData = isset($masterLayanan[$namaUpper]) ? $masterLayanan[$namaUpper] : null;
                
                $item = [
                    'id' => $p['id'],
                    'nama' => $p['nama_poli'],
                    'icon' => $masterData ? $masterData['icon'] : 'fa-stethoscope',
                    'color' => $masterData ? $masterData['color'] : 'slate',
                    'tentang' => $masterData ? $masterData['tentang'] : 'Informasi detail layanan belum tersedia.',
                    'yang_dilayani' => $masterData ? $masterData['yang_dilayani'] : [],
                    'is_admin' => $masterData && isset($masterData['is_admin']) ? $masterData['is_admin'] : false
                ];

                if ($item['is_admin'] || strpos($namaUpper, 'LOKET') !== false) {
                    $item['is_admin'] = true;
                    $layananAdmin[] = $item;
                } else {
                    $layananMedis[] = $item;
                }
            }

            function renderPuskesmasAccordion($items, $prefix, $faskesId) {
                foreach ($items as $index => $item) {
                    $accId = 'acc-' . $prefix . '-' . $index;
                    ?>
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <button onclick="toggleAccordion('<?php echo $accId; ?>')" class="w-full flex items-center justify-between p-4 bg-<?php echo $item['color']; ?>-50/50 hover:bg-<?php echo $item['color']; ?>-50 transition-colors text-left focus:outline-none group">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-<?php echo $item['color']; ?>-500 shadow-sm group-hover:scale-105 transition-transform">
                                    <i class="fa-solid <?php echo $item['icon']; ?> text-xl"></i>
                                </div>
                                <span class="font-bold text-slate-800 text-lg uppercase"><?php echo $item['nama']; ?></span>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm">
                                <i id="icon-<?php echo $accId; ?>" class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300"></i>
                            </div>
                        </button>
                        <div id="<?php echo $accId; ?>" class="hidden px-5 pb-6 pt-3 border-t border-slate-100">
                            <div class="mb-5">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tentang Layanan</h4>
                                <p class="text-slate-600 text-[15px] leading-relaxed"><?php echo $item['tentang']; ?></p>
                            </div>
                            
                            <?php if(!empty($item['yang_dilayani'])): ?>
                            <div class="mb-6">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Yang Dilayani</h4>
                                <ul class="space-y-2">
                                    <?php foreach($item['yang_dilayani'] as $yd): ?>
                                    <li class="flex items-start gap-2 text-slate-700 text-sm">
                                        <i class="fa-solid fa-circle-check text-<?php echo $item['color']; ?>-500 mt-0.5"></i>
                                        <span><?php echo $yd; ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <?php if (!$item['is_admin']): ?>
                            <div class="mt-4">
                                <button onclick="window.location.href='book.php?faskes_id=<?php echo $faskesId; ?>&poli_id=<?php echo $item['id']; ?>'" class="px-6 py-2.5 bg-primary hover:bg-primaryDark text-white font-bold text-sm rounded-xl transition-colors shadow-sm flex items-center gap-2 w-full justify-center md:w-auto md:justify-start">
                                    <i class="fa-solid fa-calendar-plus"></i> Daftar Berobat
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>

            <!-- SECTION: Informasi Faskes -->
            <div class="mb-10 animate-fade-in-up">
                <h2 class="text-2xl md:text-3xl font-bold text-[#413074] leading-tight mb-6">Informasi Faskes</h2>
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Alamat Lengkap</p>
                            <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($faskes['alamat']); ?></p>
                            <p class="text-sm text-slate-500 mt-1">Wilayah Surabaya <?php echo htmlspecialchars($faskes['wilayah']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Jam Pelayanan</p>
                            <p class="font-semibold text-slate-800"><?php echo $faskes['jam_pelayanan'] ? formatJamPelayanan($faskes['jam_pelayanan']) : '-'; ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Nomor Telepon/Kontak</p>
                            <p class="font-semibold text-slate-800"><?php echo htmlspecialchars($faskes['telepon'] ? $faskes['telepon'] : '-'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Layanan yang Tersedia -->
            <div class="mb-10 animate-fade-in-up">
                <h2 class="text-2xl md:text-3xl font-bold text-[#413074] leading-tight mb-6">Layanan yang Tersedia</h2>
                
                <?php if(empty($layananMedis)): ?>
                    <div class="p-6 text-center text-slate-500 bg-white rounded-xl border border-slate-200">
                        Informasi layanan belum tersedia.
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php renderPuskesmasAccordion($layananMedis, 'medis', $faskes_id); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($layananAdmin)): ?>
                    <h2 class="text-2xl md:text-3xl font-bold text-[#413074] leading-tight mb-6 mt-10 border-t border-slate-200 pt-10">Layanan Administrasi</h2>
                    <div class="space-y-3">
                        <?php renderPuskesmasAccordion($layananAdmin, 'admin', $faskes_id); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SECTION: Fasilitas -->
            <div class="mb-10 animate-fade-in-up border-t border-slate-200 pt-10">
                <h2 class="text-2xl md:text-3xl font-bold text-[#413074] leading-tight mb-6">Fasilitas</h2>
                <div class="flex flex-wrap gap-4">
                    <?php 
                    $fasilitasArr = $mockPuskesmas['fasilitas_umum'];
                    foreach($fasilitasArr as $fu): 
                    ?>
                    <div class="bg-white px-5 py-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                        <i class="fa-solid <?php echo $fu['icon']; ?> text-slate-400 text-lg"></i>
                        <span class="font-semibold text-slate-700 text-sm"><?php echo $fu['nama']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>


            
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

    <script>
    if (typeof toggleAccordion !== 'function') {
        function toggleAccordion(id) {
            const el = document.getElementById(id);
            const icon = document.getElementById('icon-' + id);
            
            // Close other accordions in the same group (optional, but good UX for grid)
            const allAccordions = document.querySelectorAll('[id^="acc-"]');
            const allIcons = document.querySelectorAll('[id^="icon-acc-"]');
            
            if (el.classList.contains('hidden')) {
                // Hide others first
                allAccordions.forEach(acc => {
                    if (acc.id !== id && !acc.classList.contains('hidden')) {
                        acc.classList.add('hidden');
                    }
                });
                allIcons.forEach(ic => {
                    if (ic.id !== 'icon-' + id && ic.classList.contains('rotate-180')) {
                        ic.classList.remove('rotate-180');
                    }
                });
                
                // Show current
                el.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                el.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }
    }
    </script>
</body>
</html>
