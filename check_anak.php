<?php
require_once 'config/database.php';

$res = $conn->query("
    SELECT p.id, p.nama_poli, 
           COUNT(DISTINCT fl.faskes_id) as num_faskes, 
           COUNT(jp.id) as num_jadwal 
    FROM poli p 
    LEFT JOIN faskes_layanan fl ON p.id = fl.poli_id 
    LEFT JOIN jadwal_poli jp ON fl.faskes_id = jp.faskes_id AND fl.poli_id = jp.poli_id 
    WHERE p.nama_poli LIKE '%anak%' OR p.nama_poli LIKE '%pediatri%'
    GROUP BY p.id, p.nama_poli
");

while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
