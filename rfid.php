<?php

include 'config.php';
$pgm = 'rfid.php';
if (empty($_SESSION['loc'])) {
    setLocation($pgm);
}
elseif (!empty($_REQUEST['loc']) and $_REQUEST['loc'] == 'new') {
    setLocation($pgm);
}
else {
    $loc = $_SESSION['loc'];
    $tmenu = "<a href=$pgm?loc=new>Location</a>";


    head(getLocation().'|RFID Devices', $tmenu);

    if (!empty($_REQUEST['Save'])) {
        $user = $_SERVER['PHP_AUTH_USER'];
        $id = $_REQUEST['id'];
        $assetid = mysql_real_escape_string($_REQUEST['assetid']);
        $description = mysql_real_escape_string($_REQUEST['description']);
        // $loc = $_REQUEST['loc'];
        $asset_type = $_REQUEST['asset_type'] ? $_REQUEST['asset_type'] : 'null';
        $loc = $loc ? $loc : 'null';
        $timezone = $_REQUEST['timezone'];
        $dow = $_REQUEST['dow'];
        $sn = mysql_real_escape_string($_REQUEST['sn']);
        $mac_address = mysql_real_escape_string($_REQUEST['mac_address']);
        $ip_address = mysql_real_escape_string($_REQUEST['ip_address']);
        $location = mysql_real_escape_string($_REQUEST['location']);
        $admin_user = mysql_real_escape_string($_REQUEST['admin_user']);
        $admin_pass = mysql_real_escape_string($_REQUEST['admin_pass']);
        $active = $_REQUEST['active'] == 1 ? 1 : 0;
        $ts = date('Y-m-d H:i:s');

        if ($id == 'add') {
            $query = "
            INSERT INTO asset
            (assetid, 
            description,
            timezone,
            dow,
            sn,
            mac_address,
            ip_address,
            loc,
            location,
            admin_user,
            admin_pass,
            active,
            addby,
            addts,
            chgby,
            chgts
            )
            VALUES (
            '$assetid',
            '$description',
            '$timezone',
            '$dow',
            '$sn',
            '$mac_address',
            '$ip_address',
            $loc,
            '$location',
            '$admin_user',
            '$admin_pass',
            '$active',
            '$user',
            '$ts',
            '$user',
            '$ts'
            ) ";
        }
        else {
            $query = "
            UPDATE asset
            SET
                assetid = '$assetid',
                description = '$description',
                timezone = '$timezone',
                dow = '$dow',
                sn = '$sn',
                mac_address = '$mac_address',
                ip_address = '$ip_address',
                loc= $loc,
                location = '$location',
                admin_user = '$admin_user',
                admin_pass = '$admin_pass',
                active = '$active',
                chgby = '$user',
                chgts = '$ts'
            WHERE
                id = $id
            ";
        }
        $r = sql_query($query);

        echo "<h1>Updated</h1>";
        echo "<a href='$pgm'>Continue</a>\n";
        echo "<meta http-equiv='refresh' content='2;url=$pgm'>\n";
        
    }
    elseif(!empty($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        if ($id != 'add') {
            $query = "
            SELECT *
            FROM asset
            WHERE id = $id
            ";

            $r = sql_query($query);
            $row = mysql_fetch_array($r);
        }
        // Setup defaults

        ?>
        <form action="" method="POST">
        <input type="hidden" name="id" value = "<?php echo $id;?>" />
        <table>
        <?php
        editrow('Asset ID', 'assetid', $row['assetid'], 60);
        editrow('Description', 'description', $row['description'], 60);
        editfunc('Asset Type', 'asset_type', $row['asset_type'], 'sel_asset_type');
        editfunc('Location', 'loc', $row['loc'], 'sel_loc');
        editrow('Serial Number', 'sn', $row['sn'], 20);
        editrow('Mac Address', 'mac_address', $row['mac_address'], 20);
        editrow('IP Address', 'ip_address', $row['ip_address'], 20);
        editrow('Location', 'location', $row['location'], 30);
        editrow('Admin User', 'admin_user', $row['admin_user'], 20);
        editrow('Admin Pass', 'admin_pass', $row['admin_pass'], 20);
        editchk('Active', 'active', $row['active'], 1);
        ?>
        </table>
        <input type="submit" name="Save" value="Save" />
        </form>
        <?php
    }
    else {
        $query = "
        SELECT 
            a.id,
            a.assetid,
            b.name as a_type,
            a.description,
            a.mac_address,
            a.ip_address,
            a.sn,
            a.active
        FROM asset a
	LEFT OUTER JOIN assets b ON (a.asset_type = b.id)
        WHERE loc = $loc
        ORDER BY description
        ";

        $r = sql_query($query);
        // echo "<pre>",print_r($r),"</pre>";

        ?>
        <table>
        <thead>
        <tr>
        <th>Asset ID</th>
        <th>Description</th>
        <th>Type</th>
        <th>Mac Address</th>
        <th>IP Address</th>
        <th>Serial #</th>
        <th>Active</th>
        </th>
        </thead>
        <tbody>
        <?php
        while ($row = mysql_fetch_array($r)) {
            $assetid = $row['assetid'] != '' ? $row['assetid'] : '???';
            $active = $row['active'] == 1 ? 'Yes' : 'No';
            echo "<tr>",
                "<td><a href='?id={$row['id']}'>$assetid</a></td>",
                "<td>{$row['description']}</td>",
                "<td>{$row['a_type']}</td>",
                "<td>{$row['mac_address']}</td>",
                "<td>{$row['ip_address']}</td>",
                "<td>{$row['sn']}</td>",
                "<td>$active</td>",
                "</tr>\n";
        }
        echo "<tr><td><br /></td></tr><tr><td><a href='?id=add'>Add</a></td></tr>\n";
        ?>
        </table>
        <?php
        
    }
}
?>

