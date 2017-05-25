<?php
include 'config.php';

global $username, $password;
$username = 'admin';
$password = '123456';


function getLines($url, $post = '') {
    global $username, $password;

    $process = curl_init() or die ("Init Error");
    if ($post)  {
        curl_setopt($process, CURLOPT_POSTFIELDS, $post);
    }

    curl_setopt($process, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($process, CURLOPT_URL, $url);
    curl_setopt($process, CURLOPT_HEADER, 0);
    curl_setopt($process, CURLOPT_TIMEOUT, 30);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($process) or curl_error($process);

    // $lines = explode("\n", $return);

    return $return;
}

$post['main_enable'] = 1;
$post['main_url'] = 'http://myurbansky.com/prairie';
$post['ad_enable'] = 1;
$post['ad_url'] = 'http://myurbansky.com/prairie';
$post['app_url'] = '';

$query = "
SELECT ip_address
FROM vcom
WHERE loc = 3
";

$r = sql_query($query);

while ($row = mysql_fetch_array($r)) {
    $url = "http://$row[0]/cgi-bin/webkit.cgi";
    echo $url;

    $cr = getLines($url, $post);
    if (strlen($cr) > 0) echo "  OK";

    echo "\n";
}
?>
