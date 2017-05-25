<?php
include 'config.php';

$pgm = 'ata_config.php';


if (!empty($_REQUEST['Create']) or !empty($_REQUEST['Save'])) {
    head("ATA Config Update");
    ob_flush(); flush();

    $id = $_REQUEST['id'];
    $a = $_REQUEST['a'];
    $e = $_REQUEST['e'];
    $p = $_REQUEST['p'];
    $n = $_REQUEST['n'];
    $l = $_REQUEST['l'];

    // First copy info to database
    foreach ($a as $i) {
	$data = get_data($id, $i);
	// If the entry exists update
	if ($data['port']) {
	    $e[$i] = sql_escape($e[$i]);
	    $n[$i] = sql_escape($n[$i]);
	    $p[$i] = sql_escape($p[$i]);
	    $l[$i] = sql_escape($l[$i]);
	    $query = "
	    UPDATE data
	    SET
		ext = '$e[$i]',
		name = '$n[$i]',
		secret = '$p[$i]',
		location = '$l[$i]'
	    WHERE
		ata_id = $id
		and port = $i
	    ";
	}
	else {
	    $query = "
	    INSERT INTO data
	    (port, ata_id, ext, name, secret, location)
	    VALUES($i, $id, '$e[$i]', '$n[$i]', '$p[$i]', '$l[$i]')
	    ";
	}

	$r = mysql_query($query) or die($query);
    }
    echo "<h2>Updated</h2>";
    // Now crate the config file.
    if (!empty($_REQUEST['Create'])) {
	$info = get_atainfo($id);
	$cfgname = str_replace(" ", "_", $info['name']).'.cfg';

	create_cfg($id, $cfgname);
    }

    echo "<a href='$pgm'>Continue</a>\n";
    echo "<meta http-equiv='refresh' content='2;url=$pgm'>\n";
}
elseif (!empty($_REQUEST['id']) and empty($_REQUEST['Cancel'])) {
    $id = $_REQUEST['id'];

    // First get the info about the ATA
    $info = get_atainfo($id);
    $tmenu = "<a href='$pgm'>Select</a>";

    head("ATA Config for ".$info['name'], $tmenu);
    ob_flush(); flush();
    ?>
    <form action="<?php echo $pgm;?>" method="post">
    <input type="hidden" name="id" value="<?php echo $id; ?>" />
    <table>
    <thead>
    <tr>
    <th>Port</th>
    <th>Extension</th>
    <th>Name</th>
    <th>Secret</th>
    <th>Location</th>
    <th>Status</th>
    <th>Updated</th>
    <th>RemoteIP</th>
    </tr>
    </thead>
    <tbody>
    <?php
    // Retreive info from database for the number of ports
    for ($i = 1; $i <= $info['ports']; $i++) {
	$data = get_data($id, $i);
	$a[$i] = $data['port'];
	$e[$i] = empty($data['ext']) ? '' : fix_quote($data['ext']);
	$p[$i] = empty($data['secret']) ? '' : fix_quote($data['secret']);
	$n[$i] = empty($data['ext']) ? '' : fix_quote($data['name']);
	$l[$i] = empty($data['location']) ? '' : fix_quote($data['location']);
	$ip = $data['ip'] == '' ? '' : $data['ip'];
	$st = $data['stat'] == '' ? '' : $data['stat'];
	$ts = $data['updts'];
        if (strtotime('now') - strtotime($ts) > 399) $ip = $st = $ts = '';
        if ($st == '') $ts = '';
        if ($ts != '' and substr($ts,0,4) != '1969')
            $ts = strftime("%m/%d/%y %H:%M", strtotime($ts));
        if (substr($ts,0,8) == '12/31/69')
            $ts = '';

	echo "<tr>\n",
	    "<td align=center>$i</td><input type=\"hidden\" name=\"a[$i]\" value=\"$i\" />\n",
	    "<td><input type=\"text\" name=\"e[$i]\" value=\"$e[$i]\" /></td>\n",
	    "<td><input type=\"text\" name=\"n[$i]\" value=\"$n[$i]\" /></td>\n",
	    "<td><input type=\"text\" name=\"p[$i]\" value=\"$p[$i]\" /></td>\n",
	    "<td><input type=\"text\" name=\"l[$i]\" value=\"$l[$i]\" /></td>\n",
	    "<td>$st</td>\n",
	    "<td>$ts</td>\n",
	    "<td>$ip</td>\n",
	    "</tr>\n";

    }
    ?>
    </table>
    <input type="submit" name="Save" value="Save" />
    <input type="submit" name="Cancel" value="Cancel" />
    <?php
    if (trim($info['model']) == 'Patton')
        echo "<input type='submit' name='Create' value='Create' />\n";
    ?>
    </form>
    <?php
}
else {
    head("ATA Config");
    $query = "
    SELECT *
    FROM ata
    ORDER BY Name
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
    <th>IP Address</th>
    <th>Status</th>
    <th>Config</th>
    </th>
    </thead>
    <tbody>
    <?php
    while ($row = mysql_fetch_array($r)) {
	$info = get_atainfo($row['id']);
	$stat = get_ata_status($row['id']);
	$cfgname = str_replace(" ", "_", $info['name']).'.cfg';
	if (file_exists($cfgname)) {
	    $cfg = "<a href=\"$cfgname\">Config</a>";
	}
	else {
	    $cfg = '<br />';
	}

	echo "<tr>",
	    "<td><a href='$pgm?id={$row['id']}'>{$row['name']}</a></td>",
	    "<td>{$row['location']}</td>",
	    "<td align=center>{$row['model']}</td>",
	    "<td align=center>{$row['ports']}</td>",
	    "<td align=center>{$row['ip_addr']}</td>",
	    "<td align=center>$stat</td>",
	    "<td align=center>$cfg</td>",
	    "</tr>\n";
    }
    echo "<tr><td><a href='$pgm?id=add'>Add</a></td></tr>\n";
    ?>
    </table>
    <?php
    
}

?>
</body>
</html>
