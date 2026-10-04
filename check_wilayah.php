<?php
require_once 'config/database.php';
$r = $conn->query("SELECT id, nama, wilayah FROM faskes WHERE kategori='Rumah Sakit'");
while($row = $r->fetch_assoc()) print_r($row);
?>
