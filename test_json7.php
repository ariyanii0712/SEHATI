<?php
// Simulate invalid UTF-8 string from Windows-1252 database
// \x96 is en-dash in Windows-1252
$str = "Senin\x96Kamis";

echo "Original (hex): " . bin2hex($str) . "\n";

// My previous "fix" which produced ?
$bad_fix = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
echo "Bad Fix: " . $bad_fix . "\n";

// Correct fix
if (!mb_check_encoding($str, 'UTF-8')) {
    $good_fix = mb_convert_encoding($str, 'UTF-8', 'Windows-1252');
} else {
    $good_fix = $str;
}
echo "Good Fix: " . $good_fix . "\n";

// Test if it works on valid UTF-8
$valid_utf8 = "Senin–Kamis"; // \xE2\x80\x93
if (!mb_check_encoding($valid_utf8, 'UTF-8')) {
    $good_fix2 = mb_convert_encoding($valid_utf8, 'UTF-8', 'Windows-1252');
} else {
    $good_fix2 = $valid_utf8;
}
echo "Good Fix on Valid UTF-8: " . $good_fix2 . "\n";
?>
