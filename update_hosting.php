<?php
require 'config/database.php';

$jam_bdh = "Senin-Kamis: 07.00-12.00; Jumat: 07.00-10.00 | Layanan 24 Jam: IGD & Gawat Darurat (Setiap Hari)";
$jam_eka = "Senin-Kamis: 07.00-13.00; Jumat: 07.00-11.00 | Layanan 24 Jam: IGD & Gawat Darurat (Setiap Hari)";

$conn->query("UPDATE faskes SET jam_pelayanan = '$jam_bdh' WHERE id = 65");
$conn->query("UPDATE faskes SET jam_pelayanan = '$jam_eka' WHERE id = 66");

echo "<h2 style='color: green;'>Sukses: Data jam pelayanan RSUD Bhakti Dharma Husada & RSUD Eka Candrarini berhasil diupdate.</h2>";
echo "<p><strong>PENTING:</strong> Setelah ini, silakan jalankan kembali script <code>generate_dummy.php</code> untuk memperbarui slot jadwal sesuai jam yang baru.</p>";
?>
