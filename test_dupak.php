<?php
require 'config/database.php';
$res=$conn->query("SELECT * FROM faskes WHERE nama LIKE '%dupak%'");
while($r=$res->fetch_assoc()) var_dump($r);
?>
