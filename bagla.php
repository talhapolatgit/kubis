<?php
// Klasör yollarını hosting panelindeki "Tam Yol" (Full Path) ile değiştirin
$target = '/home/kutuphaneyonetim.beyoglu.bel.tr/libraryProject/storage/app/public';
$link = '/home/kutuphaneyonetim.beyoglu.bel.tr/public_html/storage';

if(symlink($target, $link)){
    echo "Sembolik link başarıyla public_html içine oluşturuldu!";
} else {
    echo "Bir hata oluştu. Lütfen yolları kontrol edin.";
}