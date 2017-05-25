<?php

include 'config.php';
$pgm = 'vcom.php';
if (empty($_SESSION['loc'])) {
    setLocation($pgm);
}
elseif (!empty($_REQUEST['loc']) and $_REQUEST['loc'] == 'new') {
    setLocation($pgm);
}
else {
    $loc = $_SESSION['loc'];
    $tmenu = "<a href=$pgm?loc=new>Location</a>";

    head(getLocation().'|Video Intercom', $tmenu);

    if (!empty($_REQUEST['Save'])) {
        $id = $_REQUEST['id'];
        $name = $_REQUEST['name'];
        $mac_address = mac_addr($_REQUEST['mac_address']);
        $ip_address = $_REQUEST['ip_address'];
        $model = sql_escape($_REQUEST['model']);
        $sn = sql_escape($_REQUEST['sn']);
        $admin_user = sql_escape($_REQUEST['admin_user']);
        $admin_pass = sql_escape($_REQUEST['admin_pass']);
        $ip_subnet = $_REQUEST['ip_subnet'];
        $ext = $_REQUEST['ext'];
        $secret = sql_escape($_REQUEST['secret']);
        $domain = $_REQUEST['domain'];
        $sip_port = $_REQUEST['sip_port'];
        $active = $_REQUEST['active'] == 1 ? 1 : 0;

        if ($id == 'add') {
            $query = "
            INSERT INTO vcom
            (name, 
            mac_address,
            ip_address,
            loc,
            model,
            sn,
            admin_user,
            admin_pass,
            ext,
            secret,
            domain,
            sip_port,
            active
            )
            VALUES (
            '$name',
            '$mac_address',
            '$ip_address',
            $loc,
            '$model',
            '$sn',
            '$admin_user',
            '$admin_pass',
            '$ext',
            '$secret',
            '$domain',
            '$sip_port',
            $active
            ) ";
        }
        else {
            $query = "
            UPDATE vcom
            SET
                name = '$name',
                mac_address = '$mac_address',
                ip_address = '$ip_address',
                loc = $loc,
                model = '$model',
                sn = '$sn',
                admin_user = '$admin_user',
                admin_pass = '$admin_pass',
                ext = '$ext',
                secret = '$secret',
                domain = '$domain',
                sip_port = '$sip_port',
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
            FROM vcom
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
        editrow('Mac Address', 'mac_address', $row['mac_address'], 20);
        editrow('IP Address', 'ip_address', $row['ip_address'], 20);
        editrow('Model', 'model', $row['model'], 20);
        editrow('Serial Number', 'sn', $row['sn'], 20);
        editrow('Admin User', 'admin_user', $row['admin_user'], 10);
        editrow('Admin Pass', 'admin_pass', $row['admin_pass'], 10);
        editrow('Extension', 'ext', $row['ext'], 10);
        editrow('Secret', 'secret', $row['secret'], 10);
        editrow('Domain', 'domain', $row['domain'], 16);
        editrow('SIP Port', 'sip_port', $row['sip_port'], 5);
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
            a.model,
            a.sn,
            a.ext,
            a.domain,
            a.active
        FROM vcom a
        WHERE loc = $loc
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
        <th>Exten</th>
        <th>Domain</th>
        <th>Active</th>
        </th>
        </thead>
        <tbody>
        <?php
        while ($row = mysql_fetch_array($r)) {
            $active = $row['active'] == 1 ? 'Yes' : 'No';
            $ips = explode(".", $row['ip_address']);
            $port = 6800 + $ips[3];
            if ($row['active'])
                $lnk = "<a target='_blank' href='http://admin:123456@colo.myurbansky.us:$port'>{$row['ip_address']}</a>";
            else
                $lnk = $row['ip_address'];
            echo "<tr>",
                "<td><a href='$pgm?id={$row['id']}'>{$row['name']}</a></td>",
                "<td>{$row['mac_address']}</td>",
                "<td>$lnk</td>",
                "<td>{$row['model']}</td>",
                "<td>{$row['sn']}</td>",
                "<td>{$row['ext']}</td>",
                "<td>{$row['domain']}</td>",
                "<td>$active</td>",
                "</tr>\n";
            ob_flush(); flush();
        }
        echo "<tr><td><a href='?id=add'>Add</a></td></tr>\n";
        ?>
        </table>
        <?php
        
    }
}
?>
