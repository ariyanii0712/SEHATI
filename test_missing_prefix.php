<?php
require_once 'config/database.php';
$res = $conn->query("SELECT nama, jam_pelayanan FROM faskes WHERE jam_pelayanan LIKE '07.30%'");
while($row = $res->fetch_assoc()) {
    echo $row['nama'] . " => " . $row['jam_pelayanan'] . "\n";
}
?>
