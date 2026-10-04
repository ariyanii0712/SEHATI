<?php
require_once 'config/database.php';
$names = ['Bangkingan', 'Gundih', 'Kebonsari', 'Kedungdoro', 'Pegirian'];
foreach($names as $n) {
    $res=$conn->query("SELECT f.nama, COUNT(fl.poli_id) as c FROM faskes f LEFT JOIN faskes_layanan fl ON f.id=fl.faskes_id WHERE f.nama LIKE '%$n%' GROUP BY f.id");
    while($r=$res->fetch_assoc()) {
        echo $r['nama'] . ': ' . $r['c'] . " layanan\n";
    }
}
?>
