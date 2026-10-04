<?php
header('Content-Type: application/json');

// MOCK DATA
// Belum terhubung dengan tabel jadwal/slot (karena tabel tersebut belum ada di database).
// Digunakan hanya untuk prototype UI dan testing alur AJAX.

$faskes_id = isset($_GET['faskes_id']) ? intval($_GET['faskes_id']) : 0;
$poli_id = isset($_GET['poli_id']) ? intval($_GET['poli_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Simulate different slot availability based on date to show dynamic UI changes
$day = date('w', strtotime($date));

if ($day == 0) { // Sunday (Minggu)
    // Return empty array to simulate fully closed
    echo json_encode([]);
    exit;
}

$slots = [];

if ($day == 6) { // Saturday (Sabtu) - Half day
    $slots = [
        ["time" => "08:00", "available" => true, "remaining" => 5],
        ["time" => "09:00", "available" => true, "remaining" => 2],
        ["time" => "10:00", "available" => true, "remaining" => 1],
        ["time" => "11:00", "available" => false, "remaining" => 0]
    ];
} else { // Weekdays
    // Some pseudo-randomness based on date string to make it look dynamic
    $hash = crc32($date . $faskes_id);
    
    $is_full_day = ($hash % 4 == 0); // 25% chance of being full
    
    if ($is_full_day) {
        $slots = [
            ["time" => "08:00", "available" => false, "remaining" => 0],
            ["time" => "09:00", "available" => false, "remaining" => 0],
            ["time" => "10:00", "available" => false, "remaining" => 0]
        ];
    } else {
        $slots = [
            ["time" => "08:00", "available" => true, "remaining" => ($hash % 10) + 5],
            ["time" => "09:00", "available" => true, "remaining" => ($hash % 5) + 1],
            ["time" => "10:00", "available" => (($hash % 3) != 0), "remaining" => (($hash % 3) != 0) ? 2 : 0],
            ["time" => "11:00", "available" => false, "remaining" => 0],
            ["time" => "13:00", "available" => true, "remaining" => 8],
            ["time" => "14:00", "available" => true, "remaining" => 12]
        ];
    }
}

echo json_encode($slots);
?>
