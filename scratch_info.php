<?php
require_once 'config/database.php';
$res = $conn->query("DESCRIBE faskes");
while($r = $res->fetch_assoc()) {
    echo $r['Field'] . " - " . $r['Type'] . "\n";
}
echo "===============\n";
$res2 = $conn->query("SELECT * FROM faskes WHERE nama LIKE '%soewandhie%'");
print_r($res2->fetch_assoc());
?>
