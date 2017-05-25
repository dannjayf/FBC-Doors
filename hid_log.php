<?php

include 'config.php';
include 'config_hid.php';

$sid = 1;
$query = "
SELECT
    id,
    admin_user,
    admin_pass,
    ip_address
FROM asset
WHERE
    active = 1
ORDER BY ip_address
";

$r = sql_query($query);



while ($row = mysql_fetch_array($r)) {


    $hid = $row['id'];
    $host = $row['ip_address'];
    $username = $row['admin_user'];
    $password = $row['admin_pass'];

    $events = hidListEvents(256,true);
    echo $host," ",count($events),"\n";
    foreach ($events as $event) {
        $items = $event['attributes'];
        $dt = $items['timestamp'];
        $et = $items['eventType'];
        $pid = empty($items['cardholderID']) ? 'null' : $items['cardholderID'];
        $lname = empty($items['surname']) ? '' : $items['surname'];
        $fname = empty($items['forename']) ? '' : $items['forename'];
        $token = empty($items['rawCardNumber']) ? '' : $items['rawCardNumber'];
        echo "$host\t$dt\t$et\t$pid\t$fname $lname\t$token\n";
    }
    exit;

}
