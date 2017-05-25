<html>
<head>
<title>Status</title>

<style>
<!--
table {
  border-collapse: collapse;
  border:0px solid #000;
  padding:3px;
}
th {
  border:1px solid #000;
  padding:3px;
}
td {
  border:1px solid #000;
  padding:3px;
  text-align:center;
}
.dim {
  color: #ccc;
}
-->
</style>
</head>
<body>
<?php

$username = 'admin';
$password = 'le-5971591';



// curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));              

for ($port = 0; $port < 32; $port++) {

    $url = "http://172.20.1.5/fxs-port-stat.html?LOCAL_STOR:slot=0&LOCAL_STOR:port=$port";
    // echo "$url<br />\n";
    $process = curl_init() or die ("Init Error");                                                                         
    curl_setopt($process, CURLOPT_USERPWD, "$username:$password"); 
    curl_setopt($process, CURLOPT_URL, $url);
    curl_setopt($process, CURLOPT_HEADER, 0);
    curl_setopt($process, CURLOPT_TIMEOUT, 30); 
    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($process) or curl_error($process);  

    $lines = explode("\n", $return);

    foreach ($lines as $line) {
	if (strpos($line, 'Port state')) {
	    list ($key, $data) = explode(':',$line);
	    $output[$port][0] = trim($data);
	}
	elseif (strpos($line, 'Hook state:')) {
	    list ($key, $data) = explode(':',$line);
	    $output[$port][1] = trim($data);
	}
    }

}

$url = 'http://172.20.1.5/sip-gw-stat.html?LOCAL_STOR:gateway=GW_SIP_ALL_EXTENSIONS';

$process = curl_init() or die ("Init Error");                                                                         
curl_setopt($process, CURLOPT_USERPWD, "$username:$password"); 
curl_setopt($process, CURLOPT_URL, $url);
curl_setopt($process, CURLOPT_HEADER, 0);
curl_setopt($process, CURLOPT_TIMEOUT, 30); 
curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
$return = curl_exec($process) or curl_error($process);  
$lines = explode("\n", $return);
$port = -1;
foreach ($lines as $line) {
    if (strpos($line, 'SIP Registration:')) {
	$ln = 0;
	$port++;
    }
    elseif ($ln > 0 and $ln < 2) {
	list ($key, $data) = explode(':',$line);
	if ($port >= 0)
	    $output[$port][2] = trim($data);
    }
    $ln++;
}

?>
<table>
<thead>
<tr>
<th>Port</th>
<th>Status</th>
<th>On Hook</th>
<th>Register</th>
</tr>
</thead>
<tbody>
<?php
foreach ($output as $port => $data) {
    if ($data[2] != 'Registered') $data[2] = "<span class='dim'>$data[2]</span>";
    echo "<tr>",
	"<td>".($port+1)."</td>",
	"<td>$data[0]</td>",
	"<td>$data[1]</td>",
	"<td>$data[2]</td>",
	"</tr>\n";
}

?>
</tbody>
</table>
</body>
</html>
