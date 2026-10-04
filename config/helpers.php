<?php
/**
 * Helper functions for SEHATI
 */

if (!function_exists('formatJamPelayanan')) {
    function formatJamPelayanan($raw_string) {
        if (empty($raw_string) || $raw_string === '-') return '-';

        // Split by '|' to separate main hours from extra info
        $parts = explode('|', $raw_string);
        $main_hours = trim($parts[0]);
        $extra_info = isset($parts[1]) ? trim($parts[1]) : '';

        // Split main hours by ';'
        $segments = explode(';', $main_hours);
        
        $formatted_segments = [];
        foreach ($segments as $segment) {
            $segment = trim($segment);
            if (empty($segment)) continue;

            // Enforce valid UTF-8 to prevent preg_* /u modifiers from returning NULL
            // If it's invalid UTF-8, it's likely Windows-1252 from the DB, so we convert it safely.
            if (!mb_check_encoding($segment, 'UTF-8')) {
                $segment = mb_convert_encoding($segment, 'UTF-8', 'Windows-1252');
            }

            // Replace any hyphen/dash between days with en dash
            $new_seg = preg_replace('/([a-zA-Z]+)\s*[-–—]\s*([a-zA-Z]+)/u', '$1–$2', $segment);
            if ($new_seg !== null) $segment = $new_seg;

            // Replace short names (case insensitive, bounded by non-letters)
            $map = [
                'Sen' => 'Senin',
                'Sel' => 'Selasa',
                'Rab' => 'Rabu',
                'Kam' => 'Kamis',
                'Jum' => 'Jumat',
                'Sab' => 'Sabtu',
                'Min' => 'Minggu',
                'Ming' => 'Minggu'
            ];

            foreach ($map as $short => $full) {
                $new_seg = preg_replace('/(?<![a-zA-Z])' . $short . '(?![a-zA-Z])/iu', $full, $segment);
                if ($new_seg !== null) $segment = $new_seg;
            }

            // Insert colon if there is a day or day range followed by time
            if (preg_match('/^([A-Za-z–]+)\s+([\d\.\:\-\/–—\,\s]+[a-zA-Z0-9\s]*)$/u', $segment, $matches)) {
                $day_part = $matches[1];
                $time_part = trim($matches[2]);
                // Replace hyphen in time with en dash if it's between numbers
                $new_time = preg_replace('/(\d[\d\.]*)\s*[-–—]\s*([\d\.]+)/u', '$1–$2', $time_part);
                if ($new_time !== null) $time_part = $new_time;
                $segment = $day_part . ': ' . $time_part;
            } else if (preg_match('/^[\d\.\:\-\/–—\s]+$/u', $segment)) {
                // If it's just time
                $new_seg = preg_replace('/(\d[\d\.]*)\s*[-–—]\s*([\d\.]+)/u', '$1–$2', $segment);
                if ($new_seg !== null) $segment = $new_seg;
                
                // As per user request, if it's missing the day prefix, assume "Senin-Kamis"
                $segment = 'Senin–Kamis: ' . trim($segment);
            }

            $formatted_segments[] = $segment;
        }

        $result = implode("<br>\n", $formatted_segments);

        if ($extra_info) {
            $result .= "<br>\n<span class='text-xs text-slate-500 mt-1 block'>" . htmlspecialchars($extra_info) . "</span>";
        }

        return $result;
    }
}

if (!function_exists('getOpenDays')) {
    function getOpenDays($raw_jam) {
        if (empty($raw_jam) || $raw_jam === '-') {
            return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        }
        
        $raw_parts = explode('|', $raw_jam);
        if (stripos(trim($raw_parts[0]), '24 jam') !== false) {
            return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        }

        $formatted = formatJamPelayanan($raw_jam);
        $parts = explode("<br>\n<span", $formatted);
        $main_text = $parts[0];
        
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $open_days = [];
        
        $lines = explode("<br>\n", $main_text);
        foreach ($lines as $line) {
            $line = trim(strip_tags($line));
            // Ignore if explicitly says "Tutup" or similar
            if (stripos($line, 'tutup') !== false || stripos($line, 'libur') !== false) {
                continue;
            }
            
            if (strpos($line, ':') !== false) {
                $day_part = explode(':', $line)[0];
                $day_part = trim($day_part);
                
                if (strpos($day_part, '–') !== false) {
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
                    if (in_array($day_part, $days)) {
                        $open_days[] = $day_part;
                    }
                }
            }
        }
        
        return array_unique($open_days);
    }
}
