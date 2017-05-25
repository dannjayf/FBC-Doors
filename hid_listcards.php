<?php

include 'config.php';
include 'config_hid.php';

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
    echo "$host\n";


    $vars = hidListCards(1000, true);
    print_r($vars);

}
