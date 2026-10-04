<?php
$files = glob("c:/xampp/htdocs/sehati/frontend/*.php");
foreach ($files as $file) {
    $content = file_get_contents($file);
    
    $content = str_replace('<i class="fa-solid fa-house"></i> Beranda', '<i class="fa-solid fa-house w-6 text-center"></i> Beranda', $content);
    $content = str_replace('<i class="fa-solid fa-stethoscope"></i> Cari Layanan', '<i class="fa-solid fa-stethoscope w-6 text-center"></i> Cari Layanan', $content);
    $content = str_replace('<i class="fa-regular fa-calendar-check"></i> Jadwal Saya', '<i class="fa-regular fa-calendar-check w-6 text-center"></i> Jadwal Saya', $content);
    $content = str_replace('<i class="fa-solid fa-receipt"></i> Info Tarif', '<i class="fa-solid fa-receipt w-6 text-center"></i> Info Tarif', $content);
    $content = str_replace('<i class="fa-solid fa-circle-info"></i> Tentang Sehati', '<i class="fa-solid fa-circle-info w-6 text-center"></i> Tentang Sehati', $content);
    
    file_put_contents($file, $content);
}
echo "Done.\n";
?>
