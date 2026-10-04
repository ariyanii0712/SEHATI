<?php
require 'config/database.php';
$res = $conn->query("SELECT id, nama, jam_pelayanan FROM faskes WHERE nama LIKE '%Soewandhie%'");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
