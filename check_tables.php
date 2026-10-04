<?php
require_once 'config/database.php';

$res = $conn->query("SELECT * FROM faskes_layanan LIMIT 5");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
