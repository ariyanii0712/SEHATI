<?php
require_once 'config/database.php';
require_once 'config/helpers.php';
$res = $conn->query("SELECT nama, jam_pelayanan FROM faskes WHERE kategori = 'Puskesmas' LIMIT 5");
while($r = $res->fetch_assoc()) {
    echo "NAMA: " . $r['nama'] . "\n";
    echo "RAW: " . $r['jam_pelayanan'] . "\n";
    echo "FORMATTED: " . formatJamPelayanan($r['jam_pelayanan']) . "\n\n";
}
?>
