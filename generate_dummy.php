<?php
require_once 'config/database.php';

function parseJamPelayanan($jam_str) {
    // Basic normalization
    $jam_str = strtolower(trim($jam_str));
    $jam_str = str_replace(['–', '-'], '-', $jam_str);
    $jam_str = str_replace(' rawat jalan : ', '', $jam_str);
    
    // Default schedule if we can't parse
    $schedule = [
        1 => [['08:00', '12:00']],
        2 => [['08:00', '12:00']],
        3 => [['08:00', '12:00']],
        4 => [['08:00', '12:00']],
        5 => [['08:00', '11:00']],
        6 => null, // Sab
        0 => null  // Min
    ];
    
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
    
    if (strpos($jam_str, '07.15-14.25') !== false) {
        $has_parsed = true;
        for($i=1; $i<=4; $i++) $parsed_days[$i] = [['07:15', '14:25']];
        $parsed_days[5] = [['07:15', '11:15']];
    }
    
    if ($has_parsed) {
        foreach ($parsed_days as $d => $arr) {
            if (empty($arr)) $parsed_days[$d] = null;
        }
        return $parsed_days;
    }
    
    return $schedule;
}

function splitIntoSlots($start_time, $end_time) {
    $slots = [];
    $curr_time = strtotime($start_time);
    $end_time_ts = strtotime($end_time);

    while ($curr_time < $end_time_ts) {
        $next_time = strtotime('+1 hour', $curr_time);
        if ($next_time > $end_time_ts) {
            $next_time = $end_time_ts;
        }
        
        $slot_start = date('H:i', $curr_time);
        $slot_end = date('H:i', $next_time);
        
        $slots[] = [$slot_start, $slot_end];
        
        $curr_time = $next_time;
    }
    
    return $slots;
}

$conn->query("DELETE FROM jadwal_poli");
$conn->query("DELETE FROM pendaftaran WHERE id >= 9000000"); // clean our dummy pendaftaran
echo "Deleted old schedules.\n";

$faskes_res = $conn->query("SELECT id, nama, jam_pelayanan FROM faskes");
$total_inserted = 0;
$total_pend = 0;

$dummy_id_counter = 9000000;

$jadwal_values = [];
$pend_values = [];

$conn->begin_transaction();

while ($faskes = $faskes_res->fetch_assoc()) {
    $faskes_id = $faskes['id'];
    $schedule = parseJamPelayanan($faskes['jam_pelayanan']);
    
    $poli_stmt = $conn->prepare("SELECT p.id, p.nama_poli FROM poli p JOIN faskes_layanan fl ON p.id = fl.poli_id WHERE fl.faskes_id = ?");
    $poli_stmt->bind_param("i", $faskes_id);
    $poli_stmt->execute();
    $poli_res = $poli_stmt->get_result();
    
    $polis = [];
    while ($p = $poli_res->fetch_assoc()) {
        $polis[] = $p;
    }
    
    if (empty($polis)) continue;
    
    foreach ($polis as $poli) {
        $poli_id = $poli['id'];
        $poli_nama = strtolower($poli['nama_poli']);
        
        for ($i = 0; $i < 30; $i++) {
            $date = date('Y-m-d', strtotime("+$i days"));
            $day_of_week = date('w', strtotime($date));
            
            $day_blocks = $schedule[$day_of_week];
            
            // Override time for 'sore' poli
            if ($day_blocks && strpos($poli_nama, 'sore') !== false) {
                $day_blocks = [['15:00', '18:00']];
            }
            
            if ($day_blocks) {
                foreach ($day_blocks as $block) {
                    $hour_slots = splitIntoSlots($block[0], $block[1]);
                    
                    foreach ($hour_slots as $idx => $slot) {
                        $waktu_mulai = $slot[0] . ':00';
                        $waktu_selesai = $slot[1] . ':00';
                        $kuota = 10;
                        
                        $jadwal_values[] = "($faskes_id, $poli_id, '$date', '$waktu_mulai', '$waktu_selesai', $kuota)";
                        $total_inserted++;
                        
                        $seed = ($faskes_id + $poli_id + $i + $idx) % 5;
                        $terpakai = 0;
                        if ($seed == 0) $terpakai = 10;
                        else if ($seed == 1) $terpakai = 8;
                        else if ($seed == 2) $terpakai = 5;
                        else if ($seed == 3) $terpakai = 2;
                        
                        for ($k = 0; $k < $terpakai; $k++) {
                            $pend_values[] = "($dummy_id_counter, $faskes_id, $poli_id, 1, '$date', '$waktu_mulai', 'terdaftar', 'Dummy')";
                            $dummy_id_counter++;
                            $total_pend++;
                        }
                        
                        // Execute batch if large enough
                        if (count($jadwal_values) >= 2000) {
                            $conn->query("INSERT INTO jadwal_poli (faskes_id, poli_id, tanggal, waktu_mulai, waktu_selesai, kuota) VALUES " . implode(',', $jadwal_values));
                            $jadwal_values = [];
                        }
                        if (count($pend_values) >= 2000) {
                            $conn->query("INSERT IGNORE INTO pendaftaran (id, faskes_id, poli_id, patient_id, tanggal_kunjungan, waktu_kunjungan, status, keluhan) VALUES " . implode(',', $pend_values));
                            $pend_values = [];
                        }
                    }
                }
            }
        }
    }
}

if (count($jadwal_values) > 0) {
    $conn->query("INSERT INTO jadwal_poli (faskes_id, poli_id, tanggal, waktu_mulai, waktu_selesai, kuota) VALUES " . implode(',', $jadwal_values));
}
if (count($pend_values) > 0) {
    $conn->query("INSERT IGNORE INTO pendaftaran (id, faskes_id, poli_id, patient_id, tanggal_kunjungan, waktu_kunjungan, status, keluhan) VALUES " . implode(',', $pend_values));
}

$conn->commit();

echo "Successfully inserted $total_inserted dummy slots and $total_pend dummy pendaftaran.\n";
