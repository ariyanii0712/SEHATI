<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

$sql = "SELECT DISTINCT faskes.id, faskes.nama, faskes.kategori, faskes.wilayah, faskes.alamat, faskes.telepon, faskes.jam_pelayanan, faskes.latitude, faskes.longitude, faskes.rating, faskes.website, faskes.deskripsi, faskes.image_url 
        FROM faskes 
        WHERE faskes.nama = 'Puskesmas Benowo'";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
$row['jam_pelayanan'] = $row['jam_pelayanan'] ? formatJamPelayanan($row['jam_pelayanan']) : '-';
$detailsJson = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html>
<body>
    <button id="btn" data-faskes="<?php echo $detailsJson; ?>">Click me</button>
    <div id="out" style="border:1px solid red; min-height:20px;"></div>
    <script>
        document.getElementById('btn').onclick = function() {
            var data = JSON.parse(this.dataset.faskes);
            document.getElementById('out').innerHTML = data.jam_pelayanan || '-';
        }
    </script>
</body>
</html>
