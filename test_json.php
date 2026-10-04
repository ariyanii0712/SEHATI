<?php
require_once 'config/database.php';
require_once 'config/helpers.php';
$stmt = $conn->prepare("SELECT * FROM faskes WHERE nama LIKE '%Asemrowo%'");
$stmt->execute();
$f = $stmt->get_result()->fetch_assoc();
$f['jam_pelayanan'] = formatJamPelayanan($f['jam_pelayanan']);
$detailsJson = htmlspecialchars(json_encode($f), ENT_QUOTES, 'UTF-8');
echo '<button onclick="event.stopPropagation(); showDetail(event, '.$detailsJson.')">Daftar Berobat</button>';
?>
