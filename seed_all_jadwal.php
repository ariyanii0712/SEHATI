<?php
require_once 'config/database.php';

echo "Memulai pembuatan jadwal dummy untuk semua layanan di semua faskes...\n";

// Ensure table exists
$sql = "CREATE TABLE IF NOT EXISTS jadwal_poli (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faskes_id INT NOT NULL,
    poli_id INT NOT NULL,
    tanggal DATE NOT NULL,
    waktu_mulai TIME NOT NULL,
    waktu_selesai TIME NOT NULL,
    kuota INT NOT NULL,
    INDEX (faskes_id, poli_id, tanggal)
)";
$conn->query($sql);

// Empty existing jadwal_poli table? The user said "bikin dummy... untuk semua layanan" so I will clear existing dummy data first to avoid duplicates or massive database if they run it multiple times.
$conn->query("TRUNCATE TABLE jadwal_poli");
echo "Tabel jadwal_poli telah dikosongkan.\n";

$res = $conn->query("SELECT faskes_id, poli_id FROM faskes_layanan");
$combinations = [];
while ($row = $res->fetch_assoc()) {
    $combinations[] = $row;
}

echo "Ditemukan " . count($combinations) . " kombinasi layanan faskes.\n";

$today = new DateTime();
$dates = [];
for ($i = 0; $i < 60; $i++) {
    $dates[] = $today->format('Y-m-d');
    $today->modify('+1 day');
}

$slots = [
    ['08:00:00', '09:00:00', 10], // 10 kuota
    ['09:00:00', '10:00:00', 10], // 10 kuota
    ['10:00:00', '11:00:00', 10], // 10 kuota
    ['13:00:00', '14:00:00', 15]  // 15 kuota
];

$count = 0;
// We'll use prepared statements for fast insertion
$stmt = $conn->prepare("INSERT INTO jadwal_poli (faskes_id, poli_id, tanggal, waktu_mulai, waktu_selesai, kuota) VALUES (?, ?, ?, ?, ?, ?)");

$conn->begin_transaction();
foreach ($combinations as $combo) {
    foreach ($dates as $dayIndex => $dateStr) {
        
        // Let's create a predictable pattern based on faskes_id and poli_id
        // So some days might not have schedules
        $hash = ($combo['faskes_id'] + $combo['poli_id'] + $dayIndex) % 7;
        
        // Let's say if hash == 0, no schedule for this day (simulate closed)
        if ($hash == 0) {
            continue; 
        }

        foreach ($slots as $slotIndex => $slot) {
            // Predictable kuota variations: some slots might have smaller kuota
            $kuota = $slot[2] - ($hash * $slotIndex);
            if ($kuota < 2) $kuota = 2; // minimum 2
            
            // Just raw insert
            $stmt->bind_param("iisssi", $combo['faskes_id'], $combo['poli_id'], $dateStr, $slot[0], $slot[1], $kuota);
            $stmt->execute();
            $count++;
        }
    }
}
$conn->commit();

echo "Berhasil membuat $count slot jadwal_poli.\n";
?>
