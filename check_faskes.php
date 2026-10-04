<?php
require_once 'config/database.php';
$faskes_total = $conn->query("SELECT COUNT(*) FROM faskes")->fetch_row()[0];
$faskes_with_services = $conn->query("SELECT COUNT(DISTINCT faskes_id) FROM faskes_layanan")->fetch_row()[0];
echo "Total faskes: $faskes_total\n";
echo "Faskes with services: $faskes_with_services\n";
?>
