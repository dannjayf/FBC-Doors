<?php
include 'config.php';
$pgm = 'location.php';

head("MUS Locations");

if (!empty($_REQUEST['Save'])) {
    $id = $_REQUEST['id'];
    $name = mysql_real_escape_string($_REQUEST['name']);
    $address1 = mysql_real_escape_string($_REQUEST['address1']);
    $address2 = mysql_real_escape_string($_REQUEST['address2']);
    $city = mysql_real_escape_string($_REQUEST['city']);
    $state = mysql_real_escape_string($_REQUEST['state']);
    $zip = mysql_real_escape_string($_REQUEST['zip']);
    $apts = $_REQUEST['apts'] = '' ? 'null' : $_REQUEST['apts']+0;
    $port_offset = $_REQUEST['port_offset'] = '' ? 'null' : $_REQUEST['port_offset']+0;
    $ip_subnet = mysql_real_escape_string($_REQUEST['ip_subnet']);
    $ip_gateway = mysql_real_escape_string($_REQUEST['ip_gateway']);
    $vpncmd = mysql_real_escape_string($_REQUEST['vpncmd']);

    if ($id == 'add') {
	$query = "
	INSERT INTO loc
	(name, 
        address1,
        address2,
        city,
        state,
        zip,
        apts,
        ip_subnet,
        port_offset,
        ip_gateway,
        vpncmd
        )
	VALUE(
        '$name',
        '$address1',
        '$address2',
        '$city',
        '$state',
        '$zip',
        $apts,
        '$ip_subnet',
        $port_offset,
        '$ip_gateway',
        '$vpncmd'
        ) ";
    }
    else {
	$query = "
	UPDATE loc
	SET
            name = '$name',
            address1 = '$address1',
            address2 = '$address2',
            city = '$city',
            state = '$state',
            zip = '$zip',
            apts = $apts,
            ip_subnet = '$ip_subnet',
            port_offset = $port_offset,
            ip_gateway = '$ip_gateway',
            vpncmd = '$vpncmd'
	WHERE
	    id = $id
	";
    }
    $r = mysql_query($query) or die($query);

    echo "<h2>Updated</h2>";
    echo "<a href='$pgm'>Continue</a>\n";
    echo "<meta http-equiv='refresh' content='2;url=$pgm'>\n";
    
}
elseif(!empty($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    if ($id != 'add') {
	$query = "
	SELECT *
	FROM loc
	WHERE id = $id
	";

	$r = mysql_query($query) or die($query);
	$row = mysql_fetch_array($r);
    }
    // Setup defaults
    $row['domain'] = $row['domain'] == '' ? '199.89.249.44' : $row['domain'];
    $row['sip_port'] = $row['sip_port'] == '' ? '5060' : $row['sip_port'];
    $row['gateway'] = $row['gateway'] == '' ? '172.20.1.1' : $row['gateway'];
    $row['ip_mask'] = $row['ip_mask'] == '' ? '255.255.255.0' : $row['ip_mask'];
    $row['dns'] = $row['dns'] == '' ? '8.8.8.8' : $row['dns'];

    ?>
    <form action="<?php echo $pgm;?>">
    <input type="hidden" name="id" value = "<?php echo $id;?>" />
    <table>
    <?php
    editrow('Name', 'name', $row['name'], 60);
    editrow('Address1', 'address1', $row['address1'], 60);
    editrow('Address2', 'address2', $row['address2'], 60);
    editrow('City', 'city', $row['city'], 20);
    editrow('State', 'state', $row['state'], 5);
    editrow('Zip Code', 'zip', $row['zip'], 20);
    editrow('Number of Apts', 'apts', $row['apts'], 4);
    editrow('IP Subnet', 'ip_subnet', $row['ip_subnet'], 20);
    editrow('Port Offset', 'port_offset', $row['port_offset'], 6);
    editrow('IP Gateway', 'ip_gateway', $row['ip_gateway'], 20);
    editrow('VPN', 'vpncmd', $row['vpncmd'], 20);
    ?>
    </table>
    <input type="submit" name="Save" value="Save" />
    </form>
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
    <th>IP Gateway</th>
    </th>
    </thead>
    <tbody>
    <?php
    while ($row = mysql_fetch_array($r)) {
	echo "<tr>",
	    "<td><a href='$pgm?id={$row['id']}'>{$row['name']}</a></td>",
	    "<td>{$row['address1']}</td>",
	    "<td>{$row['address2']}</td>",
	    "<td>{$row['city']}</td>",
	    "<td>{$row['state']}</td>",
	    "<td>{$row['zip']}</td>",
	    "<td>{$row['apts']}</td>",
	    "<td>{$row['ip_subnet']}</td>",
	    "<td>{$row['ip_gateway']}</td>",
	    "</tr>\n";
    }
    echo "<tr><td><a href='$pgm?id=add'>Add</a></td></tr>\n";
    ?>
    </table>
    <?php
    
}
?>
