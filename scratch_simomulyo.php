<?php
require 'config/database.php';

$res = $conn->query("SELECT COUNT(*) FROM jadwal_poli");
$row = $res->fetch_array();
echo "Total jadwal: " . $row[0] . "\n";
?>
