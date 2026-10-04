<?php
require_once 'config/database.php';

$updates = [
    "RSUD Bhakti Dharma Husada" => "Barat",
    "RSUD dr. M. Soewandhie" => "Pusat",
    "RSUD Eka Candrarini" => "Timur"
];

foreach ($updates as $nama => $wilayah) {
    $stmt = $conn->prepare("UPDATE faskes SET wilayah = ? WHERE nama = ?");
    $stmt->bind_param("ss", $wilayah, $nama);
    $stmt->execute();
    echo "Updated $nama to $wilayah\n";
}
?>
