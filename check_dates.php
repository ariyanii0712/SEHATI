<?php
require 'config/database.php';
$faskes_id = 19;
$res = $conn->query("SELECT p.id, j.tanggal, j.waktu_mulai FROM poli p JOIN jadwal_poli j ON p.id = j.poli_id WHERE p.nama_poli = 'Pengobatan Tradisional' AND j.faskes_id = $faskes_id AND j.tanggal = '2026-10-30'");
while($r = $res->fetch_assoc()) {
    print_r($r);
}
?>
