<?php
include 'config.php';
include 'config_helios.php';


$did = 3; // Main Entry
$query = "
SELECT apt
FROM people
WHERE loc = 3 and apt > '300'
GROUP BY apt
";

$r = sql_query($query);

while ($row = mysql_fetch_array($r)) {
    $apt = $row['apt'];
    echo "$apt\n";
    $pbook = 'pbook_'.($apt-1);
    $query = "UPDATE params SET value = 1 WHERE device_id = $did and object = '$pbook' and attribute = 'en'";
    $r2 = sql_query($query);
    $query = "UPDATE params SET value = 'Apt_$apt' WHERE device_id = $did and object = '$pbook' and attribute = 'name'";
    $r2 = sql_query($query);
    $query = "UPDATE params SET value = '6$apt' WHERE device_id = $did and object = '$pbook' and attribute = 'n2'";
    $r2 = sql_query($query);
    $query = "UPDATE params SET value = '$apt' WHERE device_id = $did and object = '$pbook' and attribute = 's2'";
    $r2 = sql_query($query);

}
