<?php

include 'config.php';
include 'config_hid.php';
$pgm = 'token.php';
if (empty($_SESSION['loc'])) {
    setLocation($pgm);
}
elseif (!empty($_REQUEST['loc']) and $_REQUEST['loc'] == 'new') {
    setLocation($pgm);
}
else {
    $loc = $_SESSION['loc'];
    $sid = 1;
    $tmenu = "<a href=$pgm>Select</a> | <a href=$pgm?loc=new>Location</a>";

    head(getLocation().'|RFID Cards/FOBs',$tmenu);

    if (!empty($_REQUEST['Save'])) {


        $id = $_REQUEST['id'];
        $token = mysql_real_escape_string($_REQUEST['token']);
        $card_id = mysql_real_escape_string($_REQUEST['card_id']);
        $people_id = empty($_REQUEST['people_id']) ? 'null' : $_REQUEST['people_id'];
        $sdatetime = sql_date($_REQUEST['sdatetime']);
        $edatetime = sql_date($_REQUEST['edatetime']);
        // So it can be changed
        $loc = $_REQUEST['loc'];
        $loc = empty($loc) ? 'null' : $loc;


        $user = $_SERVER['PHP_AUTH_USER'];
        $ts = date('Y-m-d H:i:s');

        if ($id == 'add') {
            $query = "
            INSERT INTO token
            (token, 
            loc,
            card_id,
            people_id,
            sdatetime,
            edatetime,
            addby,
            addts,
            chgby,
            chgts
            )
            VALUES (
            '$token',
            $loc,
            '$card_id',
            $people_id,
            $sdatetime,
            $edatetime,
            '$user',
            '$ts',
            '$user',
            '$ts'
            ) ";
        }
        else {
            $query = "
            UPDATE token
            SET
                token = '$token',
                loc = '$loc',
                card_id = '$card_id',
                people_id= $people_id,
                sdatetime = $sdatetime ,
                edatetime = $edatetime ,
                chgby = '$user',
                chgts = '$ts'
            WHERE
                id = $id
            ";
        }
        $r = sql_query($query);

        if ($id == 'add') {
            $query = "
            SELECT id
            FROM token
            WHERE token = '$token'
            ";
            $r = sql_query($query);
            $row = mysql_fetch_row($r);
            $id = $row[0];
        }

        // update acl
        updACL($id, $_REQUEST['asset']);

        // Update Controllers
        
        $query = "
        SELECT 
            a.id,
            b.token_id,
            c.token,
            c.card_id,
            a.ip_address,
            a.admin_user,
            a.admin_pass,
            c.people_id,
            e.pid,
            f.id as people_id,
            f.fname,
            f.mname,
            f.lname,
            f.email,
            f.phone
        FROM asset as a
        JOIN acl as b ON (b.asset_id = a.id)
        JOIN token as c ON (b.token_id = c.id)
        LEFT JOIN hid as e ON (a.id = e.asset_id and c.people_id = e.people_id)
        LEFT JOIN people as f ON (c.people_id = f.id)
        WHERE a.loc = $loc
            and b.token_id = $id
        ";
        $r = sql_query($query);
        while ($row = mysql_fetch_assoc($r)) {
            $host = $row['ip_address'];
            $username = $row['admin_user'];
            $password = $row['admin_pass'];
            // First add card
            $vars = hidAddCard($row['token']);
            echo "<pre>"; 
            echo "Add Card "; print_r($vars[0]['type']); echo "\n";

            // Check for 'B' FOB
            $btoken = '';
            if (substr($row['token'],0,3) == '00B') {
                $btoken = strtoupper(sprintf('%08s', base_convert(substr(base_convert($row['token'], 16, 2),0,-1),2,16)));
            }
            elseif (substr($row['token'],0,3) == '02B') {
                $btoken = strtoupper(sprintf('%08s', base_convert(substr(base_convert($row['token'], 16, 2),1,-1),2,16)));
            }
            if ($btoken) {
                $q = "SELECT id FROM token WHERE token = '$btoken'";
                $r2 = sql_query($q);
                if (mysql_num_rows($r2) == 0)  {
                    $bcard = $row['card_id'].'B';
                    $query = "INSERT INTO token (token, loc, card_id, people_id, addby, addts, chgby, chgts) VALUES (
                        '$btoken',
                        $loc,
                        '$bcard',
                        {$row['people_id']},
                        '$user',
                        '$ts',
                        '$user',
                        '$ts')";

                    sql_query($query);
                }
            }

            // Add person
            if (!$row['pid']) {
                $vars = hidAddPerson($row);
                echo print_r($vars,true),"\n";
                $pid = $vars[0]['attributes']['cardholderID'];

                // Update lookup for CardHolderID
                if ($pid) {
                    $query = "INSERT INTO hid (asset_id, people_id, pid) VALUES({$row['id']}, {$row['people_id']}, $pid)";
                    sql_query($query);
                }
            }

            $vars = hidAssignCard($row['pid'], $row['token']);
            echo "Assign Card "; print_r($vars[0]['type']); echo "\n";

            $vars = hidAssignSchedule($row['pid'], $sid);
            echo "Assign Schedule "; print_r($vars[0]['type']); echo "\n";
            echo '</pre>';
            ob_flush(); flush();
        }


        echo "<h1>Updated</h1>";
        echo "<a href='$pgm'>Continue</a>\n";
        echo "<meta http-equiv='refresh' content='2;url=$pgm'>\n";
        
    }
    elseif(!empty($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        if ($id != 'add') {
            $query = "
            SELECT *
            FROM token
            WHERE id = $id
            ";

            $r = sql_query($query);
            $row = mysql_fetch_array($r);
        }
        else {
            $tokens = getTokenList();
            $row['loc'] = $loc;

            // Find the next unassigned number
            $q = "SELECT MAX(card_id) FROM token WHERE card_id REGEXP '^[0-9]+$'";
            $r2 = sql_query($q);
            $row2 = mysql_fetch_row($r2);

            $row['card_id'] = $row2[0]+1;
        }
        // Setup defaults

        ?>
        <form action="<?php echo $pgm;?>" method="POST">
        <input type="hidden" name="id" value = "<?php echo $id;?>" />
        <table>
        <?php
	if ($id == 'add') {
	    editlist('RFID', 'token', '', $tokens);
	}
	else {
	    editrow('RFID', 'token', $row['token'], 60);
	}
    editrow('Card/FOB ID', 'card_id', $row['card_id'], 10);
    editfunc('Location', 'loc', $row['loc'], 'sel_loc');
    editfunc('Person', 'people_id', $row['people_id'], 'sel_people');
    editrow('Start Date/Time', 'sdatetime', dsp_date($row['sdatetime']), 30);
    editrow('End Date/Time', 'edatetime', dsp_date($row['edatetime']), 30);
	if ($id != 'add')
	    editrfid('RFID Device', $row['loc'], $row['id']);
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
            a.token,
            a.card_id,
            CONCAT_WS(', ', b.lname, b.fname) as name,
            a.sdatetime,
            a.edatetime,
            a.loc
        FROM token a
        LEFT OUTER JOIN people b ON (a.people_id = b.id)
        ORDER BY b.lname, b.fname, a.token
        ";

        $r = sql_query($query);
        // echo "<pre>",print_r($r),"</pre>";

        ?>
        <table>
        <thead>
        <tr>
        <th>RFID</th>
        <th>Card/FOB ID</th>
        <th>Location</th>
        <th>Person</th>
        <th>Doors</th>
        <th>Start</th>
        <th>End</th>
        <th>Last Used</th>
        </th>
        </thead>
        <tbody>
        <?php
        while ($row = mysql_fetch_array($r)) {
            $doors = getRFIDs($row['id']);
	    $timestamp = hidLastEvent($row['id']);
            echo "<tr>",
                "<td><a href='?id={$row['id']}'>{$row['token']}</a></td>",
                "<td>{$row['card_id']}</td>",
                "<td>",getLocation($row['loc']),"</td>",
                "<td>{$row['name']}</td>",
                "<td>$doors</td>",
                "<td>",dsp_date($row['sdatetime']),"</td>",
                "<td>",dsp_date($row['edatetime']),"</td>",
                "<td>$timestamp</td>",
                "</tr>\n";
        }
        echo "<tr><td><a href='?id=add'>Add</a></td></tr>\n";
        ?>
        </table>
        <?php
        
    }
}
?>
