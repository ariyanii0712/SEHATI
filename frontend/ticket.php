<?php
if(session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
$user_id = $_SESSION["user_id"];
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($ticket_id === 0) {
    echo "ID Tiket tidak valid.";
    exit;
}

$stmt = $conn->prepare("
    SELECT p.id, p.tanggal_kunjungan, p.waktu_kunjungan, p.nomor_antrean, p.status, 
           pat.nama_lengkap as patient_name, pat.hubungan as relationship, 
           f.nama as facilityName, pol.nama_poli as service
    FROM pendaftaran p
    LEFT JOIN patients pat ON p.patient_id = pat.id
    LEFT JOIN faskes f ON p.faskes_id = f.id
    LEFT JOIN poli pol ON p.poli_id = pol.id
    WHERE p.id = ? AND p.user_id = ?
");
$stmt->bind_param("ii", $ticket_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "Tiket tidak ditemukan.";
    exit;
}

$app = $res->fetch_assoc();

setlocale(LC_TIME, 'id_ID');
$dateObj = new DateTime($app['tanggal_kunjungan'] . ' ' . $app['waktu_kunjungan']);
$monthsId = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
$fDate = $dateObj->format('d') . ' ' . $monthsId[$dateObj->format('n') - 1] . ' ' . $dateObj->format('Y');
$fTime = $dateObj->format('H.i');

$qrData = "SEHATI-" . $app['id'] . "-" . str_replace(' ', '', $app['nomor_antrean'] ?? 'XXX');
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);

// Mock Live Queue data
$isWaiting = ($app['status'] === 'terjadwal' || $app['status'] === 'menunggu');
$currentQueue = 12; // mock current queue
$myQueue = intval(preg_replace('/[^0-9]/', '', $app['nomor_antrean'] ?? '0'));
$remaining = max(0, $myQueue - $currentQueue);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Antrean - Sehati</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#413074',
                        primaryDark: '#2c1e54',
                        secondary: '#f6f9f9',
                        accent: '#0fb7b8'
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'], }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0ea5e9; } /* Primary background */
        .ticket-bg {
            background-image: radial-gradient(circle at 0 50%, transparent 15px, white 16px), 
                              radial-gradient(circle at 100% 50%, transparent 15px, white 16px);
            background-color: white;
            background-position: left, right;
            background-size: 51% 100%;
            background-repeat: no-repeat;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
        }
        .dash-line { border-bottom: 2px dashed #e2e8f0; }
        
        /* Pulse Animation */
        @keyframes pulse-ring {
            0% { transform: scale(0.8); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.8); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .live-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #10b981;
            animation: pulse-ring 2s infinite;
        }
        
        @media print {
            body { background-color: white !important; }
            header, .no-print { display: none !important; }
            .ticket-bg { filter: none !important; border: 1px solid #ccc; }
        }
    </style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col">

    <!-- Header -->
    <header class="text-white p-4 sticky top-0 z-50 flex items-center justify-between no-print">
        <a href="jadwal.php" class="hover:text-blue-200 transition-colors"><i class="fa-solid fa-arrow-left text-xl"></i></a>
        <h1 class="text-lg font-bold">Tiket Antrean</h1>
        <div class="w-6"></div> <!-- spacer -->
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full max-w-md mx-auto p-4 flex flex-col items-center justify-center">
        
        <!-- Ticket Card -->
        <div class="ticket-bg w-full rounded-2xl overflow-hidden relative mt-4">
            
            <!-- Top Section: Info -->
            <div class="p-6 md:p-8 text-center pb-8">
                <?php if ($isWaiting): ?>
                <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-4 border border-emerald-100">
                    <div class="live-indicator"></div> Live Tracking
                </div>
                <?php else: ?>
                <div class="inline-flex items-center gap-2 bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-4 border border-slate-200">
                    <?php echo strtoupper($app['status']); ?>
                </div>
                <?php endif; ?>
                
                <h2 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($app['facilityName']); ?></h2>
                <p class="text-slate-500 font-medium"><?php echo htmlspecialchars($app['service']); ?></p>

                <div class="mt-8 mb-4">
                    <p class="text-slate-400 text-sm font-semibold uppercase tracking-widest mb-1">Nomor Antrean Anda</p>
                    <div class="text-6xl font-black text-primary my-2"><?php echo htmlspecialchars($app['nomor_antrean'] ? $app['nomor_antrean'] : '-'); ?></div>
                    <p class="text-slate-500 font-medium text-lg"><?php echo htmlspecialchars($app['patient_name']); ?></p>
                </div>
            </div>

            <!-- Dashed divider -->
            <div class="dash-line mx-4 relative"></div>

            <!-- Bottom Section: Live Status or QR -->
            <div class="p-6 md:p-8 bg-slate-50 flex flex-col items-center">
                
                <!-- QR Code Display (Hidden by default, shown by toggle or always shown based on preference) -->
                <div id="qrSection" class="hidden flex-col items-center mb-6">
                    <img src="<?php echo $qrUrl; ?>" alt="QR Code" class="w-32 h-32 mb-2 p-1 bg-white border border-slate-200 rounded">
                    <p class="text-xs text-slate-500 text-center max-w-[200px]">Tunjukkan QR ini saat melakukan verifikasi pendaftaran.</p>
                </div>
                
                <div id="liveSection" class="w-full">
                    <?php if ($isWaiting): ?>
                    <div class="flex justify-between items-center mb-6">
                        <div class="text-center">
                            <p class="text-xs text-slate-500 font-semibold uppercase mb-1">Sedang Dilayani</p>
                            <p class="text-xl font-bold text-slate-800">B-0<?php echo $currentQueue; ?></p>
                        </div>
                        <div class="h-10 w-px bg-slate-300"></div>
                        <div class="text-center">
                            <p class="text-xs text-slate-500 font-semibold uppercase mb-1">Sisa Antrean</p>
                            <p class="text-xl font-bold text-slate-800"><?php echo $remaining; ?> <span class="text-xs font-medium text-slate-500">Orang</span></p>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center mb-6">
                        <p class="text-sm text-slate-500">Jadwal ini telah <strong><?php echo $app['status']; ?></strong>.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="text-center mt-2 w-full">
                    <p class="text-sm font-semibold text-slate-800 bg-white border border-slate-200 rounded-lg py-2">
                        <i class="fa-regular fa-calendar mr-2 text-primary"></i> <?php echo $fDate; ?> • <?php echo $fTime; ?> WIB
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="w-full mt-6 gap-3 flex flex-col no-print">
            <button onclick="toggleQR()" class="w-full py-3 bg-white text-primary font-bold text-center rounded-xl shadow flex items-center justify-center gap-2 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-qrcode text-xl"></i> <span id="qrBtnText">Tampilkan QR Check-in</span>
            </button>
            <div class="flex gap-3">
                <button onclick="window.print()" class="w-1/2 py-3 bg-primary border border-primaryDark text-white font-medium text-center rounded-xl shadow hover:bg-primaryDark transition-colors">
                    <i class="fa-solid fa-print mr-1"></i> Cetak Tiket
                </button>
                <a href="jadwal.php" class="w-1/2 py-3 bg-slate-100 text-slate-600 font-medium text-center rounded-xl shadow hover:bg-slate-200 transition-colors">
                    Kembali
                </a>
            </div>
        </div>

    </main>

    <script>
        let qrVisible = false;
        function toggleQR() {
            qrVisible = !qrVisible;
            document.getElementById('qrSection').classList.toggle('hidden', !qrVisible);
            document.getElementById('qrSection').classList.toggle('flex', qrVisible);
            
            document.getElementById('liveSection').classList.toggle('hidden', qrVisible);
            
            document.getElementById('qrBtnText').innerText = qrVisible ? "Sembunyikan QR Check-in" : "Tampilkan QR Check-in";
        }
    </script>
</body>
</html>
