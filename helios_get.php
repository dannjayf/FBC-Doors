<?php
include 'config.php';
include 'config_helios.php';

$query = "
SELECT 
    id,
    ip_address,
    admin_user,
    admin_pass
FROM helios
WHERE active = 1
ORDER BY ip_address
";

$r = sql_query($query);

while ($row = mysql_fetch_array($r)) {

    $host = $row['ip_address'];
    $did = $row['id'];
    $user = $row['admin_user'];
    $pass = $row['admin_pass'];

    echo "$host\n$user\n$pass\n";

    $lines = getLines("http://$host/notification");
    echo $lines;

    continue;

    // Retreive the login
    $lines = getLines("http://$host/enu/login.xml.p", array('user' => $user, 'pwd' => $pass));
    echo $lines;

    $vars = xmlParse($lines);

    // Session ID
    $sid = $vars['sid'];

    // Request state
    $lines = getLines("http://$host/enu/sec/state.xml.p?sid=$sid");
    $vars = xmlParse($lines);

    
    // Setup to retreive config info
    $file = 'config.xml';
    $data = array (
    'cmd-options' => 'P', 
    );

    // Request config file
    $lines = getLines("http://$host/enu/sec/config.xml?sid=$sid", $data);
    echo $lines;

    // Parse the XML into a variable.
    $vars2 = xmlParse2($lines);
    // $vars2 = xml2array($lines);
    error_log(date('Y-m-d H:M:s ').print_r($vars2,true)."\n",3,'/tmp/helios.txt');

    // $result = file_put_contents('../data/'.$vars['MacAddress'].'-P.xml', $lines);


    // Logout
    $lines = getLines("http://$host/enu/logout.p?sid=$sid");

}

?>
