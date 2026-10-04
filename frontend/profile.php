<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$userName = $user['nama_lengkap'];
$words = preg_split('/\s+/', trim($userName));
$formattedName = $userName;
if (count($words) > 3) {
    $formattedName = implode(' ', array_slice($words, 0, 3)) . ' ';
    for ($i = 3; $i < count($words); $i++) {
        $formattedName .= strtoupper(substr($words[$i], 0, 1)) . '. ';
    }
}
$formattedName = trim($formattedName);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Sehati</title>
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
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'], }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-card {
            background: white;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="text-slate-800 pb-20 md:pb-0">

    <!-- Mobile Top Bar -->
    <header class="md:hidden bg-white text-slate-800 p-4 sticky top-0 z-50 border-b border-slate-200 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-500 hover:text-primary"><i class="fa-solid fa-arrow-left text-xl"></i></a>
            <h1 class="text-lg font-bold">Profil Saya</h1>
        </div>
    </header>

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
        <main class="flex-1 w-full max-w-3xl mx-auto p-4 md:p-8 mt-2 md:mt-0">
            <h2 class="text-2xl font-bold text-slate-800 hidden md:block mb-6">Profil Saya</h2>

            <div class="glass-card rounded-2xl p-6 md:p-8 mb-6 relative overflow-hidden">
                <!-- Header Background -->
                <div class="absolute top-0 left-0 right-0 h-32 bg-primary"></div>
                
                <!-- Avatar & Basic Info -->
                <div class="relative z-10 flex flex-col items-center mt-8">
                    <div class="w-24 h-24 rounded-full bg-white border-4 border-white shadow-lg flex items-center justify-center text-primary text-4xl mb-4">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 text-center"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h3>
                    <p class="text-slate-500 font-medium mt-1">Pasien Terdaftar</p>
                </div>

                <!-- Details Grid -->
                <div class="mt-8 border-t border-slate-100 pt-6">
                    <h4 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider">Informasi Pribadi</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-xs text-slate-500 font-medium mb-1">Nomor Induk Kependudukan (NIK)</p>
                            <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($user['nik']); ?></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-xs text-slate-500 font-medium mb-1">Nomor Kartu Keluarga (KK)</p>
                            <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($user['no_kk']); ?></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-xs text-slate-500 font-medium mb-1">Nomor BPJS</p>
                            <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($user['no_bpjs'] ?: '-'); ?></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-xs text-slate-500 font-medium mb-1">Nomor Telepon</p>
                            <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($user['no_hp']); ?></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-xs text-slate-500 font-medium mb-1">Email</p>
                            <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <a href="edit_profile.php" class="w-full bg-white border border-slate-200 text-slate-700 font-semibold py-3.5 px-4 rounded-xl hover:bg-slate-50 transition-colors flex items-center justify-between shadow-sm">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-pen-to-square text-slate-400"></i> Edit Profil</span>
                    <i class="fa-solid fa-chevron-right text-slate-400 text-sm"></i>
                </a>
                <a href="change_password.php" class="w-full bg-white border border-slate-200 text-slate-700 font-semibold py-3.5 px-4 rounded-xl hover:bg-slate-50 transition-colors flex items-center justify-between shadow-sm">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-shield-halved text-slate-400"></i> Ubah Password</span>
                    <i class="fa-solid fa-chevron-right text-slate-400 text-sm"></i>
                </a>
                <a href="logout.php" class="w-full bg-red-50 border border-red-100 text-red-600 font-bold py-3.5 px-4 rounded-xl hover:bg-red-100 transition-colors flex items-center justify-center gap-2 shadow-sm mt-6">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar (Logout)
                </a>
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
    <style> .pb-safe { padding-bottom: env(safe-area-inset-bottom, 16px); } </style>
</body>
</html>
