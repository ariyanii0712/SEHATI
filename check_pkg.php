<?php
require 'config/database.php';
$res = $conn->query("SELECT faskes_id, poli_id, tanggal, waktu_mulai, waktu_selesai FROM jadwal_poli WHERE faskes_id=9 AND tanggal='2026-10-16' ORDER BY waktu_mulai ASC");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
