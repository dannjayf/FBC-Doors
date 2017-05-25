<?php

include 'config.php';
include 'config_hid.php';
$pgm = 'people.php';
if (empty($_SESSION['loc'])) {
    setLocation($pgm);
}
elseif (!empty($_REQUEST['loc']) and $_REQUEST['loc'] == 'new') {
    setLocation($pgm);
}
else {
    $loc = $_SESSION['loc'];
    $tmenu = "<a href=$pgm?loc=new>Location</a>";

    head(getLocation().'|FIrstB People', $tmenu);

    if (!empty($_REQUEST['Save'])) {
        $id = $_REQUEST['people_id'];
        $nickname = mysql_real_escape_string($_REQUEST['nickname']);
        $fname = mysql_real_escape_string($_REQUEST['fname']);
        $mname = mysql_real_escape_string($_REQUEST['mname']);
        $lname = mysql_real_escape_string($_REQUEST['lname']);
        $prefix = $_REQUEST['prefix'];
        $suffix = mysql_real_escape_string($_REQUEST['suffix']);
        $apt = $_REQUEST['apt'] ? $_REQUEST['apt'] : 'null';
        $phone = mysql_real_escape_string($_REQUEST['phone']);
        $email = mysql_real_escape_string($_REQUEST['email']);

        if ($id == 'add') {
            $query = "
            INSERT INTO people
            (prefix,
            nickname,
            fname, 
            mname,
            lname,
            suffix,
            loc,
            apt,
            phone,
            email
            )
            VALUES (
            '$prefix',
            '$nickname',
            '$fname',
            '$mname',
            '$lname',
            '$suffix',
            $loc,
            $apt,
            '$phone',
            '$email'
            ) ";
        }
        else {
            $query = "
            UPDATE people
            SET
                prefix = '$prefix',
                nickname = '$nickname',
                fname = '$fname',
                mname = '$mname',
                lname = '$lname',
                suffix = '$suffix',
                loc = $loc,
                apt = $apt,
                phone = '$phone',
                email = '$email'
            WHERE
                id = $id
            ";
        }
        $r = sql_query($query);
        
	// Find the new people_id
        if ($id == 'add') {
            $query = "
            SELECT MAX(id)
            FROM people
            ";

            $r = sql_query($query);

            $row = mysql_fetch_row($r);
            $people_id = $row[0];
        }
        else {
            $people_id = $id;
        }

        //add Names to RFID
        addName($loc, array(
            'fname' => $fname,
            'mname' => $mname,
            'lname' => $lname,
            'email' => $email,
            'phone' => $phone,
            'people_id' => $people_id,
            ));

        ?>
        <h2>Updated</h2>
        <a href='<?php echo $pgm ?>'>Continue</a>
        <meta http-equiv='refresh' content='2;url=people.php'>
        <?php
        
    }
    elseif(!empty($_REQUEST['people_id'])) {
        $id = $_REQUEST['people_id'];
        if ($id != 'add') {
            $query = "
            SELECT *
            FROM people
            WHERE id = $id
            ";

            $r = sql_query($query);
            $row = mysql_fetch_array($r);
        }
		else {
			$row = array();
		}
        // Setup defaults

        ?>
        <form action="<?php echo $pgm;?>" method="POST">
        <input type="hidden" name="people_id" value = "<?php echo $id;?>" />
        <table>
        <?php
        editlist('Prefix', 'prefix', $row['prefix'], $prefixes);
        editrow('Nickname', 'nickname', $row['nickname'], 60);
        editrow('First Name', 'fname', $row['fname'], 60);
        editrow('Middle Name', 'mname', $row['mname'], 6);
        editrow('Last Name', 'lname', $row['lname'], 60);
        editrow('Suffix', 'suffix', $row['suffix'], 10);
        // editfunc('Apartment', 'apt', $row['apt'], 'sel_apt');
        // editrow('Apartment Phone', 'phone', $row['phone'], 20);
        editrow('Email', 'email', $row['email'], 40);
        ?>
        </table>
        <input type="submit" name="Save" value="Save" />
        </form>
        <?php
    }
    else {
        $query = "
        SELECT a.id,
            a.prefix,
            a.nickname,
            a.fname,
            a.mname,
            a.lname,
            a.suffix,
            a.phone,
            a.email,
	    a.hid_timestamp,
            b.card_id,
            c.apt
        FROM people a
        LEFT OUTER JOIN token b ON (a.id = b.people_id)
        LEFT OUTER JOIN apt c ON (a.apt = c.id)
        WHERE a.loc = $loc
        ORDER BY a.lname,a.fname
        ";

        $r = sql_query($query);
        // echo "<pre>",print_r($r),"</pre>";

        ?>
        <table>
        <thead>
        <tr>
        <th>Name</th>
        <th>Nickname</th>
        <th>Phone</th>
        <th>Email</th>
        <th>RFID Card/FOB</th>
        <th>Last Entry</th>
        </th>
        </thead>
        <tbody>
        <?php
        while ($row = mysql_fetch_array($r)) {
            $name = $row['prefix'] ? $row['prefix'].' ' : '';
            $name .= $row['fname'] ? $row['fname'].' ' : '';
            $name .= $row['lname'] ? $row['lname'].' ' : '';
            $name .= $row['suffix'] ? $row['suffix'] : '';
            $loc = $row['name'];
	    $timestamp = strtotime($row['hid_timestamp']);
	    $timestamp = date('Y',$timestamp) < '1900' ? '' : date('m/d/y h:i a', $timestamp);
            echo "<tr>",
                "<td><a href='$pgm?people_id={$row['id']}'>$name</a></td>",
                "<td>{$row['nickname']}</td>",
                "<td>{$row['phone']}</td>",
                "<td>{$row['email']}</td>",
                "<td align=center>{$row['card_id']}</td>",
                "<td>$timestamp</td>",
                "</tr>\n";
        }
        echo "<tr><td><a href='$pgm?people_id=add'>Add</a></td></tr>\n";
        ?>
        </table>
        <?php
        
    }
}
?>
