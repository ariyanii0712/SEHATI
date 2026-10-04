<?php
require_once 'config/database.php';

echo "Menambahkan dummy pendaftaran untuk membuat antrean terlihat nyata...\n";

// Get all jadwal_poli records
$res = $conn->query("SELECT * FROM jadwal_poli");
$jadwals = [];
while ($row = $res->fetch_assoc()) {
    $jadwals[] = $row;
}

echo "Total slot jadwal: " . count($jadwals) . "\n";

$conn->query("SET FOREIGN_KEY_CHECKS=0;");

$conn->begin_transaction();
$stmt = $conn->prepare("INSERT INTO pendaftaran (faskes_id, poli_id, patient_id, user_id, nik, nama, keluhan, tanggal_kunjungan, waktu_kunjungan, nomor_antrean, status) VALUES (?, ?, NULL, NULL, '000000', 'Dummy', 'Dummy', ?, ?, 'A-000', 'terjadwal')");

$count = 0;

foreach ($jadwals as $j) {
    // Randomly decide if this slot should have some bookings
    $rand = rand(1, 100);
    $booked = 0;
    
    if ($rand <= 10) {
        // 10% chance to be completely full
        $booked = $j['kuota'];
    } else if ($rand <= 40) {
        // 30% chance to be partially full (1 to kuota-1)
        $booked = rand(1, max(1, $j['kuota'] - 1));
    }
    
    for ($i = 0; $i < $booked; $i++) {
        $stmt->bind_param("iiss", $j['faskes_id'], $j['poli_id'], $j['tanggal'], $j['waktu_mulai']);
        $stmt->execute();
        $count++;
    }
}

$conn->commit();
$conn->query("SET FOREIGN_KEY_CHECKS=1;");

echo "Berhasil membuat $count dummy pendaftaran untuk mengisi antrean.\n";
?>
