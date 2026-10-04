<?php
require_once 'config/database.php';
$stmt = $conn->prepare("UPDATE faskes SET jam_pelayanan = ? WHERE nama LIKE '%Ngagel Rejo%'");
$jam = "Senin-Kamis 07.30-17.30; Jumat 07.30-11.30, 14.30-17.30; Sabtu 07.30-13.00";
$stmt->bind_param("s", $jam);
$stmt->execute();
echo "Updated " . $stmt->affected_rows . " rows.\n";
?>
