<?php
require_once 'config/database.php';
$stmt = $conn->prepare("UPDATE faskes SET jam_pelayanan = ? WHERE nama LIKE '%Simomulyo%'");
$jam = "Senin-Jumat 07.30-16.30; Sabtu 07.30-13.00";
$stmt->bind_param("s", $jam);
$stmt->execute();
echo "Updated " . $stmt->affected_rows . " rows.\n";
?>
