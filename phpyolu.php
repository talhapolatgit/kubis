<?php
$output = shell_exec('/usr/local/lsws/lsphp82/bin/php /home/kutuphaneyonetim.beyoglu.bel.tr/libraryProject/artisan schedule:run 2>&1');
echo '<pre>' . $output . '</pre>';