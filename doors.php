<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

include 'config.php';
include 'config_hid.php';
global $host, $username, $password;

function getDoorStateH($h) {
    global $host, $username, $password;
    $host = $h;

    $vars = hidStatus();
    // error_log(date('Y-m-d H:i:s ')."$h:$username:$password\n".print_r($vars,true)."\n", 3, '/tmp/doors.log');

    switch ($vars[0]['attributes']['relayState']) {
    case 'set':
        $s = 'Unlocked';
        break;
    case 'unset':
        $s = 'Locked';
        break;
    default:
        $s = '<br />';
        break;
    }

    return $s;
}

function set2NLocked($id, $locked) {
    $r = sql_query("UPDATE asset SET locked = $locked where id = $id");
    return;
}

function xml2NParse($lines) {
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($lines), $xml_values);
    xml_parser_free($parser);

    $out = array();
    foreach ($xml_values as $k => $v)
        if ($v['type'] == 'complete')
            $out[] = str_replace("\n",'',$v);

    return $out;
}

function get2NState($h) {
   
    $host = $h;

    $vars = xml2NParse(getLines("http://$host/enu/lockstate.xml.p"));
    // error_log(date('Y-m-d H:i:s ').print_r($vars,true)."\n",3,'/tmp/doors.log');
    switch (substr($vars[1]['value'],0,1)) {
    case 0:
        $s = 'Locked';
        break;
    case 1:
        $s = 'Unlocked';
        break;
    default:
        $s = '<br />';
        break;
    }

    return $s;
}

function getStatus() {
    global $host, $username, $password;

    $doors[0] = get2NState('192.168.100.220');

    $username = 'admin';
    $password = 'FirstB';

    $doors[1] = getDoorStateH('192.168.100.210');
    $doors[2] = getDoorStateH('192.168.100.211');
    $doors[3] = getDoorStateH('192.168.100.212');
    $doors[4] = getDoorStateH('192.168.100.214');
    $doors[5] = getDoorStateH('192.168.100.215');

    return $doors;
}


head("Door Control");

ob_flush(); flush();

if (!empty($_GET['q'])) {
    switch ($_GET['q']) {
    case 'door1_open':
        $vars = xml2NParse(getLines('http://192.168.100.220/enu/lockstate.xml.p?lock2state=1'));
        $doors[0] = get2NState('192.168.100.220');
        set2NLocked(21, 0);
        break;
    case 'door1_close':
        $vars = xml2NParse(getLines('http://192.168.100.220/enu/lockstate.xml.p?lock2state=0'));
        $doors[0] = get2NState('192.168.100.220');
        set2NLocked(21, 1);
        break;
    case 'office_open':
        $host = '192.168.100.210';
        $username = 'admin';
        $password = 'FirstB';
        $vars = hidDoorUnlock();
        $doors[1] = getDoorState(18, array('Locked','Unlocked'));
        break;
    case 'office_close':
        $host = '192.168.100.210';
        $username = 'admin';
        $password = 'FirstB';
        $vars = hidDoorLock();
        $doors[1] = getDoorState(18, array('Locked','Unlocked'));
        break;
    case 'fellowship_open':
        $host = '192.168.100.211';
        $username = 'admin';
        $password = 'FirstB';
        $vars = hidDoorUnlock();
        $doors[2] = getDoorState(19, array('Locked','Unlocked'));
        break;
    case 'fellowship_close':
        $host = '192.168.100.211';
        $username = 'admin';
        $password = 'FirstB';
        $vars = hidDoorLock();
        $doors[2] = getDoorState(19, array('Locked','Unlocked'));
        break;
    case 'door3_open':
        $host = '192.168.100.214';
        $username = 'admin';
        $password = 'FirstB';
        $vars = hidDoorUnlock();
        $doors[4] = getDoorState(29, array('Locked','Unlocked'));
        break;
    case 'door3_close':
        $host = '192.168.100.214';
        $username = 'admin';
        $password = 'FirstB';
        $vars = hidDoorLock();
        $doors[4] = getDoorState(29, array('Locked','Unlocked'));
        break;
    case 'student_open':
        $host = '192.168.100.215';
        $username = 'admin';
        $password = 'FirstB';
        $vars = hidDoorUnlock();
        $doors[5] = getDoorState(31, array('Locked','Unlocked'));
        break;
    case 'student_close':
        $host = '192.168.100.215';
        $username = 'admin';
        $password = 'FirstB';
        $vars = hidDoorLock();
        $doors[5] = getDoorState(31, array('Locked','Unlocked'));
        break;
    default:
        $vars = print_r($_SERVER,true);
        break;
    }
    ?>
    <meta http-equiv='refresh' content='1;url=doors.php'>
    <?php

}
else {
    $door = getStatus();

    ?>
    <table>
    <tr>
    <th>Location</th>
    <th><br /></th>
    <th><br /></th>
    <th>Status</th>
    </tr>
    <tr>
    <td> <strong>Door 1</strong> </td>
    <td><button onClick="document.location.href='doors.php?q=door1_open'; return false;">Open</button></td>
    <td><button onClick="document.location.href='doors.php?q=door1_close'; return false;">Close</button></td>
    <td><?php echo $door[0]; ?></td>
    </tr>
    <tr>
    <td> <strong>Church Offices</strong></td>
    <td><button onClick="document.location.href='doors.php?q=office_open'; return false;">Open</button></td>
    <td><button onClick="document.location.href='doors.php?q=office_close'; return false;">Close</button></td>
    <td><?php echo $door[1]; ?></td>
    </tr>
    <tr>
    <td><strong>Fellowship North</strong></td>
    <td><button onClick="document.location.href='doors.php?q=fellowship_open'; return false;">Open</button></td>
    <td><button onClick="document.location.href='doors.php?q=fellowship_close'; return false;">Close</button></td>
    <td><?php echo $door[2]; ?></td>
    </tr>
    <tr>
    <td><strong>Worship Door</strong></td>
    <td><button onClick="document.location.href='doors.php?q=door3_open'; return false;">Open</button></td>
    <td><button onClick="document.location.href='doors.php?q=door3_close'; return false;">Close</button></td>
    <td><?php echo $door[4]; ?></td>
    </tr>
    <tr>
    <td><strong>Student Door</strong></td>
    <td><button onClick="document.location.href='doors.php?q=student_open'; return false;">Open</button></td>
    <td><button onClick="document.location.href='doors.php?q=student_close'; return false;">Close</button></td>
    <td><?php echo $door[5]; ?></td>
    </tr>
    </table>
    <meta http-equiv='refresh' content='10;url=doors.php'>
    <?php
}
?>
</body>
</html>
