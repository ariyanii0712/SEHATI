<?php
require_once 'generate_dummy.php';

$jam_str = "Pagi: Senin-Kamis 07.00-14.30; Jumat 07.00-11.30; Sabtu 07.00-13.00; Sore: Senin-Jumat 14.30-17.30";
$parsed = parseJamPelayanan($jam_str);
print_r($parsed);
?>
