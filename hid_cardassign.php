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
    echo "\n\n$host\n";


    $query = "
    SELECT 
        a.id,
        a.fname,
        a.lname,
        b.pid,
        c.token
    FROM people a
    JOIN hid b  ON (b.asset_id = $hid and b.people_id = a.id)
    JOIN token c  ON (c.people_id = a.id)
    JOIN acl ON ($hid = acl.asset_id and c.id = acl.token_id)
    ";

    $r2 = sql_query($query);

    while ($row2 = mysql_fetch_array($r2)) {
        $pid = $row2['pid'];
        $cid = $row2['token'];
        echo $cid," ";

        // If this person is assgined then enable.
        if ($cid) {
            echo $hid,' ',$sid,' ',$row2['id'],' ',$row2['fname'],' ',$row2['lname'],' ',$row2['pid'],' ', $row2['token'],"\n";


            $vars = hidAssignCard($pid, $cid);
            // print_r($vars); echo "\n";

            if (array_key_exists('errorMessage', $vars[0]['attributes'])) 
                echo $vars[0]['attributes']['errorMessage']," ";
            else
                echo "Assign $cid to $pid\n";


            $vars = hidAssignSchedule($pid, $sid);
            if (array_key_exists('errorMessage', $vars[0]['attributes'])) 
                echo $vars[0]['attributes']['errorMessage']," ";
            else
                echo "Assign $sid to $pid\n";
        }
        // Remove assignment
        else {
            $vars = hidAssignCard("", $cid);
            print_r($vars); echo "\n";
        }

    }

}
