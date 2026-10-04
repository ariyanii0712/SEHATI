<!DOCTYPE html>
<html>
<body>
<?php
$f = ["jam" => "A<br>\nB"];
$detailsJson = htmlspecialchars(json_encode($f), ENT_QUOTES, 'UTF-8');
echo '<button data-faskes="'.$detailsJson.'" onclick="console.log(JSON.parse(this.dataset.faskes))">Click me</button>';
?>
</body>
</html>
