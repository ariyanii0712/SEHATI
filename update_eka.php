<?php
require 'config/database.php';
$conn->query("UPDATE faskes SET jam_pelayanan = 'Senin-Kamis: 07.00-13.00; Jumat: 07.00-11.00' WHERE id = 66");
echo "Updated faskes 66.\n";
?>
