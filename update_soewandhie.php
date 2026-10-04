<?php
require 'config/database.php';

// Update jam pelayanan RSUD dr. M. Soewandhie
$new_jam = "Senin-Jumat 07:30 - 14:00 | Layanan 24 Jam: IGD & Gawat Darurat (Setiap Hari)";
$stmt = $conn->prepare("UPDATE faskes SET jam_pelayanan = ? WHERE nama LIKE '%Soewandhie%'");
$stmt->bind_param("s", $new_jam);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "<h2 style='color: green;'>Sukses: Data jam pelayanan RSUD dr. M. Soewandhie berhasil diupdate.</h2>";
} else {
    echo "<h2 style='color: orange;'>Info: Data sudah terupdate atau Faskes tidak ditemukan.</h2>";
}
echo "<p>String baru: " . htmlspecialchars($new_jam) . "</p>";
echo "<p><strong>PENTING:</strong> Setelah ini, silakan jalankan kembali script <code>generate_dummy.php</code> untuk memperbarui slot jadwal sesuai jam yang baru.</p>";
?>
