<?php
require 'config/database.php';
$faskes_id = 19;
$res = $conn->query("SELECT p.nama_poli, COUNT(j.id) as cnt FROM poli p LEFT JOIN jadwal_poli j ON p.id = j.poli_id AND j.faskes_id = $faskes_id JOIN faskes_layanan fl ON fl.poli_id = p.id WHERE fl.faskes_id = $faskes_id GROUP BY p.id");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
