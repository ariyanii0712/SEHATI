<?php
require_once 'config/database.php';
$stmt = $conn->prepare("UPDATE faskes SET jam_pelayanan = ? WHERE nama LIKE '%Tembok Dukuh%'");
$jam = "Senin-Jumat 07.00-17.30; Sabtu 07.00-13.00";
$stmt->bind_param("s", $jam);
$stmt->execute();
echo "Updated " . $stmt->affected_rows . " rows.\n";
?>
