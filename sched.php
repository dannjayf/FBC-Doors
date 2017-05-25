<?php
global $dows;

include 'config.php';
include 'config_hid.php';
$pgm = 'sched.php';

if (empty($_SESSION['loc'])) {
    setLocation($pgm);
}
elseif (!empty($_REQUEST['loc']) and $_REQUEST['loc'] == 'new') {
    setLocation($pgm);
}
else {
    $loc = $_SESSION['loc'];
    $tmenu = "<a href=$pgm>Select</a> | <a href=$pgm?loc=new>Location</a>";

    head(getLocation().'|Door Schedules',$tmenu);

    if (!empty($_REQUEST['Save'])) {

        $id = $_REQUEST['id'];
        $name = mysql_real_escape_string($_REQUEST['name']);

        $asset_id = $_REQUEST['asset_id'] ? $_REQUEST['asset_id'] : 'null';
        $sdate = sql_date($_REQUEST['sdate']);
        $edate = sql_date($_REQUEST['edate'],1);
        $open_time = sql_time($_REQUEST['open_time']);
        $close_time = sql_time($_REQUEST['close_time']);
        $dow = implode(',', $_REQUEST['dow']);

        $user = $_SERVER['PHP_AUTH_USER'];
        $ts = date('Y-m-d H:i:s');

        if ($id == 'add') {
            $query = "
            INSERT INTO entry
            (name, 
            loc,
            asset_id,
            sdate,
            edate,
            open_time,
            close_time,
            dow,
            addby,
            created_at,
            chgby,
            updated_at
            )
            VALUES (
            '$name',
            $loc,
            $asset_id,
            $sdate,
            $edate,
            $open_time,
            $close_time,
            '$dow',
            '$user',
            '$ts',
            '$user',
            '$ts'
            ) ";
        }
        else {
            $query = "
            UPDATE entry
            SET
                name = '$name',
                loc = $loc,
                asset_id = $asset_id,
                sdate = $sdate,
                edate = $edate,
                open_time = $open_time,
                close_time = $close_time,
                dow = '$dow',
                chgby = '$user',
                updated_at = '$ts'
            WHERE
                id = $id
            ";
        }
        $r = sql_query($query);

        echo "<h1>Updated</h1>";
        echo "<a href='$pgm'>Continue</a>\n";
        echo "<meta http-equiv='refresh' content='2;url=$pgm'>\n";
        
    }
    elseif(!empty($_REQUEST['Del'])) {
        
        $id = $_REQUEST['Del'];
        $query = "
        DELETE From entry
        WHERE id = $id
        ";

        sql_query($query);
        echo "<h1>Deleted</h1>";
        echo "<a href='$pgm'>Continue</a>\n";
        echo "<meta http-equiv='refresh' content='2;url=$pgm'>\n";
    }
    elseif(!empty($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        if ($id != 'add') {
            $query = "
            SELECT *
            FROM entry
            WHERE id = $id
            ";

            $r = sql_query($query);
            $row = mysql_fetch_array($r);
        }
        // Setup defaults
        else {
            $row['name'] = '';
        }

        ?>
        <form action="<?php echo $pgm;?>" method="POST">
        <input type="hidden" name="id" value = "<?php echo $id;?>" />
        <table>
        <?php
        editrow('Schedule Name', 'name', $row['name'], 60);
        editfunc('Door', 'asset_id', $row['asset_id'], 'sel_asset');
        editrow('Start Date', 'sdate', dsp_date($row['sdate']), 10);
        editrow('End Date', 'edate', dsp_date($row['edate']), 10);
        editrow('Open Time', 'open_time', dsp_time($row['open_time']), 10);
        editrow('Close Time', 'close_time', dsp_time($row['close_time']), 10);
        editfunc('Days', 'dow', $row['dow'], 'sel_dow');
        /*
        if ($id != 'add')
            editrfid('RFID Device', $row['loc'], $row['id']);
        */
        ?>
        </table>
        <input type="submit" name="Save" value="Save" />
        <input type="submit" name="Cancel" value="Cancel" />
        </form>
        <?php
    }
    else {
        $query = "
        SELECT *
        FROM entry
        ORDER BY name,sdate
        ";

        $r = sql_query($query);
        // echo "<pre>",print_r($r),"</pre>";

        ?>
        <table>
        <thead>
        <tr>
        <th>Name</th>
        <th>Door</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Open</th>
        <th>Close</th>
        <th>DOW</th>
        <th>Del</th>
        </th>
        </thead>
        <tbody>
        <?php
        while ($row = mysql_fetch_array($r)) {
            if ($row['asset_id'])
                $door = getAssetName($row['asset_id']);
            else
                $door = '??';
            $del = "<a href=\"$pgm?Del={$row['id']}\">X</a>";
            echo "<tr>",
                "<td><a href='?id={$row['id']}'>{$row['name']}</a></td>",
                "<td>$door</td>",
                "<td>",dsp_date($row['sdate']),"</td>",
                "<td>",dsp_date($row['edate']),"</td>",
                "<td>",dsp_time($row['open_time']),"</td>",
                "<td>",dsp_time($row['close_time']),"</td>",
                "<td>",$row['dow'],"</td>",
                "<td style=\"text-align:center;\">",$del,"</td>",
                "</tr>\n";
        }
        echo "<tr><td><a href='?id=add'>Add</a></td></tr>\n";
        ?>
        </table>
        <?php
    }
}
?>
