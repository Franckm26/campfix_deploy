<?php
$stiLogoPath = public_path('Campfix/Images/images.png');
if (file_exists($stiLogoPath)) {
    $stiLogoData = base64_encode(file_get_contents($stiLogoPath));
    echo '<img src="data:image/png;base64,' . $stiLogoData . '" alt="STI Logo" style="height:40px">';
}
?>
