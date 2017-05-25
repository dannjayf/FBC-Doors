<?php
include 'config.php';
$pgm = 'ata_edit.php';

head("ATA Edit");

if (!empty($_REQUEST['Save'])) {
    $id = $_REQUEST['id'];
    $name = $_REQUEST['name'];
    $model = $_REQUEST['model'];
    $location = $_REQUEST['location'];
    $ports = $_REQUEST['ports'];
    $domain = $_REQUEST['domain'];
    $sip_port = $_REQUEST['sip_port'];
    $gateway = $_REQUEST['gateway'];
    $ip_addr = $_REQUEST['ip_addr'];
    $ip_mask = $_REQUEST['ip_mask'];
    $dns = $_REQUEST['dns'];
    $macaddress = $_REQUEST['macaddress'];
    $syslog = mysql_real_escape_string($_REQUEST['syslog']);
    $snmp = mysql_real_escape_string($_REQUEST['snmp']);
    $ip_address = mysql_real_escape_string($_REQUEST['ip_address']);

    if ($id == 'add') {
	$query = "
	INSERT INTO ata
	(name, location, model, ports, domain, sip_port, gateway, ip_addr, ip_mask, dns, macaddress, syslog, snmp, ip_address)
	VALUE('$name',
            '$location',
            '$model',
            $ports,
            '$domain',
            '$sip_port',
            '$gateway',
            '$ip_addr',
            '$ip_mask',
            '$dns',
            '$macaddress',
            '$syslog',
            '$snmp',
            '$ip_address'
            ) ";
    }
    else {
	$query = "
	UPDATE ata
	SET
	    name = '$name',
	    location = '$location',
	    model = '$model',
	    ports = $ports,
            domain = '$domain',
            sip_port = '$sip_port',
            gateway = '$gateway',
            ip_addr = '$ip_addr',
            ip_mask = '$ip_mask',
            dns = '$dns',
            macaddress = '$macaddress',
            syslog = '$syslog',
            snmp = '$snmp',
            ip_address = '$ip_address'
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
	FROM ata
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
    editrow('Name', 'name', $row['name'], 40);
    editrow('Location', 'location', $row['location'], 40);
    editrow('Model', 'model', $row['model'], 40);
    editrow('Number of Ports', 'ports', $row['ports'], 3);
    editrow('SIP Domain', 'domain', $row['domain'], 20);
    editrow('SIP Port', 'sip_port', $row['sip_port'], 5);
    editrow('IP Address', 'ip_addr', $row['ip_addr'], 20);
    editrow('IP Netmask', 'ip_mask', $row['ip_mask'], 20);
    editrow('Default Gateway', 'gateway', $row['gateway'], 20);
    editrow('DNS', 'dns', $row['dns'], 20);
    editrow('MAC Address', 'macaddress', $row['macaddress'], 20);
    editrow('Syslog', 'syslog', $row['syslog'], 20);
    editrow('SNMP Server', 'snmp', $row['snmp'], 20);
    editrow('Assigned IP', 'ip_address', $row['ip_address'], 20);
    ?>
    </table>
    <input type="submit" name="Save" value="Save" />
    </form>
    <?php
}
else {
    $query = "
    SELECT *
    FROM ata
    ORDER BY name
    ";

    $r = mysql_query($query) or die($query);

    ?>
    <table>
    <thead>
    <tr>
    <th>Name</th>
    <th>Location</th>
    <th>Model</th>
    <th>Ports</th>
    <th>IP Addr</th>
    <th>Gateway</th>
    <th>MAC Address</th>
    </th>
    </thead>
    <tbody>
    <?php
    while ($row = mysql_fetch_array($r)) {
	echo "<tr>",
	    "<td><a href='$pgm?id={$row['id']}'>{$row['name']}</a></td>",
	    "<td>{$row['location']}</td>",
	    "<td>{$row['model']}</td>",
	    "<td>{$row['ports']}</td>",
	    "<td>{$row['ip_addr']}</td>",
	    "<td>{$row['gateway']}</td>",
	    "<td>{$row['macaddress']}</td>",
	    "</tr>\n";
    }
    echo "<tr><td><a href='$pgm?id=add'>Add</a></td></tr>\n";
    ?>
    </table>
    <?php
    
}
?>
