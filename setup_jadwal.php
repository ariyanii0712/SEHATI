<?php
require_once 'config/database.php';

// Create table
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

if ($conn->query($sql) === TRUE) {
    echo "Tabel jadwal_poli berhasil dibuat.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Generate mock data for Faskes ID 65 and Poli ISPA
// Wait, I need to know the Poli ID for ISPA. Let's find it.
$res = $conn->query("SELECT id FROM poli WHERE nama_poli LIKE '%ISPA%' LIMIT 1");
if ($row = $res->fetch_assoc()) {
    $poli_id = $row['id'];
} else {
    // Insert ISPA if not exists
    $conn->query("INSERT INTO poli (nama_poli, deskripsi) VALUES ('Poli ISPA', 'Penanganan Infeksi Saluran Pernapasan Akut')");
    $poli_id = $conn->insert_id;
}

$faskes_id = 65;

// Check if data already exists for this faskes and poli
$res = $conn->query("SELECT COUNT(*) as cnt FROM jadwal_poli WHERE faskes_id = $faskes_id AND poli_id = $poli_id");
$row = $res->fetch_assoc();
if ($row['cnt'] == 0) {
    echo "Membuat mock data untuk Faskes 65, Poli ISPA ($poli_id)...\n";
    
    // We create data for today and the next 6 days
    $today = new DateTime();
    
    for ($i = 0; $i < 7; $i++) {
        $dateStr = $today->format('Y-m-d');
        
        // Let's create some variations
        // Day 0 (Today): 12 available (We'll insert 20 kuota, and simulate 8 registered later if we want, or just insert 12 kuota)
        // Actually, the requirement says: available = kuota - registered.
        // We don't want to mess with 'pendaftaran' table too much to avoid side effects.
        // So we will just insert kuota=10 for multiple slots, and later I'll insert mock pendaftaran records or just assume 0.
        // Actually, let's insert some mock pendaftaran to make it realistic.
        
        if ($i == 4) {
            // Day 4: Tidak ada jadwal
            $today->modify('+1 day');
            continue;
        }
        
        $slots = [
            ['08:00:00', '09:00:00'],
            ['09:00:00', '10:00:00'],
            ['10:00:00', '11:00:00']
        ];
        
        foreach ($slots as $idx => $slot) {
            $stmt = $conn->prepare("INSERT INTO jadwal_poli (faskes_id, poli_id, tanggal, waktu_mulai, waktu_selesai, kuota) VALUES (?, ?, ?, ?, ?, ?)");
            $kuota = 10;
            $stmt->bind_param("iisssi", $faskes_id, $poli_id, $dateStr, $slot[0], $slot[1], $kuota);
            $stmt->execute();
            $jadwal_id = $stmt->insert_id; // Just in case we need it
            
            // Generate some fake registrations to reduce availability
            $terdaftar = 0;
            if ($i == 0) { // Today: 12 available (total 30 kuota, so 18 registered)
                if ($idx == 0) $terdaftar = 0;
                if ($idx == 1) $terdaftar = 8;
                if ($idx == 2) $terdaftar = 10;
            } elseif ($i == 1) { // Lusa: 8 available
                if ($idx == 0) $terdaftar = 2;
                if ($idx == 1) $terdaftar = 10;
                if ($idx == 2) $terdaftar = 10;
            } elseif ($i == 2) { // 3 tersedia
                if ($idx == 0) $terdaftar = 7;
                if ($idx == 1) $terdaftar = 10;
                if ($idx == 2) $terdaftar = 10;
            } elseif ($i == 3) { // Penuh
                $terdaftar = 10;
            } else {
                $terdaftar = rand(0, 8); // Random others
            }
            
            for ($k = 0; $k < $terdaftar; $k++) {
                $conn->query("INSERT INTO pendaftaran (faskes_id, poli_id, tanggal_kunjungan, waktu_kunjungan, status) VALUES ($faskes_id, $poli_id, '$dateStr', '{$slot[0]}', 'terjadwal')");
            }
        }
        
        $today->modify('+1 day');
    }
    
    echo "Mock data berhasil dimasukkan.\n";
} else {
    echo "Data sudah ada, tidak perlu mock lagi.\n";
}
?>
