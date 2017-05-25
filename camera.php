<?php

include 'config.php';
$pgm = 'camera.php';
if (empty($_SESSION['loc'])) {
    setLocation($pgm);
}
elseif (!empty($_REQUEST['loc']) and $_REQUEST['loc'] == 'new') {
    setLocation($pgm);
}
else {
    $loc = $_SESSION['loc'];
    $tmenu = "<a href=$pgm?loc=new>Location</a>";

    head(getLocation().'|Cameras', $tmenu);

    if (!empty($_REQUEST['Save'])) {
        $id = $_REQUEST['id'];
        $name = mysql_real_escape_string($_REQUEST['name']);
        $model = $_REQUEST['model'] == '' ? 'null' : $_REQUEST['model'];
        $sn = mysql_real_escape_string($_REQUEST['sn']);
        $mac_address = mac_addr($_REQUEST['mac_address']);
        $ip_address = $_REQUEST['ip_address'];
        $location = mysql_real_escape_string($_REQUEST['location']);
        $admin_user = mysql_real_escape_string($_REQUEST['admin_user']);
        $admin_pass = mysql_real_escape_string($_REQUEST['admin_pass']);
        $view_user = mysql_real_escape_string($_REQUEST['view_user']);
        $view_pass = mysql_real_escape_string($_REQUEST['view_pass']);
        $width = $_REQUEST['width'] == '' ? 'null' : $_REQUEST['width'];
        $height = $_REQUEST['height'] == '' ? 'null' : $_REQUEST['height'];
        $active = $_REQUEST['active'] == 1 ? 1 : 0;

        if ($id == 'add') {
            $query = "
            INSERT INTO camera
            (name, 
            model,
            sn,
            mac_address,
            ip_address,
            loc,
            location,
            admin_user,
            admin_pass,
            view_user,
            view_pass,
            width,
            height,
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
            '$view_user',
            '$view_pass',
            $width,
            $height,
            $active
            ) ";
        }
        else {
            $query = "
            UPDATE camera
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
                view_user = '$view_user',
                view_pass = '$view_pass',
                width = $width,
                height = $height,
                active = $active
            WHERE
                id = $id
            ";
        }
        error_log($query."\n", 3, '/tmp/camera.php');
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
            FROM camera
            WHERE id = $id
            ";

            $r = sql_query($query);
            $row = mysql_fetch_array($r);
            
            // Setup defaults
            if ($row['model'] and $row['width'] == '' or $row['height'] == '') {
                $query = "
                SELECT width, height
                FROM cameras
                WHERE id = {$row['model']}";
                
                $r = sql_query($query);
                $mod = mysql_fetch_array($r);

                $row['width'] = $row['width'] ? $row['width'] : $mod['width'];
                $row['height'] = $row['height'] ? $row['height'] : $mod['height'];
            }
        }
        

        ?>
        <form action="" method="POST">
        <input type="hidden" name="id" value = "<?php echo $id;?>" />
        <table>
        <?php
        editrow('Name', 'name', $row['name'], 60);
        editfunc('Model', 'model', $row['model'], 'sel_camera');
        editrow('Serial Number', 'sn', $row['sn'], 20);
        editrow('Mac Address', 'mac_address', $row['mac_address'], 20);
        editrow('IP Address', 'ip_address', $row['ip_address'], 20);
        editrow('Location on Building', 'location', $row['location'], 30);
        editrow('Admin User', 'admin_user', $row['admin_user'], 20);
        editrow('Admin Pass', 'admin_pass', $row['admin_pass'], 20);
        editrow('Viewing User', 'view_user', $row['view_user'], 20);
        editrow('Viewing Pass', 'view_pass', $row['view_pass'], 20);
        editrow('Width', 'width', $row['width'], 4);
        editrow('Height', 'height', $row['height'], 4);
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
            a.location,
            c.model as model,
            a.sn,
            a.active
        FROM camera a
        LEFT OUTER JOIN cameras c ON (a.model = c.id)
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
        <th>Building</th>
        <th>Model</th>
        <th>Serial #</th>
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
                "<td>{$row['location']}</td>",
                "<td>{$row['model']}</td>",
                "<td>{$row['sn']}</td>",
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

