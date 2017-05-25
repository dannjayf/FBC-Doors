<?php
header('content-type: multipart/x-mixed-replace; boundary=--myboundary');

while (@ob_end_clean()); 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://172.17.6.42/enc/camera640x480.jpg'); 
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$im = curl_exec($ch);


echo $im;
curl_close($ch);
?>
