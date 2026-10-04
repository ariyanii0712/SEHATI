<?php
require 'config/database.php';
$res = $conn->query('SELECT id, nama, jam_pelayanan FROM faskes LIMIT 20');
while($r = $res->fetch_assoc()) {
    echo "ID: " . $r['id'] . " | " . $r['nama'] . "\n";
    echo $r['jam_pelayanan'] . "\n";
    echo "------------------------\n";
}
