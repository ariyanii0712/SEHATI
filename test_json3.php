<?php
require_once 'config/database.php';
$names = ['Bangkingan', 'Gundih', 'Kebonsari', 'Kedungdoro', 'Pegirian'];
foreach($names as $n) {
    $res=$conn->query("SELECT id, nama, jam_pelayanan, deskripsi, alamat FROM faskes WHERE nama LIKE '%$n%'");
    while($r=$res->fetch_assoc()){
        $j = json_encode($r);
        if ($j === false) {
            echo 'FAILED: ' . $n . ' -> ' . json_last_error_msg() . "\n";
        } else {
            echo 'OK: ' . $n . "\n";
        }
    }
}
?>
