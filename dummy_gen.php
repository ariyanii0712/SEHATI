<?php
require_once 'config/database.php';
$res = $conn->query("SELECT id, nama, jam_pelayanan FROM faskes WHERE nama LIKE '%RSUD%' LIMIT 5");
while($r = $res->fetch_assoc()){
    echo json_encode($r)."\n";
}
