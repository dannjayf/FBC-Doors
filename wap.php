<?php

include 'config.php';
$pgm = 'wap.php';
if (empty($_SESSION['loc'])) {
    setLocation($pgm);
}
elseif (!empty($_REQUEST['loc']) and $_REQUEST['loc'] == 'new') {
    setLocation($pgm);
}
else {
    $loc = $_SESSION['loc'];
    $tmenu = "<a href=$pgm?loc=new>Location</a>";

    head(getLocation().'|Wireless Access Point');

    if (!empty($_REQUEST['Save'])) {
        $id = $_REQUEST['id'];
        $name = mysql_real_escape_string($_REQUEST['name']);
        $model = $_REQUEST['model'] == '' ? 'null' : $_REQUEST['model'];
        $sn = mysql_real_escape_string($_REQUEST['sn']);
        $mac_address = $_REQUEST['mac_address'];
        $ip_address = $_REQUEST['ip_address'];
        $location = mysql_real_escape_string($_REQUEST['location']);
        $admin_user = mysql_real_escape_string($_REQUEST['admin_user']);
        $admin_pass = mysql_real_escape_string($_REQUEST['admin_pass']);
        $ssid = mysql_real_escape_string($_REQUEST['ssid']);
        $wpass = mysql_real_escape_string($_REQUEST['wpass']);
        $active = $_REQUEST['active'] == 1 ? 1 : 0;

        if ($id == 'add') {
            $query = "
            INSERT INTO wap
            (name, 
            model,
            sn,
            mac_address,
            ip_address,
            loc,
            location,
            admin_user,
            admin_pass,
            ssid,
            wpass,
            active
            )
            VALUES (
            '$name',
            $model,
            '$sn',
            '$mac_address',
            '$ip_address',
            $loc,
            '$location',
            '$admin_user',
            '$admin_pass',
            '$ssid',
            '$wpass',
            $active
            ) ";
        }
        else {
            $query = "
            UPDATE wap
            SET
                name = '$name',
                model = $model,
                sn = '$sn',
                mac_address = '$mac_address',
                ip_address = '$ip_address',
                loc= $loc,
                location = '$location',
                admin_user = '$admin_user',
                admin_pass = '$admin_pass',
                ssid = '$ssid',
                wpass = '$wpass',
                active = $active
            WHERE
                id = $id
            ";
        }
        $r = sql_query($query);

        echo "<h2>Updated</h2>";
        echo "<a href='$pgm'>Continue</a>\n";
        echo "<meta http-equiv='refresh' content='2;url=$pgm'>\n";
        
    }
    elseif(!empty($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        if ($id != 'add') {
            $query = "
            SELECT *
            FROM wap
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
        editrow('Name', 'name', $row['name'], 60);
        editfunc('Model', 'model', $row['model'], 'sel_wap');
        editrow('Serial Number', 'sn', $row['sn'], 20);
        editrow('Mac Address', 'mac_address', $row['mac_address'], 20);
        editrow('IP Address', 'ip_address', $row['ip_address'], 20);
        editrow('Location', 'location', $row['location'], 30);
        editrow('Admin User', 'admin_user', $row['admin_user'], 20);
        editrow('Admin Pass', 'admin_pass', $row['admin_pass'], 20);
        editrow('SSID', 'ssid', $row['ssid'], 20);
        editrow('Wireless Pass', 'wpass', $row['wpass'], 20);
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
            a.name,
            a.mac_address,
            a.ip_address,
            c.model as model,
            a.sn,
            a.admin_user,
            a.admin_pass,
            a.ssid,
            a.wpass,
            a.active
        FROM wap a
        LEFT OUTER JOIN waps c ON (a.model = c.id)
        ORDER BY name
        ";

        $r = sql_query($query);
        // echo "<pre>",print_r($r),"</pre>";

        ?>
        <table>
        <thead>
        <tr>
        <th>Name</th>
        <th>Mac Address</th>
        <th>IP Address</th>
        <th>Model</th>
        <th>Serial #</th>
        <th>SSID</th>
        <th>Active</th>
        </th>
        </thead>
        <tbody>
        <?php
        while ($row = mysql_fetch_array($r)) {
            $active = $row['active'] == 1 ? 'Yes' : 'No';
            echo "<tr>",
                "<td><a href='?id={$row['id']}'>{$row['name']}</a></td>",
                "<td>{$row['mac_address']}</td>",
                "<td>{$row['ip_address']}</td>",
                "<td>{$row['model']}</td>",
                "<td>{$row['sn']}</td>",
                "<td>{$row['ssid']}</td>",
                "<td>$active</td>",
                "</tr>\n";
        }
        echo "<tr><td><a href='?id=add'>Add</a></td></tr>\n";
        ?>
        </table>
        <?php
        
    }
}
?>

