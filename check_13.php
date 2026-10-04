<?php
require 'config/helpers.php';
$jam_str = strtolower("24 Jam (IGD) <br> Rawat Jalan : Senin-Jumat 09.00 - 14.00");
$days = [];
if (strpos($jam_str, 'jumat') !== false || strpos($jam_str, 'jum') !== false) $days[] = 'Jumat';
if (strpos($jam_str, 'minggu') !== false || strpos($jam_str, 'min') !== false) $days[] = 'Minggu';

print_r($days);
?>
