<?php
include 'config.php';
$pgm = 'apt.php';
if (empty($_SESSION['loc'])) {
    setLocation($pgm);
}
elseif (!empty($_REQUEST['loc']) and $_REQUEST['loc'] == 'new') {
    setLocation($pgm);
}
else {
    $loc = $_SESSION['loc'];
    $tmenu = "<a href=$pgm?loc=new>Location</a>";

    head(getLocation().'|Units', $tmenu);

    if (!empty($_REQUEST['Save'])) {
        $id = $_REQUEST['id'];
        $apt = sql_escape($_REQUEST['apt']);
        $floor = sql_escape($_REQUEST['floor']);
        $ext = sql_escape($_REQUEST['ext']);
        $ip_addr = sql_escape($_REQUEST['ip_addr']);


        if ($id == 'add') {
            $query = "
            INSERT INTO apt
            (loc, 
            apt,
            floor,
            ext,
            ip_addr
            )
            VALUE(
            $loc,
            '$apt',
            '$floor',
            '$ext',
            '$ip_addr'
            ) ";
            $r = mysql_query($query) or die($query);

            // After adding find the ID
            $query = "
            SELECT MAX(id)
            FROM apt
            ";
            $r = mysql_query($query) or die($query);
            $row = mysql_fetch_array($r);
            $id = $row[0];
            
        }
        else {
            $query = "
            UPDATE apt
            SET
                loc = $loc,
                apt = '$apt',
                floor = '$floor',
                ext = '$ext',
                ip_addr = '$ip_addr'
            WHERE
                id = $id
            ";
            $r = mysql_query($query) or die($query);
        }

        // Update the ACLs
        updHACL($id, $_REQUEST['helios']);
        updAACL($id, $_REQUEST['asset']);

        echo "<h2>Updated</h2>";
        echo "<a href='$pgm'>Continue</a>\n";
        echo "<meta http-equiv='refresh' content='2;url=$pgm'>\n";
        
    }
    elseif(!empty($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        if ($id != 'add') {
            $query = "
            SELECT *
            FROM apt
            WHERE id = $id
            ";

            $r = mysql_query($query) or die($query);
            $row = mysql_fetch_array($r);
        }

        ?>
        <form action="<?php echo $pgm;?>">
        <input type="hidden" name="id" value = "<?php echo $id;?>" />
        <table>
        <?php
        editrow('Apartment', 'apt', $row['apt'], 10);
        editrow('Floor', 'floor', $row['floor'], 10);
        editrow('Extension', 'ext', $row['ext'], 10);
        edithelios('Door Panel', 'helios', $loc, $id);
        editrow('Intercom', 'ip_addr', $row['ip_addr'], 16);
        edit_apt_rfid('Select<br>Default<br>RFIDs', $loc, $id);
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
            a.apt,
            a.floor,
            a.ext,
            a.ip_addr
        FROM apt a
        WHERE loc = $loc
        ORDER BY apt
        ";

        $r = mysql_query($query) or die($query);

        ?>
        <table>
        <thead>
        <tr>
        <th>Apartment</th>
        <th>Floor</th>
        <th>Extension</th>
        <th>Intercom</th>
        <th>RFIDs</th>
        </th>
        </thead>
        <tbody>
        <?php
        while ($row = mysql_fetch_array($r)) {
            extract($row);
            $rfids = get_apt_rfid($loc, $id);
            echo "<tr>",
                "<td><a href='$pgm?id=$id'>$apt</a></td>",
                "<td>$floor</td>",
                "<td>$ext</td>",
                "<td>$ip_addr</td>",
                "<td>$rfids</td>",
                "</tr>\n";
        }
        echo "<tr><td colspan=4><hr /></td></tr><tr><td><a href='$pgm?id=add'>Add</a></td></tr>\n";
        ?>
        </table>
        <?php
        
    }
}
?>
