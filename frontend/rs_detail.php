<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';

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
$layananNames = [];
while($p = $res_poli->fetch_assoc()) {
    $polis[] = $p;
    $layananNames[] = $p['nama_poli'];
}
$faskes['layanan'] = $layananNames;
$detailsJson = htmlspecialchars(json_encode($faskes), ENT_QUOTES, 'UTF-8');

$imageUrl = isset($faskes['image_url']) && $faskes['image_url'] ? $faskes['image_url'] : 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?q=80&w=600&auto=format&fit=crop';

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
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 16px); }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
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
        <main class="flex-1 w-full flex flex-col bg-white pb-10 relative">
            <!-- Hero Section -->
            <div class="relative w-full h-[250px] sm:h-[350px] md:h-[450px]">
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($faskes['nama']); ?>" class="w-full h-full object-cover object-center">
                <div class="absolute inset-0 bg-white/40"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-white via-white/90 to-transparent"></div>
                
                <!-- Back Button -->
                <a href="find.php" class="absolute top-6 left-6 md:left-12 z-20 w-11 h-11 bg-white/80 backdrop-blur border border-slate-200 text-slate-700 flex items-center justify-center rounded-xl shadow-sm hover:bg-white hover:text-primary transition-all group">
                    <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                </a>
                


                <div class="absolute bottom-10 left-0 w-full px-6 md:px-12 text-center">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-2"><?php echo htmlspecialchars($faskes['nama']); ?></h1>
                    <p class="text-lg text-slate-700 font-medium max-w-3xl mx-auto">merupakan rumah sakit pilihan dan unggulan milik Pemerintah Kota Surabaya</p>
                    
                    <div class="mt-8 flex flex-wrap justify-center gap-4 md:gap-8">
                        <?php
                            $nama_lower = strtolower($faskes['nama']);
                            $website = '#';
                            $ig = '#';
                            $lokasi = '#';
                            $ig_handle = '';
                            
                            if (strpos($nama_lower, 'soewandhie') !== false) {
                                $website = 'https://rs-soewandhi.surabaya.go.id/';
                                $ig = 'https://instagram.com/rssoewandhie';
                                $ig_handle = '@rssoewandhie';
                                $lokasi = 'https://maps.app.goo.gl/P3KPFrz7D9Uptjat8';
                            } elseif (strpos($nama_lower, 'eka candra') !== false || strpos($nama_lower, 'ekacandrarini') !== false) {
                                $website = 'https://rsudekacandrarini.surabaya.go.id/';
                                $ig = 'https://instagram.com/rsudekacandrarini';
                                $ig_handle = '@rsudekacandrarini';
                                $lokasi = 'https://maps.app.goo.gl/x1vwoALaWdht6TFd6';
                            } elseif (strpos($nama_lower, 'bhakti dharma') !== false || strpos($nama_lower, 'bdh') !== false) {
                                $website = 'https://rsudbdh.surabaya.go.id/';
                                $ig = 'https://instagram.com/rsudbhaktidharmahusada';
                                $ig_handle = '@rsudbhaktidharmahusada';
                                $lokasi = 'https://maps.app.goo.gl/RCZAu1AvURaYUpMm6';
                            }
                        ?>
                        <?php if ($ig_handle !== ''): ?>
                        <a href="<?php echo $website; ?>" target="_blank" class="flex items-center gap-3 bg-white/70 hover:bg-white px-4 py-2.5 rounded-full backdrop-blur transition-all shadow-sm border border-white/50 group">
                            <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-base shadow-sm group-hover:scale-110 transition-transform text-[#413074]">
                                <i class="fa-solid fa-globe"></i>
                            </div>
                            <span class="font-bold text-sm text-slate-800 pr-2">Website</span>
                        </a>
                        <a href="<?php echo $ig; ?>" target="_blank" class="flex items-center gap-3 bg-white/70 hover:bg-white px-4 py-2.5 rounded-full backdrop-blur transition-all shadow-sm border border-white/50 group">
                            <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-base shadow-sm group-hover:scale-110 transition-transform text-pink-600">
                                <i class="fa-brands fa-instagram"></i>
                            </div>
                            <span class="font-bold text-sm text-slate-800 pr-2"><?php echo $ig_handle; ?></span>
                        </a>
                        <a href="<?php echo $lokasi; ?>" target="_blank" class="flex items-center gap-3 bg-white/70 hover:bg-white px-4 py-2.5 rounded-full backdrop-blur transition-all shadow-sm border border-white/50 group">
                            <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-base shadow-sm group-hover:scale-110 transition-transform text-red-500">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <span class="font-bold text-sm text-slate-800 pr-2">Lokasi RS</span>
                        </a>
                        <?php else: ?>
                        <!-- Default -->
                        <div class="flex items-center gap-3 bg-white/70 px-4 py-2.5 rounded-full backdrop-blur shadow-sm border border-white/50">
                            <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-base shadow-sm text-slate-700">
                                <i class="fa-regular fa-clock"></i>
                            </div>
                            <span class="font-bold text-sm text-slate-800 pr-2">Jam Operasional 24 Jam</span>
                        </div>
                        <div class="flex items-center gap-3 bg-white/70 px-4 py-2.5 rounded-full backdrop-blur shadow-sm border border-white/50">
                            <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-base shadow-sm text-slate-700">
                                <i class="fa-solid fa-user-doctor"></i>
                            </div>
                            <span class="font-bold text-sm text-slate-800 pr-2">Tim Medis Profesional</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="px-6 md:px-12 max-w-6xl mx-auto mt-10">
                <?php
                $selected_layanan = isset($_GET['layanan']) ? $_GET['layanan'] : '';
                $is_eka_candrarini = (strpos(strtolower($faskes['nama']), 'eka candrarini') !== false);
                ?>
                
                <h3 class="text-sm font-bold text-slate-500 tracking-widest uppercase mb-4 <?php echo $is_eka_candrarini ? 'hidden' : ''; ?>">LAYANAN RUMAH SAKIT</h3>
                <div class="<?php echo $is_eka_candrarini ? 'hidden' : 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-12'; ?>">

                    <!-- Rawat Jalan -->
                    <a href="rs_detail.php?id=<?php echo $faskes_id; ?><?php echo $selected_layanan === 'rawat_jalan' ? '' : '&layanan=rawat_jalan'; ?>" class="bg-white rounded-2xl p-5 border <?php echo $selected_layanan === 'rawat_jalan' ? 'border-[#A57BD7] shadow-md ring-1 ring-[#A57BD7]/30' : 'border-slate-200 shadow-sm'; ?> hover:-translate-y-1 hover:shadow-md hover:border-[#A57BD7] transition-all cursor-pointer group flex flex-col justify-between h-40 relative">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full <?php echo $selected_layanan === 'rawat_jalan' ? 'bg-[#413074] text-white' : 'bg-[#F6F9F9] text-[#413074] group-hover:bg-[#A57BD7]/10 group-hover:text-[#A57BD7]'; ?> flex items-center justify-center text-lg transition-colors">
                                <i class="fa-solid fa-stethoscope"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#413074] text-sm md:text-base leading-tight mb-1">Rawat Jalan</h4>
                            <p class="text-[11px] text-slate-500 leading-snug">Poli & layanan kunjungan medis</p>
                        </div>
                        <div class="absolute top-5 right-5 text-[#A57BD7] opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </a>
                    
                    <!-- Rawat Inap -->
                    <a href="rs_detail.php?id=<?php echo $faskes_id; ?><?php echo $selected_layanan === 'rawat_inap' ? '' : '&layanan=rawat_inap'; ?>" class="bg-white rounded-2xl p-5 border <?php echo $selected_layanan === 'rawat_inap' ? 'border-[#A57BD7] shadow-md ring-1 ring-[#A57BD7]/30' : 'border-slate-200 shadow-sm'; ?> hover:-translate-y-1 hover:shadow-md hover:border-[#A57BD7] transition-all cursor-pointer group flex flex-col justify-between h-40 relative">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full <?php echo $selected_layanan === 'rawat_inap' ? 'bg-[#413074] text-white' : 'bg-[#F6F9F9] text-[#413074] group-hover:bg-[#A57BD7]/10 group-hover:text-[#A57BD7]'; ?> flex items-center justify-center text-lg transition-colors">
                                <i class="fa-solid fa-bed-pulse"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#413074] text-sm md:text-base leading-tight mb-1">Rawat Inap</h4>
                            <p class="text-[11px] text-slate-500 leading-snug">Informasi ruang dan perawatan rawat inap</p>
                        </div>
                        <div class="absolute top-5 right-5 text-[#A57BD7] opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Perawatan Intensif -->
                    <a href="rs_detail.php?id=<?php echo $faskes_id; ?><?php echo $selected_layanan === 'perawatan_intensif' ? '' : '&layanan=perawatan_intensif'; ?>" class="bg-white rounded-2xl p-5 border <?php echo $selected_layanan === 'perawatan_intensif' ? 'border-[#A57BD7] shadow-md ring-1 ring-[#A57BD7]/30' : 'border-slate-200 shadow-sm'; ?> hover:-translate-y-1 hover:shadow-md hover:border-[#A57BD7] transition-all cursor-pointer group flex flex-col justify-between h-40 relative">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full <?php echo $selected_layanan === 'perawatan_intensif' ? 'bg-[#413074] text-white' : 'bg-[#F6F9F9] text-[#413074] group-hover:bg-[#A57BD7]/10 group-hover:text-[#A57BD7]'; ?> flex items-center justify-center text-lg transition-colors">
                                <i class="fa-solid fa-heart-pulse"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#413074] text-sm md:text-base leading-tight mb-1">Perawatan Intensif</h4>
                            <p class="text-[11px] text-slate-500 leading-snug">Layanan perawatan intensif dan unit khusus</p>
                        </div>
                        <div class="absolute top-5 right-5 text-[#A57BD7] opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Layanan Darurat -->
                    <a href="rs_detail.php?id=<?php echo $faskes_id; ?><?php echo $selected_layanan === 'layanan_darurat' ? '' : '&layanan=layanan_darurat'; ?>" class="bg-white rounded-2xl p-5 border <?php echo $selected_layanan === 'layanan_darurat' ? 'border-[#A57BD7] shadow-md ring-1 ring-[#A57BD7]/30' : 'border-slate-200 shadow-sm'; ?> hover:-translate-y-1 hover:shadow-md hover:border-[#A57BD7] transition-all cursor-pointer group flex flex-col justify-between h-40 relative">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full <?php echo $selected_layanan === 'layanan_darurat' ? 'bg-[#413074] text-white' : 'bg-[#F6F9F9] text-[#413074] group-hover:bg-[#A57BD7]/10 group-hover:text-[#A57BD7]'; ?> flex items-center justify-center text-lg transition-colors">
                                <i class="fa-solid fa-truck-medical"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#413074] text-sm md:text-base leading-tight mb-1">Layanan Darurat</h4>
                            <p class="text-[11px] text-slate-500 leading-snug">Informasi layanan gawat darurat dan IGD</p>
                        </div>
                        <div class="absolute top-5 right-5 text-[#A57BD7] opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </a>

                    <!-- Penunjang Medis -->
                    <a href="rs_detail.php?id=<?php echo $faskes_id; ?><?php echo $selected_layanan === 'penunjang_medis' ? '' : '&layanan=penunjang_medis'; ?>" class="bg-white rounded-2xl p-5 border <?php echo $selected_layanan === 'penunjang_medis' ? 'border-[#A57BD7] shadow-md ring-1 ring-[#A57BD7]/30' : 'border-slate-200 shadow-sm'; ?> hover:-translate-y-1 hover:shadow-md hover:border-[#A57BD7] transition-all cursor-pointer group flex flex-col justify-between h-40 relative">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full <?php echo $selected_layanan === 'penunjang_medis' ? 'bg-[#413074] text-white' : 'bg-[#F6F9F9] text-[#413074] group-hover:bg-[#A57BD7]/10 group-hover:text-[#A57BD7]'; ?> flex items-center justify-center text-lg transition-colors">
                                <i class="fa-solid fa-microscope"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#413074] text-sm md:text-base leading-tight mb-1">Penunjang Medis</h4>
                            <p class="text-[11px] text-slate-500 leading-snug">Pemeriksaan lab dan layanan penunjang</p>
                        </div>
                        <div class="absolute top-5 right-5 text-[#A57BD7] opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </a>
                </div>

                <?php if ($is_eka_candrarini): ?>
                <div class="mb-12 animate-fade-in-up">
                    <h2 class="text-2xl md:text-3xl font-bold text-[#413074] leading-tight mb-6">Layanan Spesialis</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10 items-start">
                        <?php
                        $eka_utama = [
                            ['nama' => 'Klinik Paru', 'icon' => 'fa-lungs', 'color' => 'blue', 'deskripsi' => 'Klinik Paru menyediakan pelayanan pemeriksaan dan penanganan yang berkaitan dengan kesehatan paru dan sistem pernapasan.', 'cakupan' => ['Pdp', 'Paru']],
                            ['nama' => 'Klinik Syaraf', 'icon' => 'fa-brain', 'color' => 'purple', 'deskripsi' => 'Klinik Syaraf menyediakan pelayanan pemeriksaan dan penanganan medis yang berkaitan dengan sistem saraf pusat dan perifer.', 'cakupan' => ['Syaraf']],
                            ['nama' => 'Klinik Jantung', 'icon' => 'fa-heart-pulse', 'color' => 'rose', 'deskripsi' => 'Klinik Jantung menyediakan pelayanan pemeriksaan dan penanganan yang berkaitan dengan kesehatan jantung dan pembuluh darah.', 'cakupan' => ['Jantung']],
                            ['nama' => 'Klinik Bedah Umum', 'icon' => 'fa-scalpel', 'color' => 'indigo', 'deskripsi' => 'Klinik Bedah Umum menyediakan pelayanan bedah untuk berbagai kondisi medis yang memerlukan tindakan operatif.', 'cakupan' => ['Bedah Umum']],
                            ['nama' => 'Klinik Bedah Plastik', 'icon' => 'fa-user-doctor', 'color' => 'cyan', 'deskripsi' => 'Klinik Bedah Plastik menyediakan pelayanan bedah rekonstruksi dan estetika.', 'cakupan' => ['Bedah Plastik']],
                            ['nama' => 'Klinik Penyakit Dalam', 'icon' => 'fa-stethoscope', 'color' => 'emerald', 'deskripsi' => 'Klinik Penyakit Dalam menyediakan pelayanan pemeriksaan dan penanganan berbagai kondisi kesehatan pada orang dewasa.', 'cakupan' => ['Penyakit Dalam', 'Penyakit Dalam Sore']],
                            ['nama' => 'Klinik Orthopedi', 'icon' => 'fa-bone', 'color' => 'orange', 'deskripsi' => 'Klinik Orthopedi menyediakan pelayanan untuk penanganan masalah kesehatan pada tulang, sendi, dan sistem muskuloskeletal.', 'cakupan' => ['Orthopedi']],
                            ['nama' => 'Klinik MCU', 'icon' => 'fa-notes-medical', 'color' => 'teal', 'deskripsi' => 'Klinik MCU menyediakan layanan pemeriksaan kesehatan secara menyeluruh (Medical Check Up).', 'cakupan' => ['Medical Check Up', 'Medical Check Up Pppk Paruh Waktu', 'Medical Check Up Pppk']],
                            ['nama' => 'Klinik Mata', 'icon' => 'fa-eye', 'color' => 'cyan', 'deskripsi' => 'Klinik Mata menyediakan pelayanan kesehatan mata komprehensif mulai dari pemeriksaan dasar hingga penanganan penyakit mata.', 'cakupan' => ['Mata']],
                            ['nama' => 'Klinik THT', 'icon' => 'fa-ear-listen', 'color' => 'purple', 'deskripsi' => 'Klinik THT menyediakan penanganan berbagai masalah kesehatan pada telinga, hidung, dan tenggorokan.', 'cakupan' => ['Tht']],
                            ['nama' => 'Klinik Gigi Umum', 'icon' => 'fa-tooth', 'color' => 'teal', 'deskripsi' => 'Klinik Gigi Umum menyediakan pelayanan pemeriksaan kesehatan dasar dan umum untuk gigi dan mulut.', 'cakupan' => ['Gigi Umum', 'Gigi Umum Sore']],
                            ['nama' => 'Klinik Konservasi Gigi', 'icon' => 'fa-tooth', 'color' => 'sky', 'deskripsi' => 'Klinik Konservasi Gigi menyediakan pelayanan perawatan saluran akar dan restorasi gigi.', 'cakupan' => ['Konservasi Gigi (Endodontis)']],
                            ['nama' => 'Klinik Gigi Prostodonti', 'icon' => 'fa-tooth', 'color' => 'indigo', 'deskripsi' => 'Klinik Gigi Prostodonti menyediakan pelayanan pembuatan gigi tiruan dan restorasi kompleks.', 'cakupan' => ['Gigi Prosthodonti']],
                            ['nama' => 'Klinik Jiwa', 'icon' => 'fa-head-side-virus', 'color' => 'sky', 'deskripsi' => 'Klinik Jiwa menyediakan pelayanan kesehatan jiwa untuk diagnosis dan penanganan masalah psikologis.', 'cakupan' => ['Kesehatan Jiwa', 'Kesehatan Jiwa Sore']],
                            ['nama' => 'Klinik Bedah Urologi', 'icon' => 'fa-kidneys', 'color' => 'lime', 'deskripsi' => 'Klinik Bedah Urologi menyediakan pelayanan pemeriksaan masalah pada saluran kemih dan sistem reproduksi pria.', 'cakupan' => ['Urologi']],
                            ['nama' => 'Klinik Kulit Kelamin', 'icon' => 'fa-hand-dots', 'color' => 'amber', 'deskripsi' => 'Klinik Kulit Kelamin menyediakan pelayanan untuk berbagai jenis penyakit kulit dan keluhan terkait kesehatan kelamin.', 'cakupan' => ['Kulit Kelamin']],
                            ['nama' => 'Klinik Anak', 'icon' => 'fa-child', 'color' => 'rose', 'deskripsi' => 'Klinik Anak menyediakan pelayanan kesehatan bagi pasien anak, termasuk layanan terkait tumbuh kembang sesuai cakupan yang tersedia.', 'cakupan' => ['Tumbuh Kembang', 'Anak', 'Rehabilitasi Medik Anak', 'Klinik Pijat Bayi Balita']],
                            ['nama' => 'Klinik Bedah Anak', 'icon' => 'fa-child-reaching', 'color' => 'blue', 'deskripsi' => 'Klinik Bedah Anak menyediakan pelayanan bedah yang dikhususkan untuk pasien bayi dan anak.', 'cakupan' => ['Bedah Anak']],
                            ['nama' => 'Klinik Obgyn', 'icon' => 'fa-person-pregnant', 'color' => 'fuchsia', 'deskripsi' => 'Klinik Obgyn menyediakan layanan kebidanan dan kandungan untuk pemeriksaan kehamilan serta masalah kesehatan reproduksi wanita.', 'cakupan' => ['Risti', 'Kb Dan Nifas', 'Obgyn', 'Obgyn Sore', 'Klinik Laktasi']]
                        ];

                        if (!function_exists('renderAccordion')) {
                            function renderAccordion($items, $prefix, $faskes) {
                                foreach ($items as $index => $item) {
                                    $accId = 'acc-' . $prefix . '-' . $index;
                                    
                                    // Prepare data for showDetail popup
                                    $passData = $faskes;
                                    $passData['layanan'] = $item['cakupan'];
                                    $json_data = htmlspecialchars(json_encode($passData), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                        <button onclick="toggleAccordion('<?php echo $accId; ?>')" class="w-full flex items-center justify-between p-4 bg-<?php echo $item['color']; ?>-50/50 hover:bg-<?php echo $item['color']; ?>-50 transition-colors text-left focus:outline-none group">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-<?php echo $item['color']; ?>-500 shadow-sm group-hover:scale-105 transition-transform">
                                                    <i class="fa-solid <?php echo $item['icon']; ?> text-xl"></i>
                                                </div>
                                                <span class="font-bold text-slate-800 text-lg"><?php echo $item['nama']; ?></span>
                                            </div>
                                            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm">
                                                <i id="icon-<?php echo $accId; ?>" class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300"></i>
                                            </div>
                                        </button>
                                        <div id="<?php echo $accId; ?>" class="hidden px-5 pb-6 pt-3 border-t border-slate-100">
                                            <div class="mt-2 mb-6">
                                                <button data-faskes='<?php echo $json_data; ?>' onclick='showDetail(event, JSON.parse(this.dataset.faskes))' class="px-6 py-2.5 bg-primary hover:bg-primaryDark text-white font-bold text-sm rounded-xl transition-colors shadow-sm flex items-center gap-2">
                                                    <i class="fa-solid fa-calendar-plus"></i> Daftar Berobat
                                                </button>
                                            </div>
                                            
                                            <div class="mb-5">
                                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tentang Layanan</h4>
                                                <p class="text-slate-600 text-[15px] leading-relaxed"><?php echo $item['deskripsi']; ?></p>
                                            </div>
                                            
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Cakupan Layanan</h4>
                                                <div class="flex flex-wrap gap-2">
                                                    <?php foreach($item['cakupan'] as $c): ?>
                                                        <div class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg font-medium shadow-sm">
                                                            <?php echo $c; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                        }

                        renderAccordion($eka_utama, 'eka_utama', $faskes);
                        ?>
                    </div>

                    <h2 class="text-2xl md:text-3xl font-bold text-[#413074] leading-tight mb-6 mt-10 border-t border-slate-200 pt-10">Layanan Lainnya</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10 items-start">
                        <?php
                        $eka_lainnya = [
                            ['nama' => 'Orthodonti', 'icon' => 'fa-teeth-open', 'color' => 'teal', 'deskripsi' => 'Layanan Orthodonti menyediakan penanganan untuk merapikan susunan gigi dan rahang.', 'cakupan' => ['Orthodonti']],
                            ['nama' => 'Periodonti', 'icon' => 'fa-tooth', 'color' => 'amber', 'deskripsi' => 'Layanan Periodonti berfokus pada penanganan penyakit jaringan penyangga gigi seperti gusi dan tulang.', 'cakupan' => ['Periodonti']],
                            ['nama' => 'Gigi Anak (Pedodontis)', 'icon' => 'fa-tooth', 'color' => 'rose', 'deskripsi' => 'Layanan Gigi Anak (Pedodontis) menyediakan perawatan kesehatan gigi dan mulut khusus untuk anak.', 'cakupan' => ['Gigi Anak (Pedodontis)']],
                            ['nama' => 'Gizi', 'icon' => 'fa-apple-whole', 'color' => 'emerald', 'deskripsi' => 'Layanan Gizi menyediakan konsultasi terkait nutrisi klinis untuk menunjang proses pemulihan dan kesehatan.', 'cakupan' => ['Gizi']],
                            ['nama' => 'Psikologi', 'icon' => 'fa-brain', 'color' => 'pink', 'deskripsi' => 'Layanan Psikologi menyediakan tes, konseling, dan konsultasi terkait kesehatan mental.', 'cakupan' => ['Psikologi']]
                        ];
                        
                        renderAccordion($eka_lainnya, 'eka_lainnya', $faskes);
                        ?>
                    </div>
                    
                    <script>
                    if (typeof toggleAccordion !== 'function') {
                        function toggleAccordion(id) {
                            const el = document.getElementById(id);
                            const icon = document.getElementById('icon-' + id);
                            if (el.classList.contains('hidden')) {
                                el.classList.remove('hidden');
                                icon.classList.add('rotate-180');
                            } else {
                                el.classList.add('hidden');
                                icon.classList.remove('rotate-180');
                            }
                        }
                    }
                    </script>

                    <h2 class="text-2xl md:text-3xl font-bold text-[#413074] leading-tight mb-6 mt-10 border-t border-slate-200 pt-10">Penunjang Medik dan Klinik Rumah Sakit</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- IGD -->
                        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow group">
                            <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-truck-medical"></i>
                            </div>
                            <h4 class="font-bold text-lg text-slate-800 mb-2">Instalasi Gawat Darurat & Ambulans 24 Jam</h4>
                            <p class="text-slate-500 text-sm leading-relaxed">RSUD Eka Candrarini menyediakan layanan Instalasi Gawat Darurat (IGD) dan ambulans 24 jam yang siap melayani pasien dalam kondisi kritis maupun darurat medis lainnya.</p>
                        </div>
                        
                        <!-- Laboratorium -->
                        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow group">
                            <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-flask"></i>
                            </div>
                            <h4 class="font-bold text-lg text-slate-800 mb-2">Laboratorium</h4>
                            <p class="text-slate-500 text-sm leading-relaxed">Laboratorium RSUD Eka Candrarini dilengkapi dengan peralatan modern dan tenaga analis yang profesional untuk mendukung diagnosis yang akurat dan cepat.</p>
                        </div>

                        <!-- Farmasi -->
                        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow group">
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-pills"></i>
                            </div>
                            <h4 class="font-bold text-lg text-slate-800 mb-2">Farmasi</h4>
                            <p class="text-slate-500 text-sm leading-relaxed">Komitmen kuat untuk memberikan pelayanan kesehatan dengan sepenuh hati tanpa diskriminatif dan secara sungguh-sungguh serta berpusat pada pasien.</p>
                        </div>

                        <!-- Radiologi -->
                        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow group">
                            <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-x-ray"></i>
                            </div>
                            <h4 class="font-bold text-lg text-slate-800 mb-2">Radiologi</h4>
                            <p class="text-slate-500 text-sm leading-relaxed">Unit Radiologi RSUD Eka Candrarini menyediakan layanan pencitraan diagnostik seperti rontgen (X-ray), USG, CT Scan, dan pemeriksaan lainnya dengan teknologi modern.</p>
                        </div>

                        <!-- Kamar Operasi -->
                        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow group">
                            <div class="w-12 h-12 bg-teal-50 text-teal-500 rounded-xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-bed-pulse"></i>
                            </div>
                            <h4 class="font-bold text-lg text-slate-800 mb-2">Kamar Operasi</h4>
                            <p class="text-slate-500 text-sm leading-relaxed">Kamar operasi RSUD Eka Candrarini dilengkapi dengan peralatan bedah modern dan sistem steril yang ketat untuk menjamin keamanan dan keberhasilan tindakan operasi.</p>
                        </div>

                        <!-- ICU -->
                        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow group">
                            <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-heart-pulse"></i>
                            </div>
                            <h4 class="font-bold text-lg text-slate-800 mb-2">Intensive Care Unit (ICU, NICU, PICU)</h4>
                            <p class="text-slate-500 text-sm leading-relaxed">ICU RSUD Eka Candrarini dirancang khusus untuk merawat pasien dalam kondisi kritis yang memerlukan pengawasan ketat dan penanganan medis intensif. Dilengkapi dengan alat monitoring canggih dan didukung oleh tim medis berpengalaman yang siap siaga 24 jam.</p>
                        </div>

                        <!-- Instalasi Gizi -->
                        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow group">
                            <div class="w-12 h-12 bg-lime-50 text-lime-500 rounded-xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-utensils"></i>
                            </div>
                            <h4 class="font-bold text-lg text-slate-800 mb-2">Instalasi Gizi</h4>
                            <p class="text-slate-500 text-sm leading-relaxed">Instalasi Gizi RSUD Eka Candrarini bertugas menyusun, mengelola, dan menyediakan makanan sesuai dengan kebutuhan gizi pasien berdasarkan kondisi medisnya.</p>
                        </div>

                        <!-- Bank Darah -->
                        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow group">
                            <div class="w-12 h-12 bg-red-50 text-red-500 rounded-xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-droplet"></i>
                            </div>
                            <h4 class="font-bold text-lg text-slate-800 mb-2">Bank Darah</h4>
                            <p class="text-slate-500 text-sm leading-relaxed">Bank Darah RSUD Eka Candrarini menyediakan pelayanan penyimpanan, pemeriksaan, dan distribusi darah serta komponennya untuk mendukung kebutuhan transfusi pasien.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                $nama_rs_lower = strtolower($faskes['nama']);
                $is_layanan_tab = in_array($selected_layanan, ['rawat_jalan', 'rawat_inap', 'perawatan_intensif', 'layanan_darurat', 'penunjang_medis']);
                $is_empty_rs = false;
                if (strpos($nama_rs_lower, 'eka candrarini') !== false) {
                    $is_empty_rs = true;
                }
                if (strpos($nama_rs_lower, 'bhakti dharma husada') !== false && !in_array($selected_layanan, ['rawat_jalan', 'rawat_inap', 'layanan_darurat', 'perawatan_intensif', 'penunjang_medis'])) {
                    $is_empty_rs = true;
                }
                
                if ($is_empty_rs && $is_layanan_tab) {
                    $layanan_title = ucwords(str_replace('_', ' ', $selected_layanan));
                ?>
                    <div class="text-center py-20">
                        <div class="w-20 h-20 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                            <i class="fa-solid fa-file-medical"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800 mb-2"><?php echo htmlspecialchars($layanan_title); ?></h2>
                        <p class="text-slate-500 max-w-lg mx-auto">Informasi detail mengenai layanan ini di <?php echo htmlspecialchars($faskes['nama']); ?> sedang dalam penyusunan.</p>
                    </div>
                <?php
                } else if ($selected_layanan === 'rawat_jalan') {
                    $is_bdh = (strpos(strtolower($faskes['nama']), 'bhakti dharma husada') !== false);
                    if ($is_bdh) {
                ?>
                <h3 class="font-bold text-2xl text-slate-900 mb-3">Tentang Layanan Rawat Jalan</h3>
                <p class="text-slate-800 text-[15px] leading-relaxed text-justify mb-10">
                    Rawat Jalan di RSUD Bhakti Dharma Husada menyediakan layanan konsultasi dan pengobatan bagi pasien dengan berbagai keluhan kesehatan, baik yang memerlukan penanganan spesialis maupun umum.
                </p>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Poli yang Tersedia</h3>
                
                <div class="mb-10 space-y-3">
                    <?php
                    $bdh_polis = [
                        ['nama' => 'Gigi & Mulut', 'icon' => 'fa-tooth', 'color' => 'teal', 'deskripsi' => 'Layanan Gigi & Mulut mencakup pelayanan kesehatan gigi dan mulut dengan beberapa layanan yang tersedia melalui sistem pendaftaran.', 'cakupan' => ['Gigi Umum', 'Gigi Bedah Mulut', 'Konservasi Gigi (Endodontis)', 'Gigi Anak (Pedodontis)', 'Poli Siang Konservasi Gigi (Endodontis)']],
                        ['nama' => 'Paru', 'icon' => 'fa-lungs', 'color' => 'blue', 'deskripsi' => 'Layanan Paru menyediakan pelayanan yang berkaitan dengan pemeriksaan dan penanganan masalah kesehatan paru dan saluran pernapasan.', 'cakupan' => ['Tb-Ro', 'Ispa']],
                        ['nama' => 'Anak', 'icon' => 'fa-child', 'color' => 'rose', 'deskripsi' => 'Layanan kesehatan khusus untuk bayi, anak, dan remaja yang meliputi pemeriksaan rutin dan penanganan penyakit.', 'cakupan' => ['Anak']],
                        ['nama' => 'THT', 'icon' => 'fa-ear-listen', 'color' => 'purple', 'deskripsi' => 'Pemeriksaan dan penanganan berbagai masalah kesehatan pada telinga, hidung, dan tenggorokan.', 'cakupan' => ['Tht']],
                        ['nama' => 'Jantung', 'icon' => 'fa-heart-pulse', 'color' => 'rose', 'deskripsi' => 'Pelayanan khusus untuk diagnosis, perawatan, dan pencegahan penyakit jantung dan pembuluh darah.', 'cakupan' => ['Jantung']],
                        ['nama' => 'Mata', 'icon' => 'fa-eye', 'color' => 'cyan', 'deskripsi' => 'Pelayanan kesehatan mata yang komprehensif mulai dari pemeriksaan dasar hingga penanganan penyakit mata.', 'cakupan' => ['Mata Siang', 'Mata']],
                        ['nama' => 'Obgyn', 'icon' => 'fa-person-pregnant', 'color' => 'fuchsia', 'deskripsi' => 'Layanan kebidanan dan kandungan untuk pemeriksaan kehamilan serta masalah kesehatan reproduksi wanita.', 'cakupan' => ['Obgyn']],
                        ['nama' => 'Kulit & Kelamin', 'icon' => 'fa-hand-dots', 'color' => 'amber', 'deskripsi' => 'Pelayanan untuk berbagai jenis penyakit kulit dan keluhan terkait kesehatan kelamin.', 'cakupan' => ['Kulit Kelamin']],
                        ['nama' => 'Penyakit Dalam', 'icon' => 'fa-stethoscope', 'color' => 'emerald', 'deskripsi' => 'Layanan pemeriksaan dan penanganan berbagai kondisi kesehatan pada orang dewasa.', 'cakupan' => ['Penyakit Dalam', 'Penyakit Dalam Klinik Sore']],
                        ['nama' => 'Syaraf', 'icon' => 'fa-brain', 'color' => 'purple', 'deskripsi' => 'Pemeriksaan dan perawatan medis yang berkaitan dengan sistem saraf pusat dan perifer.', 'cakupan' => ['Syaraf Klinik Sore', 'Bedah Syaraf Klinik Sore', 'Syaraf']],
                        ['nama' => 'Psikiatri', 'icon' => 'fa-head-side-virus', 'color' => 'sky', 'deskripsi' => 'Pelayanan kesehatan jiwa untuk diagnosis dan penanganan masalah psikologis dan gangguan mental.', 'cakupan' => ['Kesehatan Jiwa']],
                        ['nama' => 'Psikologi', 'icon' => 'fa-brain', 'color' => 'pink', 'deskripsi' => 'Layanan konsultasi psikologi, tes psikologi, dan konseling untuk berbagai rentang usia.', 'cakupan' => ['Psikologi']],
                        ['nama' => 'Bedah Umum', 'icon' => 'fa-scalpel', 'color' => 'indigo', 'deskripsi' => 'Layanan bedah untuk berbagai kondisi medis yang memerlukan tindakan operatif umum.', 'cakupan' => ['Bedah Umum', 'Bedah Digestif', 'Poli Sore Bedah Digestif']],
                        ['nama' => 'Bedah Plastik', 'icon' => 'fa-user-doctor', 'color' => 'blue', 'deskripsi' => 'Pelayanan bedah rekonstruksi dan estetika oleh dokter spesialis bedah plastik.', 'cakupan' => ['Bedah Plastik']],
                        ['nama' => 'Orthopedi', 'icon' => 'fa-bone', 'color' => 'orange', 'deskripsi' => 'Penanganan masalah kesehatan pada sistem muskuloskeletal termasuk tulang, sendi, dan otot.', 'cakupan' => ['Orthopedi']],
                        ['nama' => 'Rehabilitasi Medik', 'icon' => 'fa-person-walking-with-cane', 'color' => 'emerald', 'deskripsi' => 'Pelayanan terapi dan rehabilitasi untuk mengembalikan fungsi tubuh yang terganggu akibat cedera atau penyakit.', 'cakupan' => ['Rehabilitasi Medik']],
                        ['nama' => 'Urologi', 'icon' => 'fa-kidneys', 'color' => 'lime', 'deskripsi' => 'Pemeriksaan dan penanganan masalah pada saluran kemih dan sistem reproduksi pria.', 'cakupan' => ['Urologi']]
                    ];
                    
                    $bdh_lainnya = [
                        ['nama' => 'Medical Check Up', 'icon' => 'fa-notes-medical', 'color' => 'teal', 'deskripsi' => 'Layanan pemeriksaan kesehatan secara menyeluruh (Medical Check Up).', 'cakupan' => ['Medical Check Up Pppk Paruh Waktu', 'Medical Check Up Pppk']],
                        ['nama' => 'Umum', 'icon' => 'fa-stethoscope', 'color' => 'blue', 'deskripsi' => 'Pelayanan pemeriksaan kesehatan dasar dan umum.', 'cakupan' => ['Umum']],
                        ['nama' => 'Gizi', 'icon' => 'fa-apple-whole', 'color' => 'rose', 'deskripsi' => 'Layanan konsultasi gizi klinik untuk berbagai kondisi kesehatan.', 'cakupan' => ['Gizi']],
                        ['nama' => 'Bedah Onkologi', 'icon' => 'fa-ribbon', 'color' => 'pink', 'deskripsi' => 'Pelayanan bedah khusus untuk penanganan tumor dan kanker.', 'cakupan' => ['Bedah Onkologi']],
                        ['nama' => 'VCT', 'icon' => 'fa-vial-virus', 'color' => 'red', 'deskripsi' => 'Layanan Voluntary Counseling and Testing (VCT) untuk konseling dan tes HIV.', 'cakupan' => ['VCT (Voluntary Counseling and Testing)']],
                        ['nama' => 'Bedah TKV', 'icon' => 'fa-heart-circle-bolt', 'color' => 'rose', 'deskripsi' => 'Pelayanan bedah toraks, kardiak, dan vaskular.', 'cakupan' => ['Bedah Tkv']]
                    ];

                    function renderAccordion($items, $prefix, $faskes) {
                        foreach ($items as $index => $item) {
                            $accId = 'acc-' . $prefix . '-' . $index;
                            
                            // Prepare data for showDetail popup
                            $passData = $faskes;
                            $passData['layanan'] = $item['cakupan'];
                            $json_data = htmlspecialchars(json_encode($passData), ENT_QUOTES, 'UTF-8');
                            ?>
                            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                <button onclick="toggleAccordion('<?php echo $accId; ?>')" class="w-full flex items-center justify-between p-4 bg-<?php echo $item['color']; ?>-50/50 hover:bg-<?php echo $item['color']; ?>-50 transition-colors text-left focus:outline-none group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-<?php echo $item['color']; ?>-500 shadow-sm group-hover:scale-105 transition-transform">
                                            <i class="fa-solid <?php echo $item['icon']; ?> text-xl"></i>
                                        </div>
                                        <span class="font-bold text-slate-800 text-lg"><?php echo $item['nama']; ?></span>
                                    </div>
                                    <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm">
                                        <i id="icon-<?php echo $accId; ?>" class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300"></i>
                                    </div>
                                </button>
                                <div id="<?php echo $accId; ?>" class="hidden px-5 pb-6 pt-3 border-t border-slate-100">
                                    <div class="mt-2 mb-6">
                                        <button data-faskes='<?php echo $json_data; ?>' onclick='showDetail(event, JSON.parse(this.dataset.faskes))' class="px-6 py-2.5 bg-primary hover:bg-primaryDark text-white font-bold text-sm rounded-xl transition-colors shadow-sm flex items-center gap-2">
                                            <i class="fa-solid fa-calendar-plus"></i> Daftar Berobat
                                        </button>
                                    </div>
                                    
                                    <div class="mb-5">
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tentang Layanan</h4>
                                        <p class="text-slate-600 text-[15px] leading-relaxed"><?php echo $item['deskripsi']; ?></p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Cakupan Layanan</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach($item['cakupan'] as $c): ?>
                                                <div class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg font-medium shadow-sm">
                                                    <?php echo $c; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }

                    renderAccordion($bdh_polis, 'utama', $faskes);
                    ?>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6 mt-8">Layanan Lainnya</h3>
                <div class="mb-10 space-y-3">
                    <?php renderAccordion($bdh_lainnya, 'lainnya', $faskes); ?>
                </div>

                <script>
                function toggleAccordion(id) {
                    const el = document.getElementById(id);
                    const icon = document.getElementById('icon-' + id);
                    if (el.classList.contains('hidden')) {
                        el.classList.remove('hidden');
                        icon.classList.add('rotate-180');
                    } else {
                        el.classList.add('hidden');
                        icon.classList.remove('rotate-180');
                    }
                }
                </script>
                <?php
                    } else if (strpos(strtolower($faskes['nama']), 'soewandhi') !== false) {
                ?>
                <h3 class="font-bold text-2xl text-slate-900 mb-3">Tentang Layanan Rawat Jalan</h3>
                <p class="text-slate-800 text-[15px] leading-relaxed text-justify mb-10">
                    Layanan rawat jalan RSUD dr. M. Soewandhie menyediakan konsultasi dengan dokter spesialis dan pemeriksaan kesehatan lengkap. Dengan sistem antrian online dan pelayanan yang efisien, Anda dapat berkonsultasi dengan dokter pilihan tanpa harus menunggu lama.
                </p>

                <h3 class="font-bold text-lg text-slate-900 mb-4">Keunggulan Layanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-8 mb-10 text-[15px] text-slate-800">
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Konsultasi dengan 20+ dokter spesialis</li>
                        <li>Sistem pendaftaran online</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Antrian digital real-time</li>
                        <li>Hasil pemeriksaan hari yang sama</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Farmasi terintegrasi</li>
                        <li>Pembayaran cashless</li>
                    </ul>
                </div>

                <hr class="border-slate-200 my-8">

                <h3 class="font-bold text-lg text-slate-900 mb-6">Poli yang Tersedia</h3>
                
                <div class="mb-10 space-y-3">
                    <?php
                    $soewandhie_polis = [
                        ['nama' => 'Anak', 'icon' => 'fa-child', 'color' => 'rose', 'deskripsi' => 'Layanan Anak menyediakan pelayanan kesehatan bagi pasien anak, termasuk layanan terkait tumbuh kembang dan layanan anak sesuai cakupan yang tersedia.', 'cakupan' => ['Tumbuh Kembang', 'Anak', 'Hematologi Onkologi Anak']],
                        ['nama' => 'Anestesi', 'icon' => 'fa-syringe', 'color' => 'blue', 'deskripsi' => 'Layanan Anestesi menyediakan pelayanan anestesiologi dan perawatan intensif terkait persiapan maupun pasca operasi.', 'cakupan' => ['Anestesi']],
                        ['nama' => 'Bedah Digestif', 'icon' => 'fa-stomach', 'color' => 'indigo', 'deskripsi' => 'Layanan Bedah Digestif menyediakan pelayanan bedah khusus untuk masalah pada saluran cerna dan sistem pencernaan.', 'cakupan' => ['Bedah Digestif']],
                        ['nama' => 'Bedah Onkologi', 'icon' => 'fa-ribbon', 'color' => 'pink', 'deskripsi' => 'Layanan Bedah Onkologi menyediakan pelayanan bedah khusus untuk penanganan tumor dan kanker.', 'cakupan' => ['Bedah Onkologi']],
                        ['nama' => 'Bedah Plastik', 'icon' => 'fa-user-doctor', 'color' => 'cyan', 'deskripsi' => 'Layanan Bedah Plastik menyediakan pelayanan bedah rekonstruksi dan estetika.', 'cakupan' => ['Bedah Plastik']],
                        ['nama' => 'Bedah Syaraf', 'icon' => 'fa-brain', 'color' => 'purple', 'deskripsi' => 'Layanan Bedah Syaraf menyediakan pelayanan bedah untuk gangguan sistem saraf pusat dan perifer.', 'cakupan' => ['Bedah Syaraf']],
                        ['nama' => 'Bedah TKV', 'icon' => 'fa-heart-pulse', 'color' => 'red', 'deskripsi' => 'Layanan Bedah TKV (Toraks Kardiak Vaskular) menyediakan pelayanan bedah pada jantung, paru, dan pembuluh darah.', 'cakupan' => ['Bedah TKV']],
                        ['nama' => 'Bedah Umum', 'icon' => 'fa-scalpel', 'color' => 'blue', 'deskripsi' => 'Layanan Bedah Umum menyediakan pelayanan bedah untuk berbagai kondisi medis.', 'cakupan' => ['Sore Bedah', 'Bedah']],
                        ['nama' => 'Geriatri', 'icon' => 'fa-person-cane', 'color' => 'emerald', 'deskripsi' => 'Layanan Geriatri menyediakan pelayanan kesehatan yang dikhususkan bagi pasien usia lanjut.', 'cakupan' => ['Geriatri']],
                        ['nama' => 'Gigi & Mulut', 'icon' => 'fa-tooth', 'color' => 'teal', 'deskripsi' => 'Layanan Gigi & Mulut menyediakan pelayanan kesehatan gigi dan mulut dengan beberapa layanan spesifik yang tersedia melalui sistem pendaftaran.', 'cakupan' => ['Gigi', 'Endodonsi', 'Bedah Mulut', 'Gigi Anak (Pedodontis)']],
                        ['nama' => 'Gizi', 'icon' => 'fa-apple-whole', 'color' => 'rose', 'deskripsi' => 'Layanan Gizi menyediakan konsultasi asupan nutrisi klinis untuk menunjang proses penyembuhan pasien.', 'cakupan' => ['Gizi']],
                        ['nama' => 'Jantung', 'icon' => 'fa-heart-pulse', 'color' => 'rose', 'deskripsi' => 'Layanan Jantung menyediakan pemeriksaan dan penanganan penyakit jantung dan pembuluh darah.', 'cakupan' => ['Jantung', 'Jantung Sore']],
                        ['nama' => 'Kulit & Kelamin', 'icon' => 'fa-hand-dots', 'color' => 'amber', 'deskripsi' => 'Layanan Kulit & Kelamin menyediakan pelayanan untuk keluhan kesehatan kulit dan estetika medis.', 'cakupan' => ['Kulit Dan Kelamin', 'Kosmetik Medik']],
                        ['nama' => 'Mata', 'icon' => 'fa-eye', 'color' => 'cyan', 'deskripsi' => 'Layanan Mata menyediakan pelayanan kesehatan mata komprehensif mulai dari pemeriksaan dasar hingga tindak lanjut medis.', 'cakupan' => ['Mata']],
                        ['nama' => 'Medical Check Up', 'icon' => 'fa-notes-medical', 'color' => 'teal', 'deskripsi' => 'Layanan Medical Check Up menyediakan pemeriksaan kesehatan secara menyeluruh.', 'cakupan' => ['Medical Check Up', 'Medical Check Up Pppk Paruh Waktu', 'Medical Check Up Pppk']],
                        ['nama' => 'Obgyn / Kandungan', 'icon' => 'fa-person-pregnant', 'color' => 'fuchsia', 'deskripsi' => 'Layanan Obgyn / Kandungan menyediakan pemeriksaan kesehatan kehamilan dan reproduksi wanita.', 'cakupan' => ['Risti Dan Preeklamsia (Hamil)', 'Kb', 'Usg Dv', 'Kandungan', 'Nifas']],
                        ['nama' => 'Orthodonti', 'icon' => 'fa-teeth-open', 'color' => 'sky', 'deskripsi' => 'Layanan Orthodonti menyediakan pelayanan spesialis gigi untuk perbaikan posisi gigi dan rahang.', 'cakupan' => ['Orthodonti']],
                        ['nama' => 'Orthopedi', 'icon' => 'fa-bone', 'color' => 'orange', 'deskripsi' => 'Layanan Orthopedi menyediakan penanganan masalah kesehatan pada tulang, sendi, dan otot.', 'cakupan' => ['Orthopedi']],
                        ['nama' => 'Paru', 'icon' => 'fa-lungs', 'color' => 'blue', 'deskripsi' => 'Layanan Paru menyediakan pemeriksaan dan perawatan keluhan pada saluran pernapasan dan kondisi paru-paru.', 'cakupan' => ['Paru', 'Onkologi Toraks']],
                        ['nama' => 'Penyakit Dalam', 'icon' => 'fa-stethoscope', 'color' => 'emerald', 'deskripsi' => 'Layanan Penyakit Dalam menyediakan pelayanan pemeriksaan dan penanganan kondisi kesehatan pada pasien dewasa.', 'cakupan' => ['Penyakit Dalam', 'Penyakit Dalam Klinik Sore']],
                        ['nama' => 'Periodonti', 'icon' => 'fa-tooth', 'color' => 'teal', 'deskripsi' => 'Layanan Periodonti menyediakan pelayanan perawatan penyakit pada jaringan penyangga gigi (gusi dan tulang).', 'cakupan' => ['Periodonti']],
                        ['nama' => 'Psikiatri / Kesehatan Jiwa', 'icon' => 'fa-head-side-virus', 'color' => 'sky', 'deskripsi' => 'Layanan Psikiatri / Kesehatan Jiwa menyediakan penanganan dan konsultasi masalah kesehatan mental.', 'cakupan' => ['Kesehatan Jiwa']],
                        ['nama' => 'Psikologi', 'icon' => 'fa-brain', 'color' => 'pink', 'deskripsi' => 'Layanan Psikologi menyediakan layanan tes psikologi, konseling, dan konsultasi kesehatan mental.', 'cakupan' => ['Psikologi']],
                        ['nama' => 'Radioterapi', 'icon' => 'fa-radiation', 'color' => 'amber', 'deskripsi' => 'Layanan Radioterapi menyediakan penanganan kanker menggunakan terapi radiasi.', 'cakupan' => ['Radioterapi']],
                        ['nama' => 'Rehabilitasi Medik', 'icon' => 'fa-person-walking-with-cane', 'color' => 'emerald', 'deskripsi' => 'Layanan Rehabilitasi Medik menyediakan terapi fisik untuk mengembalikan fungsi tubuh akibat cedera atau penyakit.', 'cakupan' => ['Rehab Medik']],
                        ['nama' => 'Syaraf', 'icon' => 'fa-brain', 'color' => 'purple', 'deskripsi' => 'Layanan Syaraf menyediakan pemeriksaan medis yang berkaitan dengan sistem saraf.', 'cakupan' => ['Syaraf']],
                        ['nama' => 'THT', 'icon' => 'fa-ear-listen', 'color' => 'purple', 'deskripsi' => 'Layanan THT menyediakan penanganan masalah kesehatan pada telinga, hidung, dan tenggorokan.', 'cakupan' => ['Tht', 'Onkologi Tht - Bedah Kepala Dan Leher']],
                        ['nama' => 'Urologi', 'icon' => 'fa-kidneys', 'color' => 'lime', 'deskripsi' => 'Layanan Urologi menyediakan pemeriksaan masalah pada saluran kemih dan sistem reproduksi pria.', 'cakupan' => ['Urologi']],
                        ['nama' => 'VCT', 'icon' => 'fa-vial-virus', 'color' => 'red', 'deskripsi' => 'Layanan VCT menyediakan konseling dan pengetesan sukarela terkait HIV/AIDS.', 'cakupan' => ['VCT (Voluntary Counseling and Testing)']]
                    ];
                    
                    if (!function_exists('renderAccordion')) {
                        function renderAccordion($items, $prefix, $faskes) {
                            foreach ($items as $index => $item) {
                                $accId = 'acc-' . $prefix . '-' . $index;
                                
                                // Prepare data for showDetail popup
                                $passData = $faskes;
                                $passData['layanan'] = $item['cakupan'];
                                $json_data = htmlspecialchars(json_encode($passData), ENT_QUOTES, 'UTF-8');
                                ?>
                                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                    <button onclick="toggleAccordion('<?php echo $accId; ?>')" class="w-full flex items-center justify-between p-4 bg-<?php echo $item['color']; ?>-50/50 hover:bg-<?php echo $item['color']; ?>-50 transition-colors text-left focus:outline-none group">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-<?php echo $item['color']; ?>-500 shadow-sm group-hover:scale-105 transition-transform">
                                                <i class="fa-solid <?php echo $item['icon']; ?> text-xl"></i>
                                            </div>
                                            <span class="font-bold text-slate-800 text-lg"><?php echo $item['nama']; ?></span>
                                        </div>
                                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm">
                                            <i id="icon-<?php echo $accId; ?>" class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300"></i>
                                        </div>
                                    </button>
                                    <div id="<?php echo $accId; ?>" class="hidden px-5 pb-6 pt-3 border-t border-slate-100">
                                        <div class="mt-2 mb-6">
                                            <button data-faskes='<?php echo $json_data; ?>' onclick='showDetail(event, JSON.parse(this.dataset.faskes))' class="px-6 py-2.5 bg-primary hover:bg-primaryDark text-white font-bold text-sm rounded-xl transition-colors shadow-sm flex items-center gap-2">
                                                <i class="fa-solid fa-calendar-plus"></i> Daftar Berobat
                                            </button>
                                        </div>
                                        
                                        <div class="mb-5">
                                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tentang Layanan</h4>
                                            <p class="text-slate-600 text-[15px] leading-relaxed"><?php echo $item['deskripsi']; ?></p>
                                        </div>
                                        
                                        <div>
                                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Cakupan Layanan</h4>
                                            <div class="flex flex-wrap gap-2">
                                                <?php foreach($item['cakupan'] as $c): ?>
                                                    <div class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg font-medium shadow-sm">
                                                        <?php echo $c; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    }

                    renderAccordion($soewandhie_polis, 'soewandhie', $faskes);
                    ?>
                </div>

                <script>
                if (typeof toggleAccordion !== 'function') {
                    function toggleAccordion(id) {
                        const el = document.getElementById(id);
                        const icon = document.getElementById('icon-' + id);
                        if (el.classList.contains('hidden')) {
                            el.classList.remove('hidden');
                            icon.classList.add('rotate-180');
                        } else {
                            el.classList.add('hidden');
                            icon.classList.remove('rotate-180');
                        }
                    }
                }
                </script>

                <hr class="border-slate-200 my-8">

                <h3 class="font-bold text-lg text-slate-900 mb-6">Alur Pelayanan</h3>
                <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-10 px-4">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#78e6e0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(0 0, 85% 0, 100% 50%, 85% 100%, 0 100%, 15% 50%);">
                            <i class="fa-regular fa-file-lines text-white text-3xl pl-4"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">1 - Pendaftaran</span>
                    </div>
                    <!-- Step 2 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#34dedb] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-stethoscope text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">2 - Pemeriksaan Tanda Vital</span>
                    </div>
                    <!-- Step 3 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#3bbbf0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-user-doctor text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">3 - Konsultasi Dokter</span>
                    </div>
                    <!-- Step 4 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#2d8cd6] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-magnifying-glass text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">4 - Pemeriksaan Penunjang</span>
                    </div>
                    <!-- Step 5 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#1a5b93] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 100% 0, 100% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-prescription-bottle-medical text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">5 - Pengambilan Obat</span>
                    </div>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Fasilitas Tersedia</h3>
                <div class="flex flex-wrap justify-between gap-4 mb-12">
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-wind text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Ruang tunggu nyaman ber-AC</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-car text-4xl text-black shrink-0 relative"><span class="absolute -top-1 -right-2 text-[10px] bg-white border border-black rounded-full w-4 h-4 flex items-center justify-center font-bold">P</span></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Area parkir luas</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <div class="w-12 h-12 rounded-full border-[3px] border-black flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-utensils text-2xl text-black"></i>
                        </div>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Kantin</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-mosque text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Mushola</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-money-check-dollar text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">ATM Center</span>
                    </div>
                </div>

                <div class="flex justify-end mt-12 mb-10">
                    <!-- Generic register button for fallback, wait we don't really need this if each accordion has one, but it's good to keep the original content -->
                    <button data-faskes='<?php echo $detailsJson; ?>' onclick='showDetail(event, JSON.parse(this.dataset.faskes))' class="px-8 py-3 bg-primary hover:bg-primaryDark text-white font-bold text-base rounded-xl transition-colors shadow-md hidden">
                        Daftar Berobat
                    </button>
                </div>
                <?php
                    } else {
                ?>
                <h3 class="font-bold text-2xl text-slate-900 mb-3">Tentang Layanan Rawat Jalan</h3>
                <p class="text-slate-800 text-[15px] leading-relaxed text-justify mb-10">
                    Layanan rawat jalan <?php echo htmlspecialchars($faskes['nama']); ?> menyediakan konsultasi dengan dokter spesialis dan pemeriksaan kesehatan lengkap. Dengan sistem antrian online dan pelayanan yang efisien, Anda dapat berkonsultasi dengan dokter pilihan tanpa harus menunggu lama.
                </p>

                <h3 class="font-bold text-lg text-slate-900 mb-4">Keunggulan Layanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-8 mb-10 text-[15px] text-slate-800">
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Konsultasi dengan 20+ dokter spesialis</li>
                        <li>Sistem pendaftaran online</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Antrian digital real-time</li>
                        <li>Hasil pemeriksaan hari yang sama</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Farmasi terintegrasi</li>
                        <li>Pembayaran cashless</li>
                    </ul>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Alur Pelayanan</h3>
                <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-10 px-4">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#78e6e0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(0 0, 85% 0, 100% 50%, 85% 100%, 0 100%, 15% 50%);">
                            <i class="fa-regular fa-file-lines text-white text-3xl pl-4"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">1 - Pendaftaran</span>
                    </div>
                    <!-- Step 2 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#34dedb] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-stethoscope text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">2 - Pemeriksaan Tanda Vital</span>
                    </div>
                    <!-- Step 3 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#3bbbf0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-user-doctor text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">3 - Konsultasi Dokter</span>
                    </div>
                    <!-- Step 4 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#2d8cd6] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-magnifying-glass text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">4 - Pemeriksaan Penunjang</span>
                    </div>
                    <!-- Step 5 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#1a5b93] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 100% 0, 100% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-prescription-bottle-medical text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">5 - Pengambilan Obat</span>
                    </div>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Fasilitas Tersedia</h3>
                <div class="flex flex-wrap justify-between gap-4 mb-12">
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-wind text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Ruang tunggu nyaman ber-AC</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-car text-4xl text-black shrink-0 relative"><span class="absolute -top-1 -right-2 text-[10px] bg-white border border-black rounded-full w-4 h-4 flex items-center justify-center font-bold">P</span></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Area parkir luas</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <div class="w-12 h-12 rounded-full border-[3px] border-black flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-utensils text-2xl text-black"></i>
                        </div>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Kantin</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-mosque text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Mushola</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-money-check-dollar text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">ATM Center</span>
                    </div>
                </div>

                <div class="flex justify-end mt-12 mb-10">
                    <button data-faskes='<?php echo $detailsJson; ?>' onclick='showDetail(event, JSON.parse(this.dataset.faskes))' class="px-8 py-3 bg-primary hover:bg-primaryDark text-white font-bold text-base rounded-xl transition-colors shadow-md">
                        Daftar Berobat
                    </button>
                </div>
                <?php
                    }
                } else if ($selected_layanan === 'rawat_inap') {
                    $is_bdh = (strpos(strtolower($faskes['nama']), 'bhakti dharma husada') !== false);
                    if ($is_bdh) {
                ?>
                <h3 class="font-bold text-2xl text-slate-900 mb-3">Tentang Layanan Rawat Inap</h3>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8 italic text-blue-800 text-[15px]">
                    "Setiap hari di ruang ini adalah langkah menuju pulang. Tetap kuat, kesembuhan sedang dalam perjalanan"
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                    <!-- VIP -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-amber-50 px-6 py-4 border-b border-amber-100 flex items-center justify-between">
                            <h4 class="font-bold text-slate-800 text-lg">Kelas VIP</h4>
                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">Rp. 500.000</span>
                        </div>
                        <div class="p-6">
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Fasilitas Kamar:</p>
                            <ul class="space-y-2 text-slate-700 text-sm">
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> Ruangan ber AC</li>
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> Kamar Mandi Dalam</li>
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> TV & Kulkas</li>
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> Sofa & Bed Penunggu</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Kelas 1 -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-emerald-50 px-6 py-4 border-b border-emerald-100 flex items-center justify-between">
                            <h4 class="font-bold text-slate-800 text-lg">Kelas I</h4>
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">Rp. 250.000</span>
                        </div>
                        <div class="p-6">
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Fasilitas Kamar:</p>
                            <ul class="space-y-2 text-slate-700 text-sm">
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> 2 Tempat Tidur / Kamar</li>
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> Ruangan ber AC</li>
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> Kamar Mandi Dalam</li>
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> TV, Kulkas & Sofa Penunggu</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Kelas 2 -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex items-center justify-between">
                            <h4 class="font-bold text-slate-800 text-lg">Kelas II</h4>
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">Rp. 150.000</span>
                        </div>
                        <div class="p-6">
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Fasilitas Kamar:</p>
                            <ul class="space-y-2 text-slate-700 text-sm">
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> 4 Tempat Tidur / Kamar</li>
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> Ruangan ber AC</li>
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> Kamar Mandi Dalam</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Kelas 3 -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                            <h4 class="font-bold text-slate-800 text-lg">Kelas III</h4>
                            <span class="bg-slate-200 text-slate-700 px-3 py-1 rounded-full text-xs font-bold">Rp. 100.000</span>
                        </div>
                        <div class="p-6">
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Fasilitas Kamar:</p>
                            <ul class="space-y-2 text-slate-700 text-sm">
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> 4 Tempat Tidur / Kamar</li>
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> Ruangan ber AC</li>
                                <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500 w-4"></i> Kamar Mandi Dalam</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl mb-10 flex gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                    <div class="text-sm text-blue-800 leading-relaxed font-medium">
                        <ul class="list-disc pl-4 space-y-1">
                            <li>Tarif diatas belum termasuk tindakan dan visite dokter</li>
                            <li>Tarif berdasarkan Peraturan Daerah Kota Surabaya No. 7 Tahun 2023 Tentang Pajak Daerah dan Retribusi daerah</li>
                        </ul>
                    </div>
                </div>
                <?php
                    } else {
                ?>
                <h3 class="font-bold text-2xl text-slate-900 mb-3">Tentang Layanan Rawat Inap</h3>
                <p class="text-slate-800 text-[15px] leading-relaxed text-justify mb-10">
                    Fasilitas rawat inap ini menyediakan berbagai pilihan kelas kamar mulai dari VIP hingga kelas 3. Setiap kamar dilengkapi dengan fasilitas modern dan perawatan 24 jam oleh tenaga medis profesional.
                </p>

                <h3 class="font-bold text-lg text-slate-900 mb-4">Keunggulan Layanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-8 mb-10 text-[15px] text-slate-800">
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Kamar VIP, Kelas 1, 2, dan 3</li>
                        <li>Perawatan 24 jam oleh perawat profesional</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Menu nutrisi khusus dari ahli gizi</li>
                        <li>Fasilitas untuk keluarga pasien</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>TV, AC, dan WiFi di setiap kamar</li>
                        <li>Tombol panggil perawat</li>
                    </ul>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Alur Pendaftaran</h3>
                <div class="mb-12 overflow-x-auto pb-4">
                    <div class="flex items-center min-w-[700px] justify-between relative px-2">
                        <!-- Connecting Line -->
                        <div class="absolute top-10 left-12 right-12 h-px bg-slate-100 -z-10"></div>
                        
                        <!-- Step 1 -->
                        <div class="flex flex-col items-start gap-4">
                            <div class="w-20 h-20 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-3xl shrink-0 shadow-sm border-[3px] border-white">
                                <i class="fa-regular fa-clock"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-lg">Registrasi</h4>
                                <p class="text-slate-500 text-sm">Loket Admisi</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex flex-col items-start gap-4">
                            <div class="w-20 h-20 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-3xl shrink-0 shadow-sm border-[3px] border-white">
                                <i class="fa-regular fa-hospital"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-lg">Pilih Kamar</h4>
                                <p class="text-slate-500 text-sm">Sesuai Kelas</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex flex-col items-start gap-4">
                            <div class="w-20 h-20 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-3xl shrink-0 shadow-sm border-[3px] border-white">
                                <i class="fa-solid fa-stethoscope"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-lg">Verifikasi</h4>
                                <p class="text-slate-500 text-sm">Data & BPJS</p>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex flex-col items-start gap-4">
                            <div class="w-20 h-20 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center text-3xl shrink-0 shadow-sm border-[3px] border-white">
                                <i class="fa-solid fa-bed"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-lg">Masuk</h4>
                                <p class="text-slate-500 text-sm">Ruang Rawat</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Alur Pelayanan</h3>
                <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-10 px-4">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#78e6e0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(0 0, 85% 0, 100% 50%, 85% 100%, 0 100%, 15% 50%);">
                            <i class="fa-solid fa-file-signature text-white text-3xl pl-4"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">1 - Admisi</span>
                    </div>
                    <!-- Step 2 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#34dedb] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-bed-pulse text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">2 - Perawatan Harian</span>
                    </div>
                    <!-- Step 3 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#3bbbf0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-user-doctor text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">3 - Visit Dokter</span>
                    </div>
                    <!-- Step 4 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#2d8cd6] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-notes-medical text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">4 - Pemeriksaan Berkala</span>
                    </div>
                    <!-- Step 5 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#1a5b93] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 100% 0, 100% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-house-chimney-medical text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">5 - Discharge Planning</span>
                    </div>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Fasilitas Tersedia</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                    <!-- VIP & Private -->
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-5 border-b border-amber-200 pb-4">
                            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-crown"></i>
                            </div>
                            <h4 class="font-bold text-lg text-amber-900">VIP & Private</h4>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check-circle text-amber-500 mt-0.5"></i>
                                <span class="text-sm font-medium text-slate-700 leading-snug">AC Private & Smart TV</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check-circle text-amber-500 mt-0.5"></i>
                                <span class="text-sm font-medium text-slate-700 leading-snug">Kamar Mandi Dalam (Water Heater)</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check-circle text-amber-500 mt-0.5"></i>
                                <span class="text-sm font-medium text-slate-700 leading-snug">Sofa Bed Penunggu</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check-circle text-amber-500 mt-0.5"></i>
                                <span class="text-sm font-medium text-slate-700 leading-snug">Kulkas Mini & Dispenser</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check-circle text-amber-500 mt-0.5"></i>
                                <span class="text-sm font-medium text-slate-700 leading-snug">Menu Makanan Premium</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Reguler (Kelas 1-3) -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-bed"></i>
                            </div>
                            <h4 class="font-bold text-lg text-slate-800">Reguler (Kelas 1-3)</h4>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check-circle text-blue-400 mt-0.5"></i>
                                <span class="text-sm font-medium text-slate-700 leading-snug">AC Central / Split</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check-circle text-blue-400 mt-0.5"></i>
                                <span class="text-sm font-medium text-slate-700 leading-snug">Kamar Mandi Bersih</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check-circle text-blue-400 mt-0.5"></i>
                                <span class="text-sm font-medium text-slate-700 leading-snug">Nurse Call System 24 Jam</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check-circle text-blue-400 mt-0.5"></i>
                                <span class="text-sm font-medium text-slate-700 leading-snug">Lemari Penyimpanan Pasien</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-check-circle text-blue-400 mt-0.5"></i>
                                <span class="text-sm font-medium text-slate-700 leading-snug">Makan 3x Sehari (Ahli Gizi)</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <h3 class="font-bold text-lg text-slate-900 mb-6">Informasi Tarif</h3>
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm mb-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 font-semibold">Kategori Ruangan</th>
                                    <th class="px-6 py-4 font-semibold">Fasilitas Utama</th>
                                    <th class="px-6 py-4 font-semibold text-right">Tarif Per Hari</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-amber-600">Paviliun (VIP)</td>
                                    <td class="px-6 py-4">1 Bed, Sofa, TV, Kulkas</td>
                                    <td class="px-6 py-4 font-semibold text-right text-slate-900">Rp 750.000 - 1.200.000</td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-800">Kelas I</td>
                                    <td class="px-6 py-4">2 Bed, TV LCD, AC</td>
                                    <td class="px-6 py-4 font-semibold text-right text-slate-900">Rp 400.000 - 600.000</td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-800">Kelas II</td>
                                    <td class="px-6 py-4">3-4 Bed, AC</td>
                                    <td class="px-6 py-4 font-semibold text-right text-slate-900">Rp 250.000 - 350.000</td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-800">Kelas III</td>
                                    <td class="px-6 py-4">6 Bed, AC/Kipas</td>
                                    <td class="px-6 py-4 font-semibold text-right text-slate-900">Rp 150.000 - 200.000</td>
                                </tr>
                                <tr class="hover:bg-slate-50 transition-colors bg-blue-50/30">
                                    <td class="px-6 py-4 font-semibold text-blue-700">ICU / NICU</td>
                                    <td class="px-6 py-4 text-blue-600">Perawatan Intensif</td>
                                    <td class="px-6 py-4 font-semibold text-right text-blue-700">Sesuai Tindakan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl mb-12 flex gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                    <p class="text-sm text-blue-800 leading-relaxed font-medium">
                        Tarif di atas adalah estimasi jasa sarana kamar (akomodasi). Belum termasuk jasa dokter, obat-obatan, dan tindakan medis lainnya.
                    </p>
                </div>

                <?php
                    }
                } else if ($selected_layanan === 'perawatan_intensif') {
                    $is_bdh = (strpos(strtolower($faskes['nama']), 'bhakti dharma husada') !== false || strpos(strtolower($faskes['nama']), 'bdh') !== false);
                    if ($is_bdh) {
                ?>
                <h3 class="font-bold text-2xl text-slate-900 mb-3">Tentang Layanan Perawatan Intensif</h3>
                <p class="text-slate-800 text-[15px] leading-relaxed text-justify mb-8">
                    Instalasi Perawatan Intensif RSUD Bhakti Dharma Husada menyediakan perawatan komprehensif untuk pasien dewasa, anak, dan bayi yang membutuhkan pengawasan ketat.
                </p>

                <div class="space-y-6 mb-12">
                    <!-- ICU -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-heart-pulse"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">ICU (Intensive Care Unit)</h4>
                        </div>
                        <div class="p-6">
                            <p class="text-[15px] text-slate-700 leading-relaxed mb-4 text-justify">
                                ICU adalah unit perawatan intensif untuk pasien dewasa yang mengalami kondisi kritis atau mengancam nyawa, seperti gagal napas, pasca operasi besar, serangan jantung, stroke berat, atau trauma berat. ICU RSUD Bhakti Dharma Husada dilengkapi dengan peralatan monitoring canggih, ventilator, dan tenaga medis terlatih 24 jam.
                            </p>
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Fasilitas Utama:</p>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-blue-500 w-4 mt-0.5"></i> Monitoring pasien secara terus-menerus</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-blue-500 w-4 mt-0.5"></i> Ventilator dan alat bantu napas</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-blue-500 w-4 mt-0.5"></i> Tim dokter spesialis & perawat intensif</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-blue-500 w-4 mt-0.5"></i> Pengendalian infeksi yang ketat</li>
                            </ul>
                        </div>
                    </div>

                    <!-- NICU -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-amber-50 px-6 py-4 border-b border-amber-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-baby"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">NICU (Neonatal Intensive Care Unit)</h4>
                        </div>
                        <div class="p-6">
                            <p class="text-[15px] text-slate-700 leading-relaxed mb-4 text-justify">
                                NICU adalah unit perawatan khusus untuk bayi baru lahir dengan kondisi kritis, prematur, berat lahir rendah, gangguan pernapasan, infeksi, atau kelainan bawaan. Di NICU RSUD Bhakti Dharma Husada, bayi dirawat dengan teknologi terkini dan diawasi oleh dokter spesialis anak dan perawat terlatih.
                            </p>
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Fasilitas Utama:</p>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-amber-500 w-4 mt-0.5"></i> Inkubator modern & alat pemantau bayi</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-amber-500 w-4 mt-0.5"></i> Terapi oksigen & fototerapi</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-amber-500 w-4 mt-0.5"></i> Perawatan nutrisi parenteral & enteral</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-amber-500 w-4 mt-0.5"></i> Dukungan dokter spesialis anak & perawat terlatih</li>
                            </ul>
                        </div>
                    </div>

                    <!-- PICU -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-emerald-50 px-6 py-4 border-b border-emerald-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-child-reaching"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">PICU (Pediatric Intensive Care Unit)</h4>
                        </div>
                        <div class="p-6">
                            <p class="text-[15px] text-slate-700 leading-relaxed mb-4 text-justify">
                                PICU adalah unit intensif untuk anak-anak usia 1 bulan hingga 18 tahun yang mengalami kondisi serius seperti infeksi berat, kejang tak terkontrol, cedera, atau pasca operasi besar. PICU RSUD Bhakti Dharma Husada menyediakan perawatan medis intensif dengan pendekatan ramah anak dan dukungan spesialis anak.
                            </p>
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Fasilitas Utama:</p>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-500 w-4 mt-0.5"></i> Alat pemantauan vital khusus anak</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-500 w-4 mt-0.5"></i> Tim multidisiplin: dokter anak, perawat, dan ahli gizi</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-500 w-4 mt-0.5"></i> Pendekatan psikologis yang suportif untuk anak & keluarga</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-500 w-4 mt-0.5"></i> Ruang rawat yang higienis & terstandarisasi</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
                    } else {
                ?>
                <h3 class="font-bold text-2xl text-slate-900 mb-3">Tentang Layanan Perawatan Intensif</h3>
                <p class="text-slate-800 text-[15px] leading-relaxed text-justify mb-10">
                    Unit Perawatan Intensif menyediakan perawatan khusus untuk pasien dengan kondisi kritis. Dilengkapi dengan peralatan monitoring canggih dan rasio perawat-pasien yang optimal untuk memastikan perawatan terbaik.
                </p>

                <h3 class="font-bold text-lg text-slate-900 mb-4">Keunggulan Layanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-8 mb-10 text-[15px] text-slate-800">
                    <ul class="list-disc pl-5 space-y-4">
                        <li>ICU (Intensive Care Unit) 20 bed</li>
                        <li>ICCU (Cardiac Care) 10 bed</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>NICU (Neonatal) 15 bed</li>
                        <li>PICU (Pediatric) 8 bed</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Monitoring 24 jam</li>
                        <li>Ventilator dan life support lengkap</li>
                    </ul>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Alur Pelayanan</h3>
                <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-10 px-4">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#78e6e0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(0 0, 85% 0, 100% 50%, 85% 100%, 0 100%, 15% 50%);">
                            <i class="fa-solid fa-list-check text-white text-3xl pl-4"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">1 - Admission Criteria</span>
                    </div>
                    <!-- Step 2 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#34dedb] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-heart-pulse text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">2 - Monitoring Ketat</span>
                    </div>
                    <!-- Step 3 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#3bbbf0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-stethoscope text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">3 - Daily Assessment</span>
                    </div>
                    <!-- Step 4 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#2d8cd6] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-lungs text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">4 - Weaning</span>
                    </div>
                    <!-- Step 5 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#1a5b93] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 100% 0, 100% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-arrow-right-from-bracket text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">5 - Step Down</span>
                    </div>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Fasilitas Tersedia</h3>
                <div class="flex flex-wrap justify-between gap-4 mb-12">
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-desktop text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Monitor bedside</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-mask-ventilator text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Ventilator</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-syringe text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Infusion pump</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-bolt text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Defibrilator</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-filter text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">CRRT</span>
                    </div>
                </div>
                <?php
                    }
                } else if ($selected_layanan === 'layanan_darurat') {
                    $is_bdh = (strpos(strtolower($faskes['nama']), 'bhakti dharma husada') !== false);
                    if ($is_bdh) {
                ?>
                <h3 class="font-bold text-2xl text-slate-900 mb-3">Tentang Layanan Gawat Darurat (IGD)</h3>
                <p class="text-slate-800 text-[15px] leading-relaxed text-justify mb-10">
                    Instalasi Gawat Darurat (IGD) RSUD Bhakti Dharma Husada beroperasi selama 24 jam setiap hari, melayani berbagai kondisi darurat medis dengan cepat dan profesional.
                </p>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Fasilitas dan Layanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user-nurse text-xl"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-base">Siaga 24 Jam</h4>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">Tim dokter dan perawat berpengalaman yang terlatih untuk menangani kasus-kasus darurat seperti kecelakaan, serangan jantung, stroke, dan kondisi kritis lainnya.</p>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-clipboard-list text-xl"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-base">Fasilitas Triage</h4>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">Sistem triage untuk memprioritaskan pasien berdasarkan tingkat keparahan kondisi, memastikan mereka yang memerlukan penanganan segera mendapatkan prioritas.</p>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-heart-pulse text-xl"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-base">Ruang Resusitasi dan Trauma</h4>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">Dilengkapi dengan peralatan medis modern seperti ventilator, defibrillator, dan monitor jantung untuk menangani pasien dengan kegawatdaruratan kritis.</p>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-truck-medical text-xl"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-base">Ambulans Siaga</h4>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">Layanan ambulans lengkap dengan peralatan medis darurat dan paramedis yang terlatih.</p>
                    </div>

                    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm hover:shadow-md transition-shadow md:col-span-2">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-microscope text-xl"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-base">Pelayanan Radiologi & Laboratorium 24 Jam</h4>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">Tersedia akses cepat ke layanan radiologi dan laboratorium untuk menunjang diagnosa dalam situasi darurat.</p>
                    </div>
                </div>
                
                <div class="flex justify-end mt-12 mb-10">
                    <button data-faskes='<?php echo $detailsJson; ?>' onclick='showDetail(event, JSON.parse(this.dataset.faskes))' class="px-8 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-base rounded-xl transition-colors shadow-md">
                        Telepon Darurat (112)
                    </button>
                </div>
                <?php
                    } else {
                ?>
                <h3 class="font-bold text-2xl text-slate-900 mb-3">Tentang Layanan Darurat</h3>
                <p class="text-slate-800 text-[15px] leading-relaxed text-justify mb-10">
                    Unit Gawat Darurat (UGD) <?php echo htmlspecialchars($faskes['nama']); ?> beroperasi 24 jam dengan tim medis terlatih dan peralatan lengkap untuk menangani berbagai kondisi darurat. Sistem triase memastikan pasien ditangani sesuai tingkat kegawatdaruratan.
                </p>

                <h3 class="font-bold text-lg text-slate-900 mb-4">Keunggulan Layanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-8 mb-10 text-[15px] text-slate-800">
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Layanan 24 jam non-stop</li>
                        <li>Tim medis terlatih ACLS/ATLS</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Ambulans siaga dengan peralatan lengkap</li>
                        <li>Sistem triase 5 level</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Resusitasi dan stabilisasi</li>
                        <li>Akses langsung ke OK dan ICU</li>
                    </ul>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Alur Pelayanan</h3>
                <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-10 px-4">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#78e6e0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(0 0, 85% 0, 100% 50%, 85% 100%, 0 100%, 15% 50%);">
                            <i class="fa-solid fa-clipboard-list text-white text-3xl pl-4"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">1 - Triase</span>
                    </div>
                    <!-- Step 2 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#34dedb] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-heart-pulse text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">2 - Stabilisasi</span>
                    </div>
                    <!-- Step 3 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#3bbbf0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-stethoscope text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">3 - Pemeriksaan Awal</span>
                    </div>
                    <!-- Step 4 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#2d8cd6] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-kit-medical text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">4 - Penanganan Darurat</span>
                    </div>
                    <!-- Step 5 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#1a5b93] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 100% 0, 100% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-truck-medical text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">5 - Transfer / Rawat Inap</span>
                    </div>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Fasilitas Tersedia</h3>
                <div class="flex flex-wrap justify-between gap-4 mb-12">
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-heart-pulse text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Ruang resusitasi</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-syringe text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Ruang tindakan</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-desktop text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Ruang observasi</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-truck-medical text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Ambulans 3 unit</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-helicopter text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Helipad</span>
                    </div>
                </div>

                <div class="flex justify-end mt-12 mb-10">
                    <button data-faskes='<?php echo $detailsJson; ?>' onclick='showDetail(event, JSON.parse(this.dataset.faskes))' class="px-8 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-base rounded-xl transition-colors shadow-md">
                        Telepon Darurat (112)
                    </button>
                </div>
                <?php
                    }
                } else if ($selected_layanan === 'penunjang_medis') {
                    $is_bdh = (strpos(strtolower($faskes['nama']), 'bhakti dharma husada') !== false || strpos(strtolower($faskes['nama']), 'bdh') !== false);
                    if ($is_bdh) {
                ?>
                <h3 class="font-bold text-2xl text-slate-900 mb-3">Tentang Layanan Penunjang Medis</h3>
                <p class="text-slate-800 text-[15px] leading-relaxed text-justify mb-8">
                    Layanan Penunjang Medis RSUD Bhakti Dharma Husada dilengkapi dengan peralatan canggih dan tenaga profesional untuk mendukung diagnosis yang akurat dan perawatan yang optimal.
                </p>

                <div class="space-y-6 mb-12">
                    <!-- LABORATORIUM -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-microscope"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">Laboratorium</h4>
                        </div>
                        <div class="p-6">
                            <p class="text-[15px] text-slate-700 leading-relaxed mb-4 text-justify">
                                Layanan laboratorium RSUD Bhakti Dharma Husada terdiri dari pemeriksaan patologi klinik dan patologi anatomi. Laboratorium Patologi Klinik melayani pemeriksaan bahan cair tubuh secara akurat dengan peralatan modern. Laboratorium Patologi Anatomi menangani pemeriksaan jaringan tubuh untuk mendeteksi adanya kelainan sel atau tumor.
                            </p>
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Jenis Pemeriksaan:</p>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-blue-500 w-4 mt-0.5"></i> Hematologi: Darah lengkap, LED, dll</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-blue-500 w-4 mt-0.5"></i> Kimia Klinik: Gula darah, kolesterol, dll</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-blue-500 w-4 mt-0.5"></i> Imunoserologi & Urinalisa</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-blue-500 w-4 mt-0.5"></i> PA Jaringan (Biopsi) & Sitositologi</li>
                            </ul>
                        </div>
                    </div>

                    <!-- RADIOLOGI -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-x-ray"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">Radiologi</h4>
                        </div>
                        <div class="p-6">
                            <p class="text-[15px] text-slate-700 leading-relaxed mb-4 text-justify">
                                Instalasi Radiologi menyediakan layanan medis untuk menunjang diagnosa dokter, dengan teknologi digital dan tenaga ahli radiografer serta dokter spesialis radiologi.
                            </p>
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Layanan Radiologi:</p>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-indigo-500 w-4 mt-0.5"></i> Rontgen</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-indigo-500 w-4 mt-0.5"></i> USG (abdominal, kehamilan, transvaginal)</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-indigo-500 w-4 mt-0.5"></i> CT-Scan</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-indigo-500 w-4 mt-0.5"></i> Panoramic</li>
                            </ul>
                        </div>
                    </div>

                    <!-- HEMODIALISIS -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-emerald-50 px-6 py-4 border-b border-emerald-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-filter"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">Hemodialisis</h4>
                        </div>
                        <div class="p-6">
                            <p class="text-[15px] text-slate-700 leading-relaxed mb-4 text-justify">
                                Layanan Hemodialisis RSUD Bhakti Dharma Husada diperuntukkan bagi pasien gagal ginjal kronis dan akut, dengan pengawasan dokter spesialis penyakit dalam dan tenaga perawat bersertifikasi.
                            </p>
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Fasilitas:</p>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-500 w-4 mt-0.5"></i> Mesin hemodialisis modern & steril</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-500 w-4 mt-0.5"></i> Layanan terjadwal rutin</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-500 w-4 mt-0.5"></i> Area ruang nyaman & privasi pasien dijaga</li>
                            </ul>
                        </div>
                    </div>

                    <!-- BANK DARAH -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-rose-50 px-6 py-4 border-b border-rose-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-droplet"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">Bank Darah (BDRS)</h4>
                        </div>
                        <div class="p-6">
                            <p class="text-[15px] text-slate-700 leading-relaxed mb-4 text-justify">
                                Bank Darah RSUD BDH berfungsi untuk memenuhi kebutuhan transfusi darah bagi pasien secara cepat, aman, dan sesuai standar Kementerian Kesehatan.
                            </p>
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Layanan BDRS:</p>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-rose-500 w-4 mt-0.5"></i> Penyediaan darah lengkap & komponen darah</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-rose-500 w-4 mt-0.5"></i> Pemeriksaan kecocokan silang (cross-matching)</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-rose-500 w-4 mt-0.5"></i> Screening penyakit menular pada darah donor</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-rose-500 w-4 mt-0.5"></i> Pelayanan 24 jam untuk kasus darurat</li>
                            </ul>
                        </div>
                    </div>

                    <!-- FORENSIK DAN PEMULASARAN JENAZAH -->
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-truck-medical"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">Forensik dan Pemulasaran Jenazah</h4>
                        </div>
                        <div class="p-6">
                            <p class="text-[15px] text-slate-700 leading-relaxed mb-4 text-justify">
                                Layanan kedokteran forensik dan pemulasaran jenazah RSUD Bhakti Dharma Husada memberikan pelayanan 24 jam dengan standar medis, kepatutan, dan keagamaan, meliputi perawatan jenazah dan kebutuhan terkait lainnya.
                            </p>
                            <p class="text-sm font-bold text-slate-500 mb-3 uppercase tracking-wider">Layanan Tersedia:</p>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-slate-500 w-4 mt-0.5"></i> Pemulasaran dan perawatan jenazah 24 jam</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-slate-500 w-4 mt-0.5"></i> Penerbitan surat keterangan kematian</li>
                                <li class="flex items-start gap-2"><i class="fa-solid fa-check text-slate-500 w-4 mt-0.5"></i> Pelayanan sesuai standar medis & agama</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
                    } else {
                ?>
                <h3 class="font-bold text-2xl text-slate-900 mb-3">Tentang Layanan Penunjang Medis</h3>
                <p class="text-slate-800 text-[15px] leading-relaxed text-justify mb-10">
                    Layanan penunjang medis lengkap meliputi laboratorium dengan berbagai pemeriksaan, radiologi dengan CT Scan dan MRI, farmasi 24 jam, serta unit rehabilitasi medik untuk pemulihan optimal.
                </p>

                <h3 class="font-bold text-lg text-slate-900 mb-4">Keunggulan Layanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-8 mb-10 text-[15px] text-slate-800">
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Laboratorium terakreditasi</li>
                        <li>Radiologi: X-Ray, USG, CT Scan, MRI</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Farmasi 24 jam</li>
                        <li>Rehabilitasi medik lengkap</li>
                    </ul>
                    <ul class="list-disc pl-5 space-y-4">
                        <li>Bank darah</li>
                        <li>Hemodialisa</li>
                    </ul>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Alur Pelayanan</h3>
                <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-10 px-4">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#78e6e0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(0 0, 85% 0, 100% 50%, 85% 100%, 0 100%, 15% 50%);">
                            <i class="fa-solid fa-syringe text-white text-3xl pl-4"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">1 - Pengambilan Sampel</span>
                    </div>
                    <!-- Step 2 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#34dedb] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-microscope text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">2 - Pemeriksaan</span>
                    </div>
                    <!-- Step 3 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#3bbbf0] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-chart-pie text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">3 - Analisis</span>
                    </div>
                    <!-- Step 4 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#2d8cd6] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 85% 0, 100% 50%, 85% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-file-medical text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">4 - Pelaporan</span>
                    </div>
                    <!-- Step 5 -->
                    <div class="flex flex-col items-center w-full md:w-[18%]">
                        <div class="relative w-full h-16 bg-[#1a5b93] flex items-center justify-center shrink-0 mb-3" style="clip-path: polygon(15% 0, 100% 0, 100% 100%, 15% 100%, 30% 50%);">
                            <i class="fa-solid fa-user-doctor text-white text-3xl pl-2"></i>
                        </div>
                        <span class="text-sm font-bold text-slate-800 text-center">5 - Konsultasi Hasil</span>
                    </div>
                </div>

                <h3 class="font-bold text-lg text-slate-900 mb-6">Fasilitas Tersedia</h3>
                <div class="flex flex-wrap justify-between gap-4 mb-12">
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-flask text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Lab lengkap</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-circle-notch text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">CT Scan 128 slice</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-magnet text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">MRI 3T</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-wave-square text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">USG 4D</span>
                    </div>
                    <div class="flex-1 min-w-[150px] bg-[#e5e7eb] border border-black rounded-[30px] p-4 flex flex-col md:flex-row items-center gap-3">
                        <i class="fa-solid fa-person-walking text-4xl text-black shrink-0"></i>
                        <span class="text-xs font-bold text-black text-center md:text-left leading-tight">Fisioterapi</span>
                    </div>
                </div>
                <?php
                    }
                } else {
                    $is_bdh = (strpos(strtolower($faskes['nama']), 'bhakti dharma husada') !== false);
                    $is_eka_candrarini = (strpos(strtolower($faskes['nama']), 'eka candrarini') !== false);
                    
                    if ($is_eka_candrarini) {
                        // Tidak menampilkan informasi tarif untuk RSUD Eka Candrarini
                    } else if ($is_bdh) {
                ?>
                <div class="mb-10 animate-fade-in-up">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 bg-gradient-to-br from-primary/20 to-primary/5 text-primary rounded-2xl flex items-center justify-center text-2xl shrink-0 shadow-inner">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl md:text-3xl font-bold text-slate-800 leading-tight">Informasi Tarif</h2>
                            <p class="text-slate-500 mt-1">Transparansi biaya layanan kesehatan di <?php echo htmlspecialchars($faskes['nama']); ?> Surabaya.</p>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Akomodasi -->
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <i class="fa-solid fa-bed"></i>
                                </div>
                                <h3 class="font-bold text-slate-800 text-lg">Kelompok Akomodasi</h3>
                            </div>
                            <div class="p-0 overflow-x-auto">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-white text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                                        <tr>
                                            <th class="px-6 py-4 font-semibold">Layanan</th>
                                            <th class="px-6 py-4 font-semibold text-right">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 text-slate-700">
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-emerald-600 transition-colors">Akomodasi Rawat Gabung Bayi</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 125.000</td>
                                        </tr>
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-emerald-600 transition-colors">Akomodasi Rawat Inap</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 500.000</td>
                                        </tr>
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-emerald-600 transition-colors">Akomodasi Rawat Inap Perinatal</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 150.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Konsultasi -->
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                    <i class="fa-solid fa-user-doctor"></i>
                                </div>
                                <h3 class="font-bold text-slate-800 text-lg">Kelompok Konsultasi</h3>
                            </div>
                            <div class="p-0 overflow-x-auto">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-white text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                                        <tr>
                                            <th class="px-6 py-4 font-semibold">Layanan</th>
                                            <th class="px-6 py-4 font-semibold text-right">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 text-slate-700">
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-blue-600 transition-colors">Konsultasi Dokter Spesialis</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 75.000</td>
                                        </tr>
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-blue-600 transition-colors">Konsultasi Dokter Spesialis Di Luar Jam Kerja Datang</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 60.000 - Rp 100.000 (Estimasi)</td>
                                        </tr>
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-blue-600 transition-colors">Konsultasi Dokter Spesialis Via Telepon</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 25.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Retribusi -->
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <h3 class="font-bold text-slate-800 text-lg">Kelompok Retribusi</h3>
                            </div>
                            <div class="p-0 overflow-x-auto">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-white text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                                        <tr>
                                            <th class="px-6 py-4 font-semibold">Layanan</th>
                                            <th class="px-6 py-4 font-semibold text-right">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 text-slate-700">
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-amber-600 transition-colors">Pemeriksaan Gawat Darurat Dokter Umum</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 50.000</td>
                                        </tr>
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-amber-600 transition-colors">Pemeriksaan Klinik Spesialis</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 75.000</td>
                                        </tr>
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-amber-600 transition-colors">Pemeriksaan Klinik Umum</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 50.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Visite -->
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center">
                                    <i class="fa-solid fa-stethoscope"></i>
                                </div>
                                <h3 class="font-bold text-slate-800 text-lg">Kelompok Visite</h3>
                            </div>
                            <div class="p-0 overflow-x-auto">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-white text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                                        <tr>
                                            <th class="px-6 py-4 font-semibold">Layanan</th>
                                            <th class="px-6 py-4 font-semibold text-right">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 text-slate-700">
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-purple-600 transition-colors">Visite Dokter Spesialis</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 55.000 - Rp 125.000 (Estimasi)</td>
                                        </tr>
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-purple-600 transition-colors">Visite Dokter Umum</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 35.000 - Rp 70.000 (Estimasi)</td>
                                        </tr>
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-purple-600 transition-colors">Visite Psikolog</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 20.000 - Rp 35.000 (Estimasi)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl mt-8 flex gap-3 shadow-sm">
                        <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                        <p class="text-sm text-blue-800 leading-relaxed font-medium">
                            Tarif yang disebutkan di atas berlaku untuk pasien dengan pembayaran umum. Sementara itu, pasien BPJS atau pemegang asuransi lain akan mengikuti ketentuan polis maupun kerja sama yang berlaku.
                        </p>
                    </div>
                </div>
                <?php
                    } else {
                ?>
                <div class="mb-10 animate-fade-in-up">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 bg-gradient-to-br from-primary/20 to-primary/5 text-primary rounded-2xl flex items-center justify-center text-2xl shrink-0 shadow-inner">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl md:text-3xl font-bold text-slate-800 leading-tight">Informasi Tarif</h2>
                            <p class="text-slate-500 mt-1">Transparansi biaya layanan kesehatan di <?php echo htmlspecialchars($faskes['nama']); ?> Surabaya.</p>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Administrasi -->
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                    <i class="fa-solid fa-hospital-user"></i>
                                </div>
                                <h3 class="font-bold text-slate-800 text-lg">1. Administrasi</h3>
                            </div>
                            <div class="p-0 overflow-x-auto">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-white text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                                        <tr>
                                            <th class="px-6 py-4 font-semibold">Layanan</th>
                                            <th class="px-6 py-4 font-semibold text-right">Harga</th>
                                            <th class="px-6 py-4 font-semibold text-center">VIP</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 text-slate-700">
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-primary transition-colors">Administrasi Pasien Baru</td>
                                            <td class="px-6 py-4 text-primary font-bold text-right">Rp 50.000</td>
                                            <td class="px-6 py-4 text-center text-slate-400">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Akomodasi -->
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <i class="fa-solid fa-bed"></i>
                                </div>
                                <h3 class="font-bold text-slate-800 text-lg">2. Akomodasi</h3>
                            </div>
                            <div class="p-0 overflow-x-auto">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-white text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                                        <tr>
                                            <th class="px-6 py-4 font-semibold">Layanan</th>
                                            <th class="px-6 py-4 font-semibold text-right">Kelas 1</th>
                                            <th class="px-6 py-4 font-semibold text-right">Kelas 2</th>
                                            <th class="px-6 py-4 font-semibold text-right">Kelas 3</th>
                                            <th class="px-6 py-4 font-semibold text-right">VIP</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 text-slate-700">
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-emerald-600 transition-colors">Kamar Rawat Inap</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 750.000</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 500.000</td>
                                            <td class="px-6 py-4 text-right font-medium">Rp 250.000</td>
                                            <td class="px-6 py-4 text-amber-500 font-bold text-right">Rp 1.500.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pelayanan Medik -->
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center">
                                    <i class="fa-solid fa-user-doctor"></i>
                                </div>
                                <h3 class="font-bold text-slate-800 text-lg">3. Pelayanan Medik</h3>
                            </div>
                            <div class="p-0 overflow-x-auto">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-white text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                                        <tr>
                                            <th class="px-6 py-4 font-semibold">Layanan</th>
                                            <th class="px-6 py-4 font-semibold text-right">Harga</th>
                                            <th class="px-6 py-4 font-semibold text-center">VIP</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 text-slate-700">
                                        <tr class="hover:bg-slate-50 transition-colors group">
                                            <td class="px-6 py-4 font-medium group-hover:text-purple-600 transition-colors">Konsultasi Dokter Spesialis</td>
                                            <td class="px-6 py-4 text-primary font-bold text-right">Rp 150.000</td>
                                            <td class="px-6 py-4 text-center text-slate-400">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                    }
                }
                ?>
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
    function showDetail(e, data) {
        e.stopPropagation();
        
        const modal = document.getElementById('serviceModal');
        const modalBox = document.getElementById('serviceModalBox');
        const listContainer = document.getElementById('serviceListContainer');
        const form = document.getElementById('serviceSelectionForm');
        
        // Set hidden faskes_id
        document.getElementById('form_faskes_id').value = data.id;
        
        // Generate radio options
        let html = '';
        if (data.layanan && data.layanan.length > 0) {
            data.layanan.forEach((lay, idx) => {
                const checked = idx === 0 ? 'checked' : '';
                html += `
                <label class="flex items-center p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors mb-3 group">
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 text-[15px] group-hover:text-primary transition-colors">${lay}</h4>
                    </div>
                    <div class="ml-4">
                        <input type="radio" name="poli_nama" value="${lay}" class="w-5 h-5 text-primary focus:ring-primary border-slate-300" ${checked} required>
                    </div>
                </label>
                `;
            });
        } else {
            html = '<p class="text-slate-500 text-center py-4">Layanan detail tidak tersedia.</p>';
            document.getElementById('btnSubmitService').disabled = true;
        }
        
        listContainer.innerHTML = html;
        
        // Show modal
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalBox.classList.remove('scale-95');
            modalBox.classList.add('scale-100');
        }, 10);
    }
    
    function closeServiceModal() {
        const modal = document.getElementById('serviceModal');
        const modalBox = document.getElementById('serviceModalBox');
        
        modal.classList.add('opacity-0');
        modalBox.classList.remove('scale-100');
        modalBox.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
    </script>
    
    <!-- Service Selection Modal -->
    <div id="serviceModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300" onclick="if(event.target === this) closeServiceModal()">
        <div id="serviceModalBox" class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh]">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <h3 class="text-lg font-bold text-slate-800">Pilih Layanan</h3>
                <button onclick="closeServiceModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto">
                <form id="serviceSelectionForm" action="book.php" method="GET">
                    <input type="hidden" name="faskes_id" id="form_faskes_id" value="">
                    
                    <p class="text-sm text-slate-500 mb-4">Silakan pilih layanan detail yang sesuai dengan keluhan Anda untuk melanjutkan pendaftaran:</p>
                    
                    <div id="serviceListContainer" class="mb-2">
                        <!-- Options injected here -->
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" id="btnSubmitService" class="w-full bg-primary hover:bg-primaryDark text-white font-bold py-3.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                            Lanjutkan Pendaftaran <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
