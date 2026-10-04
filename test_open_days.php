<?php
require_once 'config/helpers.php';

function parseOpenDays($raw_jam) {
    if (empty($raw_jam) || $raw_jam === '-') {
        return []; // or all? let's return all to be safe if no data
    }
    
    // If it contains 24 Jam, open all days
    if (stripos($raw_jam, '24 jam') !== false) {
        return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    }

    $formatted = formatJamPelayanan($raw_jam);
    // Remove extra info
    $parts = explode("<br>\n<span", $formatted);
    $main_text = $parts[0];
    
    $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    $open_days = [];
    
    $lines = explode("<br>\n", $main_text);
    foreach ($lines as $line) {
        // e.g., "Senin–Kamis: 07.30–14.30"
        $line = trim(strip_tags($line));
        if (strpos($line, ':') !== false) {
            $day_part = explode(':', $line)[0];
            $day_part = trim($day_part);
            
            if (strpos($day_part, '–') !== false) {
                // Range
                $range = explode('–', $day_part);
                $start = trim($range[0]);
                $end = trim($range[1]);
                
                $start_idx = array_search($start, $days);
                $end_idx = array_search($end, $days);
                
                if ($start_idx !== false && $end_idx !== false) {
                    for ($i = $start_idx; $i <= $end_idx; $i++) {
                        $open_days[] = $days[$i];
                    }
                }
            } else {
                // Single day or multiple days separated by comma?
                if (in_array($day_part, $days)) {
                    $open_days[] = $day_part;
                }
            }
        } else {
            // What if it's just time?
            // formatJamPelayanan prepends "Senin–Kamis: " to just-time segments now.
            // But let's be safe.
        }
    }
    
    return array_unique($open_days);
}

$test_cases = [
    "Sen-Kam 07.30-14.30; Jum 07.30-11.30; Sab 07.30-13.00",
    "Setiap Hari 24 Jam",
    "Senin-Sabtu 08.00-20.00; Minggu Tutup",
    "07.30-14.30" // missing day
];

foreach ($test_cases as $tc) {
    echo "Raw: $tc\n";
    echo "Open: " . implode(", ", parseOpenDays($tc)) . "\n\n";
}
?>
