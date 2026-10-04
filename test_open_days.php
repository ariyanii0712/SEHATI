<?php
require 'config/helpers.php';
$jam = "Pagi: Senin-Kamis 07.00-14.30; Jumat 07.00-11.30; Sabtu 07.00-13.00; Sore: Senin-Jumat 14.30-17.30";
echo formatJamPelayanan($jam) . "\n\n";
print_r(getOpenDays($jam));
?>
