<?php

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

        // Replace any hyphen/dash between days with en dash
        $segment = preg_replace('/([a-zA-Z]+)\s*[-–—]\s*([a-zA-Z]+)/u', '$1–$2', $segment);

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
            $segment = preg_replace('/(?<![a-zA-Z])' . $short . '(?![a-zA-Z])/iu', $full, $segment);
        }

        // Insert colon if there is a day or day range followed by time
        if (preg_match('/^([A-Za-z–]+)\s+([\d\.\:\-\/–—\s]+[a-zA-Z0-9\s]*)$/u', $segment, $matches)) {
            $day_part = $matches[1];
            $time_part = trim($matches[2]);
            // Replace hyphen in time with en dash if it's between numbers
            $time_part = preg_replace('/(\d[\d\.]*)\s*[-–—]\s*([\d\.]+)/u', '$1–$2', $time_part);
            $segment = $day_part . ': ' . $time_part;
        } else if (preg_match('/^[\d\.\:\-\/–—\s]+$/u', $segment)) {
            // If it's just time
            $segment = preg_replace('/(\d[\d\.]*)\s*[-–—]\s*([\d\.]+)/u', '$1–$2', $segment);
        }

        $formatted_segments[] = $segment;
    }

    $result = implode("<br>\n", $formatted_segments);

    if ($extra_info) {
        $result .= "<br>\n<span class='text-xs text-slate-500 mt-1 block'>" . htmlspecialchars($extra_info) . "</span>";
    }

    return $result;
}

$tests = [
    "Sen–Kam 07.30–14.30; Jum 07.30–11.30; Sab 07.30–13.00",
    "Sen-Kam 07.00-14.00; Jum 07.00-11.30; Sab 07.30-13.00",
    "Sen–Kam 07.00–14.30; Jum 07.00–11.30; Sab 07.00–13.00; sore",
    "07.15–14.25",
    "Sen–Sab 07.00/07.30–14.30; sore tersedia",
    "Sen–Kam 07.30–14.30; Jum 07.30–11.30; Sab 07.30–13.00 | Layanan 24 Jam: Rawat Inap Umum & Bersalin"
];

foreach ($tests as $t) {
    echo "IN : $t\n";
    echo "OUT:\n" . formatJamPelayanan($t) . "\n";
    echo "-------------------\n";
}
