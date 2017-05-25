<?php

$host = '172.20.1.5';
$port = '23';
$user = 'admin';
$pass = 'le-5971591';
$timeout = 10;

function output($con) {
    stream_set_timeout($con, 2);
    $timeoutCount = 0;
    $out = '';
    while (!feof($con)){
        $content = fgets($con, 256);
     
        # If the router say "press space for more", send space char:
        if (ereg('--More--', $content) ){ // IF current line contain --More-- expression,
            fputs ($connection, " "); // sending space char for next part of output.
        } # The "more" controlling part complated.

        $info = stream_get_meta_data($con);
        if ($info['timed_out']) { // If timeout of connection info has got a value, the router not returning a output.
            $timeoutCount++; // We want to count, how many times repeating.
        } 
        if ($timeoutCount >2){ // If repeating more than 2 times,
            break;   // the connection terminating..
        }
        $out .= $content;
    } 
    $out = str_replace("\n", "", $out);
    return explode("\r", $out);

}
$con = fsockopen($host, $port, $errno, $errstr, $timeout);

if (!$con) {
    exit;
}
else {
    fputs($con, "$user\r");
    $content = fgets($con, 128); // echo $content;
    fputs($con, "$pass\r");
    $content = fgets($con, 128); // echo $content;
    fputs($con, "enable\r");
    $content = fgets($con, 128); // echo $content;

    fputs($con, "show port fxs detail 3\r");
    $content = fgets($con, 128); // echo $content;

    $out = output($con); 
    foreach ($out as $line) {
        if (strpos($line, 'DuSLIC Port')) {
            $items = explode(': ', $line);
            $p = substr($items[1], 2);
        }
        elseif (strpos($line, 'Hook state: ')) {
            $items = explode(': ', $line);
            $ports[$p] = trim($items[1]);
        }
    }

    // print_r($ports);

    fputs($con, "show context sip-gateway detail 4\r");
    $content = fgets($con, 128); // echo $content;

    $out = output($con); 
    $i = 0;
    foreach ($out as $line) {
        if (strpos($line, 'SIP Registration:')) {
            $i++;
            $items = explode(': ', $line);
            $sip[$i][0] = trim($items[1]);
        }
        elseif (strpos($line, '    State:')) {
            $items = explode(': ', $line);
            $sip[$i][1] = trim($items[1]);
        }
    }

    print_r($sip);



    fputs($con, "exit\r");
    $content = fgets($con, 128); // echo $content;
    fputs($con, "exit\r");
    $content = fgets($con, 128); // echo $content;

}
?>
