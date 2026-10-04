<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php'; 
require_once '../config/helpers.php';

// Parameter Pencarian
$q = isset($_GET['q']) ? $_GET['q'] : '';
$tipe = isset($_GET['tipe']) ? $_GET['tipe'] : '';
$wilayah = isset($_GET['wilayah']) ? $_GET['wilayah'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'nearest'; 
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371) {
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
      cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

// Build Query
$where = ["faskes.status = 'Aktif'"];
$params = [];
$types = "";

if ($q !== '') {
    $where[] = "(faskes.nama LIKE ? OR poli.nama_poli LIKE ? OR faskes.jam_pelayanan LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $types .= "sss";
}
if ($tipe !== '') {
    $where[] = "faskes.kategori = ?";
    $params[] = $tipe;
    $types .= "s";
}
if ($wilayah !== '') {
    $where[] = "faskes.wilayah = ?";
    $params[] = $wilayah;
    $types .= "s";
}

$whereClause = implode(" AND ", $where);
$orderBy = "CASE WHEN faskes.kategori = 'Rumah Sakit' THEN 1 ELSE 2 END ASC, faskes.nama ASC";

$sql = "SELECT DISTINCT faskes.id, faskes.nama, faskes.kategori, faskes.wilayah, faskes.alamat, faskes.telepon, faskes.jam_pelayanan, faskes.latitude, faskes.longitude, faskes.rating, faskes.website, faskes.deskripsi, faskes.image_url 
        FROM faskes 
        LEFT JOIN faskes_layanan ON faskes.id = faskes_layanan.faskes_id 
        LEFT JOIN poli ON faskes_layanan.poli_id = poli.id 
        WHERE $whereClause 
        ORDER BY $orderBy";

$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$fasilitas = [];
while ($row = $result->fetch_assoc()) {
    $f_id = $row['id'];
    // Ambil layanan/poli
    $sql_poli = "SELECT p.nama_poli FROM poli p JOIN faskes_layanan fl ON p.id = fl.poli_id WHERE fl.faskes_id = $f_id";
    $res_poli = $conn->query($sql_poli);
    $polis = [];
    while($p = $res_poli->fetch_assoc()) {
        $polis[] = $p['nama_poli'];
    }
    $row['layanan'] = $polis;
    $row['jam_pelayanan'] = $row['jam_pelayanan'] ? formatJamPelayanan($row['jam_pelayanan']) : '-';
    
    if ($lat !== null && $lng !== null && $row['latitude'] !== null && $row['longitude'] !== null) {
        $row['distance_km'] = haversineGreatCircleDistance($lat, $lng, $row['latitude'], $row['longitude']);
    } else {
        $row['distance_km'] = null;
    }
    $fasilitas[] = $row;
}

if ($lat !== null && $lng !== null) {
    usort($fasilitas, function($a, $b) {
        if ($a['distance_km'] === null && $b['distance_km'] === null) return 0;
        if ($a['distance_km'] === null) return 1;
        if ($b['distance_km'] === null) return -1;
        return $a['distance_km'] <=> $b['distance_km'];
    });
}

function renderFasilitasList($fasilitas) {
    if (count($fasilitas) == 0) {
        echo '<div class="col-span-full text-center p-8 bg-white rounded-[20px] border border-slate-200 mt-4"><i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-3"></i><p class="text-slate-500 font-medium">Tidak ada fasilitas yang sesuai dengan pencarian Anda.</p></div>';
        return;
    }
    
    foreach ($fasilitas as $f) {
        $isRS = strtolower($f['kategori']) == 'rumah sakit';
        
        // Enforce valid UTF-8 to prevent json_encode from failing on invalid characters (like CP1252 en-dash)
        $safe_f = [];
        foreach($f as $k => $v) {
            if (is_string($v)) {
                $safe_f[$k] = !mb_check_encoding($v, 'UTF-8') ? mb_convert_encoding($v, 'UTF-8', 'Windows-1252') : $v;
            } else if (is_array($v)) {
                $safe_v = [];
                foreach($v as $vk => $vv) {
                    $safe_v[$vk] = is_string($vv) && !mb_check_encoding($vv, 'UTF-8') ? mb_convert_encoding($vv, 'UTF-8', 'Windows-1252') : $vv;
                }
                $safe_f[$k] = $safe_v;
            } else {
                $safe_f[$k] = $v;
            }
        }
        // Fallback flag if PHP >= 7.2
        $encode_flags = defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0;
        $encoded_json = json_encode($safe_f, $encode_flags);
        $detailsJson = htmlspecialchars($encoded_json ? $encoded_json : '{}', ENT_QUOTES, 'UTF-8');

        $rating = isset($f['rating']) && $f['rating'] ? $f['rating'] : '4.5';
        $deskripsi = isset($f['deskripsi']) && $f['deskripsi'] ? $f['deskripsi'] : 'Pelayanan kesehatan tingkat pertama dengan layanan umum, ibu dan anak, imunisasi, serta program kesehatan masyarakat.';
        if ($isRS && (!isset($f['deskripsi']) || !$f['deskripsi'])) {
             $deskripsi = 'Tempat untuk mendapatkan layanan kesehatan umum dan spesialis, pemeriksaan, perawatan, serta layanan penunjang medis terintegrasi.';
        }
        $imageUrl = isset($f['image_url']) && $f['image_url'] ? $f['image_url'] : 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?q=80&w=600&auto=format&fit=crop';
        $kategoriDisplay = $isRS ? 'Rumah Sakit' : 'Puskesmas';
        $phone = $f['telepon'] ? $f['telepon'] : '-';
        $alamat = $f['alamat'] ? $f['alamat'] : '-';
        if (isset($f['distance_km']) && $f['distance_km'] !== null) {
            $distance = '± ' . number_format($f['distance_km'], 1) . ' km';
        } else {
            $distance = rand(2, 8) . '.' . rand(1, 9) . ' km'; // Mock distance
        }

        $detailUrl = $isRS ? 'rs_detail.php?id='.$f['id'] : 'puskesmas_detail.php?id='.$f['id'];
        // Card Container
        echo '<div id="facility-card-'.$f['id'].'" class="bg-white rounded-[20px] border border-slate-200 overflow-hidden shadow-sm hover:shadow-md hover:border-[#A57BD7] transition-all flex flex-col h-full group cursor-pointer" onclick="window.location.href=\''.$detailUrl.'\'">';
        
        // Image
        echo '<div class="relative w-full h-[180px] shrink-0 bg-slate-100 overflow-hidden">';
        echo '  <img src="'.htmlspecialchars($imageUrl).'" alt="Faskes Image" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">';
        echo '  <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-full text-[11px] font-bold text-[#413074] shadow-sm flex items-center gap-1.5 border border-slate-100">';
        if($isRS) {
            echo '      <i class="fa-solid fa-hospital"></i> Rumah Sakit';
        } else {
            echo '      <i class="fa-solid fa-clinic-medical"></i> Puskesmas';
        }
        echo '  </div>';
        echo '</div>';
        
        // Content
        echo '<div class="p-5 flex-1 flex flex-col">';
        
        // Title
        echo '  <h3 class="font-bold text-lg text-[#413074] leading-tight mb-2 group-hover:text-[#A57BD7] transition-colors line-clamp-2">'.htmlspecialchars($f['nama']).'</h3>';
        
        // Rating & Status & Distance
        echo '  <div class="text-[12px] font-medium text-slate-500 mb-3 flex items-center flex-wrap gap-1.5">';
        echo '      <i class="fa-solid fa-star text-[#413074]"></i> <span class="text-slate-700 font-semibold">'.htmlspecialchars($rating).'</span> ('.rand(100, 999).') &bull; ';
        echo '      <span class="text-emerald-600 font-semibold flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> Buka</span> &bull; ';
        echo '      <span class="flex items-center gap-1"><i class="fa-solid fa-location-dot text-slate-400"></i> '. $distance .'</span>';
        echo '  </div>';
        
        // Info lines (Alamat)
        echo '  <div class="text-[12px] text-slate-600 mb-4 flex items-start gap-2">';
        echo '      <i class="fa-solid fa-map-pin mt-0.5 text-slate-400"></i>';
        echo '      <span class="line-clamp-2 leading-relaxed">'.htmlspecialchars($alamat).'</span>';
        echo '  </div>';
        
        // Description
        echo '  <p class="text-[13px] text-slate-500 leading-relaxed font-medium line-clamp-3 mb-5 flex-1">'.htmlspecialchars($deskripsi).'</p>';
        
        // Buttons
        echo '  <div class="flex gap-2 mt-auto pt-4 border-t border-slate-100">';
        echo '      <button class="flex-1 py-2.5 px-3 bg-[#413074] text-white text-xs font-bold rounded-xl hover:bg-[#A57BD7] transition-colors text-center shadow-sm" onclick="event.stopPropagation(); window.location.href=\''.$detailUrl.'\'">Lihat Detail</button>';
        echo '      <button class="flex-1 py-2.5 px-3 bg-white border border-[#A57BD7] text-[#413074] text-xs font-bold rounded-xl hover:bg-[#F6F9F9] hover:text-[#A57BD7] transition-colors text-center" data-faskes="'.$detailsJson.'" onclick="event.stopPropagation(); showDetail(event, JSON.parse(this.dataset.faskes))">Daftar Berobat</button>';
        echo '  </div>';
        
        echo '</div>'; // End Content
        
        echo '</div>'; // End Card
    }
}

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    renderFasilitasList($fasilitas);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sehati - Portal Pasien</title>
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
                        accent: '#f59e0b', // Amber 500
                        success: '#10b981', // Emerald 500
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
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F6F9F9; }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="text-slate-800 pb-20 md:pb-0">

    <!-- Splash Screen -->
    <div id="splash-screen" class="fixed inset-0 bg-white z-[9999] flex flex-col items-center justify-center transition-opacity duration-500">
        <img src="/sehati/frontend/logo.png" alt="SEHATI Logo" class="h-28 mb-6 animate-bounce">
        <h1 class="text-primary text-3xl font-bold mb-2 tracking-wide">SEHATI</h1>
        <p class="text-slate-500 text-sm md:text-base font-medium text-center px-6">Sistem Elektronik Kesehatan Terintegrasi</p>
    </div>
    
    <script>
        // Splash screen fade out logic
        document.addEventListener('DOMContentLoaded', () => {
            const splash = document.getElementById('splash-screen');
            if (splash) {
                setTimeout(() => {
                    splash.style.opacity = '0';
                    setTimeout(() => {
                        splash.style.display = 'none';
                    }, 500); 
                }, 1800); 
            }
        });
    </script>


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
        <main class="flex-1 w-full max-w-6xl mx-auto p-4 md:p-6 lg:p-10 flex flex-col relative">
            
            <!-- Header -->
            <div class="mb-8 flex flex-col gap-2 pt-4">
                <div class="w-12 h-1 bg-[#0fb7b8] rounded-full mb-1"></div>
                <h2 class="text-3xl md:text-[34px] font-bold text-[#413074] tracking-tight">Temukan Layanan & Fasilitas Kesehatan</h2>
                <p class="text-slate-500 text-sm md:text-base font-medium">Temukan layanan dan fasilitas kesehatan yang sesuai dengan kebutuhan Anda di Surabaya.</p>
            </div>

            <!-- Search and Filter Row -->
            <div class="w-full mb-10 flex flex-col md:flex-row gap-4 items-center">
                <!-- Search Bar -->
                <form id="searchForm" onsubmit="handleSearch(event)" class="w-full md:flex-1">
                    <div class="relative rounded-[20px] overflow-hidden border border-slate-200 shadow-sm focus-within:border-[#A57BD7] transition-all bg-white flex items-center hover:border-slate-300">
                        <div class="pl-5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-[#413074] text-lg"></i>
                        </div>
                        <input type="text" id="searchInput" class="w-full pl-3 pr-4 py-3.5 text-slate-800 bg-white placeholder-slate-400 font-medium focus:outline-none text-sm" placeholder="Cari layanan, poli, atau fasilitas kesehatan..." value="<?php echo htmlspecialchars($q); ?>">
                    </div>
                    <button type="submit" class="hidden">Cari</button>
                </form>

                <!-- Filters -->
                <div class="flex gap-3 w-full md:w-auto shrink-0 flex-wrap md:flex-nowrap">
                    <div class="relative flex-1 md:w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-regular fa-building text-[#413074]"></i>
                        </div>
                        <select id="filterTipe" onchange="submitSearch()" class="appearance-none w-full bg-white border border-slate-200 text-slate-700 text-sm rounded-xl focus:outline-none focus:border-[#A57BD7] block py-3.5 pl-10 pr-8 font-medium cursor-pointer shadow-sm transition-colors hover:border-slate-300">
                            <option value="">Jenis Fasilitas</option>
                            <option value="Rumah Sakit" <?php echo $tipe == 'Rumah Sakit' ? 'selected' : ''; ?>>Rumah Sakit</option>
                            <option value="Puskesmas" <?php echo $tipe == 'Puskesmas' ? 'selected' : ''; ?>>Puskesmas</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-[#413074]">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    
                    <div class="relative flex-1 md:w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-location-dot text-[#413074]"></i>
                        </div>
                        <select id="filterWilayah" onchange="submitSearch()" class="appearance-none w-full bg-white border border-slate-200 text-slate-700 text-sm rounded-xl focus:outline-none focus:border-[#A57BD7] block py-3.5 pl-10 pr-8 font-medium cursor-pointer shadow-sm transition-colors hover:border-slate-300">
                            <option value="">Wilayah</option>
                            <option value="Pusat" <?php echo $wilayah == 'Pusat' ? 'selected' : ''; ?>>Pusat</option>
                            <option value="Utara" <?php echo $wilayah == 'Utara' ? 'selected' : ''; ?>>Utara</option>
                            <option value="Timur" <?php echo $wilayah == 'Timur' ? 'selected' : ''; ?>>Timur</option>
                            <option value="Selatan" <?php echo $wilayah == 'Selatan' ? 'selected' : ''; ?>>Selatan</option>
                            <option value="Barat" <?php echo $wilayah == 'Barat' ? 'selected' : ''; ?>>Barat</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-[#413074]">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    <!-- Hidden sort select to not break JS -->
                    <select id="filterSort" class="hidden">
                        <option value="nearest">nearest</option>
                    </select>
                </div>
            </div>

            <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <?php if ($q !== ''): ?>
                        <p class="text-slate-600 font-medium text-sm">Menampilkan fasilitas untuk <span class="font-bold text-primary">"<?php echo htmlspecialchars($q); ?>"</span></p>
                    <?php endif; ?>
                    <?php if ($lat !== null && $lng !== null): ?>
                        <p class="text-emerald-600 font-medium text-xs mt-1"><i class="fa-solid fa-location-dot"></i> Jarak perkiraan dari lokasi Anda</p>
                    <?php endif; ?>
                </div>
                <button onclick="requestLocation()" id="btnLocation" class="flex items-center gap-2 bg-white border border-[#A57BD7] text-[#413074] px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-[#A57BD7] hover:text-white transition-colors shrink-0">
                    <i class="fa-solid fa-location-crosshairs"></i> Gunakan lokasi saya
                </button>
            </div>
            
            <?php if ($lat !== null && $lng !== null): ?>
            <div class="mb-8 h-[300px] md:h-[400px] w-full bg-slate-100 rounded-[20px] overflow-hidden border border-slate-200 relative z-0">
                <div id="facilityMap" class="w-full h-full"></div>
            </div>
            <?php endif; ?>

            <!-- Facility List Grid -->
            <div id="facilityListContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-20 md:pb-10">
                <?php renderFasilitasList($fasilitas); ?>
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
    <!-- Facility Detail Modal -->
    <div id="detailModal" class="fixed inset-0 bg-slate-900/50 z-[100] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity opacity-0">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform flex flex-col max-h-[90vh]" id="detailModalContent">
            
            <!-- Modal Header -->
            <div class="bg-primary p-6 text-white relative shrink-0">
                <button onclick="closeDetail()" class="absolute top-4 right-4 text-white/70 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-full w-8 h-8 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div class="inline-block px-3 py-1 bg-white/20 rounded-full text-xs font-bold mb-3 backdrop-blur-md" id="modalTypeBadge">
                    Rumah Sakit
                </div>
                <h2 id="modalTitle" class="text-2xl font-bold leading-tight">Nama Fasilitas</h2>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                <div class="space-y-4">
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-solid fa-map-location-dot"></i></div>
                        <div>
                            <p class="text-slate-500 text-xs font-medium uppercase tracking-wide">Alamat & Wilayah</p>
                            <p class="text-slate-800 font-medium mt-0.5" id="modalAddress">Alamat Lengkap</p>
                            <p class="text-slate-500 mt-0.5">Surabaya <span id="modalRegion">Timur</span></p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-regular fa-clock"></i></div>
                        <div>
                            <p class="text-slate-500 text-xs font-medium uppercase tracking-wide">Jam Pelayanan</p>
                            <p class="text-slate-800 font-medium mt-0.5" id="modalHours">Senin - Jumat: 08:00 - 14:00</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5"><i class="fa-solid fa-phone"></i></div>
                        <div>
                            <p class="text-slate-500 text-xs font-medium uppercase tracking-wide">Nomor Telepon</p>
                            <p class="text-slate-800 font-medium mt-0.5" id="modalPhone">031-1234567</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <p class="text-slate-500 text-xs font-medium uppercase tracking-wide mb-3">Layanan Tersedia</p>
                    <div id="modalServices" class="flex flex-col">
                        <!-- services container dynamically built by JS -->
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="p-4 sm:p-6 bg-slate-50 border-t border-slate-100 flex gap-3 shrink-0">
                <button onclick="closeDetail()" class="flex-1 py-3 px-4 bg-white border border-slate-300 text-slate-700 font-bold rounded-xl hover:bg-slate-100 transition-colors">Tutup</button>
                <button id="modalBtnDaftar" class="flex-1 py-3 px-4 bg-slate-200 text-slate-400 font-bold rounded-xl cursor-not-allowed transition-colors text-center flex items-center justify-center gap-2" disabled>Daftar Berobat</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function quickSearch(keyword) {
            document.getElementById('searchInput').value = keyword;
            submitSearch();
        }

        function handleSearch(e) {
            e.preventDefault();
            submitSearch();
        }

        function submitSearch() {
            const q = document.getElementById('searchInput').value;
            const tipe = document.getElementById('filterTipe').value;
            const wilayah = document.getElementById('filterWilayah').value;
            const sort = document.getElementById('filterSort').value;
            
            const params = new URLSearchParams({
                q: q,
                tipe: tipe,
                wilayah: wilayah,
                sort: sort
            });

            const newUrl = 'find.php?' + params.toString();
            window.history.pushState({path: newUrl}, '', newUrl);

            document.getElementById('facilityListContainer').style.opacity = '0.5';

            fetch('find.php?ajax=1&' + params.toString())
                .then(response => response.text())
                .then(html => {
                    const container = document.getElementById('facilityListContainer');
                    container.innerHTML = html;
                    container.style.opacity = '1';
                })
                .catch(err => {
                    console.error('AJAX Error:', err);
                    window.location.href = newUrl;
                });
        }

        window.addEventListener('popstate', function(e) {
            window.location.reload();
        });

        const modal = document.getElementById('detailModal');
        const modalContent = document.getElementById('detailModalContent');

        function showDetail(e, data) {
            e.stopPropagation();
            
            document.getElementById('modalTitle').textContent = data.nama;
            
            const isRS = data.kategori.toLowerCase() === 'rumah sakit';
            const badge = document.getElementById('modalTypeBadge');
            badge.textContent = (isRS ? '🔴 ' : '🟢 ') + data.kategori;
            badge.className = `inline-block px-3 py-1 rounded-full text-xs font-bold mb-3 backdrop-blur-md ${isRS ? 'bg-rose-500/20 text-rose-100' : 'bg-emerald-500/20 text-emerald-100'}`;
            
            document.getElementById('modalAddress').textContent = data.alamat || '-';
            document.getElementById('modalRegion').textContent = data.wilayah || '-';
            document.getElementById('modalHours').innerHTML = data.jam_pelayanan || '-';
            document.getElementById('modalPhone').textContent = data.telepon || '-';
            
            const servicesContainer = document.getElementById('modalServices');
            servicesContainer.innerHTML = '';
            
            if (data.layanan && data.layanan.length > 0) {
                const total = data.layanan.length;
                const maxInitial = 8;
                
                // NEW STATE VARIABLES
                let selectedPoliName = null;
                const btnDaftar = document.getElementById('modalBtnDaftar');
                
                // RESET BUTTON STATE
                btnDaftar.disabled = true;
                btnDaftar.className = 'flex-1 py-3 px-4 bg-slate-200 text-slate-400 font-bold rounded-xl cursor-not-allowed transition-colors text-center flex items-center justify-center gap-2';
                btnDaftar.innerHTML = 'Daftar Berobat';
                
                function handlePoliClick(chip, poliName) {
                    // Cek jika poli yang diklik adalah yang sudah terpilih (Undo)
                    if (selectedPoliName === poliName) {
                        chip.classList.remove('bg-primary', 'text-white', 'border-primary', 'shadow-md');
                        chip.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
                        chip.innerHTML = poliName;
                        
                        selectedPoliName = null;
                        
                        btnDaftar.disabled = true;
                        btnDaftar.className = 'flex-1 py-3 px-4 bg-slate-200 text-slate-400 font-bold rounded-xl cursor-not-allowed transition-colors text-center flex items-center justify-center gap-2';
                        btnDaftar.innerHTML = 'Daftar Berobat';
                        return;
                    }

                    // Remove selected from all chips
                    document.querySelectorAll('.modal-service-chip').forEach(c => {
                        c.classList.remove('bg-primary', 'text-white', 'border-primary', 'shadow-md');
                        c.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
                        c.innerHTML = c.dataset.name;
                    });
                    
                    // Add selected state to clicked
                    chip.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
                    chip.classList.add('bg-primary', 'text-white', 'border-primary', 'shadow-md');
                    chip.innerHTML = poliName + ' <i class="fa-solid fa-check ml-1"></i>';
                    
                    selectedPoliName = poliName;
                    
                    // Enable button
                    btnDaftar.disabled = false;
                    btnDaftar.className = 'flex-1 py-3 px-4 bg-primary text-white font-bold rounded-xl hover:bg-primaryDark transition-colors text-center shadow-md flex items-center justify-center gap-2';
                    btnDaftar.innerHTML = 'Daftar Berobat <i class="fa-solid fa-arrow-right ml-1"></i>';
                }

                btnDaftar.onclick = function() {
                    if (selectedPoliName) {
                        window.location.href = 'book.php?faskes_id=' + data.id + '&poli_nama=' + encodeURIComponent(selectedPoliName);
                    }
                };
                
                // Primary container for initial chips
                const primaryContainer = document.createElement('div');
                primaryContainer.className = 'flex flex-wrap gap-2';
                
                // Render initial chips (up to 8, or 6 if there are more)
                const initialCount = total > maxInitial ? 6 : total;
                for (let i = 0; i < initialCount; i++) {
                    const span = document.createElement('button');
                    span.className = 'modal-service-chip px-3 py-1.5 bg-white text-slate-700 text-xs rounded-lg font-semibold border border-slate-200 hover:border-[#A57BD7] transition-all text-left focus:outline-none focus:ring-2 focus:ring-[#A57BD7] focus:border-transparent';
                    span.dataset.name = data.layanan[i];
                    span.textContent = data.layanan[i];
                    span.onclick = () => handlePoliClick(span, data.layanan[i]);
                    primaryContainer.appendChild(span);
                }
                
                servicesContainer.appendChild(primaryContainer);
                
                if (total > maxInitial) {
                    const remaining = total - initialCount;
                    
                    const remainingText = document.createElement('p');
                    remainingText.className = 'text-slate-500 text-xs font-medium mt-3';
                    remainingText.textContent = `+ ${remaining} layanan lainnya`;
                    servicesContainer.appendChild(remainingText);
                    
                    const toggleBtn = document.createElement('button');
                    toggleBtn.className = 'text-primary text-sm font-semibold mt-2 hover:underline flex items-center gap-1 w-fit';
                    toggleBtn.innerHTML = 'Lihat semua layanan <i class="fa-solid fa-chevron-down text-[10px] ml-1 mt-0.5"></i>';
                    
                    const allServicesContainer = document.createElement('div');
                    allServicesContainer.className = 'hidden mt-4 bg-slate-50 border border-slate-200 rounded-xl overflow-hidden flex flex-col transition-all';
                    
                    // Search input
                    const searchBox = document.createElement('div');
                    searchBox.className = 'p-3 border-b border-slate-200 bg-white relative';
                    searchBox.innerHTML = '<i class="fa-solid fa-magnifying-glass absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i><input type="text" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-9 pr-3 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" placeholder="Cari layanan..." onkeyup="filterModalServices(this.value)">';
                    allServicesContainer.appendChild(searchBox);
                    
                    // Scrollable list
                    const listContainer = document.createElement('div');
                    listContainer.className = 'p-4 max-h-[250px] overflow-y-auto custom-scrollbar flex flex-wrap gap-2 items-start content-start';
                    listContainer.id = 'allServicesList';
                    
                    data.layanan.forEach(l => {
                        const span = document.createElement('button');
                        span.className = 'modal-service-chip px-3 py-1.5 bg-white text-slate-700 text-xs rounded-lg font-semibold border border-slate-200 hover:border-[#A57BD7] transition-all text-left shadow-sm focus:outline-none focus:ring-2 focus:ring-[#A57BD7] focus:border-transparent';
                        span.dataset.name = l;
                        span.textContent = l;
                        span.onclick = () => handlePoliClick(span, l);
                        listContainer.appendChild(span);
                    });
                    
                    allServicesContainer.appendChild(listContainer);
                    
                    toggleBtn.onclick = function() {
                        const isHidden = allServicesContainer.classList.contains('hidden');
                        if (isHidden) {
                            allServicesContainer.classList.remove('hidden');
                            toggleBtn.innerHTML = 'Sembunyikan layanan <i class="fa-solid fa-chevron-up text-[10px] ml-1 mt-0.5"></i>';
                        } else {
                            allServicesContainer.classList.add('hidden');
                            toggleBtn.innerHTML = 'Lihat semua layanan <i class="fa-solid fa-chevron-down text-[10px] ml-1 mt-0.5"></i>';
                        }
                    };
                    
                    servicesContainer.appendChild(toggleBtn);
                    servicesContainer.appendChild(allServicesContainer);
                }
            } else {
                servicesContainer.innerHTML = '<span class="text-slate-400 text-sm">Tidak ada data layanan</span>';
                const btnDaftar = document.getElementById('modalBtnDaftar');
                btnDaftar.disabled = true;
                btnDaftar.className = 'flex-1 py-3 px-4 bg-slate-200 text-slate-400 font-bold rounded-xl cursor-not-allowed transition-colors text-center flex items-center justify-center gap-2';
                btnDaftar.innerHTML = 'Daftar Berobat';
            }
            
            modal.classList.remove('hidden');
            void modal.offsetWidth;
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }

        function closeDetail() {
            modal.classList.add('opacity-0');
            modalContent.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        modal.addEventListener('click', (e) => {
            if(e.target === modal) closeDetail();
        });
        
        function filterModalServices(val) {
            const lowerVal = val.toLowerCase();
            const chips = document.querySelectorAll('#allServicesList .modal-service-chip');
            chips.forEach(chip => {
                if (chip.textContent.toLowerCase().includes(lowerVal)) {
                    chip.style.display = '';
                } else {
                    chip.style.display = 'none';
                }
            });
        }

        function requestLocation() {
            const btn = document.getElementById('btnLocation');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mencari lokasi Anda...';
            btn.disabled = true;

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        const url = new URL(window.location);
                        url.searchParams.set('lat', lat);
                        url.searchParams.set('lng', lng);
                        window.location.href = url.toString();
                    },
                    function(error) {
                        console.error(error);
                        alert("Lokasi tidak dapat ditemukan. Pastikan izin lokasi pada browser telah diberikan. Menampilkan fasilitas berdasarkan area Surabaya.");
                        
                        const lat = -7.250445;
                        const lng = 112.768845;
                        const url = new URL(window.location);
                        url.searchParams.set('lat', lat);
                        url.searchParams.set('lng', lng);
                        window.location.href = url.toString();
                    },
                    { timeout: 10000 }
                );
            } else {
                alert("Geolocation tidak didukung oleh browser Anda.");
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        <?php if ($lat !== null && $lng !== null): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const userLat = <?php echo $lat; ?>;
            const userLng = <?php echo $lng; ?>;
            
            const map = L.map('facilityMap').setView([userLat, userLng], 13);
            
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
            }).addTo(map);

            const userIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<div class='w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-[0_0_10px_rgba(59,130,246,0.8)]'></div>",
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });
            L.marker([userLat, userLng], {icon: userIcon}).addTo(map).bindPopup("Lokasi Anda").openPopup();

            const bounds = L.latLngBounds();
            bounds.extend([userLat, userLng]);

            <?php
            foreach ($fasilitas as $index => $f) {
                if ($f['latitude'] !== null && $f['longitude'] !== null) {
                    echo "
                    var marker_$index = L.marker([{$f['latitude']}, {$f['longitude']}]).addTo(map)
                        .bindPopup(`<b>" . addslashes($f['nama']) . "</b><br>± " . number_format($f['distance_km'], 1) . " km`);
                    bounds.extend([{$f['latitude']}, {$f['longitude']}]);
                    
                    const card_$index = document.getElementById('facility-card-{$f['id']}');
                    if (card_$index) {
                        card_$index.addEventListener('mouseenter', function() {
                            marker_$index.openPopup();
                        });
                    }
                    ";
                }
            }
            ?>
            map.fitBounds(bounds, {padding: [50, 50]});
        });
        <?php endif; ?>
    </script>
    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 16px); }
        .custom-scrollbar::-webkit-scrollbar,
        #facilityListContainer::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track,
        #facilityListContainer::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb,
        #facilityListContainer::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover,
        #facilityListContainer::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }
    </style>
</body>
</html>



