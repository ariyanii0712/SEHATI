<?php
require 'config/database.php';
$q = "sore";
$sql = "SELECT DISTINCT faskes.id, faskes.nama, poli.nama_poli 
        FROM faskes 
        LEFT JOIN faskes_layanan ON faskes.id = faskes_layanan.faskes_id 
        LEFT JOIN poli ON faskes_layanan.poli_id = poli.id 
        WHERE faskes.nama LIKE '%$q%' OR poli.nama_poli LIKE '%$q%'";
$res = $conn->query($sql);
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
