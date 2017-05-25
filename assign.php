<?php

include 'config.php';
$pgm = 'assign.php';

head('Assign RFID Cards/FOBs');

if (!empty($_REQUEST['Save'])) {
}
// The person is selected
elseif (!empty($_REQUEST['pid'])) {
    $loc = $_REQUEST['loc'];
    $pid = $_REQUEST['pid'];
    if ($pid != 'add') {
	$query = "
	SELECT *
	FROM people
	WHERE id = $pid
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
    editlist('Prefix', 'prefix', $row['prefix'], $prefixes);
    editrow('First Name', 'fname', $row['fname'], 60);
    editrow('Middle Name', 'mname', $row['mname'], 6);
    editrow('Last Name', 'lname', $row['lname'], 60);
    editrow('Suffix', 'suffix', $row['suffix'], 20);
    editfunc('Building', 'location', $row['location'], 'sel_location');
    editrow('Apartment', 'apt', $row['apt'], 20);
    editrow('Apartment Phone', 'phone', $row['phone'], 20);
    editrow('Email', 'email', $row['email'], 20);
    editrfid('RFID', $loc, $pid);
    ?>
    </table>
    <input type="submit" name="Save" value="Save" />
    </form>
    <?php
}
// Location is selected.
elseif (!empty($_REQUEST['loc'])) {
    $loc = $_REQUEST['loc'];

    $query = "
    SELECT a.id,
        a.prefix,
        a.fname,
        a.mname,
        a.lname,
        a.suffix,
        a.phone,
        a.email
    FROM people a
    WHERE loc = $loc
    ORDER BY a.lname,a.fname
    ";

    $r = sql_query($query);
    // echo "<pre>",print_r($r),"</pre>";

    ?>
    <table>
    <thead>
    <tr>
    <th>Name</th>
    <th>Location</th>
    <th>Phone</th>
    <th>Email</th>
    </th>
    </thead>
    <tbody>
    <?php
    while ($row = mysql_fetch_array($r)) {
        $name = $row['prefix'] ? $row['prefix'].' ' : '';
        $name .= $row['fname'] ? $row['fname'].' ' : '';
        $name .= $row['lname'] ? $row['lname'].' ' : '';
        $name .= $row['suffix'] ? $row['suffix'] : '';
        $location = $row['name'];
	echo "<tr>",
	    "<td><a href='?loc=$loc&amp;pid={$row['id']}'>$name</a></td>",
            "<td>{$row['name']}</td>",
            "<td>{$row['phone']}</td>",
            "<td>{$row['email']}</td>",
	    "</tr>\n";
    }
    echo "<tr><td><a href='?loc=$loc&amp;pid=add'>Add</a></td></tr>\n";
    ?>
    </table>
    <?php
    
    
}
else {
    $query = "
    SELECT *
    FROM loc
    ORDER BY name
    ";

    $r = mysql_query($query) or die($query);

    ?>
    <table>
    <thead>
    <tr>
    <th>Name</th>
    <th>Address1</th>
    <th>Address2</th>
    <th>City</th>
    <th>State</th>
    <th>Zip</th>
    <th>Apts</th>
    <th>IP Subnet</th>
    </th>
    </thead>
    <tbody>
    <?php
    while ($row = mysql_fetch_array($r)) {
	echo "<tr>",
	    "<td><a href='$pgm?loc={$row['id']}'>{$row['name']}</a></td>",
	    "<td>{$row['address1']}</td>",
	    "<td>{$row['address2']}</td>",
	    "<td>{$row['city']}</td>",
	    "<td>{$row['state']}</td>",
	    "<td>{$row['zip']}</td>",
	    "<td>{$row['apts']}</td>",
	    "<td>{$row['ip_subnet']}</td>",
	    "</tr>\n";
    }
    echo "<tr><td><a href='$pgm?id=add'>Add</a></td></tr>\n";
    ?>
    </table>
    <?php
    
}
