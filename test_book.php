<?php
require 'config/database.php';
$faskes_id = 19;
$poli_id = 10;
$stmt = $conn->prepare("SELECT * FROM jadwal_poli WHERE faskes_id = ? AND poli_id = ?");
$stmt->bind_param('ii', $faskes_id, $poli_id);
$stmt->execute();
$res = $stmt->get_result();
echo "Rows: " . $res->num_rows . "\n";

// check 30 oct
$stmt = $conn->prepare("SELECT * FROM jadwal_poli WHERE faskes_id = ? AND poli_id = ? AND tanggal = '2026-10-30'");
$stmt->bind_param('ii', $faskes_id, $poli_id);
$stmt->execute();
$res = $stmt->get_result();
echo "Rows on 30 oct: " . $res->num_rows . "\n";
?>
