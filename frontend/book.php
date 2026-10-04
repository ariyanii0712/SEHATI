<?php
if(session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
require_once '../config/helpers.php';
$user_id = $_SESSION["user_id"];

// Fetch Faskes & Poli data
$faskes_id = isset($_GET['faskes_id']) ? intval($_GET['faskes_id']) : 0;
$poli_id = isset($_GET['poli_id']) ? intval($_GET['poli_id']) : 0;
$poli_nama_param = isset($_GET['poli_nama']) ? trim($_GET['poli_nama']) : "";

$faskes_nama = "Fasilitas Tidak Diketahui";
$poli_nama = $poli_nama_param !== "" ? htmlspecialchars($poli_nama_param) : "Poli Umum";
$jam_pelayanan_text = "Jam pelayanan tidak tersedia";
$layanan_24_jam = "";

if ($faskes_id > 0) {
    $stmt = $conn->prepare("SELECT nama, jam_pelayanan, kategori FROM faskes WHERE id = ?");
    $stmt->bind_param("i", $faskes_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $faskes_nama = $row['nama'];
        $raw_jam = $row['jam_pelayanan'];
        $kategori = strtolower($row['kategori'] ?? '');
        
        // Parsing " | Layanan 24 Jam: "
        $parts = explode(" | Layanan 24 Jam: ", $raw_jam);
        $jam_pelayanan_text = formatJamPelayanan($parts[0]);
        if(isset($parts[1])) {
            $layanan_24_jam = $parts[1];
        }
    }
}

$back_url = "find.php"; // default
if ($faskes_id > 0 && isset($kategori)) {
    if (strpos($kategori, 'rumah sakit') !== false || strpos($kategori, 'rsud') !== false || strpos(strtolower($faskes_nama), 'rsud') !== false) {
        $back_url = "rs_detail.php?id=" . $faskes_id;
    } else {
        $back_url = "puskesmas_detail.php?id=" . $faskes_id;
    }
}

if ($poli_id > 0) {
    $stmt = $conn->prepare("SELECT nama_poli FROM poli WHERE id = ?");
    $stmt->bind_param("i", $poli_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $poli_nama = $row['nama_poli'];
    }
} else if ($poli_nama_param !== "") {
    // Resolve poli_id from nama_poli
    if ($faskes_id > 0) {
        // Try exact match within this faskes first
        $stmt = $conn->prepare("SELECT p.id FROM poli p JOIN faskes_layanan fl ON p.id = fl.poli_id WHERE fl.faskes_id = ? AND p.nama_poli = ? LIMIT 1");
        $stmt->bind_param("is", $faskes_id, $poli_nama_param);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $poli_id = $row['id'];
        } else {
            // Fallback to LIKE within this faskes
            $stmt = $conn->prepare("SELECT p.id FROM poli p JOIN faskes_layanan fl ON p.id = fl.poli_id WHERE fl.faskes_id = ? AND p.nama_poli LIKE ? LIMIT 1");
            $search_poli = "%" . $poli_nama_param . "%";
            $stmt->bind_param("is", $faskes_id, $search_poli);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $poli_id = $row['id'];
            }
        }
    }
    
    // If still not found, fallback to global search
    if ($poli_id == 0) {
        $stmt = $conn->prepare("SELECT id FROM poli WHERE nama_poli = ? LIMIT 1");
        $stmt->bind_param("s", $poli_nama_param);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $poli_id = $row['id'];
        } else {
            $stmt = $conn->prepare("SELECT id FROM poli WHERE nama_poli LIKE ? LIMIT 1");
            $search_poli = "%" . $poli_nama_param . "%";
            $stmt->bind_param("s", $search_poli);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $poli_id = $row['id'];
            }
        }
    }
}

// ==========================================
// AUTO LOAD 30-DAY AVAILABILITY
// ==========================================
$availability_data = [];
$start_date = new DateTime(); // Today
$end_date = clone $start_date;
$end_date->modify('+30 days');

$start_str = $start_date->format('Y-m-d');
$end_str = $end_date->format('Y-m-d');

// 1. Fetch all schedule slots for the next 30 days
$slots = [];
$stmt = $conn->prepare("SELECT * FROM jadwal_poli WHERE faskes_id = ? AND poli_id = ? AND tanggal BETWEEN ? AND ? ORDER BY tanggal ASC, waktu_mulai ASC");
$stmt->bind_param("iiss", $faskes_id, $poli_id, $start_str, $end_str);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $slots[] = $row;
}

// 2. Fetch all registrations for these dates to calculate remaining quota
$pendaftar = [];
$stmt2 = $conn->prepare("SELECT tanggal_kunjungan, waktu_kunjungan, COUNT(*) as cnt FROM pendaftaran WHERE faskes_id = ? AND poli_id = ? AND tanggal_kunjungan BETWEEN ? AND ? AND status != 'batal' GROUP BY tanggal_kunjungan, waktu_kunjungan");
$stmt2->bind_param("iiss", $faskes_id, $poli_id, $start_str, $end_str);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($row = $res2->fetch_assoc()) {
    $pendaftar[$row['tanggal_kunjungan']][$row['waktu_kunjungan']] = $row['cnt'];
}

// 3. Build the availability array (30 days)
$current_date = clone $start_date;
$open_days_list = isset($raw_jam) ? getOpenDays($raw_jam) : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

for ($i = 0; $i <= 30; $i++) {
    $d_str = $current_date->format('Y-m-d');
    $day_name = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')[$current_date->format('w')];
    $date_formatted = $current_date->format('d M');
    
    $day_slots = [];
    $total_sisa = 0;
    $has_schedule = false;
    
    $now = new DateTime();
    $today_str = $now->format('Y-m-d');
    
    // Check if faskes is actually open on this day
    if (!in_array($day_name, $open_days_list)) {
        $status = 'NO_SCHEDULE';
        $status_text = 'Tutup';
    } else {
        $slots_existed = false;
        
        foreach ($slots as $slot) {
            if ($slot['tanggal'] === $d_str) {
                $slots_existed = true;
                
                // If it's today, skip slots that have already started
                if ($d_str === $today_str && $slot['waktu_mulai'] <= $now->format('H:i:s')) {
                    continue;
                }
                
                $has_schedule = true;
                $terdaftar = isset($pendaftar[$d_str][$slot['waktu_mulai']]) ? $pendaftar[$d_str][$slot['waktu_mulai']] : 0;
                $sisa = max(0, $slot['kuota'] - $terdaftar);
                
                $total_sisa += $sisa;
                $day_slots[] = [
                    'waktu_mulai' => date('H:i', strtotime($slot['waktu_mulai'])),
                    'waktu_selesai' => date('H:i', strtotime($slot['waktu_selesai'])),
                    'kuota' => $slot['kuota'],
                    'terdaftar' => $terdaftar,
                    'sisa' => $sisa
                ];
            }
        }
        
        $status = 'NO_SCHEDULE';
        if ($slots_existed && !$has_schedule) {
            $status_text = 'Waktu terlewat';
        } else {
            $status_text = 'Tidak ada jadwal';
        }
        
        if ($has_schedule) {
            if ($total_sisa >= 10) {
                $status = 'AVAILABLE';
                $status_text = $total_sisa . ' kuota tersedia';
            } else if ($total_sisa > 0) {
                $status = 'LIMITED';
                $status_text = $total_sisa . ' kuota tersisa';
            } else {
                $status = 'FULL';
                $status_text = 'Antrean penuh';
            }
        }
    }
    
    $availability_data[] = [
        'date' => $d_str,
        'day_name' => $day_name,
        'date_formatted' => $date_formatted,
        'status' => $status,
        'status_text' => $status_text,
        'total_sisa' => $total_sisa,
        'slots' => $day_slots
    ];
    
    $current_date->modify('+1 day');
}

$availability_json = json_encode($availability_data);


// Fetch Patients
$patients = [];
$stmt = $conn->prepare("SELECT * FROM patients WHERE user_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()) {
    $patients[] = $row;
}

if(empty($patients)) {
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
        
        $stmt->execute();
        $res = $stmt->get_result();
        while($row = $res->fetch_assoc()) {
            $patients[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran - Sehati</title>
    <!-- CSS Normal Semantic -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/sehati/includes/styles.css">
</head>
<body class="registration-page">

    <header class="registration-header">
        <a href="<?php echo $back_url; ?>" aria-label="Kembali"><i class="fa-solid fa-arrow-left"></i></a>
        <h1>Pendaftaran Berobat</h1>
    </header>

    <div class="registration-container">
        <main class="registration-main">
            
            <div class="progress-steps">
                <div class="progress-steps-line"></div>
                <div class="progress-steps-fill" id="progressFill" style="width: 50%;"></div>
                
                <div class="progress-steps-flex">
                    <div class="progress-step done">
                        <div class="progress-step-circle"><i class="fa-solid fa-check"></i></div>
                        <span class="progress-step-label">Layanan</span>
                    </div>
                    <div class="progress-step done">
                        <div class="progress-step-circle"><i class="fa-solid fa-check"></i></div>
                        <span class="progress-step-label">Faskes</span>
                    </div>
                    <div class="progress-step active" id="stepIndicator3">
                        <div class="progress-step-circle">3</div>
                        <span class="progress-step-label">Jadwal</span>
                    </div>
                    <div class="progress-step pending" id="stepIndicator4">
                        <div class="progress-step-circle">4</div>
                        <span class="progress-step-label">Data</span>
                    </div>
                    <div class="progress-step pending" id="stepIndicator5">
                        <div class="progress-step-circle">5</div>
                        <span class="progress-step-label">Konfirmasi</span>
                    </div>
                </div>
            </div>

            <div class="registration-card">
                
                <div class="facility-summary" id="facilitySummaryTop">
                    <div class="facility-icon">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                    <div>
                        <h3 id="faskesNameText"><?php echo htmlspecialchars($faskes_nama); ?></h3>
                        <p id="poliNameText" style="margin-bottom: 4px; font-weight: 500; color: #475569;"><?php echo htmlspecialchars($poli_nama); ?></p>
                        <div id="jamPelayananText" style="font-size: 0.85rem; color: #64748b; line-height: 1.4;">
                            <?php echo $jam_pelayanan_text; ?>
                        </div>
                        <?php if($layanan_24_jam !== ""): ?>
                            <p style="font-size:0.75rem; color:#10b981; margin-top:4px; font-weight:600;"><i class="fa-solid fa-clock"></i> 24 Jam: <?php echo htmlspecialchars($layanan_24_jam); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <style>
                    /* Custom styles for availability cards */
                    .date-scroll-container {
                        display: flex;
                        overflow-x: auto;
                        gap: 12px;
                        padding-top: 4px;
                        padding-left: 2px;
                        padding-bottom: 10px;
                        scrollbar-width: thin;
                        scrollbar-color: #cbd5e1 transparent;
                    }
                    .date-scroll-container::-webkit-scrollbar {
                        height: 6px;
                    }
                    .date-scroll-container::-webkit-scrollbar-thumb {
                        background-color: #cbd5e1;
                        border-radius: 4px;
                    }
                    .date-card {
                        flex: 0 0 auto;
                        width: 110px;
                        border: 1px solid #e2e8f0;
                        border-radius: 12px;
                        padding: 12px 10px;
                        text-align: center;
                        cursor: pointer;
                        background: #fff;
                        transition: all 0.2s;
                        position: relative;
                        overflow: hidden;
                    }
                    .date-card:hover:not(.disabled) {
                        border-color: #A57BD7;
                        transform: translateY(-2px);
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                    }
                    .date-card.selected {
                        border-color: #413074;
                        background: #F6F9F9;
                        ring: 2px solid #413074;
                        box-shadow: 0 0 0 1px #413074;
                    }
                    .date-card.disabled {
                        opacity: 0.6;
                        cursor: not-allowed;
                        background: #f8fafc;
                    }
                    .date-day { font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 4px; }
                    .date-card.selected .date-day { color: #413074; }
                    .date-date { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
                    .date-card.selected .date-date { color: #413074; }
                    .date-status { font-size: 0.7rem; font-weight: 600; border-radius: 4px; padding: 2px 0; }
                    
                    .status-available { color: #059669; background: #d1fae5; }
                    .status-limited { color: #d97706; background: #fef3c7; }
                    .status-full { color: #dc2626; background: #fee2e2; }
                    .status-none { color: #64748b; background: #f1f5f9; }

                    .time-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                        gap: 12px;
                    }
                    .time-card {
                        border: 1px solid #e2e8f0;
                        border-radius: 12px;
                        padding: 14px;
                        background: #fff;
                        cursor: pointer;
                        transition: all 0.2s;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        position: relative;
                        overflow: hidden;
                    }
                    /* HOVER STATE */
                    .time-card:hover:not(.disabled):not(.selected) {
                        border-color: #A57BD7;
                        background: #faf5ff;
                    }
                    /* SELECTED STATE */
                    .time-card.selected {
                        border-color: #413074;
                        background: #F6F9F9;
                        box-shadow: 0 0 0 2px #413074;
                    }
                    /* FULL/DISABLED STATE */
                    .time-card.disabled {
                        opacity: 0.6;
                        cursor: not-allowed;
                        background: #f1f5f9;
                        border-color: #cbd5e1;
                    }
                    
                    /* Text styles */
                    .time-range { font-size: 1.05rem; font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
                    .time-card:not(.selected) .time-range { color: #1e293b; }
                    .time-card.selected .time-range { color: #413074; }
                    
                    .time-sisa { font-size: 0.75rem; font-weight: 500; }
                    .time-card:not(.selected) .time-sisa { color: #64748b; }
                    .time-card.selected .time-sisa { color: #A57BD7; font-weight: 600; }
                    
                    /* Selected indicator text (Dipilih) */
                    .time-selected-text {
                        font-size: 0.75rem;
                        font-weight: 700;
                        color: #413074;
                        margin-top: 6px;
                        display: none;
                        background: #e0d8f0;
                        padding: 2px 10px;
                        border-radius: 10px;
                    }
                    .time-card.selected .time-selected-text {
                        display: inline-block;
                    }
                    
                    /* Checkmark */
                    .time-check { display: none; color: #413074; font-size: 1rem; }
                    .time-card.selected .time-check { display: inline; }
                    
                    /* Radio input hidden for time and date */
                    .hidden-radio { display: none; }
                </style>

                <!-- ==============================
                     STEP 3: JADWAL
                     ============================== -->
                <div id="step3-jadwal">
                    <h2 class="registration-section-title">Pilih Tanggal & Waktu</h2>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.75rem;">
                            <p class="registration-section-subtitle" style="margin-bottom: 0;">Pilih Tanggal Kunjungan</p>
                            <div style="position: relative;">
                                <a href="#" onclick="document.getElementById('calendarPicker').showPicker(); return false;" style="font-size: 0.8rem; color: #A57BD7; font-weight: 600; text-decoration: none;"><i class="fa-regular fa-calendar-days"></i> Lihat kalender</a>
                                <input type="date" id="calendarPicker" style="position: absolute; right: 0; bottom: 0; opacity: 0; pointer-events: none; width: 0; height: 0;" min="<?php echo $start_str; ?>" onchange="loadDateFromCalendar(this.value)">
                            </div>
                        </div>
                        
                        <div class="date-scroll-container" id="dateContainer">
                            <!-- Dates injected by JS -->
                        </div>
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <p class="registration-section-subtitle" style="margin-bottom: 0.75rem;">Waktu Kunjungan</p>
                        <div id="timeContainer">
                            <!-- Time slots injected by JS -->
                            <div style="padding: 2rem 1rem; text-align: center; color: #94a3b8; font-size: 0.9rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px;">
                                <i class="fa-regular fa-calendar-check" style="font-size: 1.5rem; margin-bottom: 8px;"></i><br>
                                Pilih tanggal yang tersedia di atas untuk melihat jadwal.
                            </div>
                        </div>
                    </div>

                    <div class="registration-actions">
                        <a href="<?php echo $back_url; ?>" class="btn-back">Kembali</a>
                        <button type="button" id="btnNextToData" class="btn-primary" style="opacity: 0.5; cursor: not-allowed;" onclick="goToStep(4)" disabled>Lanjut Konfirmasi</button>
                    </div>
                </div>

                <!-- ==============================
                     STEP 4: DATA PASIEN
                     ============================== -->
                <div id="step4-data" style="display: none;">
                    <h2 class="registration-section-title">Siapa yang akan mendapatkan layanan?</h2>
                    <div class="patient-selector" id="patientSelector">
                        <?php foreach($patients as $idx => $p): ?>
                            <label class="patient-option">
                                <input type="radio" name="patient_id" value="<?php echo $p['id']; ?>" data-name="<?php echo htmlspecialchars($p['nama_lengkap']); ?>" <?php echo $idx === 0 ? 'checked' : ''; ?>>
                                <div class="patient-option-card">
                                    <div class="patient-info-flex">
                                        <div class="patient-avatar">
                                            <i class="fa-solid <?php echo $p['hubungan'] === 'Saya sendiri' ? 'fa-user-tie' : 'fa-user'; ?>"></i>
                                        </div>
                                        <div>
                                            <p class="patient-name"><?php echo htmlspecialchars($p['nama_lengkap']); ?></p>
                                            <p class="patient-details"><?php echo htmlspecialchars($p['hubungan']); ?> • <span><?php echo htmlspecialchars($p['metode_pembiayaan']); ?></span></p>
                                        </div>
                                    </div>
                                    <div class="patient-radio">
                                        <div class="patient-radio-inner"></div>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                        
                        <a href="keluarga.php" class="patient-add-btn">
                            <i class="fa-solid fa-plus"></i> Tambah Orang Baru
                        </a>
                    </div>
                    
                    <div class="registration-actions">
                        <button type="button" class="btn-back" onclick="goToStep(3)">Kembali</button>
                        <button type="button" id="btnNextToConfirm" class="btn-primary" onclick="goToStep(5)">Lanjut Konfirmasi</button>
                    </div>
                </div>

                <!-- ==============================
                     STEP 5: KONFIRMASI
                     ============================== -->
                <div id="step5-konfirmasi" style="display: none;">
                    
                    <div id="konfirmasi-content">
                        <h2 class="registration-section-title">Konfirmasi Pendaftaran</h2>
                        <div class="konfirmasi-summary bg-slate-50 p-6 rounded-2xl border border-slate-200 mb-6">
                            <h3 class="font-bold text-lg mb-1" id="sumFaskes"></h3>
                            <p class="text-primary font-medium mb-4" id="sumPoli"></p>
                            
                            <div class="grid grid-cols-2 gap-y-4 gap-x-2">
                                <div>
                                    <p class="text-xs text-slate-500 mb-1 font-semibold">PASIEN</p>
                                    <p class="font-bold text-slate-800" id="sumPasien"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 mb-1 font-semibold">TANGGAL</p>
                                    <p class="font-bold text-slate-800" id="sumTanggal"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 mb-1 font-semibold">WAKTU</p>
                                    <p class="font-bold text-slate-800" id="sumWaktu"></p>
                                </div>
                            </div>
                        </div>

                        <div class="registration-actions" id="actionConfirm">
                            <button type="button" class="btn-back" onclick="goToStep(4)">Kembali</button>
                            <button type="button" id="btnSubmitBooking" class="btn-primary" onclick="submitBooking(event)">Konfirmasi Pendaftaran</button>
                        </div>
                    </div>
                    
                    <div id="successMessage" style="display:none;" class="text-center py-6">
                        <div class="w-20 h-20 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl shadow-sm"><i class="fa-solid fa-check"></i></div>
                        <h3 class="text-2xl font-bold mb-2 text-slate-800">Pendaftaran Berhasil!</h3>
                        <p class="text-slate-500 mb-6 max-w-sm mx-auto">Jadwal kunjungan Anda telah terkonfirmasi. Silakan periksa halaman Jadwal Saya untuk melihat QR Tiket Anda.</p>
                        <a href="jadwal.php" class="px-6 py-4 bg-primary hover:bg-primaryDark transition-colors text-white font-bold rounded-xl block w-full text-center shadow-md">Lihat Jadwal Saya</a>
                    </div>
                </div>
                
            </div>
            
        </main>
    </div>
    
    <!-- External JS logic -->
    <script>
        const CFG_FASKES_ID = <?php echo $faskes_id; ?>;
        const CFG_POLI_ID = <?php echo $poli_id; ?>;
        const availabilityData = <?php echo $availability_json; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            renderDateCards();
        });
        
        function renderDateCards() {
            const container = document.getElementById('dateContainer');
            container.innerHTML = '';
            
            availabilityData.forEach(day => {
                let statusClass = '';
                let isDisabled = false;
                
                if (day.status === 'AVAILABLE') statusClass = 'status-available';
                else if (day.status === 'LIMITED') statusClass = 'status-limited';
                else if (day.status === 'FULL') { statusClass = 'status-full'; isDisabled = true; }
                else if (day.status === 'NO_SCHEDULE') { statusClass = 'status-none'; isDisabled = true; }
                
                const card = document.createElement('label');
                card.className = `date-card ${isDisabled ? 'disabled' : ''} date-option`;
                card.setAttribute('data-date', day.date);
                
                if (!isDisabled) {
                    card.onclick = function(e) {
                        // Prevent triggering if clicked directly on input
                        if(e.target.tagName === 'INPUT') return;
                        
                        if (this.classList.contains('selected')) {
                            // Undo selection
                            this.classList.remove('selected');
                            const radio = this.querySelector('input[type="radio"]');
                            if (radio) radio.checked = false;
                            
                            // Reset time slots
                            document.getElementById('timeContainer').innerHTML = `
                                <div style="padding: 2rem 1rem; text-align: center; color: #94a3b8; font-size: 0.9rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px;">
                                    <i class="fa-regular fa-calendar-check" style="font-size: 1.5rem; margin-bottom: 8px;"></i><br>
                                    Pilih tanggal yang tersedia di atas untuk melihat jadwal.
                                </div>
                            `;
                            
                            // Disable submit button
                            const btnNext = document.getElementById('btnNextToData');
                            if (btnNext) {
                                btnNext.disabled = true;
                                btnNext.style.opacity = '0.5';
                                btnNext.style.cursor = 'not-allowed';
                            }
                            return;
                        }
                        
                        document.querySelectorAll('.date-card').forEach(c => c.classList.remove('selected'));
                        this.classList.add('selected');
                        
                        // Select the hidden radio
                        const radio = this.querySelector('input[type="radio"]');
                        if (radio) radio.checked = true;
                        
                        renderTimeSlots(day);
                    };
                }
                
                card.innerHTML = `
                    <input type="radio" name="date" value="${day.date}" class="hidden-radio" ${isDisabled ? 'disabled' : ''}>
                    <div class="date-day">${day.day_name}</div>
                    <div class="date-date">${day.date_formatted}</div>
                    <div class="date-status ${statusClass}">${day.status_text}</div>
                `;
                
                container.appendChild(card);
            });
        }
        
        function renderTimeSlots(dayData) {
            const container = document.getElementById('timeContainer');
            const btnNext = document.getElementById('btnNextToData');
            
            // Reset submit button
            if (btnNext) {
                btnNext.disabled = true;
                btnNext.style.opacity = '0.5';
                btnNext.style.cursor = 'not-allowed';
            }
            
            if (dayData.slots.length === 0) {
                container.innerHTML = `
                    <div style="padding: 2rem 1rem; text-align: center; color: #94a3b8; font-size: 0.9rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px;">
                        Tidak ada jadwal tersedia pada tanggal ini.
                    </div>
                `;
                return;
            }
            
            let html = '<div class="time-grid">';
            
            dayData.slots.forEach(slot => {
                const isFull = slot.sisa <= 0;
                
                html += `
                    <label class="time-card ${isFull ? 'disabled' : ''}" onclick="selectTime(event, this, ${isFull})">
                        <input type="radio" name="time" value="${slot.waktu_mulai}" class="hidden-radio" ${isFull ? 'disabled' : ''}>
                        <div class="time-range"><i class="fa-solid fa-check time-check"></i> ${slot.waktu_mulai} - ${slot.waktu_selesai}</div>
                        <div class="time-sisa ${isFull ? 'text-red-500' : ''}">${isFull ? 'Antrean penuh' : 'Sisa ' + slot.sisa + ' kuota'}</div>
                        <div class="time-selected-text">Dipilih</div>
                    </label>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }
        
        function selectTime(event, labelElement, isFull) {
            if (event.target.tagName === 'INPUT') return;
            if (isFull) return;
            
            // Check radio button
            const radio = labelElement.querySelector('input[type="radio"]');
            
            if (labelElement.classList.contains('selected')) {
                labelElement.classList.remove('selected');
                if (radio) radio.checked = false;
                
                // Disable next button
                const btnNext = document.getElementById('btnNextToData');
                if (btnNext) {
                    btnNext.disabled = true;
                    btnNext.style.opacity = '0.5';
                    btnNext.style.cursor = 'not-allowed';
                }
                return;
            }
            
            if (radio) radio.checked = true;
            
            // Update UI
            document.querySelectorAll('.time-card').forEach(c => c.classList.remove('selected'));
            labelElement.classList.add('selected');
            
            // Enable next button
            const btnNext = document.getElementById('btnNextToData');
            if (btnNext) {
                btnNext.disabled = false;
                btnNext.style.opacity = '1';
                btnNext.style.cursor = 'pointer';
            }
        }
        
        function goToStep(step) {
            // Hide all steps
            document.getElementById('step3-jadwal').style.display = 'none';
            document.getElementById('step4-data').style.display = 'none';
            document.getElementById('step5-konfirmasi').style.display = 'none';
            
            // Manage Progress UI
            const indicator3 = document.getElementById('stepIndicator3');
            const indicator4 = document.getElementById('stepIndicator4');
            const indicator5 = document.getElementById('stepIndicator5');
            const progressFill = document.getElementById('progressFill');
            
            indicator3.className = 'progress-step';
            indicator4.className = 'progress-step';
            indicator5.className = 'progress-step';
            
            if (step === 3) {
                document.getElementById('step3-jadwal').style.display = 'block';
                indicator3.classList.add('active');
                indicator4.classList.add('pending');
                indicator5.classList.add('pending');
                progressFill.style.width = '50%';
            } 
            else if (step === 4) {
                document.getElementById('step4-data').style.display = 'block';
                indicator3.classList.add('done');
                indicator4.classList.add('active');
                indicator5.classList.add('pending');
                progressFill.style.width = '75%';
            } 
            else if (step === 5) {
                document.getElementById('step5-konfirmasi').style.display = 'block';
                indicator3.classList.add('done');
                indicator4.classList.add('done');
                indicator5.classList.add('active');
                progressFill.style.width = '100%';
                
                // Populate summary
                const selectedPatient = document.querySelector('input[name="patient_id"]:checked');
                const selectedDate = document.querySelector('.date-option.selected');
                const selectedTime = document.querySelector('input[name="time"]:checked');
                
                document.getElementById('sumFaskes').innerText = document.getElementById('faskesNameText').innerText;
                document.getElementById('sumPoli').innerText = document.getElementById('poliNameText').innerText;
                
                if (selectedPatient) document.getElementById('sumPasien').innerText = selectedPatient.getAttribute('data-name');
                
                if (selectedDate) {
                    const dText = selectedDate.querySelector('.date-date').innerText;
                    const dDay = selectedDate.querySelector('.date-day').innerText;
                    document.getElementById('sumTanggal').innerText = dDay + ', ' + dText;
                }
                if (selectedTime) {
                    // Extract just the time string "HH:MM - HH:MM" from the label
                    const timeRangeStr = selectedTime.closest('label').querySelector('.time-range').innerText.replace('', '').trim();
                    document.getElementById('sumWaktu').innerText = timeRangeStr;
                }
            }
        }
        
        function submitBooking(event) {
            event.preventDefault();
            const btn = event.target;
            
            const selectedPatient = document.querySelector('input[name="patient_id"]:checked');
            const selectedDate = document.querySelector('.date-option.selected');
            const selectedTime = document.querySelector('input[name="time"]:checked');
            
            if (!selectedPatient || !selectedDate || !selectedTime) {
                alert('Silakan pilih pasien, tanggal, dan waktu kunjungan terlebih dahulu.');
                return;
            }
            
            const patientId = selectedPatient.value;
            const date = selectedDate.getAttribute('data-date');
            const time = selectedTime.value;
            
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
            btn.disabled = true;
            
            fetch('/sehati/ajax/process_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    faskes_id: CFG_FASKES_ID,
                    poli_id: CFG_POLI_ID,
                    patient_id: patientId,
                    date: date,
                    time: time
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Hide confirm content and show success message
                    document.getElementById('konfirmasi-content').style.display = 'none';
                    document.getElementById('successMessage').style.display = 'block';
                    
                    // Mark step 5 as done
                    document.getElementById('stepIndicator5').classList.remove('active');
                    document.getElementById('stepIndicator5').classList.add('done');
                    
                } else {
                    alert(data.message || 'Maaf, antrean penuh atau terjadi kesalahan.');
                    btn.innerHTML = 'Konfirmasi Pendaftaran';
                    btn.disabled = false;
                    
                    // If full, go back to step 3 to reselect
                    goToStep(3);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Gagal memproses pendaftaran. Periksa koneksi Anda.');
                btn.innerHTML = 'Konfirmasi Pendaftaran';
                btn.disabled = false;
            });
        }
        function loadDateFromCalendar(dateStr) {
            if (!dateStr) return;
            
            const existingCard = document.querySelector(`.date-card[data-date="${dateStr}"]`);
            if (existingCard) {
                existingCard.click();
                existingCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                return;
            }
            
            const oldCustomCard = document.querySelector('.date-card.custom-calendar-card');
            if (oldCustomCard) {
                oldCustomCard.remove();
            }
            
            fetch(`/sehati/ajax/get_day_availability.php?faskes_id=${CFG_FASKES_ID}&poli_id=${CFG_POLI_ID}&date=${dateStr}`)
                .then(res => res.json())
                .then(day => {
                    let statusClass = '';
                    let isDisabled = false;
                    
                    if (day.status === 'AVAILABLE') statusClass = 'status-available';
                    else if (day.status === 'LIMITED') statusClass = 'status-limited';
                    else if (day.status === 'FULL') { statusClass = 'status-full'; isDisabled = true; }
                    else if (day.status === 'NO_SCHEDULE') { statusClass = 'status-none'; isDisabled = true; }
                    
                    const card = document.createElement('label');
                    card.className = `date-card custom-calendar-card ${isDisabled ? 'disabled' : ''} date-option`;
                    card.setAttribute('data-date', day.date);
                    
                    if (!isDisabled) {
                        card.onclick = function(e) {
                            if(e.target.tagName === 'INPUT') return;
                            
                            if (this.classList.contains('selected')) {
                                this.classList.remove('selected');
                                const radio = this.querySelector('input[type="radio"]');
                                if (radio) radio.checked = false;
                                
                                document.getElementById('timeContainer').innerHTML = `
                                    <div style="padding: 2rem 1rem; text-align: center; color: #94a3b8; font-size: 0.9rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px;">
                                        <i class="fa-regular fa-calendar-check" style="font-size: 1.5rem; margin-bottom: 8px;"></i><br>
                                        Pilih tanggal yang tersedia di atas untuk melihat jadwal.
                                    </div>
                                `;
                                
                                const btnNext = document.getElementById('btnNextToData');
                                if (btnNext) {
                                    btnNext.disabled = true;
                                    btnNext.style.opacity = '0.5';
                                    btnNext.style.cursor = 'not-allowed';
                                }
                                return;
                            }
                            
                            document.querySelectorAll('.date-card').forEach(c => c.classList.remove('selected'));
                            this.classList.add('selected');
                            
                            const radio = this.querySelector('input[type="radio"]');
                            if (radio) radio.checked = true;
                            
                            renderTimeSlots(day);
                        };
                    }
                    
                    card.innerHTML = `
                        <input type="radio" name="date" value="${day.date}" class="hidden-radio" ${isDisabled ? 'disabled' : ''}>
                        <div class="date-day">${day.day_name}</div>
                        <div class="date-date">${day.date_formatted}</div>
                        <div class="date-status ${statusClass}">${day.status_text}</div>
                    `;
                    
                    const container = document.getElementById('dateContainer');
                    container.appendChild(card);
                    
                    card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'end' });
                    
                    if (!isDisabled) {
                        card.click();
                    }
                })
                .catch(err => {
                    console.error('Error fetching date availability', err);
                    alert('Gagal mengambil jadwal untuk tanggal tersebut.');
                });
        }
    </script>
    <script src="/sehati/includes/js.js"></script>
</body>
</html>
