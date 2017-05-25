<?php
include 'config.php';
include 'config_hid.php';

$query = "
    SELECT
        a.description,
        a.ip_address,
        a.admin_user,
        a.admin_pass,
        b.name
    FROM asset a
    LEFT OUTER JOIN loc b ON (a.loc = b.id)
    WHERE a.active=1";

$r = sql_query($query);

while( $row = mysql_fetch_assoc($r)) {
    echo "\n{$row['description']}\n";
    $host = $row['ip_address'];
    $username = $row['admin_user'];
    $password = $row['admin_pass'];
 
    hidSetTime();
}

?>
