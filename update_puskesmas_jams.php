<?php
require_once 'config/database.php';

$updates = [
    'Tembok Dukuh' => 'Senin-Jumat 07.00-17.30; Sabtu 07.00-13.00',
    'Sawah Pulo' => 'Senin-Kamis 07.30-14.30; Jumat 07.30-11.30; Sabtu 07.30-13.00',
    'Tanah Kali Kedinding' => 'Pagi: Senin-Kamis 07.30-14.30; Jumat 07.30-11.30; Sabtu 07.30-13.00; Sore: Senin-Jumat 14.30-17.30',
    'Kalirungkut' => 'Pagi: Senin-Kamis 07.30-14.30; Jumat 07.30-11.30; Sabtu 07.30-13.00; Sore: Senin-Jumat 14.30-17.30',
    'Pacarkeling' => 'Pagi: Senin-Kamis 07.30-14.30; Jumat 07.30-11.30; Sabtu 07.30-13.00; Sore: Senin-Jumat 14.30-17.30',
    'Kebonsari' => 'Senin-Kamis 07.30-14.30; Jumat 07.30-11.30; Sabtu 07.30-13.00',
    'Ngagel Rejo' => 'Pagi: Senin-Kamis 07.30-14.30; Jumat 07.30-11.30; Sabtu 07.30-13.00; Sore: Senin-Jumat 14.30-17.30',
    'Sawahan' => 'Senin-Kamis 07.30-14.30; Jumat 07.30-11.30; Sabtu 07.30-13.00'
];

$stmt = $conn->prepare("UPDATE faskes SET jam_pelayanan = ? WHERE nama LIKE ?");

$total_updated = 0;
foreach ($updates as $name => $jam) {
    $search = '%' . $name . '%';
    $stmt->bind_param("ss", $jam, $search);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "Updated $name\n";
        $total_updated += $stmt->affected_rows;
    } else {
        echo "Not found or unchanged: $name\n";
    }
}

echo "Total rows updated: $total_updated\n";
?>
