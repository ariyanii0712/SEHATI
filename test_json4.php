<?php
require_once 'config/database.php';
$names = ['Bangkingan', 'Gundih', 'Kebonsari', 'Kedungdoro', 'Pegirian'];
foreach($names as $n) {
    $res=$conn->query("SELECT id, nama, jam_pelayanan, deskripsi, alamat FROM faskes WHERE nama LIKE '%$n%'");
    while($r=$res->fetch_assoc()){
        echo $n . ":\n";
        echo htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8');
        echo "\n\n";
    }
}
?>
