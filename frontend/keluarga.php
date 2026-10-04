<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

$user_id = $_SESSION["user_id"];

// Auto-generate "Saya Sendiri" if patients table is empty for this user
$check_patients = $conn->prepare("SELECT id FROM patients WHERE user_id = ?");
$check_patients->bind_param("i", $user_id);
$check_patients->execute();
$res = $check_patients->get_result();

if ($res->num_rows === 0) {
    // Get user data
    $get_user = $conn->prepare("SELECT nik, nama_lengkap, no_bpjs FROM users WHERE id = ?");
    $get_user->bind_param("i", $user_id);
    $get_user->execute();
    $user_data = $get_user->get_result()->fetch_assoc();
    
    if ($user_data) {
        $hubungan = 'Saya sendiri';
        $metode = !empty($user_data['no_bpjs']) ? 'JKN' : 'Umum';
        
        $insert_self = $conn->prepare("INSERT INTO patients (user_id, nama_lengkap, nik, hubungan, metode_pembiayaan, no_jkn) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_self->bind_param("isssss", $user_id, $user_data['nama_lengkap'], $user_data['nik'], $hubungan, $metode, $user_data['no_bpjs']);
        $insert_self->execute();
    }
}

// Handle Add/Edit Patient via AJAX POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    
    if ($action === 'save_patient') {
        $patient_id = isset($_POST['patient_id']) && $_POST['patient_id'] !== '' ? intval($_POST['patient_id']) : null;
        $nama = $_POST['nama_lengkap'];
        $nik = $_POST['nik'];
        $tgl_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
        $hubungan = isset($_POST['hubungan']) ? $_POST['hubungan'] : null;
        $metode = $_POST['metode_pembiayaan'];
        $no_jkn = $metode === 'JKN' ? $_POST['no_jkn'] : null;
        $asuransi = $metode === 'Asuransi Lain' ? $_POST['nama_asuransi'] : null;
        
        if ($patient_id) {
            // Check if patient is currently 'Saya sendiri'
            $check_stmt = $conn->prepare("SELECT hubungan FROM patients WHERE id=? AND user_id=?");
            $check_stmt->bind_param("ii", $patient_id, $user_id);
            $check_stmt->execute();
            $curr = $check_stmt->get_result()->fetch_assoc();
            
            if ($curr && $curr['hubungan'] === 'Saya sendiri') {
                $hubungan = 'Saya sendiri'; // Force it to remain Saya sendiri
            } else if ($hubungan === 'Saya sendiri') {
                echo json_encode(['status' => 'error', 'message' => 'Status "Saya sendiri" hanya untuk pemilik akun']);
                exit;
            }

            // Update
            $stmt = $conn->prepare("UPDATE patients SET nama_lengkap=?, nik=?, tanggal_lahir=?, hubungan=?, metode_pembiayaan=?, no_jkn=?, nama_asuransi=? WHERE id=? AND user_id=?");
            $stmt->bind_param("sssssssii", $nama, $nik, $tgl_lahir, $hubungan, $metode, $no_jkn, $asuransi, $patient_id, $user_id);
        } else {
            if ($hubungan === 'Saya sendiri') {
                echo json_encode(['status' => 'error', 'message' => 'Status "Saya sendiri" hanya untuk pemilik akun']);
                exit;
            }

            // Insert
            $stmt = $conn->prepare("INSERT INTO patients (user_id, nama_lengkap, nik, tanggal_lahir, hubungan, metode_pembiayaan, no_jkn, nama_asuransi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $user_id, $nama, $nik, $tgl_lahir, $hubungan, $metode, $no_jkn, $asuransi);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data']);
        }
        exit;
    }
    
    if ($action === 'delete_patient') {
        $patient_id = intval($_POST['patient_id']);

        $check_stmt = $conn->prepare("SELECT hubungan FROM patients WHERE id=? AND user_id=?");
        $check_stmt->bind_param("ii", $patient_id, $user_id);
        $check_stmt->execute();
        $curr = $check_stmt->get_result()->fetch_assoc();
        
        if ($curr && $curr['hubungan'] === 'Saya sendiri') {
            echo json_encode(['status' => 'error', 'message' => 'Profil pemilik akun (Saya sendiri) tidak dapat dihapus']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM patients WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $patient_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data']);
        }
        exit;
    }
}

// Fetch all patients
$patients = [];
$stmt = $conn->prepare("SELECT * FROM patients WHERE user_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()) {
    $patients[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluarga - Sehati</title>
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
        .glass-card { background: white; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="text-slate-800 pb-20 md:pb-0 min-h-screen flex flex-col md:flex-row bg-[#F6F9F9]">

    <!-- Mobile Top Bar -->
    <header class="md:hidden bg-primary text-white p-4 sticky top-0 z-50 shadow-md flex justify-between items-center w-full">
        <h1 class="text-xl font-bold flex items-center gap-2">
            <img src="/sehati/frontend/logo.png" alt="SEHATI Logo" class="h-8 brightness-0 invert"> SEHATI
        </h1>
        <button onclick="openModal()" class="bg-white/20 text-white px-3 py-1.5 rounded-lg text-sm font-semibold hover:bg-white/30 transition-colors">
            <i class="fa-solid fa-plus"></i> Tambah
        </button>
    </header>

    <!-- Desktop Sidebar & Layout Wrapper -->
    <div class="flex min-h-screen w-full">
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
        <main class="flex-1 w-full max-w-6xl mx-auto p-4 md:p-6 lg:p-8 flex flex-col min-h-[calc(100vh-4rem)] md:min-h-screen relative">
            
            <!-- Breadcrumb -->
            <div class="text-sm text-slate-500 mb-6 hidden md:block">
                <a href="index.php" class="hover:text-primary transition-colors">Beranda</a> 
                <span class="mx-2">/</span> 
                <span class="text-slate-700 font-medium">Orang yang Saya Kelola</span>
            </div>

            <!-- Page Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-primary">Orang yang Saya Kelola</h2>
                    <p class="text-slate-500 mt-2 text-sm max-w-2xl">Kelola profil diri, keluarga, atau orang lain yang Anda bantu untuk mengakses layanan kesehatan.</p>
                </div>
                <button onclick="openModal()" class="hidden md:flex bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-primaryDark transition-colors items-center gap-2 shrink-0">
                    <i class="fa-solid fa-plus"></i> Tambah Orang
                </button>
            </div>

            <!-- Profiles Grid -->
            <h3 class="text-lg font-bold text-slate-700 mb-4 border-b border-slate-200 pb-2">Profil yang Dikelola</h3>
            
            <?php if (count($patients) === 0): ?>
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-white rounded-2xl border border-slate-200 mb-6">
                    <div class="w-20 h-20 bg-primary/10 text-primary rounded-full flex items-center justify-center text-4xl mb-4">
                        <i class="fa-solid fa-users-slash"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Belum ada profil lain</h3>
                    <p class="text-slate-500 max-w-sm mb-6">Tambahkan keluarga atau orang yang Anda bantu agar pendaftaran layanan kesehatan menjadi lebih mudah.</p>
                    <button onclick="openModal()" class="bg-primary text-white px-6 py-3 rounded-xl text-sm font-bold shadow-md hover:bg-primaryDark transition-colors">
                        <i class="fa-solid fa-plus mr-2"></i> Tambah Orang
                    </button>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php foreach($patients as $p): ?>
                        <?php 
                            $icon = "fa-user";
                            if ($p['hubungan'] === 'Saya sendiri') $icon = "fa-user-tie";
                            elseif ($p['hubungan'] === 'Orang tua') $icon = "fa-person-cane";
                            elseif ($p['hubungan'] === 'Anak') $icon = "fa-child";
                            elseif ($p['hubungan'] === 'Pasangan') $icon = "fa-user-group";
                            
                            // Mask NIK safely
                            $maskedNik = strlen($p['nik']) >= 8 
                                ? substr($p['nik'], 0, 4) . ' •••• •••• ' . substr($p['nik'], -4) 
                                : $p['nik'];
                        ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow flex flex-col h-full">
                            <!-- Header: Icon & Badge -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-[#A57BD7]/10 text-[#A57BD7] flex items-center justify-center text-xl shrink-0">
                                    <i class="fa-solid <?php echo $icon; ?>"></i>
                                </div>
                                <span class="px-2.5 py-1 <?php echo ($p['hubungan'] === 'Saya sendiri') ? 'bg-[#0fb7b8]/10 text-[#00b8c9]' : 'bg-[#A57BD7]/10 text-[#A57BD7]'; ?> text-[10px] font-bold rounded-md uppercase tracking-wide">
                                    <?php echo htmlspecialchars($p['hubungan']); ?>
                                </span>
                            </div>
                            
                            <!-- Identity info -->
                            <div class="flex-1">
                                <h3 class="font-bold text-lg text-slate-800 line-clamp-1" title="<?php echo htmlspecialchars($p['nama_lengkap']); ?>"><?php echo htmlspecialchars($p['nama_lengkap']); ?></h3>
                                <p class="text-sm text-slate-500 mt-1 font-mono text-[13px]"><?php echo $maskedNik; ?></p>
                                
                                <div class="mt-4 mb-2">
                                    <?php if($p['metode_pembiayaan'] === 'JKN'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-600 text-xs font-bold rounded-lg border border-emerald-100"><i class="fa-solid fa-shield-heart mr-1.5"></i> JKN Aktif</span>
                                    <?php elseif($p['metode_pembiayaan'] === 'Umum'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-lg border border-blue-100"><i class="fa-solid fa-wallet mr-1.5"></i> Umum</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 bg-[#413074]/5 text-[#413074] text-xs font-bold rounded-lg border border-[#413074]/10"><i class="fa-solid fa-umbrella mr-1.5"></i> <?php echo htmlspecialchars($p['metode_pembiayaan']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between gap-3 shrink-0">
                                <button onclick='editPatient(<?php echo json_encode($p); ?>)' class="flex-1 py-2 text-primary text-sm font-semibold rounded-lg hover:bg-primary/5 transition-colors border border-transparent flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <?php if ($p['hubungan'] !== 'Saya sendiri'): ?>
                                <button onclick="deletePatient(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars($p['nama_lengkap'], ENT_QUOTES); ?>')" class="w-9 h-9 flex items-center justify-center text-red-500 bg-white border border-red-200 hover:bg-red-50 hover:border-red-300 transition-colors rounded-lg shrink-0" title="Hapus Profil">
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </button>
                                <?php else: ?>
                                <div class="w-9 h-9"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Privacy Note -->
            <div class="mt-8">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-col md:flex-row gap-4 items-start md:items-center text-sm text-slate-600 relative overflow-hidden shadow-sm">
                    <div class="absolute -right-4 -bottom-4 text-[#A57BD7]/10 text-6xl">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-[#F6F9F9] border border-slate-200 flex items-center justify-center text-[#00b8c9] shrink-0 z-10">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div class="z-10 relative">
                        <h4 class="font-bold text-slate-800 mb-1">Privasi & Akses</h4>
                        <p>Menambahkan seseorang ke daftar Anda tidak otomatis memberikan akses penuh ke seluruh rekam medis atau informasi kesehatan sensitifnya. Anda hanya dapat membantu mengelola layanan sesuai dengan izin yang berlaku.</p>
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


    <!-- Patient Modal -->
    <div id="patientModal" class="fixed inset-0 bg-slate-900/50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity opacity-0">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform flex flex-col max-h-[90vh]" id="patientModalContent">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <h3 class="font-bold text-lg text-slate-800" id="modalFormTitle">Tambah Orang</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="p-5 overflow-y-auto flex-1 custom-scrollbar">
                <form id="patientForm" onsubmit="savePatient(event)">
                    <input type="hidden" id="form_patient_id" name="patient_id">
                    <input type="hidden" name="action" value="save_patient">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap *</label>
                            <input type="text" id="form_nama" name="nama_lengkap" required class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg p-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">NIK (16 Digit) *</label>
                            <input type="text" id="form_nik" name="nik" required pattern="[0-9]{16}" class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg p-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Lahir</label>
                            <input type="date" id="form_tgl" name="tanggal_lahir" class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg p-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Hubungan dengan saya *</label>
                            <select id="form_hubungan" name="hubungan" required class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg p-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                                <option value="Saya sendiri">Saya sendiri</option>
                                <option value="Orang tua">Orang tua</option>
                                <option value="Anak">Anak</option>
                                <option value="Saudara">Saudara</option>
                                <option value="Pasangan">Pasangan</option>
                                <option value="Wali">Wali</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="pt-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Metode Pembiayaan *</label>
                            <p class="text-xs text-slate-500 mb-3">Metode pembiayaan dapat berbeda untuk setiap anggota yang Anda kelola.</p>
                            
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="metode_pembiayaan" value="JKN" class="peer hidden" onchange="toggleCoverageFields()" required>
                                    <div class="p-3 border border-slate-200 rounded-xl text-center peer-checked:bg-primary/5 peer-checked:border-primary peer-checked:text-primary hover:bg-slate-50 transition-colors text-sm font-semibold">JKN / BPJS</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="metode_pembiayaan" value="Umum" class="peer hidden" onchange="toggleCoverageFields()">
                                    <div class="p-3 border border-slate-200 rounded-xl text-center peer-checked:bg-primary/5 peer-checked:border-primary peer-checked:text-primary hover:bg-slate-50 transition-colors text-sm font-semibold">Umum / Pribadi</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="metode_pembiayaan" value="Asuransi Lain" class="peer hidden" onchange="toggleCoverageFields()">
                                    <div class="p-3 border border-slate-200 rounded-xl text-center peer-checked:bg-primary/5 peer-checked:border-primary peer-checked:text-primary hover:bg-slate-50 transition-colors text-sm font-semibold">Asuransi Lain</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="metode_pembiayaan" value="Belum tahu" class="peer hidden" onchange="toggleCoverageFields()">
                                    <div class="p-3 border border-slate-200 rounded-xl text-center peer-checked:bg-primary/5 peer-checked:border-primary peer-checked:text-primary hover:bg-slate-50 transition-colors text-sm font-semibold">Belum Tahu</div>
                                </label>
                            </div>
                            
                            <div class="mt-3 flex items-center justify-between bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <span class="text-xs text-slate-500 font-medium">
                                    <i class="fa-solid fa-circle-info mr-1"></i> Bingung memilih metode pembiayaan?
                                </span>
                                <a href="tarif.php#panduan-pembayaran" target="_blank" class="text-xs font-bold text-primary hover:text-primaryDark flex items-center">
                                    Lihat Panduan <i class="fa-solid fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div id="field_jkn" class="hidden pt-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Peserta JKN / BPJS</label>
                            <input type="text" id="form_no_jkn" name="no_jkn" class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg p-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Opsional">
                        </div>
                        
                        <div id="field_asuransi" class="hidden pt-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Asuransi</label>
                            <input type="text" id="form_asuransi" name="nama_asuransi" class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg p-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Opsional">
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="p-5 border-t border-slate-100 bg-white shrink-0 flex gap-3">
                <button type="button" onclick="closeModal()" class="w-1/3 py-2.5 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" form="patientForm" class="w-2/3 py-2.5 bg-primary text-white font-bold rounded-xl shadow-md hover:bg-primaryDark transition-colors">Simpan Profil</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('patientModal');
        const modalContent = document.getElementById('patientModalContent');
        const form = document.getElementById('patientForm');

        function openModal() {
            form.reset();
            document.getElementById('form_patient_id').value = '';
            document.getElementById('modalFormTitle').textContent = 'Tambah Orang';
            
            let optionSaya = document.querySelector('#form_hubungan option[value="Saya sendiri"]');
            if (optionSaya) {
                optionSaya.disabled = true;
                optionSaya.hidden = true;
            }
            document.getElementById('form_hubungan').disabled = false;
            document.getElementById('form_hubungan').value = 'Orang tua';

            toggleCoverageFields();
            
            modal.classList.remove('hidden');
            void modal.offsetWidth;
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }

        function closeModal() {
            modal.classList.add('opacity-0');
            modalContent.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function toggleCoverageFields() {
            const method = document.querySelector('input[name="metode_pembiayaan"]:checked')?.value;
            document.getElementById('field_jkn').classList.toggle('hidden', method !== 'JKN');
            document.getElementById('field_asuransi').classList.toggle('hidden', method !== 'Asuransi Lain');
        }

        function editPatient(data) {
            document.getElementById('form_patient_id').value = data.id;
            document.getElementById('form_nama').value = data.nama_lengkap;
            document.getElementById('form_nik').value = data.nik;
            document.getElementById('form_tgl').value = data.tanggal_lahir || '';
            
            let optionSaya = document.querySelector('#form_hubungan option[value="Saya sendiri"]');
            if (data.hubungan === 'Saya sendiri') {
                if (optionSaya) {
                    optionSaya.disabled = false;
                    optionSaya.hidden = false;
                }
                document.getElementById('form_hubungan').value = 'Saya sendiri';
                document.getElementById('form_hubungan').disabled = true;
            } else {
                if (optionSaya) {
                    optionSaya.disabled = true;
                    optionSaya.hidden = true;
                }
                document.getElementById('form_hubungan').disabled = false;
                document.getElementById('form_hubungan').value = data.hubungan;
            }
            
            const radios = document.getElementsByName('metode_pembiayaan');
            for(let r of radios) {
                if(r.value === data.metode_pembiayaan) r.checked = true;
            }
            
            document.getElementById('form_no_jkn').value = data.no_jkn || '';
            document.getElementById('form_asuransi').value = data.nama_asuransi || '';
            
            document.getElementById('modalFormTitle').textContent = 'Edit Profil Orang';
            toggleCoverageFields();
            
            modal.classList.remove('hidden');
            void modal.offsetWidth;
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }

        function deletePatient(id, name) {
            if(confirm(`Apakah Anda yakin ingin menghapus profil "${name}" dari daftar?`)) {
                const fd = new FormData();
                fd.append('action', 'delete_patient');
                fd.append('patient_id', id);
                
                fetch('keluarga.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if(res.status === 'success') window.location.reload();
                    else alert('Gagal menghapus profil.');
                });
            }
        }

        function savePatient(e) {
            e.preventDefault();
            const fd = new FormData(form);
            fetch('keluarga.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if(res.status === 'success') {
                    window.location.reload();
                } else {
                    alert(res.message || 'Terjadi kesalahan saat menyimpan.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
            });
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
    </style>
</body>
</html>
