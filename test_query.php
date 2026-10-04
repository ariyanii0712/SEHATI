<?php
require 'config/database.php';
$q = 'dupak';
$tipe = '';
$wilayah = '';

$where = ["faskes.status = 'Aktif'"];
$params = [];
$types = "";

if ($q !== '') {
    $where[] = "(faskes.nama LIKE ? OR poli.nama_poli LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $types .= "ss";
}
if ($tipe !== '') {
    $where[] = "faskes.kategori = ?";
    $params[] = $tipe;
    $types .= "s";
}
if ($wilayah !== '') {
    $where[] = "faskes.wilayah = ?";
    $params[] = $wilayah;
    $types .= "s";
}

$whereClause = implode(" AND ", $where);
$orderBy = "CASE WHEN faskes.kategori = 'Rumah Sakit' THEN 1 ELSE 2 END ASC, faskes.nama ASC";

$sql = "SELECT DISTINCT faskes.id, faskes.nama, faskes.kategori, faskes.wilayah, faskes.alamat, faskes.telepon, faskes.jam_pelayanan, faskes.latitude, faskes.longitude, faskes.rating, faskes.website, faskes.deskripsi, faskes.image_url 
        FROM faskes 
        LEFT JOIN faskes_layanan ON faskes.id = faskes_layanan.faskes_id 
        LEFT JOIN poli ON faskes_layanan.poli_id = poli.id 
        WHERE $whereClause 
        ORDER BY $orderBy";

$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$fasilitas = [];
while ($row = $result->fetch_assoc()) {
    $fasilitas[] = $row['nama'];
}

echo "Found: " . count($fasilitas) . "\n";
print_r($fasilitas);
?>
