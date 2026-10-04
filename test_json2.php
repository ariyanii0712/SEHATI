<!DOCTYPE html>
<html>
<body>
<?php
$f = ["jam" => "A<br>\nB"];
$detailsJson = htmlspecialchars(json_encode($f), ENT_QUOTES, 'UTF-8');
echo '<button id="btn" data-faskes="'.$detailsJson.'">Click me</button>';
?>
<div id="out">out</div>
<script>
    document.getElementById('btn').onclick = function() {
        var data = JSON.parse(this.dataset.faskes);
        console.log(data);
        document.getElementById('out').innerHTML = data.jam || '-';
    }
</script>
</body>
</html>
