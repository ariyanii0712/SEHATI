<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
$user_id = $_SESSION["user_id"];

// Fetch Patients for Filter
$patients = [];
$stmt = $conn->prepare("SELECT id, nama_lengkap, hubungan FROM patients WHERE user_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $patients[] = $row;
}

$filter_patient_id = isset($_GET['patient_id']) && $_GET['patient_id'] !== '' ? intval($_GET['patient_id']) : null;

// Fetch Appointments
$appointments = [];
$query = "
    SELECT p.id, p.tanggal_kunjungan, p.waktu_kunjungan, p.nomor_antrean, p.status, 
           pat.nama_lengkap as patient_name, pat.hubungan as relationship, 
           f.nama as facilityName, pol.nama_poli as service, jp.waktu_selesai
    FROM pendaftaran p
    LEFT JOIN patients pat ON p.patient_id = pat.id
    LEFT JOIN faskes f ON p.faskes_id = f.id
    LEFT JOIN poli pol ON p.poli_id = pol.id
    LEFT JOIN jadwal_poli jp ON p.faskes_id = jp.faskes_id AND p.poli_id = jp.poli_id AND p.tanggal_kunjungan = jp.tanggal AND p.waktu_kunjungan = jp.waktu_mulai
    WHERE p.user_id = ?
";
if ($filter_patient_id) {
    $query .= " AND p.patient_id = ?";
}
$query .= " ORDER BY p.tanggal_kunjungan ASC, p.waktu_kunjungan ASC";

$stmt = $conn->prepare($query);
if ($filter_patient_id) {
    $stmt->bind_param("ii", $user_id, $filter_patient_id);
} else {
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    // Determine exact status
    $status = $row['status'];
    $today = date('Y-m-d');
    $now = date('H:i:s');
    
    if ($status === 'terjadwal') {
        $waktu_batas = !empty($row['waktu_selesai']) ? $row['waktu_selesai'] : $row['waktu_kunjungan'];
        if ($row['tanggal_kunjungan'] < $today || ($row['tanggal_kunjungan'] === $today && $waktu_batas < $now)) {
            $status = 'terlewat';
        } elseif ($row['tanggal_kunjungan'] === $today) {
            $status = 'hari ini';
        }
    }

    $row['display_status'] = $status;
    $appointments[] = $row;
}

// Calculate Summaries
$mendatangCount = 0;
$selesaiCount = 0;
$batalCount = 0;
$terlewatCount = 0;
foreach ($appointments as $app) {
    if ($app['display_status'] === 'selesai') {
        $selesaiCount++;
    } elseif ($app['display_status'] === 'batal') {
        $batalCount++;
    } elseif ($app['display_status'] === 'terlewat') {
        $terlewatCount++;
    } else {
        $mendatangCount++;
    }
}

// Separate Upcoming and History
$upcoming = [];
$history = [];
foreach ($appointments as $app) {
    if (in_array($app['display_status'], ['selesai', 'batal', 'terlewat'])) {
        $history[] = $app;
    } else {
        $upcoming[] = $app;
    }
}

// Calendar Logic
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$numberDays = date('t', $firstDayOfMonth);
$dateComponents = getdate($firstDayOfMonth);
$monthName = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"][$month - 1];
$dayOfWeek = $dateComponents['wday']; 
if ($dayOfWeek == 0) $dayOfWeek = 7; // Make Monday=1, Sunday=7
$dayOfWeek -= 1; // 0-indexed for loop

// Gather appointment dates for the calendar
$appointmentDates = [];
foreach ($upcoming as $app) {
    $appointmentDates[] = $app['tanggal_kunjungan'];
}

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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Saya - Sehati</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#413074',
                        primaryDark: '#2c1e54',
                        secondary: '#A57BD7',
                        accent: '#0fb7b8',
                        background: '#F6F9F9',
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
        body { font-family: 'Inter', sans-serif; background-color: #F6F9F9; }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 16px); }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
        }
        .day-header {
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            padding-bottom: 0.5rem;
        }
        .day-cell {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
        }
        .day-cell:hover:not(.empty) { background-color: #f1f5f9; }
        .day-cell.today { background-color: #e0e7ff; color: #4338ca; font-weight: 700; }
        .day-cell.has-appointment::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background-color: #0fb7b8;
        }
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-terjadwal { background-color: #e0e7ff; color: #4338ca; }
        .status-hari-ini { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .status-menunggu { background-color: #fef3c7; color: #b45309; }
        .status-selesai { background-color: #d1fae5; color: #065f46; }
        .status-batal { background-color: #fee2e2; color: #b91c1c; }
        
        /* Dropdown Menu */
        .action-dropdown { display: none; position: absolute; right: 0; top: 100%; background: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 0.5rem; border: 1px solid #e2e8f0; z-index: 10; min-width: 150px; overflow: hidden;}
        .action-dropdown.show { display: block; }
        .dropdown-item { display: block; padding: 0.75rem 1rem; text-align: left; font-size: 0.875rem; color: #475569; width: 100%; transition: background 0.2s; }
        .dropdown-item:hover { background-color: #f8fafc; color: #0f172a; }
        .dropdown-item.danger { color: #ef4444; }
        .dropdown-item.danger:hover { background-color: #fef2f2; }
    </style>
</head>
<body class="text-slate-800 pb-20 md:pb-0">

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
        <main class="flex-1 w-full max-w-6xl mx-auto p-4 md:p-8 overflow-x-hidden relative">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-1">Jadwal Saya</h2>
                    <p class="text-slate-500 text-sm">Kelola jadwal layanan kesehatan Anda dan keluarga dalam satu tempat.</p>
                </div>
                
                <!-- Patient Filter -->
                <form action="jadwal.php" method="GET" class="relative min-w-[200px]" id="filterForm">
                    <select name="patient_id" class="w-full appearance-none bg-white border border-slate-300 rounded-lg py-2.5 pl-4 pr-10 text-sm font-medium text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Pasien</option>
                        <?php foreach($patients as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo $filter_patient_id === intval($p['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nama_lengkap']); ?> 
                                <?php echo $p['hubungan'] === 'Saya sendiri' ? '(Saya)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div class="text-3xl font-bold text-primary mb-1"><?php echo $mendatangCount; ?></div>
                    <div class="text-xs md:text-sm font-medium text-slate-500">Jadwal Mendatang</div>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div class="text-3xl font-bold text-emerald-500 mb-1"><?php echo $selesaiCount; ?></div>
                    <div class="text-xs md:text-sm font-medium text-slate-500">Selesai</div>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div class="text-3xl font-bold text-red-500 mb-1"><?php echo $batalCount; ?></div>
                    <div class="text-xs md:text-sm font-medium text-slate-500">Dibatalkan</div>
                </div>
            </div>

            <!-- Main Layout: 2 Columns for Calendar and Upcoming -->
            <div class="flex flex-col lg:flex-row gap-8 mb-8">
                
                <!-- LEFT: Calendar -->
                <div class="w-full lg:w-1/3 shrink-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sticky top-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-slate-800 text-lg uppercase tracking-wide">
                                <?php echo $monthName . " " . $year; ?>
                            </h3>
                            <div class="flex gap-2">
                                <a href="jadwal.php?month=<?php echo $month-1; ?>&year=<?php echo $year; ?>&patient_id=<?php echo $filter_patient_id; ?>" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                                    <i class="fa-solid fa-chevron-left text-xs"></i>
                                </a>
                                <a href="jadwal.php?month=<?php echo $month+1; ?>&year=<?php echo $year; ?>&patient_id=<?php echo $filter_patient_id; ?>" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="calendar-grid mb-2">
                            <div class="day-header">Sen</div>
                            <div class="day-header">Sel</div>
                            <div class="day-header">Rab</div>
                            <div class="day-header">Kam</div>
                            <div class="day-header">Jum</div>
                            <div class="day-header">Sab</div>
                            <div class="day-header">Min</div>
                        </div>
                        <div class="calendar-grid">
                            <?php 
                                // empty slots before 1st day
                                for ($i = 0; $i < $dayOfWeek; $i++) {
                                    echo '<div class="day-cell empty"></div>';
                                }
                                // days of month
                                $todayDate = date('Y-m-d');
                                for ($i = 1; $i <= $numberDays; $i++) {
                                    $currentDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                    
                                    $classes = [];
                                    if ($currentDate == $todayDate) $classes[] = 'today';
                                    if (in_array($currentDate, $appointmentDates)) $classes[] = 'has-appointment';
                                    
                                    echo '<div class="day-cell ' . implode(' ', $classes) . '" data-date="'.$currentDate.'" onclick="filterByDate(\''.$currentDate.'\')">' . $i . '</div>';
                                }
                            ?>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500 flex items-center gap-2 justify-center">
                            <div class="w-2 h-2 rounded-full bg-accent"></div> Ada jadwal
                            <div class="w-4"></div>
                            <div class="w-4 h-4 rounded bg-indigo-100 flex items-center justify-center text-[10px] text-indigo-700 font-bold">H</div> Hari ini
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Appointments -->
                <div class="w-full lg:w-2/3 flex flex-col gap-8">
                    
                    <!-- UPCOMING SECTION -->
                    <section>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-slate-800">Jadwal Mendatang</h3>
                            <button id="clearDateFilter" class="hidden text-sm text-primary font-medium hover:underline" onclick="clearFilter()">Lihat Semua</button>
                        </div>
                        
                        <div class="space-y-4" id="upcomingList">
                            <?php if (empty($upcoming)): ?>
                                <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-400">
                                        <i class="fa-regular fa-calendar-xmark text-2xl"></i>
                                    </div>
                                    <h4 class="text-slate-800 font-semibold mb-2">Belum ada jadwal</h4>
                                    <p class="text-slate-500 text-sm mb-6 max-w-sm">Jadwal layanan kesehatan Anda akan muncul di sini setelah melakukan pendaftaran.</p>
                                    <a href="find.php" class="px-6 py-2.5 bg-primary text-white rounded-lg font-medium hover:bg-primaryDark transition shadow-sm">Cari Layanan</a>
                                </div>
                            <?php else: ?>
                                <?php foreach($upcoming as $app): 
                                    // Formatting dates
                                    setlocale(LC_TIME, 'id_ID');
                                    $dateObj = new DateTime($app['tanggal_kunjungan'] . ' ' . $app['waktu_kunjungan']);
                                    $formattedDate = strtoupper(strftime('%d %B %Y', $dateObj->getTimestamp())); // Since windows might not support strftime properly, let's use manual array if needed, but date() is safer.
                                    
                                    $monthsId = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                                    $fDate = $dateObj->format('d') . ' ' . strtoupper($monthsId[$dateObj->format('n') - 1]) . ' ' . $dateObj->format('Y');
                                    
                                    $fTime = $dateObj->format('H.i') . ' WIB';
                                    
                                    $isToday = $app['display_status'] === 'hari ini';
                                    
                                    // Reminder label
                                    $tomorrow = new DateTime('tomorrow');
                                    $isTomorrow = $dateObj->format('Y-m-d') === $tomorrow->format('Y-m-d');
                                    $reminderLabel = "";
                                    if ($isToday) $reminderLabel = "Hari Ini";
                                    else if ($isTomorrow) $reminderLabel = "Besok";
                                ?>
                                
                                <div class="bg-white rounded-2xl border <?php echo $isToday ? 'border-primary shadow-md' : 'border-slate-200 shadow-sm'; ?> p-5 relative appointment-card" data-date="<?php echo $app['tanggal_kunjungan']; ?>">
                                    <?php if($reminderLabel): ?>
                                    <div class="absolute -top-3 left-6 bg-accent text-slate-900 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm border border-cyan-300">
                                        <?php echo $reminderLabel; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex justify-between items-start mb-4 pt-1">
                                        <div class="text-sm font-bold text-slate-500 tracking-wider">
                                            <?php echo $fDate; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-hospital text-xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-bold text-slate-800 text-lg"><?php echo htmlspecialchars($app['facilityName']); ?></h4>
                                            <p class="text-primary font-medium mb-3"><?php echo htmlspecialchars($app['service']); ?></p>
                                            
                                            <div class="flex flex-wrap gap-y-2 gap-x-6 text-sm text-slate-600 mb-4 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                                <div class="flex items-center gap-2">
                                                    <i class="fa-solid fa-user text-slate-400"></i> Untuk: <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($app['patient_name']); ?></span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i class="fa-regular fa-clock text-slate-400"></i> <span class="font-semibold text-slate-700"><?php echo $fTime; ?></span>
                                                </div>
                                                <?php if($app['nomor_antrean']): ?>
                                                <div class="flex items-center gap-2">
                                                    <i class="fa-solid fa-hashtag text-slate-400"></i> No. Antrean: <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($app['nomor_antrean']); ?></span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="flex flex-wrap items-center justify-between gap-4 mt-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs text-slate-500 uppercase font-semibold">Status:</span>
                                                    <?php 
                                                        $badgeClass = 'status-terjadwal';
                                                        $statusLabel = 'Terjadwal';
                                                        if ($app['display_status'] === 'hari ini') {
                                                            $badgeClass = 'status-hari-ini';
                                                            $statusLabel = 'Hari Ini';
                                                        } elseif ($app['display_status'] === 'menunggu') {
                                                            $badgeClass = 'status-menunggu';
                                                            $statusLabel = 'Sedang Antre';
                                                        }
                                                    ?>
                                                    <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></span>
                                                </div>
                                                
                                                <div class="flex gap-2">
                                                    <?php if ($app['display_status'] === 'menunggu'): ?>
                                                    <button class="px-4 py-2 bg-amber-100 text-amber-700 text-sm font-semibold rounded-lg hover:bg-amber-200 transition">Lihat Antrean</button>
                                                    <?php endif; ?>
                                                    
                                                    <button onclick="cancelAppointment(<?php echo $app['id']; ?>, '<?php echo addslashes($app['service']); ?>', '<?php echo addslashes($app['facilityName']); ?>', '<?php echo $fDate; ?>', '<?php echo $fTime; ?>')" class="px-4 py-2 bg-white border border-red-200 text-red-500 text-sm font-semibold rounded-lg hover:bg-red-50 transition flex items-center gap-2">
                                                        <i class="fa-solid fa-xmark"></i> Batalkan
                                                    </button>
                                                    
                                                    <a href="ticket.php?id=<?php echo $app['id']; ?>" class="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primaryDark transition flex items-center gap-2">
                                                        <i class="fa-solid fa-qrcode"></i> QR Tiket
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <div id="noDataMessage" class="hidden bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center">
                                <p class="text-slate-500">Belum ada jadwal pada tanggal ini.</p>
                                <button onclick="clearFilter()" class="mt-4 px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200 font-medium">Lihat Semua Tanggal</button>
                            </div>
                        </div>
                    </section>

                    <!-- HISTORY SECTION MOVED OUT -->


                </div>
            </div>
            
            <!-- HISTORY SECTION (Full Width) -->
            <section class="mt-8">
                <h3 class="text-xl font-bold text-slate-800 mb-4">Riwayat Jadwal</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php if (empty($history)): ?>
                        <div class="col-span-full">
                            <p class="text-sm text-slate-500 italic">Belum ada riwayat jadwal.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($history as $app): 
                            $dateObj = new DateTime($app['tanggal_kunjungan']);
                            $monthsId = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                            $fDate = $dateObj->format('d') . ' ' . $monthsId[$dateObj->format('n') - 1] . ' ' . $dateObj->format('Y');
                            
                            $dispStatus = strtolower($app['display_status']);
                            $isSelesai = $dispStatus === 'selesai';
                            $isTerlewat = $dispStatus === 'terlewat';
                            
                            $iconColorClass = 'bg-red-50 text-red-500';
                            $iconClass = 'fa-xmark';
                            if ($isSelesai) {
                                $iconColorClass = 'bg-emerald-50 text-emerald-500';
                                $iconClass = 'fa-check';
                            } elseif ($isTerlewat) {
                                $iconColorClass = 'bg-slate-100 text-slate-500';
                                $iconClass = 'fa-clock-rotate-left';
                            }
                        ?>
                        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-between opacity-80 hover:opacity-100 transition-opacity shadow-sm">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-10 h-10 rounded-full <?php echo $iconColorClass; ?> flex items-center justify-center shrink-0">
                                    <i class="fa-solid <?php echo $iconClass; ?>"></i>
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <p class="font-bold text-slate-800 text-sm truncate"><?php echo htmlspecialchars($app['service']); ?></p>
                                    <p class="text-slate-500 text-xs truncate mb-1"><?php echo htmlspecialchars($app['facilityName']); ?></p>
                                    <p class="text-xs text-slate-400 font-medium">Untuk: <?php echo htmlspecialchars($app['patient_name']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-3 border-t border-slate-100 mt-auto">
                                <p class="text-[11px] font-medium text-slate-400"><?php echo $fDate; ?></p>
                                <?php 
                                    $badgeClass = 'status-batal';
                                    $badgeText = 'Dibatalkan';
                                    if ($isSelesai) {
                                        $badgeClass = 'status-selesai';
                                        $badgeText = 'Selesai';
                                    } elseif ($isTerlewat) {
                                        $badgeClass = 'bg-slate-200 text-slate-600';
                                        $badgeText = 'Terlewat';
                                    }
                                ?>
                                <span class="status-badge <?php echo $badgeClass; ?>">
                                    <?php echo $badgeText; ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            
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
    
    <!-- Cancel Confirmation Modal -->
    <div id="cancelModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex-col items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center transform scale-95 opacity-0 transition-all duration-200" id="cancelModalInner">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Batalkan jadwal ini?</h3>
            <p class="text-slate-500 text-sm mb-6" id="cancelDescText">Jadwal Anda akan dibatalkan.</p>
            
            <div class="flex gap-3">
                <button onclick="closeCancelModal()" class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-700 font-semibold rounded-lg hover:bg-slate-200 transition">Kembali</button>
                <button id="confirmCancelBtn" class="flex-1 px-4 py-2.5 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition">Ya, Batalkan</button>
            </div>
        </div>
    </div>

    <script>
        // Dropdown Logic
        function toggleDropdown(id) {
            // close all other dropdowns
            document.querySelectorAll('.action-dropdown').forEach(el => {
                if (el.id !== id) el.classList.remove('show');
            });
            document.getElementById(id).classList.toggle('show');
        }
        
        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.fa-ellipsis-vertical') && !event.target.parentElement.matches('button')) {
                var dropdowns = document.getElementsByClassName("action-dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
        
        // Calendar Filtering
        function filterByDate(date) {
            const cards = document.querySelectorAll('.appointment-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                if (card.getAttribute('data-date') === date) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            if (visibleCount === 0 && cards.length > 0) {
                document.getElementById('noDataMessage').classList.remove('hidden');
                document.getElementById('noDataMessage').classList.add('block');
            } else {
                document.getElementById('noDataMessage').classList.add('hidden');
                document.getElementById('noDataMessage').classList.remove('block');
            }
            
            document.getElementById('clearDateFilter').classList.remove('hidden');
            
            // Highlight calendar day
            document.querySelectorAll('.day-cell').forEach(el => el.style.border = 'none');
            const target = document.querySelector(`.day-cell[data-date="${date}"]`);
            if(target) target.style.border = '2px solid #413074';
        }
        
        function clearFilter() {
            document.querySelectorAll('.appointment-card').forEach(card => {
                card.style.display = 'block';
            });
            document.getElementById('noDataMessage').classList.add('hidden');
            document.getElementById('noDataMessage').classList.remove('block');
            document.getElementById('clearDateFilter').classList.add('hidden');
            
            document.querySelectorAll('.day-cell').forEach(el => el.style.border = 'none');
        }
        
        // Cancel Appointment Logic
        let currentCancelId = null;
        
        function cancelAppointment(id, poli, faskes, date, time) {
            currentCancelId = id;
            document.getElementById('cancelDescText').innerHTML = `Jadwal Anda di <strong>${poli}</strong> &mdash; <strong>${faskes}</strong> pada <strong>${date}</strong> pukul <strong>${time}</strong> akan dibatalkan.`;
            
            const modal = document.getElementById('cancelModal');
            const inner = document.getElementById('cancelModalInner');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                inner.classList.remove('scale-95', 'opacity-0');
                inner.classList.add('scale-100', 'opacity-100');
            }, 10);
            
            // Close dropdown
            document.getElementById('dropdown-' + id).classList.remove('show');
        }
        
        function closeCancelModal() {
            const modal = document.getElementById('cancelModal');
            const inner = document.getElementById('cancelModalInner');
            inner.classList.remove('scale-100', 'opacity-100');
            inner.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                currentCancelId = null;
            }, 200);
        }
        
        document.getElementById('confirmCancelBtn').addEventListener('click', function() {
            if (!currentCancelId) return;
            
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
            this.disabled = true;
            
            fetch('/sehati/ajax/cancel_booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + currentCancelId
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Jadwal berhasil dibatalkan.');
                    window.location.reload();
                } else {
                    alert(data.message || 'Terjadi kesalahan.');
                    closeCancelModal();
                    this.innerHTML = 'Ya, Batalkan';
                    this.disabled = false;
                }
            })
            .catch(err => {
                alert('Gagal memproses pembatalan.');
                closeCancelModal();
                this.innerHTML = 'Ya, Batalkan';
                this.disabled = false;
            });
        });
    </script>
</body>
</html>
