<?php

$nports = 32;
$datafile = '/home/dann/patton/data.txt';
$cfgfile  = '/home/dann/patton/patton.cfg';
$tplfile  = '/home/dann/patton/template.cfg';

if (empty($_POST['Update'])) {

    $fp = fopen($datafile, "r") or die("Unable to open $datafile");
    $i = 0;
    while (!feof($fp)) {
	$line = trim(fgets($fp, 1024));
	$data = explode("\t", $line);
	$i = $data[0];
	if ($i > 0) {
	    $a[$i] = $data[0];
	    $e[$i] = empty($data[1]) ? '' : $data[1];
	    $p[$i] = empty($data[2]) ? '' : $data[2];
	    $n[$i] = empty($data[3]) ? '' : $data[3];
	    $l[$i] = empty($data[4]) ? '' : $data[4];
	}
    }
    fclose($fp);
    
    ?>
    <html>
    <head>
    <title>Update Patton</title>
    </head>
    <body>
    <h1>Edit Patton ATA</h1>
    <form action="" method="post">
    <table>
    <thead>
    <tr>
    <th>Port</th>
    <th>Extension</th>
    <th>Name</th>
    <th>Secret</th>
    <th>Location</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach($a as $i) {
	echo "<tr>\n",
	    "<td>$i</td><input type='hidden' name='a[$i]' value='$i' />\n",
	    "<td><input type='text' name='e[$i]' value='$e[$i]' /></td>\n",
	    "<td><input type='text' name='n[$i]' value='$n[$i]' /></td>\n",
	    "<td><input type='text' name='p[$i]' value='$p[$i]' /></td>\n",
	    "<td><input type='text' name='l[$i]' value='$l[$i]' /></td>\n",
	    "</tr>\n";
    }
    ?>
    </tbody>
    </table>
    <input type='submit' name='Update' value='Update' />
    </form>
    </body>
    </html>
    <?php
}
else {
    // Merging function (update keywords in text)
    function template_merge($text, $key, $value) {
	$d = explode('{'.$key.'}', $text);
	for ($i = 1; $i < count($d); $i++) {
	    $d[$i] = $d[$i-1].$value.$d[$i];
	}
	$i--;

	return $d[$i];
    }

    // Retreave the template
    function get_template() {
	global $tplfile;

	$cfg = file_get_contents($tplfile) or die("unable to read file ($tplfile)");

	return $cfg;
    }

    $a = $_POST['a'];
    $e = $_POST['e'];
    $p = $_POST['p'];
    $n = $_POST['n'];
    $l = $_POST['l'];

    // First save the file
    $fp = fopen($datafile, "w");
    foreach ($a as $i) {
	fputs($fp, "$i\t$e[$i]\t$p[$i]\t$n[$i]\t$l[$i]\n");
    }
    fclose($fp);

    // Now update the config file
    $cfg = get_template();
    foreach($a as $i) {
	$cfgi = $i+100;
	$cfge = "e$cfgi";
	$cfgp = "p$cfgi";
	$cfgn = "n$cfgi";

	if ($e[$i] == '') {
	    $cfg = template_merge($cfg, $cfge, $cfgi);
	    $cfg = template_merge($cfg, $cfgp, $cfgi);
	    $cfg = template_merge($cfg, $cfgn, $cfgi);
	}
	else {
	    $cfg = template_merge($cfg, $cfge, $e[$i]);
	    $cfg = template_merge($cfg, $cfgp, $p[$i]);
	    $cfg = template_merge($cfg, $cfgn, $n[$i]);
	}
    }

    file_put_contents($cfgfile, $cfg);
    
    ?>
    <html>
    <head>
    <title>Update Patton</title>
    </head>
    <body>
    <h1>Patton Config File Created</h1>
    </body>
    </html>
    <?php

}

?>
