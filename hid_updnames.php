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


    $query = "
    SELECT 
        a.id,
        a.fname,
        a.mname,
        a.lname,
        a.email,
        a.phone,
        b.pid
    FROM people a
    LEFT OUTER JOIN hid b ON (b.asset_id = $hid and b.people_id = a.id)
    WHERE b.pid is not null
    ";

    $r2 = sql_query($query);

    while ($row2 = mysql_fetch_array($r2)) {
        echo $row2['fname'],' ',$row2['lname'],"\n";

        $vars = hidEditPerson($row2['pid'], $row2);
        echo  $vars[0]['tag'],"\n";

    }
}
?>
