<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$faskes_id = isset($_GET['faskes_id']) ? intval($_GET['faskes_id']) : 0;
$poli_id = isset($_GET['poli_id']) ? intval($_GET['poli_id']) : 0;
$date_str = isset($_GET['date']) ? trim($_GET['date']) : date('Y-m-d');

if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date_str)) {
    echo json_encode(['error' => 'Invalid date format']);
    exit;
}

$date_obj = new DateTime($date_str);
$day_name = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')[$date_obj->format('w')];

// Formatting to match frontend 'd M' like '12 Okt'
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$date_formatted = $date_obj->format('d') . ' ' . $months[$date_obj->format('n') - 1];

// 1. Fetch all schedule slots for the selected date
$slots = [];
$stmt = $conn->prepare("SELECT * FROM jadwal_poli WHERE faskes_id = ? AND poli_id = ? AND tanggal = ? ORDER BY waktu_mulai ASC");
$stmt->bind_param("iis", $faskes_id, $poli_id, $date_str);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $slots[] = $row;
}

// 2. Fetch all registrations for this date
$pendaftar = [];
$stmt2 = $conn->prepare("SELECT waktu_kunjungan, COUNT(*) as cnt FROM pendaftaran WHERE faskes_id = ? AND poli_id = ? AND tanggal_kunjungan = ? AND status != 'batal' GROUP BY waktu_kunjungan");
$stmt2->bind_param("iis", $faskes_id, $poli_id, $date_str);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($row = $res2->fetch_assoc()) {
    $pendaftar[$row['waktu_kunjungan']] = $row['cnt'];
}

// 3. Build the availability array
$day_slots = [];
$total_sisa = 0;
$has_schedule = false;

foreach ($slots as $slot) {
    $has_schedule = true;
    $terdaftar = isset($pendaftar[$slot['waktu_mulai']]) ? $pendaftar[$slot['waktu_mulai']] : 0;
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

$status = 'NO_SCHEDULE';
$status_text = 'Tidak ada jadwal';

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

$response = [
    'date' => $date_str,
    'day_name' => $day_name,
    'date_formatted' => $date_formatted,
    'status' => $status,
    'status_text' => $status_text,
    'total_sisa' => $total_sisa,
    'slots' => $day_slots
];

echo json_encode($response);
?>
