<?php
require_once 'config/database.php';
$res = $conn->query("DESCRIBE users");
while($r = $res->fetch_assoc()) {
    echo $r['Field'] . " - " . $r['Type'] . "\n";
}
?>
