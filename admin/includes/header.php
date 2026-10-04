<?php
// Start session for future auth
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Configuration will be included here later
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sehati</title>
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
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
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
        .active-nav { background-color: #0ea5e9; color: white; }
        .active-nav i { color: white; }
    </style>
</head>
<body class="text-slate-800 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col h-full shrink-0">
        <div class="p-6 bg-slate-950 text-white">
            <h1 class="text-xl font-bold flex items-center gap-2 text-primary">
                <img src="/sehati/frontend/logo.png" alt="SEHATI Logo" class="h-8 brightness-0 invert"> SEHATI <span class="text-xs font-normal text-slate-400">ADMIN</span>
            </h1>
        </div>
        
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Main</p>
            <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active-nav' : ''; ?>">
                <i class="fa-solid fa-chart-pie w-5"></i> Dashboard
            </a>
            
            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Master Data</p>
            <a href="users.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active-nav' : ''; ?>">
                <i class="fa-solid fa-users w-5"></i> Pengguna
            </a>
            <a href="facilities.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors <?php echo (basename($_SERVER['PHP_SELF']) == 'facilities.php') ? 'active-nav' : ''; ?>">
                <i class="fa-solid fa-hospital w-5"></i> Faskes
            </a>
            <a href="doctors.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors <?php echo (basename($_SERVER['PHP_SELF']) == 'doctors.php') ? 'active-nav' : ''; ?>">
                <i class="fa-solid fa-user-doctor w-5"></i> Dokter & Staf
            </a>
            <a href="services.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors <?php echo (basename($_SERVER['PHP_SELF']) == 'services.php') ? 'active-nav' : ''; ?>">
                <i class="fa-solid fa-notes-medical w-5"></i> Layanan Medis
            </a>
            <a href="tariffs.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors <?php echo (basename($_SERVER['PHP_SELF']) == 'tariffs.php') ? 'active-nav' : ''; ?>">
                <i class="fa-solid fa-money-bill-wave w-5"></i> Tarif Layanan
            </a>
            <a href="schedules.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors <?php echo (basename($_SERVER['PHP_SELF']) == 'schedules.php') ? 'active-nav' : ''; ?>">
                <i class="fa-regular fa-calendar-alt w-5"></i> Jadwal Faskes
            </a>

            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Monitoring</p>
            <a href="registrations.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-colors <?php echo (basename($_SERVER['PHP_SELF']) == 'registrations.php') ? 'active-nav' : ''; ?>">
                <i class="fa-solid fa-list-check w-5"></i> Semua Pendaftaran
            </a>
        </nav>
        
        <div class="p-4 border-t border-slate-800">
            <a href="#" class="flex items-center gap-3 px-4 py-3 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition-colors">
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center font-bold text-sm text-white">A</div>
                <div>
                    <p class="text-sm font-semibold">Super Admin</p>
                    <p class="text-xs text-slate-400">Logout</p>
                </div>
            </a>
        </div>
    </aside>

    <!-- Main Content wrapper -->
    <main class="flex-1 h-full overflow-y-auto bg-slate-50 flex flex-col">
        <!-- Top Header -->
        <header class="bg-white border-b border-slate-200 p-4 flex justify-between items-center shrink-0">
            <h2 class="text-lg font-bold text-slate-800" id="page-title">Admin Dashboard</h2>
            <div class="flex items-center gap-4">
                <button class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:text-primary hover:bg-blue-50 transition-colors relative">
                    <i class="fa-regular fa-bell"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>
            </div>
        </header>

        <div class="p-6 md:p-8 flex-1">


