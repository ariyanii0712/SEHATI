<?php
require_once 'config/database.php';

$new_jam = "Pagi: Senin-Kamis 07.00-14.30; Jumat 07.00-11.30; Sabtu 07.00-13.00; Sore: Senin-Jumat 14.30-17.30";
$stmt = $conn->prepare("UPDATE faskes SET jam_pelayanan = ? WHERE nama LIKE '%Simomulyo%'");
$stmt->bind_param("s", $new_jam);
$stmt->execute();
echo "Rows updated: " . $stmt->affected_rows . "\n";
?>
