<?php
require_once 'config/database.php';

$new_jam = "Senin-Jumat 07:30 - 14:00 | Layanan 24 Jam: IGD & Gawat Darurat (Setiap Hari)";
$stmt = $conn->prepare("UPDATE faskes SET jam_pelayanan = ? WHERE nama LIKE '%Soewandhie%'");
$stmt->bind_param("s", $new_jam);
$stmt->execute();
echo "Rows updated: " . $stmt->affected_rows . "\n";
?>
