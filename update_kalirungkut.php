<?php
require_once 'config/database.php';
$stmt = $conn->prepare("UPDATE faskes SET jam_pelayanan = ? WHERE nama LIKE '%Kalirungkut%' OR nama LIKE '%Kali Rungkut%'");
$jam = "Senin-Kamis 07.30-14.00, 14.30-17.00; Jumat 07.30-11.30, 14.30-17.00; Sabtu 07.30-13.00";
$stmt->bind_param("s", $jam);
$stmt->execute();
echo "Updated " . $stmt->affected_rows . " rows.\n";
?>
