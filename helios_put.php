<?php

include 'config.php';
include 'config_helios.php';

$query = "
SELECT 
    id,
    ip_address,
    mac_address,
    admin_user,
    admin_pass
FROM helios
WHERE active = 1
and ip_address like '172.17.6%'
and id=3
ORDER BY ip_address
";

$r = sql_query($query);

while ($row = mysql_fetch_array($r)) {
    $ini = get_Ini($row['id']);

    $host = $row['ip_address'];
    $user = $row['admin_user'];
    $pass = $row['admin_pass'];
    $mac = $row['mac_address'];
    $mac = strtoupper($mac);
    $mac = str_replace(':', '-', $mac);



    // WRite out the config file
    $fp = fopen("$mac.ini", "w");

    fputs($fp, "Version=0\r\n");

    foreach ($ini as $obj => $attr) {
        fputs($fp, "\r\n[$obj]\r\n");
        foreach ($attr as $k => $v)
            fputs($fp, "$k=$v\r\n");
    }
    fclose($fp);


    $lines = getLines("http://$host/enu/login.xml.p", "user=$user&pwd=$pass");

    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($lines), $xml_values);
    xml_parser_free($parser);
    foreach ($xml_values as $data) {
        if ($data['type'] == 'complete')
            $vars[$data['tag']] = $data['value'];
    }


    $sid = $vars['sid'];
    echo "$sid\n";
    $file = "$mac.ini";

    $data = array (
    'cmd-options' => 'NSCP', 
    'file-config' => "@$file"
    );

    // Put the config file
    $lines = getLines("http://$host/enu/sec/result.xml.p?sid=$sid", $data);

    print_r($lines);

    // Logout
    $lines = getLines("http://$host/enu/logout.p?sid=$sid");

}

?>
