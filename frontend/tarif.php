<?php
if(session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info Tarif - Sehati</title>
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
        .tariff-box:hover .icon-box {
            background-color: #413074;
            color: white;
        }
        /* Custom scrollbar for table */
        .table-container::-webkit-scrollbar { height: 8px; }
        .table-container::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .table-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .table-container::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="text-slate-800 pb-20 md:pb-0 relative">

    <!-- Mobile Top Bar -->
    <header class="md:hidden bg-white text-slate-800 p-4 sticky top-0 z-50 border-b border-slate-200 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-500 hover:text-primary"><i class="fa-solid fa-arrow-left text-xl"></i></a>
            <h1 class="text-lg font-bold">Info Tarif & Pembiayaan</h1>
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
                    <i class="fa-solid fa-receipt w-6 text-center"></i> Info Tarif & Pembiayaan
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
        <main class="flex-1 w-full p-4 md:p-8 mt-2 md:mt-0 relative">
            <div class="max-w-5xl mx-auto">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-slate-800 hidden md:block mb-3">Info Tarif & Pembiayaan</h2>
                    
                    <h3 class="text-xl font-bold text-[#413074] mt-8 mb-4 border-b border-slate-200 pb-2">Tarif Layanan</h3>
                    
                    <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl shadow-sm mb-4">
                        <p class="text-indigo-900 font-medium leading-relaxed text-sm md:text-base">
                            Tarif Retribusi Pelayanan Kesehatan berdasarkan Peraturan Wali Kota Surabaya Nomor 20 Tahun 2025 dan Peraturan Daerah Kota Surabaya Nomor 7 Tahun 2023.
                        </p>
                    </div>
                    <p class="text-slate-500 text-sm md:text-base font-medium">Pilih kategori di bawah ini untuk melihat detail tabel biaya dan jenis pelayanan kesehatan.</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 mb-12">
                    <!-- Boxes will be generated by JS -->
                </div>

                <!-- SECTION 2: PANDUAN PEMBAYARAN -->
                <div id="panduan-pembayaran" class="mb-8">
                    <h3 class="text-xl font-bold text-[#413074] mb-2 border-b border-slate-200 pb-2">Panduan Pembayaran & Jaminan</h3>
                    <p class="text-slate-500 text-sm md:text-base font-medium mb-6">
                        Ketahui informasi umum mengenai metode pembiayaan layanan kesehatan sebelum melakukan pendaftaran.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                        <!-- JKN / BPJS -->
                        <div onclick="openPanduanModal('jkn')" class="glass-card rounded-2xl p-5 md:p-6 cursor-pointer transition-all duration-300 hover:shadow-lg hover:-translate-y-1 hover:border-[#A57BD7] flex flex-col h-full group">
                            <div class="w-12 h-12 bg-[#F6F9F9] text-[#A57BD7] group-hover:bg-[#A57BD7] group-hover:text-white rounded-full flex items-center justify-center text-xl mb-4 transition-colors duration-300">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-sm md:text-base mb-2">JKN / BPJS Kesehatan</h3>
                            <p class="text-xs text-slate-500 leading-relaxed mb-4 flex-grow">Informasi umum mengenai penggunaan JKN untuk mendapatkan pelayanan kesehatan.</p>
                            <span class="text-xs font-bold text-primary group-hover:text-accent transition-colors flex items-center mt-auto">Lihat Panduan <i class="fa-solid fa-arrow-right ml-1"></i></span>
                        </div>
                        
                        <!-- Umum / Pribadi -->
                        <div onclick="openPanduanModal('umum')" class="glass-card rounded-2xl p-5 md:p-6 cursor-pointer transition-all duration-300 hover:shadow-lg hover:-translate-y-1 hover:border-[#A57BD7] flex flex-col h-full group">
                            <div class="w-12 h-12 bg-[#F6F9F9] text-[#A57BD7] group-hover:bg-[#A57BD7] group-hover:text-white rounded-full flex items-center justify-center text-xl mb-4 transition-colors duration-300">
                                <i class="fa-solid fa-wallet"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-sm md:text-base mb-2">Umum / Pribadi</h3>
                            <p class="text-xs text-slate-500 leading-relaxed mb-4 flex-grow">Informasi pembayaran layanan menggunakan biaya pribadi.</p>
                            <span class="text-xs font-bold text-primary group-hover:text-accent transition-colors flex items-center mt-auto">Lihat Panduan <i class="fa-solid fa-arrow-right ml-1"></i></span>
                        </div>
                        
                        <!-- Asuransi Lain -->
                        <div onclick="openPanduanModal('asuransi')" class="glass-card rounded-2xl p-5 md:p-6 cursor-pointer transition-all duration-300 hover:shadow-lg hover:-translate-y-1 hover:border-[#A57BD7] flex flex-col h-full group">
                            <div class="w-12 h-12 bg-[#F6F9F9] text-[#A57BD7] group-hover:bg-[#A57BD7] group-hover:text-white rounded-full flex items-center justify-center text-xl mb-4 transition-colors duration-300">
                                <i class="fa-solid fa-umbrella-beach"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-sm md:text-base mb-2">Asuransi Kesehatan Lain</h3>
                            <p class="text-xs text-slate-500 leading-relaxed mb-4 flex-grow">Panduan umum penggunaan asuransi kesehatan di luar JKN.</p>
                            <span class="text-xs font-bold text-primary group-hover:text-accent transition-colors flex items-center mt-auto">Lihat Panduan <i class="fa-solid fa-arrow-right ml-1"></i></span>
                        </div>
                        
                        <!-- Belum Tahu -->
                        <div onclick="openPanduanModal('belumtahu')" class="glass-card rounded-2xl p-5 md:p-6 cursor-pointer transition-all duration-300 hover:shadow-lg hover:-translate-y-1 hover:border-[#A57BD7] flex flex-col h-full group">
                            <div class="w-12 h-12 bg-[#F6F9F9] text-[#A57BD7] group-hover:bg-[#A57BD7] group-hover:text-white rounded-full flex items-center justify-center text-xl mb-4 transition-colors duration-300">
                                <i class="fa-solid fa-circle-question"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 text-sm md:text-base mb-2">Belum Tahu</h3>
                            <p class="text-xs text-slate-500 leading-relaxed mb-4 flex-grow">Pelajari cara menentukan metode pembiayaan yang sesuai.</p>
                            <span class="text-xs font-bold text-primary group-hover:text-accent transition-colors flex items-center mt-auto">Lihat Panduan <i class="fa-solid fa-arrow-right ml-1"></i></span>
                        </div>
                    </div>
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

    <!-- Modal Background -->
    <div id="tariffModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4 md:p-12 opacity-0 transition-opacity duration-300">
        
        <!-- Prev Button -->
        <button id="prevBtn" onclick="prevModal(event)" class="absolute left-2 md:left-8 w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-[#a57bd7] to-[#0fb7b8] text-white rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(165,123,215,0.6)] hover:brightness-110 hover:shadow-[0_0_20px_rgba(0,220,241,0.6)] hover:scale-110 transition-all z-[110] border-[3px] border-white/50 hidden">
            <i class="fa-solid fa-chevron-left text-xl md:text-3xl ml-[-2px]"></i>
        </button>

        <!-- Next Button -->
        <button id="nextBtn" onclick="nextModal(event)" class="absolute right-2 md:right-8 w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-[#a57bd7] to-[#0fb7b8] text-white rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(165,123,215,0.6)] hover:brightness-110 hover:shadow-[0_0_20px_rgba(0,220,241,0.6)] hover:scale-110 transition-all z-[110] border-[3px] border-white/50 hidden">
            <i class="fa-solid fa-chevron-right text-xl md:text-3xl mr-[-2px]"></i>
        </button>

        <!-- Modal Content -->
        <div id="modalContentBox" class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh] z-[105] relative">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <div class="flex items-center gap-3">
                    <div id="modalIconBox" class="w-10 h-10 bg-primary text-white rounded-lg flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-stethoscope"></i>
                    </div>
                    <h3 id="modalTitle" class="text-xl font-bold text-slate-800">Tarif Pelayanan</h3>
                </div>
                <button onclick="closeModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <!-- Search Bar -->
            <div class="px-6 pt-5 pb-2 bg-white">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text" id="modalSearch" placeholder="Cari jenis pelayanan..." class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all shadow-sm">
                </div>
            </div>
            
            <!-- Table Content -->
            <div class="px-6 pb-6 overflow-y-auto table-container">
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-sm whitespace-nowrap md:whitespace-normal">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 w-16 text-center">NO</th>
                                <th class="px-4 py-3">JENIS PELAYANAN</th>
                                <th class="px-4 py-3 text-right">BIAYA</th>
                                <th class="px-4 py-3">SATUAN</th>
                                <th class="px-4 py-3 min-w-[200px]">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody id="modalTableBody" class="divide-y divide-slate-100 text-slate-700">
                            <!-- Dynamic Content -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 text-center">
                <p id="modalDasar" class="text-xs text-slate-500 italic leading-tight"></p>
            </div>
        </div>
    </div>

    <!-- Modal Background untuk Panduan Pembayaran -->
    <div id="panduanModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4 md:p-12 opacity-0 transition-opacity duration-300">
        <!-- Modal Content -->
        <div id="panduanModalBox" class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh] z-[105] relative">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <div class="flex items-center gap-3">
                    <div id="panduanModalIconBox" class="w-10 h-10 bg-primary text-white rounded-lg flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-info-circle"></i>
                    </div>
                    <h3 id="panduanModalTitle" class="text-xl font-bold text-slate-800">Panduan Pembayaran</h3>
                </div>
                <button onclick="closePanduanModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <!-- Body Content -->
            <div id="panduanModalBody" class="px-6 py-6 overflow-y-auto text-slate-600 text-[15px] leading-relaxed">
                <!-- Panduan konten diinject via JS -->
            </div>
        </div>
    </div>

    <style> .pb-safe { padding-bottom: env(safe-area-inset-bottom, 16px); } </style>

    <script>
        // Data Tarif (Mock Data based on user request)
                const categories = [
            {
                id: 'umum', title: 'Pengobatan Umum', icon: 'fa-stethoscope',
                items: [
                    { no: '1', jenis: 'Pemeriksaan dan Pengobatan Dasar', biaya: '20.000', satuan: 'Pasien', ket: 'Termasuk konsultasi medis dan obat' },
                    { no: '2', jenis: 'Pemeriksaan dan Pengobatan Dasar Kunjungan Sore', biaya: '25.000', satuan: 'Pasien', ket: 'Termasuk konsultasi medis dan obat' },
                    { no: '3.a', jenis: 'Pemasangan Infus', biaya: '30.000', satuan: 'Tindakan', ket: '-' },
                    { no: '3.b', jenis: 'Ganti cairan infus', biaya: '20.000', satuan: 'Tindakan', ket: '-' },
                    { no: '4', jenis: 'Injeksi Intravena', biaya: '35.000', satuan: 'Tindakan', ket: '-' },
                    { no: '5', jenis: 'Injeksi Intramuscular (IM) / Subcutaneous (SC) / Intracutaneus (IC)', biaya: '35.000', satuan: 'Tindakan', ket: '-' },
                    { no: '6', jenis: 'Skin test', biaya: '30.000', satuan: 'Tindakan', ket: '-' },
                    { no: '7', jenis: 'Pengambilan sampel darah', biaya: '20.000', satuan: 'Tindakan', ket: '-' },
                    { no: '8', jenis: 'Bandaging / pemasangan bandage', biaya: '20.000', satuan: 'Tindakan', ket: '-' },
                    { no: '9', jenis: 'Pelepasan / pemasangan drain', biaya: '25.000', satuan: 'Tindakan', ket: '-' },
                    { no: '10.a', jenis: 'Sehat', biaya: '20.000', satuan: 'Orang', ket: '-' },
                    { no: '10.b', jenis: 'Kelahiran', biaya: '20.000', satuan: 'Orang', ket: '-' },
                    { no: '10.c', jenis: 'Visum hidup', biaya: '20.000', satuan: 'Orang', ket: '-' },
                    { no: '11', jenis: 'ECG', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '12', jenis: 'Home Care (Home Visit)', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '13.a', jenis: 'Pemeriksaan visus mata', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '13.b', jenis: 'Tindakan irigasi mata (trauma kimia)', biaya: '30.000', satuan: 'Tindakan', ket: '-' },
                    { no: '13.c', jenis: 'Pengambilan Corpus alienum (benda asing)', biaya: '35.000', satuan: 'Tindakan', ket: '-' },
                    { no: '14', jenis: 'Ekstraksi serumen', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '14', jenis: 'Ekstraksi benda asing THT', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '15', jenis: 'Irigasi Telinga', biaya: '35.000', satuan: 'Pasien', ket: '-' },
                    { no: '16', jenis: 'Lepas Naso Gastric Tube', biaya: '10.000', satuan: 'Pasien', ket: '-' },
                    { no: '17', jenis: 'Pasang Collar Brace (Neck Collar)', biaya: '125.000', satuan: 'Pasien', ket: '-' },
                    { no: '18', jenis: 'Pelayanan Rekam Medis', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '19', jenis: 'Pemasangan Bidai per lokasi', biaya: '50.000', satuan: 'Pasien', ket: '-' }
                ]
            },
            {
                id: 'bedah', title: 'Tindakan Medik Operatif', icon: 'fa-syringe',
                items: [
                    { no: '1', jenis: 'Sirkumsisi cauter', biaya: '300.000', satuan: 'Tindakan', ket: '-' },
                    { no: '2', jenis: 'Sirkumsisi dengan smart clem', biaya: '350.000', satuan: 'Tindakan', ket: '-' },
                    { no: '3', jenis: 'Insisi Abses', biaya: '30.000', satuan: 'Tindakan', ket: '-' },
                    { no: '4', jenis: 'Ekstraksi Kuku', biaya: '50.000', satuan: 'Tindakan', ket: '-' },
                    { no: '5', jenis: 'Stump plasty', biaya: '30.000', satuan: 'Tindakan', ket: '-' },
                    { no: '6', jenis: 'Ekstirpasi Mata Ikan (Eksisi clavus)', biaya: '70.000', satuan: 'Tindakan', ket: '-' },
                    { no: '7.a', jenis: '1 - 3', biaya: '41.000', satuan: 'Jahitan', ket: '-' },
                    { no: '7.b', jenis: 'Tambahan per jahitan', biaya: '5.000', satuan: 'Jahitan', ket: '-' },
                    { no: '7.c', jenis: 'Angkat / lepas jahitan', biaya: '20.000', satuan: 'Luka', ket: '-' },
                    { no: '8', jenis: 'Ekstirpasi lipoma', biaya: '85.000', satuan: 'Tindakan', ket: '-' },
                    { no: '9', jenis: 'Bulektomi', biaya: '100.000', satuan: 'Tindakan', ket: '-' },
                    { no: '10', jenis: 'Pasang Naso Gastric Tube (NGT)', biaya: '25.000', satuan: 'Tindakan', ket: 'termasuk AMHP' },
                    { no: '11', jenis: 'Jahit 1 Telinga dawir', biaya: '50.000', satuan: 'Telinga', ket: '-' },
                    { no: '12', jenis: 'Cross insisi', biaya: '30.000', satuan: 'Tindakan', ket: '-' }
                ]
            },
            {
                id: 'gawat', title: 'Kegawatdaruratan', icon: 'fa-heart-pulse',
                items: [
                    { no: '1.a', jenis: 'Pemeriksaan Kegawatdaruratan', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '1.b', jenis: 'Observasi UGD (per jam) paling lama 6 jam', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '2.a', jenis: 'Rawat luka ringan (Kecil < 5 cm)', biaya: '25.000', satuan: 'Luka', ket: '-' },
                    { no: '2.a', jenis: 'Rawat luka sedang (5 - 10 cm)', biaya: '30.000', satuan: 'Luka', ket: '-' },
                    { no: '2.a', jenis: 'Rawat luka berat (> 10 cm)', biaya: '40.000', satuan: 'Luka', ket: '-' },
                    { no: '2.a', jenis: 'Rawat luka gangren', biaya: '30.000', satuan: 'Luka', ket: '-' },
                    { no: '2.b', jenis: 'Rawat luka bakar derajat I / regio (kecil)', biaya: '40.000', satuan: 'Luka', ket: '-' },
                    { no: '2.b', jenis: 'Rawat luka bakar derajat II / regio (sedang)', biaya: '50.000', satuan: 'Luka', ket: '-' },
                    { no: '2.c', jenis: 'Pasang kateter / dower kateter', biaya: '45.000', satuan: 'Tindakan', ket: '-' },
                    { no: '2.c', jenis: 'Lepas kateter', biaya: '20.000', satuan: 'Tindakan', ket: '-' },
                    { no: '2.d', jenis: 'Pemasangan fiksasi dada', biaya: '50.000', satuan: 'Tindakan', ket: '-' },
                    { no: '2.e', jenis: 'Resusitasi Jantung Paru', biaya: '75.000', satuan: 'Tindakan', ket: '-' },
                    { no: '3.a', jenis: 'Pemakaian nebulizer (tanpa obat)', biaya: '25.000', satuan: 'Pasien', ket: 'Menggunakan Aqua pro injeksi' },
                    { no: '3.b', jenis: 'Pemakaian nebulizer (dengan obat)', biaya: '33.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.c', jenis: 'Pelayanan pemakaian infus pump', biaya: '21.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.d', jenis: 'Pelayanan pemakaian syringe pump', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.e', jenis: 'Pelayanan pemakaian suction pump', biaya: '21.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.f', jenis: 'Pemakaian 1 jam I', biaya: '30.000', satuan: 'Tindakan', ket: '-' },
                    { no: '3.f', jenis: 'Pemakaian per jam berikutnya', biaya: '7.000', satuan: 'Tindakan', ket: '-' },
                    { no: '4', jenis: 'Resusitasi Jantung Paru dengan Alat', biaya: '100.000', satuan: 'Tindakan', ket: '-' }
                ]
            },
            {
                id: 'gigi', title: 'Pengobatan Gigi', icon: 'fa-tooth',
                items: [
                    { no: '1', jenis: 'Pemeriksaan Gigi Umum', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '2', jenis: 'Pembersihan karang gigi / scaling per Regio', biaya: '25.000', satuan: 'Regio', ket: '-' },
                    { no: '3', jenis: 'Penanganan Dry Socket', biaya: '23.000', satuan: 'Regio', ket: '-' },
                    { no: '4', jenis: 'Pencabutan Gigi Sulung dengan Chlorethyl', biaya: '30.000', satuan: 'Gigi', ket: '-' },
                    { no: '5', jenis: 'Pencabutan Gigi Sulung dengan Injeksi Lokal Anestesi', biaya: '35.000', satuan: 'Gigi', ket: '-' },
                    { no: '6', jenis: 'Pencabutan Gigi Permanent', biaya: '40.000', satuan: 'Gigi', ket: '-' },
                    { no: '7', jenis: 'Pencabutan Gigi Permanent M3', biaya: '70.000', satuan: 'Gigi', ket: '-' },
                    { no: '8', jenis: 'Pencabutan Gigi Permanent M3 Miring', biaya: '100.000', satuan: 'Gigi', ket: '-' },
                    { no: '9', jenis: 'Insici Abses', biaya: '25.000', satuan: 'Pasien', ket: 'Ejaan sesuai naskah Perwali' },
                    { no: '10', jenis: 'Open Bur', biaya: '35.000', satuan: 'Gigi', ket: '-' },
                    { no: '11', jenis: 'Pulp Capping', biaya: '35.000', satuan: 'Gigi', ket: '-' },
                    { no: '12', jenis: 'Pulpotomi dengan Antibiotika', biaya: '0', satuan: 'Gigi', ket: 'Tarif Rp 0 sesuai naskah Perwali' },
                    { no: '13', jenis: 'Sterilisasi Ruang Pulpa', biaya: '20.000', satuan: 'Gigi', ket: '-' },
                    { no: '14', jenis: 'Pulpotomi', biaya: '35.000', satuan: 'Gigi', ket: '-' },
                    { no: '15', jenis: 'Tumpatan Basis', biaya: '0', satuan: 'Gigi', ket: 'Tarif Rp 0 sesuai naskah Perwali' },
                    { no: '16', jenis: 'Tumpatan Tetap Glass Ionomer Cement', biaya: '50.000', satuan: 'Gigi', ket: '-' },
                    { no: '17', jenis: 'Tumpatan Tetap Composit', biaya: '75.000', satuan: 'Gigi', ket: '-' },
                    { no: '18', jenis: 'Eugenol Tumpatan Sementara', biaya: '35.000', satuan: 'Gigi', ket: '-' },
                    { no: '19', jenis: 'Devitalisasi Pulpa', biaya: '35.000', satuan: 'Gigi', ket: '-' },
                    { no: '20', jenis: 'Tumpatan tetap Fissure sealent', biaya: '30.000', satuan: 'Gigi', ket: '-' },
                    { no: '21', jenis: 'Curetase pocket gingiva', biaya: '30.000', satuan: 'Gigi', ket: '-' },
                    { no: '22', jenis: 'Operculectomy / Gingivectomy / Frenulectomi', biaya: '75.000', satuan: 'Regio', ket: '-' },
                    { no: '23', jenis: 'Flap periodontal', biaya: '175.000', satuan: 'Regio', ket: '-' },
                    { no: '24', jenis: 'Alveolectomy', biaya: '50.000', satuan: 'Regio', ket: '-' },
                    { no: '25', jenis: 'Topical Aplikasi / per regio', biaya: '20.000', satuan: 'Regio', ket: '-' },
                    { no: '26', jenis: 'Kontrol Post Tindakan', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '27', jenis: 'Penanganan Trismus', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '28.a', jenis: 'Plat + 1 Elemen', biaya: '150.000', satuan: 'Pasien', ket: '-' },
                    { no: '28.a', jenis: 'Tambahan per Elemen Gigi', biaya: '30.000', satuan: 'Pasien', ket: '-' },
                    { no: '28.b', jenis: 'Full Denture per rahang', biaya: '600.000', satuan: 'Pasien', ket: '-' },
                    { no: '28.c', jenis: 'Reparasi Prothesa tanpa Klamer', biaya: '30.000', satuan: 'Pasien', ket: '-' },
                    { no: '28.c', jenis: 'Reparasi Prothesa dengan 1 Klamer', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '29.a', jenis: 'Perawatan ortodonti lepasan sederhana', biaya: '500.000', satuan: 'Rahang', ket: '-' },
                    { no: '29.b', jenis: 'Kontrol perawatan ortodonti sederhana', biaya: '40.000', satuan: 'Pasien', ket: '-' },
                    { no: '30', jenis: 'Angkat Jahitan Gigi', biaya: '25.000', satuan: 'Jahitan', ket: '-' },
                    { no: '31', jenis: 'Oclusal Grending', biaya: '35.000', satuan: 'Gigi', ket: 'Ejaan sesuai naskah Perwali' },
                    { no: '32', jenis: 'Pencabutan Gigi Permanen dengan komplikasi', biaya: '70.000', satuan: 'Gigi', ket: '-' },
                    { no: '33', jenis: 'Perawatan Jaringan Lunak Rongga Mulut Ringan', biaya: '20.000', satuan: 'Regio', ket: '-' }
                ]
            },
            {
                id: 'kia', title: 'KIA & KB', icon: 'fa-baby',
                items: [
                    { no: '1', jenis: 'Pelayanan Pemeriksaan dan Pengobatan Dasar', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '2.a', jenis: 'ANC disertai USG', biaya: '140.000', satuan: 'Pasien', ket: '-' },
                    { no: '2.a', jenis: 'ANC', biaya: '60.000', satuan: 'Pasien', ket: '-' },
                    { no: '2.b', jenis: 'Pelayanan Pemeriksaan dan Pengobatan Dasar PNC', biaya: '40.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.a', jenis: 'Pap Smear', biaya: '125.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.b', jenis: 'See', biaya: '35.000', satuan: 'Pasien', ket: 'termasuk rangkaian pemeriksaan IVA' },
                    { no: '3.b', jenis: 'Treat Ringan', biaya: '6.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.b', jenis: 'Cryo Therapy', biaya: '150.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.c', jenis: 'Inspekulo / Pemeriksaan Dalam (VT)', biaya: '27.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.d', jenis: 'Pasang / lepas vagina tampon', biaya: '36.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.e', jenis: 'Vagina Hygiene', biaya: '27.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.f', jenis: 'Imunisasi TT pengantin / ibu hamil', biaya: '20.000', satuan: 'Tindakan', ket: '-' },
                    { no: '4.a', jenis: 'Tindik', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.b', jenis: 'Deteksi Dini Tumbuh Kembang Anak', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.c', jenis: 'Fototerapi', biaya: '25.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.d', jenis: 'Perawatan tali pusar', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '5.a', jenis: 'KB Suntik 3 bulan', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '5.b', jenis: 'KB Suntik 1 bulan', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '5.c', jenis: 'KB PIL', biaya: '5.000', satuan: 'Pasien', ket: '-' },
                    { no: '5.d', jenis: 'Pasang tanpa komplikasi', biaya: '100.000', satuan: 'Pasien', ket: 'tidak termasuk alat kontrasepsi' },
                    { no: '5.d', jenis: 'Pasang dengan komplikasi', biaya: '125.000', satuan: 'Pasien', ket: 'tidak termasuk alat kontrasepsi' },
                    { no: '5.d', jenis: 'Cabut / lepas tanpa komplikasi', biaya: '100.000', satuan: 'Pasien', ket: '-' },
                    { no: '5.d', jenis: 'Cabut / lepas dengan komplikasi', biaya: '150.000', satuan: 'Pasien', ket: '-' },
                    { no: '5.e', jenis: 'Pasang tanpa penyulit', biaya: '100.000', satuan: 'Pasien', ket: 'tidak termasuk alat kontrasepsi' },
                    { no: '5.e', jenis: 'Pasang dengan komplikasi', biaya: '125.000', satuan: 'Pasien', ket: 'tidak termasuk alat kontrasepsi' },
                    { no: '5.e', jenis: 'Cabut / lepas tanpa komplikasi', biaya: '100.000', satuan: 'Pasien', ket: '-' },
                    { no: '5.e', jenis: 'Cabut / lepas dengan komplikasi', biaya: '150.000', satuan: 'Pasien', ket: '-' },
                    { no: '6', jenis: 'Paket Pasca Persalinan', biaya: '150.000', satuan: 'Pasien', ket: 'Perawatan plasenta, pelatihan memandikan bayi' }
                ]
            },
            {
                id: 'penunjang', title: 'Penunjang Medik', icon: 'fa-microscope',
                items: [
                    { no: '1', jenis: 'USG Kandungan', biaya: '60.000', satuan: 'Pasien', ket: 'termasuk print out' },
                    { no: '2', jenis: 'Rontgen Foto Dental (gigi)', biaya: '35.000', satuan: 'Film', ket: '-' }
                ]
            },
            {
                id: 'khusus', title: 'Kesehatan Khusus', icon: 'fa-user-doctor',
                items: [
                    { no: '1', jenis: 'Gizi (Pojok Gizi)', biaya: '10.000', satuan: 'Pasien', ket: '-' },
                    { no: '2.a', jenis: 'Pemeriksaan jenazah', biaya: '27.500', satuan: 'Jenazah', ket: 'termasuk penerbitan surat kematian' },
                    { no: '2.b', jenis: 'Pemeriksaan jenazah di luar jam kerja', biaya: '50.000', satuan: 'Jenazah', ket: '-' },
                    { no: '3.a', jenis: 'Konsultasi Psikologi', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.b', jenis: 'Tes IQ', biaya: '100.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.b', jenis: 'Tes Bakat Minat', biaya: '130.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.b', jenis: 'Tes Minat', biaya: '75.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.b', jenis: 'Tes MMPI', biaya: '75.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.b', jenis: 'Tes Rekruitmen (IQ dan Performance Test)', biaya: '150.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.b', jenis: 'Tes Kebutuhan', biaya: '25.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.b', jenis: 'Konseling', biaya: '30.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.c', jenis: 'Terapi Wicara', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.c', jenis: 'Terapi Okupasi', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.d', jenis: 'ECT (Electro Convulsive Therapy)', biaya: '75.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.e', jenis: 'Konseling VCT', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.f', jenis: 'Pelayanan Program Terapi Rumatan Metadon', biaya: '30.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.g', jenis: 'Terapi Perilaku (Psikolog Klinis)', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.a', jenis: 'Akupuntur', biaya: '35.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.b', jenis: 'Akupuntur dg elektro stimulator', biaya: '45.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.c', jenis: 'Akupresur', biaya: '35.000', satuan: 'Pasien', ket: 'Termasuk Totok wajah' },
                    { no: '4.d', jenis: 'Aromatherapi', biaya: '30.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.e', jenis: 'Konsultasi Peracikan Herbal', biaya: '10.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.f', jenis: 'Umur 0 - 1 tahun', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.f', jenis: 'Umur > 1 tahun', biaya: '25.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.g', jenis: 'Pijat Laktasi', biaya: '35.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.h', jenis: 'Akupuntur Dengan Moksa', biaya: '75.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.i', jenis: 'Lulur / Boreh', biaya: '35.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.j', jenis: 'Fisioterapi', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.k', jenis: 'Terapi Kerokan', biaya: '35.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.l', jenis: 'Terapi Kop Kering', biaya: '35.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.m', jenis: 'Moksibusi', biaya: '35.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.n', jenis: 'Pijat Ibu Hamil', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.o', jenis: 'Pijat Refleksi', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.p', jenis: 'Pijat Swedish', biaya: '100.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.q', jenis: 'Pijat Tradisional Durasi 60 Menit', biaya: '100.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.r', jenis: 'Pijat Tuina', biaya: '35.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.s', jenis: 'Pijat Wajah / Totok Wajah', biaya: '45.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.t', jenis: 'Sediaan Pemakaian Luar (Masker)', biaya: '35.000', satuan: 'Pasien', ket: '-' },
                    { no: '5', jenis: 'Terapi Infra Merah', biaya: '35.000', satuan: 'Pasien', ket: '-' },
                    { no: '6.a', jenis: 'Imunisasi HPV (non program)', biaya: '850.000', satuan: '1x suntikan', ket: 'Harus 3x suntikan' },
                    { no: '6.b', jenis: 'Imunisasi Influenza (non program)', biaya: '155.000', satuan: 'Tindakan', ket: '-' },
                    { no: '7', jenis: 'Vaksin DBD', biaya: '550.000', satuan: 'Suntikan', ket: 'Harus 2x suntikan' }
                ]
            },
            {
                id: 'ranap', title: 'Rawat Inap', icon: 'fa-bed-pulse',
                items: [
                    { no: '1.a', jenis: 'Bayi', biaya: '40.000', satuan: 'Hari', ket: '-' },
                    { no: '1.b', jenis: 'Anak / Dewasa', biaya: '90.000', satuan: 'Hari', ket: '-' },
                    { no: '2', jenis: 'Asuhan medik (visite) Dokter Umum', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.a', jenis: 'Dokter Umum / Jaga UGD', biaya: '10.000', satuan: 'Pasien', ket: '-' },
                    { no: '3.b', jenis: 'Obat / Farmasi', biaya: '10.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.a', jenis: 'Minimum nursing care (< 4 jam / hari)', biaya: '10.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.b', jenis: 'Parsial nursing care (4-6 jam / hari)', biaya: '12.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.c', jenis: 'Total nursing care (7-9 jam / hari)', biaya: '15.000', satuan: 'Pasien', ket: '-' },
                    { no: '4.d', jenis: 'Parsial nursing care (4-6 jam / hari)', biaya: '12.000', satuan: 'Pasien', ket: 'Duplikat dari 4.b pada naskah Perda' },
                    { no: '4.e', jenis: 'Total nursing care (7-9 jam / hari)', biaya: '15.000', satuan: 'Pasien', ket: 'Duplikat dari 4.c pada naskah Perda' }
                ]
            },
            {
                id: 'persalinan', title: 'Persalinan (PONED)', icon: 'fa-children',
                items: [
                    { no: '1', jenis: 'Persalinan normal', biaya: '700.000', satuan: 'Tindakan', ket: '-' },
                    { no: '2', jenis: 'Persalinan patologis', biaya: '1.000.000', satuan: 'Tindakan', ket: '-' },
                    { no: '3', jenis: 'Hecting Portio', biaya: '70.000', satuan: 'Tindakan', ket: '-' },
                    { no: '4', jenis: 'Hecting Ruptur Perineum Totalis', biaya: '70.000', satuan: 'Tindakan', ket: '-' },
                    { no: '5', jenis: 'Resusitasi asfiksia', biaya: '40.000', satuan: 'Tindakan', ket: '-' },
                    { no: '6', jenis: 'Resusitasi BBL', biaya: '25.000', satuan: 'Tindakan', ket: '-' },
                    { no: '7', jenis: 'Inkubator', biaya: '35.000', satuan: 'Pasien/hari', ket: '-' },
                    { no: '8', jenis: 'Infant warmer', biaya: '35.000', satuan: 'Pasien/hari', ket: '-' }
                ]
            },
            {
                id: 'lab', title: 'Lab Kesehatan', icon: 'fa-flask',
                items: [
                    { no: 'A.1.a', jenis: 'Darah Lengkap (3 diff): Hemoglobin, Eritrosit, Leukosit, Trombosit/PLT, Hematokrit, Hitung jenis leukosit', biaya: '50.000', satuan: 'Sampel', ket: '-' },
                    { no: 'A.1.b', jenis: 'Laju Endap Darah (LED)', biaya: '20.000', satuan: 'Sampel', ket: '-' },
                    { no: 'A.2.a', jenis: 'Pemeriksaan Golongan Darah', biaya: '20.000', satuan: 'Sampel', ket: 'Pada naskah tertulis huruf besar A; lihat sheet Catatan' },
                    { no: 'B.a', jenis: 'Urine Rutin / Urine Lengkap: Protein, Glukosa Urine / Reduksi, Bilirubin', biaya: '20.000', satuan: 'Sampel', ket: '-' },
                    { no: 'B.b', jenis: 'Pemeriksaan Sedimen Urine (Ca / Cl)', biaya: '6.000', satuan: 'Sampel', ket: '-' },
                    { no: 'B.c', jenis: 'Narkoba: THC/Marijuana, Methamphetamine (MET), Benzodiazepines (BZO), Ecstasy (MDMA), Morphine (MOR), Cocaine, Opiate', biaya: '100.000', satuan: 'Sampel', ket: '-' },
                    { no: 'C', jenis: 'Feses Rutin / Feses Lengkap (Telur Cacing, Amoeba)', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'D.a', jenis: 'Pemeriksaan gula darah stick', biaya: '15.000', satuan: 'Parameter', ket: '-' },
                    { no: 'D.b', jenis: 'Pemeriksaan gula darah fotometer', biaya: '23.000', satuan: 'Parameter', ket: '-' },
                    { no: 'D.c', jenis: 'Pemeriksaan Gula Darah Acak (GDA)', biaya: '23.000', satuan: 'Tindakan', ket: '-' },
                    { no: 'E.a', jenis: 'SGOT / ALAT', biaya: '25.000', satuan: 'Parameter', ket: '-' },
                    { no: 'E.b', jenis: 'SGPT / ALAT', biaya: '25.000', satuan: 'Parameter', ket: '-' },
                    { no: 'F.a', jenis: 'Creatinin', biaya: '40.000', satuan: 'Parameter', ket: '-' },
                    { no: 'F.b', jenis: 'Urea - N (BUN)', biaya: '40.000', satuan: 'Parameter', ket: '-' },
                    { no: 'F.c', jenis: 'Asam Urat Stick', biaya: '40.000', satuan: 'Parameter', ket: '-' },
                    { no: 'F.c', jenis: 'Asam Urat Fotometer', biaya: '40.000', satuan: 'Parameter', ket: '-' },
                    { no: 'G.a', jenis: 'Cholesterol Total', biaya: '40.000', satuan: 'Parameter', ket: '-' },
                    { no: 'G.b', jenis: 'Trigliserida', biaya: '40.000', satuan: 'Parameter', ket: '-' },
                    { no: 'I', jenis: 'EKG', biaya: '25.000', satuan: 'Sampel', ket: 'Penomoran melompat dari G ke I' },
                    { no: 'J.a', jenis: 'HBsAg Rapid', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'J.b', jenis: 'HIV Rapid', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'J.c', jenis: 'HIV konfirmasi (2 kali)', biaya: '70.000', satuan: 'Sampel', ket: '-' },
                    { no: 'J.d', jenis: 'IgG Dengue', biaya: '140.000', satuan: 'Sampel', ket: '-' },
                    { no: 'J.e', jenis: 'IgM Dengue', biaya: '140.000', satuan: 'Sampel', ket: '-' },
                    { no: 'J.f', jenis: 'VDRL', biaya: '43.000', satuan: 'Sampel', ket: '-' },
                    { no: 'J.g', jenis: 'TPHA', biaya: '52.000', satuan: 'Sampel', ket: '-' },
                    { no: 'J.h', jenis: 'RPR', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'J.i', jenis: 'Widal', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'K.a', jenis: 'BTA / TBC', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'K.b', jenis: 'BTA / Kusta', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'K.c', jenis: 'Malaria', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'K.d', jenis: 'VDRL / GO / TPHA (per item parameter)', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'K.e', jenis: 'Pengecatan Gram', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'K.f', jenis: 'Pemeriksaan Jamur', biaya: '15.000', satuan: 'Sampel', ket: '-' },
                    { no: 'K.g', jenis: 'Trichomonas', biaya: '15.000', satuan: 'Sampel', ket: '-' },
                    { no: 'K.i', jenis: 'Sekret vagina', biaya: '35.000', satuan: 'Sampel', ket: 'Penomoran melompat dari g ke i' },
                    { no: 'K.j', jenis: 'Difteri (hapusan)', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'K.k', jenis: 'Plano Test (Tes Kehamilan Urine)', biaya: '25.000', satuan: 'Sampel', ket: '-' },
                    { no: 'L', jenis: 'Pemeriksaan Bilirubin Dengan Alat Jaundice Meter', biaya: '50.000', satuan: 'Sampel', ket: '-' },
                    { no: 'M', jenis: 'Pemeriksaan Hb Stick', biaya: '20.000', satuan: 'Sampel', ket: '-' },
                    { no: 'N', jenis: 'Pemeriksaan Kolesterol Stik', biaya: '35.000', satuan: 'Sampel', ket: '-' },
                    { no: 'O', jenis: 'Pemeriksaan Ns1', biaya: '75.000', satuan: 'Sampel', ket: '-' },
                    { no: 'P', jenis: 'Pemeriksaan Trigliserida Stik', biaya: '40.000', satuan: 'Sampel', ket: '-' },
                    { no: 'Q', jenis: 'HbA1c', biaya: '150.000', satuan: 'Sampel', ket: '-' }
                ]
            },
            {
                id: 'haji', title: 'Kesehatan Haji', icon: 'fa-plane',
                items: [
                    { no: '1', jenis: 'Pemeriksaan Kesehatan Tahap I', biaya: '200.000', satuan: 'Pasien', ket: 'Pemeriksaan kesehatan dasar, Kesehatan Jiwa (SRQ20), Mini COG & CDT4, AMT, ADL Indeks Barthel, Urine Lengkap, Plano Test, EKG, Siskohat dan monev' },
                    { no: '2', jenis: 'Pemeriksaan Kesehatan Tahap II', biaya: '250.000', satuan: 'Pasien', ket: '-' },
                    { no: '3', jenis: 'Pemeriksaan Kesehatan Untuk Pendidikan', biaya: '20.000', satuan: 'Pasien', ket: '-' },
                    { no: '4', jenis: 'Paket A', biaya: '353.000', satuan: 'Pasien', ket: 'Gula Darah; Fungsi Hati; Fungsi Ginjal; Pemeriksaan lemak' },
                    { no: '4', jenis: 'Paket B', biaya: '528.000', satuan: 'Pasien', ket: 'Gula Darah; Fungsi Hati; Fungsi Ginjal; Pemeriksaan lemak; HbA1C; EKG' },
                    { no: '5', jenis: 'Pemeriksaan Kesehatan untuk Asuransi', biaya: '50.000', satuan: 'Pasien', ket: '-' },
                    { no: '6', jenis: 'Pemeriksaan Kesehatan Calon Tenaga Kerja', biaya: '20.000', satuan: 'Pasien', ket: '-' }
                ]
            },
            {
                id: 'rujukan', title: 'Rujukan', icon: 'fa-hospital',
                items: [
                    { no: '1.a', jenis: 'Pelayanan pra rujukan', biaya: '30.000', satuan: 'Pasien', ket: '-' },
                    { no: '1.b', jenis: 'Dalam Kota Surabaya', biaya: '30.000', satuan: 'Rujukan', ket: '-' },
                    { no: '1.b', jenis: 'Luar Kota Surabaya', biaya: '65.000', satuan: 'Rujukan', ket: '-' },
                    { no: '2.a', jenis: 'Pemakaian awal 5 km I (10 km - PP)', biaya: '70.000', satuan: 'unit', ket: '-' },
                    { no: '2.b', jenis: 'Pemakaian setiap penambahan 1 km berikutnya', biaya: '7.000', satuan: 'unit', ket: '-' }
                ]
            }
        ];

        // Generate the 12 boxes
        const gridContainer = document.querySelector('.grid');
        let html = '';
        categories.forEach((cat, index) => {
            let titleLines = cat.title.replace(' (PONED)', '<br>(PONED)');
            if(cat.id === 'bedah') titleLines = 'Tindakan Medik<br>Operatif';
            if(cat.id === 'gigi') titleLines = 'Pengobatan<br>Gigi';
            if(cat.id === 'penunjang') titleLines = 'Penunjang<br>Medik';
            if(cat.id === 'khusus') titleLines = 'Kesehatan<br>Khusus';
            if(cat.id === 'haji') titleLines = 'Kesehatan<br>Haji';

            html += `
            <div onclick="openModal(${index})" class="tariff-box glass-card rounded-2xl p-5 md:p-6 text-center cursor-pointer transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="icon-box w-14 h-14 mx-auto bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-2xl mb-4 transition-colors duration-300">
                    <i class="fa-solid ${cat.icon}"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-sm md:text-base leading-tight">${titleLines}</h3>
            </div>
            `;
        });
        gridContainer.innerHTML = html;

        // Modal Logic
        let currentModalIndex = -1;
        const modal = document.getElementById('tariffModal');
        const modalBox = document.getElementById('modalContentBox');
        const modalTitle = document.getElementById('modalTitle');
        const modalIconBox = document.getElementById('modalIconBox');
        const modalTableBody = document.getElementById('modalTableBody');
        const modalSearch = document.getElementById('modalSearch');
        const modalDasar = document.getElementById('modalDasar');

        // Search Logic
        modalSearch.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = modalTableBody.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const jenisCell = rows[i].getElementsByTagName('td')[1]; // Kolom JENIS PELAYANAN
                if (jenisCell) {
                    const txtValue = jenisCell.textContent || jenisCell.innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        });

        function openModal(index) {
            currentModalIndex = index;
            const data = categories[index];

            // Show hide nav buttons
            document.getElementById('prevBtn').classList.toggle('hidden', index === 0);
            document.getElementById('nextBtn').classList.toggle('hidden', index === categories.length - 1);

            modalTitle.innerText = data.title;
            modalIconBox.innerHTML = `<i class="fa-solid ${data.icon}"></i>`;
            modalSearch.value = ''; // Reset search input
            
            // Set Dasar Hukum
            if (['ranap', 'persalinan', 'rujukan'].includes(data.id)) {
                modalDasar.innerText = "Dasar: Lampiran I Perda Kota Surabaya No. 7 Tahun 2023, huruf A angka 1";
            } else {
                modalDasar.innerText = "Dasar: Lampiran Perwali Kota Surabaya No. 20 Tahun 2025, huruf A angka 1";
            }

            
            let tbodyHtml = '';
            data.items.forEach((item, i) => {
                const jenis = item.jenis || item.status; // handling tiny typo in mock data
                const no = item.no || (i + 1);
                tbodyHtml += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-4 text-center font-bold text-slate-400">${no}</td>
                        <td class="px-4 py-4 font-semibold">${jenis}</td>
                        <td class="px-4 py-4 text-right font-bold text-primary">${item.biaya}</td>
                        <td class="px-4 py-4 text-slate-500">${item.satuan}</td>
                        <td class="px-4 py-4 text-slate-500 text-xs md:text-sm whitespace-normal">${item.ket}</td>
                    </tr>
                `;
            });
            modalTableBody.innerHTML = tbodyHtml;

            modal.classList.remove('hidden');
            // Small delay to allow display:block to apply before animating opacity
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalBox.classList.remove('scale-95');
                modalBox.classList.add('scale-100');
            }, 10);
        }

        function closeModal() {
            modal.classList.add('opacity-0');
            modalBox.classList.remove('scale-100');
            modalBox.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function prevModal(e) {
            e.stopPropagation();
            if (currentModalIndex > 0) openModal(currentModalIndex - 1);
        }

        function nextModal(e) {
            e.stopPropagation();
            if (currentModalIndex < categories.length - 1) openModal(currentModalIndex + 1);
        }

        // Close on clicking outside for tariff modal
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // --- PANDUAN PEMBAYARAN MODAL LOGIC ---
        const panduanData = {
            'jkn': {
                title: 'JKN / BPJS Kesehatan',
                icon: 'fa-id-card',
                intro: 'JKN merupakan program jaminan kesehatan nasional yang diselenggarakan oleh BPJS Kesehatan.',
                subtitle: 'Sebelum menggunakan layanan, pastikan:',
                list: [
                    'Kepesertaan JKN/BPJS dalam kondisi aktif.',
                    'Fasilitas kesehatan dan layanan yang dipilih sesuai dengan ketentuan JKN.',
                    'Mengikuti prosedur pelayanan yang berlaku.',
                    'Menyiapkan dokumen atau identitas yang diperlukan.',
                    'Memastikan apakah diperlukan rujukan atau prosedur tertentu sebelum mendapatkan layanan.'
                ],
                catatan: 'Ketentuan manfaat, prosedur, dan cakupan pelayanan dapat berubah sesuai kebijakan BPJS Kesehatan. Informasi pada SEHATI merupakan panduan umum dan bukan pengganti ketentuan resmi BPJS Kesehatan.',
                btnText: 'Lihat Informasi Resmi BPJS Kesehatan ↗',
                btnLink: 'https://www.bpjs-kesehatan.go.id/#/jaminan-kesehatan-manfaat'
            },
            'umum': {
                title: 'Umum / Pribadi',
                icon: 'fa-wallet',
                intro: 'Metode pembiayaan umum/pribadi berarti biaya pelayanan kesehatan ditanggung oleh pengguna sesuai tarif dan ketentuan fasilitas kesehatan.',
                subtitle: 'Sebelum mendapatkan layanan, perhatikan:',
                list: [
                    'Periksa tarif layanan yang akan digunakan.',
                    'Tanyakan kemungkinan biaya tambahan jika ada.',
                    'Pastikan metode pembayaran yang diterima oleh fasilitas kesehatan.',
                    'Perkirakan total biaya pelayanan yang mungkin diperlukan.',
                    'Simpan bukti pembayaran setelah transaksi.'
                ],
                catatan: 'Tarif yang ditampilkan pada halaman ini merupakan informasi tarif layanan berdasarkan ketentuan yang tercantum pada halaman Tarif Layanan SEHATI. Biaya aktual dapat bergantung pada jenis pelayanan yang diterima dan ketentuan fasilitas kesehatan.',
                btnText: '',
                btnLink: ''
            },
            'asuransi': {
                title: 'Asuransi Kesehatan Lain',
                icon: 'fa-umbrella-beach',
                intro: 'Ketentuan penggunaan asuransi kesehatan di luar JKN dapat berbeda berdasarkan perusahaan asuransi dan polis yang dimiliki pengguna.',
                subtitle: 'Sebelum mendapatkan layanan, periksa:',
                list: [
                    'Manfaat yang ditanggung dalam polis.',
                    'Apakah fasilitas kesehatan termasuk jaringan/provider asuransi.',
                    'Mekanisme pembayaran: cashless atau reimbursement.',
                    'Dokumen yang perlu disiapkan.',
                    'Apakah diperlukan surat jaminan, rujukan, atau persetujuan terlebih dahulu.',
                    'Batas manfaat, plafon, deductible/co-payment, dan pengecualian yang berlaku.'
                ],
                listType: 'decimal',
                catatan: 'SEHATI tidak menentukan ketentuan polis atau menjamin suatu layanan ditanggung oleh perusahaan asuransi tertentu. Untuk informasi yang paling sesuai dengan polis Anda, periksa dokumen polis atau hubungi perusahaan asuransi terkait.',
                btnText: '',
                btnLink: ''
            },
            'belumtahu': {
                title: 'Belum Tahu Metode Pembiayaan?',
                icon: 'fa-circle-question',
                intro: 'Jika Anda belum mengetahui metode pembiayaan yang sesuai, periksa kartu kepesertaan atau aplikasi layanan kesehatan yang Anda gunakan.',
                subtitle: 'Anda dapat melakukan langkah berikut:',
                list: [
                    'Periksa apakah Anda memiliki kepesertaan JKN/BPJS.',
                    'Jika memiliki asuransi lain, periksa kartu atau aplikasi asuransi.',
                    'Periksa fasilitas kesehatan yang dapat digunakan.',
                    'Konfirmasi mekanisme pembayaran kepada fasilitas kesehatan sebelum mendapatkan layanan.'
                ],
                listType: 'decimal',
                catatan: '',
                btnText: 'Kembali ke Pengaturan Metode Pembiayaan',
                btnLink: 'keluarga.php'
            }
        };

        const pModal = document.getElementById('panduanModal');
        const pModalBox = document.getElementById('panduanModalBox');
        const pModalTitle = document.getElementById('panduanModalTitle');
        const pModalIconBox = document.getElementById('panduanModalIconBox');
        const pModalBody = document.getElementById('panduanModalBody');

        function openPanduanModal(key) {
            const data = panduanData[key];
            if (!data) return;

            pModalTitle.innerText = data.title;
            pModalIconBox.innerHTML = `<i class="fa-solid ${data.icon}"></i>`;

            let listHtml = '';
            if (data.list && data.list.length > 0) {
                const listTag = data.listType === 'decimal' ? 'ol' : 'ul';
                const listClass = data.listType === 'decimal' ? 'list-decimal list-outside pl-5 space-y-2' : 'space-y-2';
                
                listHtml = `<${listTag} class="${listClass}">`;
                data.list.forEach(item => {
                    if(data.listType === 'decimal') {
                        listHtml += `<li class="pl-1"><span class="text-slate-700">${item}</span></li>`;
                    } else {
                        listHtml += `<li class="flex items-start gap-2"><i class="fa-solid fa-check text-emerald-500 mt-1"></i> <span class="text-slate-700">${item}</span></li>`;
                    }
                });
                listHtml += `</${listTag}>`;
            }

            let btnHtml = '';
            if (data.btnText && data.btnLink) {
                const target = data.btnLink.startsWith('http') ? 'target="_blank"' : '';
                btnHtml = `
                <div class="mt-8 mb-2">
                    <a href="${data.btnLink}" ${target} class="inline-block px-6 py-3 bg-primary hover:bg-primaryDark text-white font-bold rounded-xl transition-colors shadow-sm w-full md:w-auto text-center">
                        ${data.btnText}
                    </a>
                </div>
                `;
            }

            let catatanHtml = '';
            if (data.catatan) {
                catatanHtml = `
                <div class="mt-6 p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm italic text-slate-500">
                    ${data.catatan}
                </div>
                `;
            }

            pModalBody.innerHTML = `
                <p class="font-medium text-slate-800 mb-6 text-base">${data.intro}</p>
                <div class="mb-4">
                    <h4 class="font-bold text-slate-800 mb-3">${data.subtitle}</h4>
                    ${listHtml}
                </div>
                ${catatanHtml}
                ${btnHtml}
            `;

            pModal.classList.remove('hidden');
            setTimeout(() => {
                pModal.classList.remove('opacity-0');
                pModalBox.classList.remove('scale-95');
                pModalBox.classList.add('scale-100');
            }, 10);
        }

        function closePanduanModal() {
            pModal.classList.add('opacity-0');
            pModalBox.classList.remove('scale-100');
            pModalBox.classList.add('scale-95');
            setTimeout(() => {
                pModal.classList.add('hidden');
            }, 300);
        }

        // Close on clicking outside for panduan modal
        pModal.addEventListener('click', function(e) {
            if (e.target === pModal) {
                closePanduanModal();
            }
        });

        // Close on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (!modal.classList.contains('hidden')) closeModal();
                if (!pModal.classList.contains('hidden')) closePanduanModal();
            }
        });
    </script>
</body>
</html>
