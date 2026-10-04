<?php
require_once 'config/database.php';
require_once 'config/helpers.php';
$res = $conn->query("SELECT nama, jam_pelayanan FROM faskes WHERE nama LIKE '%Benowo%'");
$r = $res->fetch_assoc();
echo "RAW: " . $r['jam_pelayanan'] . "\n";
echo "FORMATTED: " . formatJamPelayanan($r['jam_pelayanan']) . "\n";
?>
