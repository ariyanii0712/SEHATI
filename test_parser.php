<?php
require_once 'config/database.php';

function parseJamPelayanan($jam_str) {
    // Basic normalization
    $jam_str = strtolower(trim($jam_str));
    $jam_str = str_replace(['–', '-'], '-', $jam_str);
    $jam_str = str_replace(' rawat jalan : ', '', $jam_str);
    
    // Default schedule if we can't parse
    $schedule = [
        1 => ['08:00', '12:00'],
        2 => ['08:00', '12:00'],
        3 => ['08:00', '12:00'],
        4 => ['08:00', '12:00'],
        5 => ['08:00', '11:00'],
        6 => null, // Sab
        0 => null  // Min
    ];
    
    // Extract time patterns
    $parts = explode(';', $jam_str);
    foreach ($parts as $part) {
        $part = trim($part);
        if (empty($part)) continue;
        
        // Find time like 07.30-14.30 or 07:30 - 14:30
        if (preg_match('/(\d{1,2})[.:](\d{2})\s*-\s*(\d{1,2})[.:](\d{2})/', $part, $matches)) {
            $start = sprintf("%02d:%02d", $matches[1], $matches[2]);
            $end = sprintf("%02d:%02d", $matches[3], $matches[4]);
            
            if (strpos($part, 'senin') !== false && strpos($part, 'jumat') !== false) {
                for($i=1; $i<=5; $i++) $schedule[$i] = [$start, $end];
            }
            elseif (strpos($part, 'senin') !== false && strpos($part, 'kamis') !== false) {
                for($i=1; $i<=4; $i++) $schedule[$i] = [$start, $end];
            }
            elseif (strpos($part, 'sen') !== false && strpos($part, 'kam') !== false) {
                for($i=1; $i<=4; $i++) $schedule[$i] = [$start, $end];
            }
            elseif (strpos($part, 'senin kamis') !== false) {
                for($i=1; $i<=4; $i++) $schedule[$i] = [$start, $end];
            }
            elseif (strpos($part, 'jumat') !== false || strpos($part, 'jum') !== false) {
                $schedule[5] = [$start, $end];
            }
            elseif (strpos($part, 'sabtu') !== false || strpos($part, 'sab') !== false) {
                $schedule[6] = [$start, $end];
            }
            elseif (strpos($part, 'minggu') !== false || strpos($part, 'min') !== false) {
                $schedule[0] = [$start, $end];
            }
        }
    }
    
    return $schedule;
}

$res = $conn->query("SELECT id, nama, jam_pelayanan FROM faskes WHERE nama LIKE '%RSUD%' LIMIT 5");
while($r = $res->fetch_assoc()){
    echo $r['nama'] . " | " . $r['jam_pelayanan'] . "\n";
    print_r(parseJamPelayanan($r['jam_pelayanan']));
    echo "------------------\n";
}
