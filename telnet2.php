<?php

require_once 'php-telnet.php';


function pattonStatus($host, $user, $pw) {
    return $output;
}

function grandstreamStatus($host, $user, $pw) {
    $t = new PHPTelnet($host, $user, $pw);
    
    return $output;
}

/*
$hosts[] = '172.20.1.5';
$hosts[] = '172.20.2.7';
$hosts[] = '172.20.2.8';
$hosts[] = '172.17.1.21';
$hosts[] = '172.17.2.21';
$hosts[] = '172.17.6.21';

foreach ($hosts as $host) {

    echo "\n\n$host\n";
    $next = false;
    $lines = explode("\r", pattonStatus($host, 'admin', 'le-5971591'));
    foreach ($lines as $line) {
        if (strpos($line, 'SIP Registration:')) {
            $d = explode(':', $line);
            $d = explode('@', $d[2]);
            $num = $d[0];
            echo "$num: ";
            $next = true;
        }
        elseif ($next) {
            $next = false;
            $d = explode(':', $line);
            echo trim($d[1]),"\n";
        }
    }
}
*/

$gshosts[] = '172.17.3.21';
$gshosts[] = '172.17.4.21';

foreach ($gshosts as $host) {

    echo "\n\n$host\n";
    $lines = explode("\r", grandstreamStatus($host, 'admin', 'le-5971591'));
    foreach ($lines as $line) {
        if (strpos($line, '    Port')) {
            $d = explode(':', $line);

            $num = $d[0];
            $reg = substr(trim($d[1]),12);
            echo "$num: $reg";
        }
    }
}

?>
