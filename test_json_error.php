<?php
require_once 'config/database.php';
require_once 'config/helpers.php';
$res = $conn->query("SELECT nama, jam_pelayanan FROM faskes WHERE nama LIKE '%Benowo%'");
$r = $res->fetch_assoc();
$r['jam_pelayanan'] = formatJamPelayanan($r['jam_pelayanan']);
$json = json_encode($r);
echo "JSON: ";
var_dump($json);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error: " . json_last_error_msg() . "\n";
}
?>
