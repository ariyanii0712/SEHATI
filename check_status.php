<?php
require_once 'config/database.php';

$res = $conn->query("
    SELECT p.id, p.user_id, p.tanggal_kunjungan, p.waktu_kunjungan, p.nomor_antrean, p.status, 
           pat.nama_lengkap as patient_name
    FROM pendaftaran p
    LEFT JOIN patients pat ON p.patient_id = pat.id
    WHERE p.nomor_antrean = 'A-001'
");

while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
