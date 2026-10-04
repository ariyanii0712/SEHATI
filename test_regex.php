<?php
$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$line = "Pagi: Senin–Kamis 07.00-14.30";
$open_days = [];
if (preg_match_all('/([A-Za-z]+)\s*[-–—]\s*([A-Za-z]+)/u', $line, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $start = ucfirst(strtolower(trim($match[1])));
        $end = ucfirst(strtolower(trim($match[2])));
        echo "start=$start, end=$end\n";
        
        $start_idx = array_search($start, $days);
        $end_idx = array_search($end, $days);
        
        echo "start_idx=$start_idx, end_idx=$end_idx\n";
        if ($start_idx !== false && $end_idx !== false && $start_idx <= $end_idx) {
            for ($i = $start_idx; $i <= $end_idx; $i++) {
                $open_days[] = $days[$i];
            }
        }
    }
}
print_r($open_days);
?>
