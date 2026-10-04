<?php
require_once 'config/database.php';
$res = $conn->query("SELECT nama, jam_pelayanan FROM faskes WHERE nama LIKE '%Dukuh Kupang%'");
print_r($res->fetch_assoc());
?>
