<?php
header('Content-Type: application/json');

$faskes_id = isset($_GET['faskes_id']) ? intval($_GET['faskes_id']) : 0;
$poli_id = isset($_GET['poli_id']) ? intval($_GET['poli_id']) : 0;
$days_to_load = isset($_GET['days']) ? intval($_GET['days']) : 7;

$availability = [];
$today = new DateTime();

$dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

for ($i = 0; $i < $days_to_load; $i++) {
    $currentDate = clone $today;
    $currentDate->modify("+$i days");
    
    $dateStr = $currentDate->format('Y-m-d');
    $dayOfWeek = $currentDate->format('w');
    $dayName = $dayNames[$dayOfWeek];
    $dateNum = $currentDate->format('d');
    $monthName = $monthNames[$currentDate->format('n') - 1];
    
    if ($dayOfWeek == 0) { // Sunday
        $availability[] = [
            "date" => $dateStr,
            "day" => $dayName,
            "date_display" => $dateNum . ' ' . $monthName,
            "status" => "NO_SCHEDULE",
            "message" => "Tidak ada jadwal",
            "total_remaining" => 0
        ];
        continue;
    }
    
    $totalRemaining = 0;
    $slots_exist = false;
    
    if ($dayOfWeek == 6) { // Saturday
        $slots = [
            ["time" => "08:00", "available" => true, "remaining" => 5],
            ["time" => "09:00", "available" => true, "remaining" => 2],
            ["time" => "10:00", "available" => true, "remaining" => 1],
            ["time" => "11:00", "available" => false, "remaining" => 0]
        ];
        $slots_exist = true;
    } else { // Weekdays
        $hash = crc32($dateStr . $faskes_id);
        $is_full_day = ($hash % 4 == 0); // Must match get_slots.php logic!
        
        if ($is_full_day) {
            $slots = [
                ["time" => "08:00", "available" => false, "remaining" => 0],
                ["time" => "09:00", "available" => false, "remaining" => 0],
                ["time" => "10:00", "available" => false, "remaining" => 0]
            ];
            $slots_exist = true;
        } else {
            $slots = [
                ["time" => "08:00", "available" => true, "remaining" => ($hash % 10) + 5],
                ["time" => "09:00", "available" => true, "remaining" => ($hash % 5) + 1],
                ["time" => "10:00", "available" => (($hash % 3) != 0), "remaining" => (($hash % 3) != 0) ? 2 : 0],
                ["time" => "11:00", "available" => false, "remaining" => 0],
                ["time" => "13:00", "available" => true, "remaining" => 8],
                ["time" => "14:00", "available" => true, "remaining" => 12]
            ];
            $slots_exist = true;
        }
    }
    
    foreach ($slots as $slot) {
        if ($slot['available']) {
            $totalRemaining += $slot['remaining'];
        }
    }
    
    $status = "";
    $message = "";
    
    if (!$slots_exist) {
        $status = "NO_SCHEDULE";
        $message = "Tidak ada jadwal";
    } else if ($totalRemaining >= 10) {
        $status = "AVAILABLE";
        $message = $totalRemaining . " antrean tersedia";
    } else if ($totalRemaining > 0) {
        $status = "LIMITED";
        $message = $totalRemaining . " antrean tersedia";
    } else {
        $status = "FULL";
        $message = "Antrean penuh";
    }

    $availability[] = [
        "date" => $dateStr,
        "day" => $dayName,
        "date_display" => $dateNum . ' ' . $monthName,
        "status" => $status,
        "message" => $message,
        "total_remaining" => $totalRemaining
    ];
}

echo json_encode($availability);
?>
