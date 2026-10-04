<?php
if(session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petugas - Dashboard Antrean</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#413074',
                        primaryDark: '#2c1e54',
                        secondary: '#f6f9f9',
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
<body class="text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col h-full shrink-0">
        <div class="p-6 bg-slate-950">
            <h1 class="text-xl font-bold flex items-center gap-2 text-primary">
                <img src="/sehati/frontend/logo.png" alt="SEHATI Logo" class="h-8 brightness-0 invert"> SEHATI <span class="text-xs font-normal text-slate-400">STAFF</span>
            </h1>
        </div>
        <div class="p-4 border-b border-slate-800">
            <p class="text-xs text-slate-400 uppercase font-semibold">Fasilitas Aktif</p>
            <p class="font-bold text-sm mt-1">Puskesmas Mulyorejo</p>
            <div class="mt-2 bg-slate-800 rounded-lg p-2 flex items-center justify-between cursor-pointer">
                <span class="text-sm font-medium">Poli Umum</span>
                <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
            </div>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="#" class="flex items-center gap-3 px-4 py-3 bg-primary text-white font-medium rounded-lg">
                <i class="fa-solid fa-users-viewfinder"></i> Kelola Antrean
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white transition-colors rounded-lg">
                <i class="fa-solid fa-list-check"></i> Daftar Pasien Hari Ini
            </a>
        </nav>
        <div class="p-4 border-t border-slate-800 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-sm font-bold">DR</div>
            <div>
                <p class="text-sm font-medium">Dr. Andi</p>
                <p class="text-xs text-slate-400">Keluar</p>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 h-full overflow-y-auto bg-slate-50 flex flex-col">
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200 p-4 flex justify-between items-center shrink-0">
            <h2 class="text-lg font-bold">Dashboard Antrean</h2>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-500 font-medium">04 September 2026</span>
                <div class="h-6 w-px bg-slate-200"></div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    <span class="text-sm font-bold text-emerald-600">Pendaftaran Buka</span>
                </div>
            </div>
        </header>

        <div class="p-6 md:p-8 flex-1 grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Queue Control Panel (Takes up 2 columns) -->
            <div class="lg:col-span-2 space-y-6 flex flex-col">
                
                <!-- Current Serving -->
                <div class="glass-card rounded-2xl p-8 flex-1 flex flex-col items-center justify-center text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
                    
                    <p class="text-slate-500 font-semibold uppercase tracking-widest mb-2">Sedang Dilayani</p>
                    <div class="text-8xl font-black text-slate-800 my-4">#21</div>
                    <h3 class="text-2xl font-bold text-slate-700">Siti Aminah</h3>
                    <p class="text-slate-500 mt-1">Keluhan: Pusing dan Mual</p>
                    
                    <div class="flex gap-4 mt-10 w-full max-w-md">
                        <button class="flex-1 py-3 bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold rounded-xl hover:bg-emerald-200 transition-colors">
                            <i class="fa-solid fa-check mr-2"></i> Selesai
                        </button>
                        <button class="flex-1 py-3 bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-xl hover:bg-amber-200 transition-colors">
                            <i class="fa-solid fa-rotate-right mr-2"></i> Panggil Ulang
                        </button>
                    </div>
                </div>

                <!-- Next Patient Controls -->
                <div class="glass-card rounded-2xl p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 font-medium mb-1">Antrean Berikutnya</p>
                        <div class="flex items-end gap-3">
                            <span class="text-3xl font-black text-slate-800">#22</span>
                            <span class="text-lg font-bold text-slate-600 mb-1">Ahmad Yani</span>
                        </div>
                    </div>
                    <button class="px-8 py-4 bg-primary text-white font-bold text-lg rounded-xl shadow-lg hover:bg-primaryDark transition-colors flex items-center gap-3">
                        <i class="fa-solid fa-bullhorn"></i> Panggil Berikutnya
                    </button>
                </div>
            </div>

            <!-- Waiting List -->
            <div class="glass-card rounded-2xl flex flex-col h-full overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-white sticky top-0 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Daftar Tunggu</h3>
                    <span class="bg-primary bg-opacity-10 text-primary text-xs font-bold px-2 py-1 rounded-md">6 Orang</span>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    
                    <div class="bg-blue-50 border border-blue-100 p-3 rounded-xl flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white text-primary font-bold flex items-center justify-center shadow-sm">22</div>
                            <div>
                                <p class="font-bold text-sm text-slate-800">Ahmad Yani</p>
                                <p class="text-xs text-slate-500">08:15</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-primary uppercase">Menunggu</span>
                    </div>

                    <div class="bg-white border border-slate-100 p-3 rounded-xl flex items-center justify-between hover:border-slate-300 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-50 text-slate-600 font-bold flex items-center justify-center border border-slate-200">23</div>
                            <div>
                                <p class="font-bold text-sm text-slate-800">Ratna Sari</p>
                                <p class="text-xs text-slate-500">08:30</p>
                            </div>
                        </div>
                        <button class="text-slate-400 hover:text-slate-700 p-2"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    </div>

                    <div class="bg-white border border-slate-100 p-3 rounded-xl flex items-center justify-between hover:border-slate-300 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-50 text-slate-600 font-bold flex items-center justify-center border border-slate-200">24</div>
                            <div>
                                <p class="font-bold text-sm text-slate-800">Doni Kusuma</p>
                                <p class="text-xs text-slate-500">08:45</p>
                            </div>
                        </div>
                        <button class="text-slate-400 hover:text-slate-700 p-2"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    </div>

                    <div class="bg-white border border-slate-100 p-3 rounded-xl flex items-center justify-between hover:border-slate-300 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-50 text-slate-600 font-bold flex items-center justify-center border border-slate-200">25</div>
                            <div>
                                <p class="font-bold text-sm text-slate-800">Eka Putra</p>
                                <p class="text-xs text-slate-500">09:00</p>
                            </div>
                        </div>
                        <button class="text-slate-400 hover:text-slate-700 p-2"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    </div>
                </div>
            </div>

        </div>
    </main>
</body>
</html>





