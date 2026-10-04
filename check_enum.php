<?php
require_once 'config/database.php';
$r = $conn->query("DESCRIBE pendaftaran");
while($row = $r->fetch_assoc()) {
    print_r($row);
}
?>
