<?php
require 'generate_dummy.php'; // Wait, I can't require it directly without running it. I will copy the function.

function testParse($jam_str) {
    // Basic normalization
    $jam_str = strtolower(trim($jam_str));
    $jam_str = str_replace(['–', '-'], '-', $jam_str);
    $jam_str = str_replace(' rawat jalan : ', '', $jam_str);
    
    // Default schedule if we can't parse
    $schedule = [];
    
    // Extract time patterns
    $parts = explode(';', $jam_str);
    $parsed_days = [0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[], 6=>[]];
    $has_parsed = false;
    $last_days = [];
    
    foreach ($parts as $part) {
        $part = trim($part);
        if (empty($part)) continue;
        
        // Find time like 07.30-14.30 or 07:30 - 14:30
        if (preg_match('/(\d{1,2})[.:](\d{2})\s*-\s*(\d{1,2})[.:](\d{2})/', $part, $matches)) {
            $start = sprintf("%02d:%02d", $matches[1], $matches[2]);
            $end = sprintf("%02d:%02d", $matches[3], $matches[4]);
            $has_parsed = true;
            
            $current_days = [];
            
            if (strpos($part, 'senin') !== false && strpos($part, 'jumat') !== false) {
                for($i=1; $i<=5; $i++) $current_days[] = $i;
            }
            elseif (strpos($part, 'senin') !== false && strpos($part, 'kamis') !== false) {
                for($i=1; $i<=4; $i++) $current_days[] = $i;
            }
            elseif (strpos($part, 'sen') !== false && strpos($part, 'kam') !== false) {
                for($i=1; $i<=4; $i++) $current_days[] = $i;
            }
            elseif (strpos($part, 'senin kamis') !== false) {
                for($i=1; $i<=4; $i++) $current_days[] = $i;
            }
            elseif (strpos($part, 'jumat') !== false || strpos($part, 'jum') !== false) {
                $current_days[] = 5;
            }
            elseif (strpos($part, 'sabtu') !== false || strpos($part, 'sab') !== false) {
                $current_days[] = 6;
            }
            elseif (strpos($part, 'minggu') !== false || strpos($part, 'min') !== false) {
                $current_days[] = 0;
            }
            
            if (empty($current_days) && !empty($last_days)) {
                $current_days = $last_days; // Inherit from previous part if no days explicitly mentioned
            }
            
            foreach ($current_days as $d) {
                $parsed_days[$d][] = [$start, $end];
            }
            
            if (!empty($current_days)) {
                $last_days = $current_days;
            }
        }
    }
    
    return $parsed_days;
}

print_r(testParse("Sen-Kam 07.30-14.30; Jum 07.30-11.30; Sab 07.30-13.00"));
?>
