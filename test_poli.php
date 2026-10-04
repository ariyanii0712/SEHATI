<?php
require 'config/database.php';
$res=$conn->query("SELECT * FROM poli WHERE nama_poli LIKE '%Tradisional%'");
while($r=$res->fetch_assoc()) print_r($r);

echo "Checking faskes_layanan:\n";
$res2=$conn->query("SELECT * FROM faskes_layanan WHERE faskes_id = 19");
while($r=$res2->fetch_assoc()) print_r($r);
?>
