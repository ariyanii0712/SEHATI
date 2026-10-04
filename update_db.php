<?php
require 'config/database.php';

$jam_bdh = "Senin-Kamis: 07.00-12.00; Jumat: 07.00-10.00 | Layanan 24 Jam: IGD & Gawat Darurat (Setiap Hari)";
$jam_eka = "Senin-Kamis: 07.00-13.00; Jumat: 07.00-11.00 | Layanan 24 Jam: IGD & Gawat Darurat (Setiap Hari)";

$conn->query("UPDATE faskes SET jam_pelayanan = '$jam_bdh' WHERE id = 65");
$conn->query("UPDATE faskes SET jam_pelayanan = '$jam_eka' WHERE id = 66");

echo "Update successful.";
?>
