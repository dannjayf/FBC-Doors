<?php
include 'config.php';

function getOutput ($fp, $n = 1) {
    $out = '';
    for ($i = 0; $i < $n ; $i++) {
        do {   
            $out .= fread($fp, 1024);    // read line by line, or at least small chunks
            $stat = socket_get_status($fp);
        } while($stat["unread_bytes"]);
        usleep(125000);
    }

    return $out;
}

function pattonStatus($host, $user, $pw) {
    # This is the difficult part, the Telnet header
    $header1=chr(0xFF).chr(0xFB).chr(0x1F).chr(0xFF).chr(0xFB).
    chr(0x20).chr(0xFF).chr(0xFB).chr(0x18).chr(0xFF).chr(0xFB).
    chr(0x27).chr(0xFF).chr(0xFD).chr(0x01).chr(0xFF).chr(0xFB).
    chr(0x03).chr(0xFF).chr(0xFD).chr(0x03).chr(0xFF).chr(0xFC).
    chr(0x23).chr(0xFF).chr(0xFC).chr(0x24).chr(0xFF).chr(0xFA).
    chr(0x1F).chr(0x00).chr(0x50).chr(0x00).chr(0x18).chr(0xFF).
    chr(0xF0).chr(0xFF).chr(0xFA).chr(0x20).chr(0x00).chr(0x33).
    chr(0x38).chr(0x34).chr(0x30).chr(0x30).chr(0x2C).chr(0x33).
    chr(0x38).chr(0x34).chr(0x30).chr(0x30).chr(0xFF).chr(0xF0).
    chr(0xFF).chr(0xFA).chr(0x27).chr(0x00).chr(0xFF).chr(0xF0).
    chr(0xFF).chr(0xFA).chr(0x18).chr(0x00).chr(0x58).chr(0x54).
    chr(0x45).chr(0x52).chr(0x4D).chr(0xFF).chr(0xF0);
    $header2=chr(0xFF).chr(0xFC).chr(0x01).chr(0xFF).chr(0xFC).
    chr(0x22).chr(0xFF).chr(0xFE).chr(0x05).chr(0xFF).chr(0xFC).chr(0x21);

    # connecting
    $fp=fsockopen("$host",23);

    # sending the Telnet header
    fputs($fp,$header1);
    usleep(125000);
    fputs($fp,$header2);
    usleep(125000);

    # login
    $output =  getOutput($fp, 1);
    fputs($fp,"$user\r");
    $output .=  getOutput($fp, 1);
    fputs($fp,"$pw\r");

    # root looks nice
    fputs($fp,"enable\r");
    $output .=  getOutput($fp, 1);
    fputs($fp,"show context sip-gateway detail 4\r");
    $output .=  getOutput($fp, 1);

    # some tests
    fputs($fp,"exit\r");        
    $output .=  getOutput($fp, 1);
    fputs($fp,"exit\r");        
    $output .=  getOutput($fp, 1);
    // fputs($fp,"echo year telnet php connect works|wall\r");

    # we had to wait

    # show the output
    $output .=  getOutput($fp, 3);
     
    // $output = str_replace("\n", "<br>", $output);
    fclose($fp);

    return $output;
}

function grandstreamStatus($host, $user, $pw) {
    # This is the difficult part, the Telnet header
    $header1=chr(0xFF).chr(0xFB).chr(0x1F).chr(0xFF).chr(0xFB).
    chr(0x20).chr(0xFF).chr(0xFB).chr(0x18).chr(0xFF).chr(0xFB).
    chr(0x27).chr(0xFF).chr(0xFD).chr(0x01).chr(0xFF).chr(0xFB).
    chr(0x03).chr(0xFF).chr(0xFD).chr(0x03).chr(0xFF).chr(0xFC).
    chr(0x23).chr(0xFF).chr(0xFC).chr(0x24).chr(0xFF).chr(0xFA).
    chr(0x1F).chr(0x00).chr(0x50).chr(0x00).chr(0x18).chr(0xFF).
    chr(0xF0).chr(0xFF).chr(0xFA).chr(0x20).chr(0x00).chr(0x33).
    chr(0x38).chr(0x34).chr(0x30).chr(0x30).chr(0x2C).chr(0x33).
    chr(0x38).chr(0x34).chr(0x30).chr(0x30).chr(0xFF).chr(0xF0).
    chr(0xFF).chr(0xFA).chr(0x27).chr(0x00).chr(0xFF).chr(0xF0).
    chr(0xFF).chr(0xFA).chr(0x18).chr(0x00).chr(0x58).chr(0x54).
    chr(0x45).chr(0x52).chr(0x4D).chr(0xFF).chr(0xF0);
    $header2=chr(0xFF).chr(0xFC).chr(0x01).chr(0xFF).chr(0xFC).
    chr(0x22).chr(0xFF).chr(0xFE).chr(0x05).chr(0xFF).chr(0xFC).chr(0x21);

    # connecting
    $fp=fsockopen("$host",23);

    # sending the Telnet header
    // fputs($fp,$header1);
    // usleep(125000);
    // fputs($fp,$header2);
    // usleep(512000);
    $out =  getOutput($fp, 2);

    # login
    fputs($fp,"$pw\r");

    $out =  getOutput($fp);

    # status
    fputs($fp,"status\r");

    $output = getOutput($fp);


    # finish
    fputs($fp,"exit\r");        

    $output .=  getOutput($fp);

    fclose($fp);
     
    return $output;
}

function getPorts($id) {
    $query = "
    SELECT port,ext
    FROM data
    WHERE ata_id = $id
    ORDER BY port
    ";

    $r = sql_query($query);
    while ($row = mysql_fetch_array($r)) {
        $ports[$row['port']] = $row['ext'];
    }

    return $ports;
}

$query = "
SELECT id,ip_address,model
FROM ata
WHERE ip_address <> ''
ORDER BY ip_address
";

$r = sql_query($query);
while ($row = mysql_fetch_array($r)) {

    switch(substr($row['model'],0,1)) {
    case 'P':
        $hosts[] = $row['ip_address'];
        break;
    case 'G':
        $gshosts[] = $row['ip_address'];
        $gsports[$row['ip_address']] = getPorts($row['id']);
        break;
    default:
    }

}

// Patton
foreach ($hosts as $host) {

    echo "\n\n$host\n";
    $next = false;
    $lines = explode("\r", pattonStatus($host, 'admin', 'le-5971591'));
    foreach ($lines as $line) {
        if (strpos($line, 'SIP Registration:')) {
            $d = explode(':', $line);
            $d = explode('@', $d[2]);
            $num = $d[0];
            echo "$num: ";
            $next = true;
        }
        elseif ($next) {
            $next = false;
            $d = explode(':', $line);
            echo trim($d[1]),"\n";
        }
    }
}

// GrandStream
foreach ($gshosts as $host) {

    echo "\n\n$host\n";
    $lines = explode("\r", grandstreamStatus($host, 'admin', 'le-5971591'));
    foreach ($lines as $line) {
        if (strpos($line, '   Port')) {
            $d = explode(':', $line);

            $num = substr($d[0], 10);
            $reg = substr(trim($d[1]),11);
            if ($gsports[$host][$num] != '')
                echo "$num: $reg\n";
        }
    }
}

?>
