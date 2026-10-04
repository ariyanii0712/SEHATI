<?php
require_once 'config/database.php';
$names = ['Bangkingan', 'Gundih', 'Kebonsari', 'Kedungdoro', 'Pegirian'];
foreach($names as $n) {
    $res=$conn->query("SELECT kategori FROM faskes WHERE nama LIKE '%$n%'");
    while($r=$res->fetch_assoc()) {
        echo $n . ': ' . var_export($r['kategori'], true) . "\n";
    }
}
?>
