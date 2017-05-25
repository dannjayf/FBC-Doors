<?php
global $xml1, $xml2, $tzlist;
global $host, $username, $password;

$tzlist = array(
'EDT' => 'EST6EDT',
'EST' => 'EST6EDT',
'CDT' => 'CST6CDT',
'CST' => 'CST6CDT',
'MST' => 'MST',
'MDT' => 'MST7MDT',
'MDT' => 'MST7MDT',
'PDT' => 'PST8PDT',
'PDT' => 'PST8PDT',
);

$xml1 = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><VertXMessage>";
$xml2 = "</VertXMessage>";

function getLines($url, $post = '', $timeout = 60) {
    global $username, $password;

    $process = curl_init() or die ("Init Error");
    if ($post)  {
        curl_setopt($process, CURLOPT_POSTFIELDS, $post);
    }

    // curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
    curl_setopt($process, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($process, CURLOPT_URL, $url);
    curl_setopt($process, CURLOPT_HEADER, 0);
    curl_setopt($process, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($process) or curl_error($process);

    // $lines = explode("\n", $return);

    $lines = str_replace(">",">\n", $return);
    // error_log(date('Y-m-d H:i:s ').print_r($lines,true)."\n",3,'/tmp/doors.log');

    return $lines;
}

// Parse XML
function xmlParse($lines) {
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($lines), $xml_values);
    xml_parser_free($parser);

    $out = array();
    foreach ($xml_values as $k => $v)
        if ($v['type'] == 'complete')
            $out[] = $v;


    return $out;
}

function hid($xml) {
	global $host, $xml1, $xml2;

	$xml = urlencode($xml1.$xml.$xml2);
	$url = "http://$host/cgi-bin/vertx_xml.cgi?XML=$xml";
	return getLines($url);
}

function hidAddCard($cid) {
	$xml = '<hid:Credentials action="AD">
	<hid:Credential cardNumber="'.$cid.'" isCard="true" endTime=""  />
	</hid:Credentials>
	';

	return xmlParse(hid($xml));
}

function hidListCards($recs = 1000, $expand = false) {
	$expand = $expand === true ? 'responseFormat="expanded" ' : '';
	$xml = '<hid:Credentials action="LR" '.$expand.'recordOffset="0" recordCount="'.$recs.'"/>';

	return xmlParse(hid($xml));
}

function hidAssignCard($pid, $cid) {
	$xml = '<hid:Credentials action="UD" rawCardNumber="'.$cid.'" isCard="true">
	<hid:Credential cardholderID="'.$pid.'"/>
	</hid:Credentials>
	';

	return xmlParse(hid($xml));
}

function hidAssignSchedule($pid, $sid) {
	$xml = '<hid:RoleSet action="UD" roleSetID="'.$pid.'">
	<hid:Roles><hid:Role roleID="'.$pid.'" scheduleID="'.$sid.'" resourceID="0"/>
	</hid:Roles></hid:RoleSet>
	';

	return xmlParse(hid($xml));
}


function hidAddPerson($data) {
    $fname = empty($data['fname']) ? '' : $data['fname'];
    $mname = empty($data['mname']) ? '' : $data['mname'];
    $lname = empty($data['lname']) ? '' : $data['lname'];
    $email = empty($data['email']) ? '' : $data['email'];
    $phone = empty($data['phone']) ? '' : $data['phone'];

	$d['forename'] = $fname;
	$d['middleName'] = $mname;
	$d['surname'] = $lname;
	$d['exemptFromPassback'] = 'true';
	$d['extendedAccess'] = 'false';
	$d['confirmingPin'] = '';
	$d['email'] = $email;
	$d['custom1'] = '';
	$d['custom2'] = '';
	$d['phone'] = $phone;

	$xml = '<hid:Cardholders action="AD"><hid:Cardholder ';
	foreach ($d as $k => $v)
		$xml .= "$k=\"$v\" ";
	$xml .= ' /></hid:Cardholders>';

	return xmlParse(hid($xml));
}

function hidEditPerson($pid, $data) {
    $fname = empty($data['fname']) ? '' : $data['fname'];
    $mname = empty($data['mname']) ? '' : $data['mname'];
    $lname = empty($data['lname']) ? '' : $data['lname'];
    $email = empty($data['email']) ? '' : $data['email'];
    $phone = empty($data['phone']) ? '' : $data['phone'];

    $d['forename'] = $fname;
    $d['middleName'] = $mname;
    $d['surname'] = $lname;
    $d['RoleSetID'] = $pid;
    $d['exemptFromPassback'] = 'true';
    $d['extendedAccess'] = 'false';
    $d['confirmingPin'] = '';
    $d['email'] = $email;
    $d['custom1'] = '';
    $d['custom2'] = '';
    $d['phone'] = $phone;

    $xml = '<hid:Cardholders action="UD" cardholderID="'.$pid.'\"><hid:Cardholder ';
    foreach ($d as $k => $v)
            $xml .= "$k=\"$v\" ";
    $xml .= ' /></hid:Cardholders>';

    return xmlParse(hid($xml));
}

function hidListPeople($recs = 1000, $expand = false) {
	$expand = $expand === true ? 'responseFormat="expanded" ' : '';
	$xml = '<hid:Cardholders action="LR" '.$expand.'recordOffset="0" recordCount="'.$recs.'"/>';

	return xmlParse(hid($xml));
}

function hidEventNum() {
	$xml = '<hid:EventMessages action="DR" />';
        $rtn = xmlParse(hid($xml));
        if (array_key_exists(0, $rtn) && array_key_exists('attributes', $rtn[0])) {
            $var = $rtn[0]['attributes'];
            $num = array_key_exists('eventsInUse', $var) ? $var['eventsInUse'] : 0;
        }
        else
            $num = null;
	return $num;
}

function hidListEvents($recs = 30, $offset = 0, $expand = false) {
	$expand = $expand === true ? 'responseFormat="expanded" ' : '';
	$xml = '<hid:EventMessages action="LR" '.$expand.'recordOffset="'.$offset.'" recordCount="'.$recs.'"/>';

	return xmlParse(hid($xml));
}

function hidListSchedules($rec = 10, $expand = false) {
	$expand = $expand === true ? 'responseFormat="expanded" ' : '';
	$xml = '<hid:Schedules action="LR" '.$expand.'recordOffset="0" recordCount="'.$recs.'"/>';

	return xmlParse(hid($xml));
}

function hidStatus($expand = false) {
	$expand = $expand === true ? 'responseFormat="expanded" ' : '';
	$xml = '<hid:Doors action="LR" responseFormat="status" />';

	return xmlParse(hid($xml));
}

function hidParam() {
	$xml = '<hid:EdgeSoloParameters action="DR"/>';

	return xmlParse(hid($xml));
}

function hidGetTime() {
	$xml = '<hid:Time action="DR"/>';

	return xmlParse(hid($xml));
}

// Set time on device, retreive time to get timezone.
function hidSetTime() {
        global $tzlist;

        $time = hidGetTime();
        if (array_key_exists(0, $time)) {
            $tzone = $time[0]['attributes']['timeZone'];
            $tz = $time[0]['attributes']['TZ'];
            $tzcode = $time[0]['attributes']['TZCode'];

            // Save Server Timezone
            $systz = date_default_timezone_get();
            // Change to device time zone
            date_default_timezone_set($tzlist[$tzone]);
            $time = getdate();
            // Change back to Server Timezone
            date_default_timezone_set($systz);


            $d['month'] = $time['mon'];
            $d['dayOfMonth'] = $time['mday'];
            $d['year'] = $time['year'];
            $d['hour'] = $time['hours'];
            $d['minute'] = $time['minutes'];
            $d['second'] = $time['seconds'];
            $d['timeZone'] = $tzone;
            $d['TZ'] = $tz;
            $d['TZCode'] = $tzcode;

            $xml = '<hid:Time action="UD" ';
            foreach ($d as $k => $v)
                $xml .= $k.'="'.$v.'" ';
            $xml .= '/>';

            $rtn = hid($xml);

            return xmlParse($rtn);
        }
        else {
            return '';
        }
}

function hidNumEvents() {
	$info = hid('<hid:EventMessages action="DR"/>');
	$vars = xmlParse($info);

	return $vars[0]['attributes']['eventsInUse'];
}

function hidNumCards() {
	$vars = xmlParse(hid('<hid:Credentials action="DR"/>'));

	return $vars[0]['attributes']['credentialsInUse'];
}

function hidNumPeople() {
	$vars = xmlParse(hid('<hid:Cardholders action="DR"/>'));

	return $vars[0]['attributes']['cardholdersInUse'];
}

function hidListParameters() {
	$vars = xmlParse(hid('<hid:EdgeSoloParameters action="DR"/>'));

	return $vars[0]['attributes'];
}

function hidDoorGrant() {
	$vars = xmlParse(hid('<hid:Doors action="CM" command="grantAccess"/>'));

	return $vars[0];
}

function hidDoorUnlock() {
	$vars = xmlParse(hid('<hid:Doors action="CM" command="unlockDoor"/>'));

	return $vars[0];
}

function hidDoorLock() {
	$vars = xmlParse(hid('<hid:Doors action="CM" command="lockDoor"/>'));

	return $vars[0];
}


function getPID($asset_id, $people_id) {
    if ($asset_id > 0 and $people_id > 0) {
        $query = "
        SELECT pid
        FROM hid
        WHERE asset_id = $asset_id
            and people_id = $people_id
        ";

        $r = sql_query($query);
        $row = mysql_fetch_row($r);

        return $row[0];
    }
    else
        return null;
}

// Return list of pids
function getPIDs($asset_id) {
    if ($asset_id > 0) {
        $query = "
        SELECT pid, people_id
        FROM hid
        WHERE asset_id = $asset_id
        ";

        $r = sql_query($query);
        $people = array();
        while ($row = mysql_fetch_assoc($r)) {
            extract ($row);
            $people[$pid] = $people_id;
        }

        return $people;
    }
    else
        return null;
}


// Return list of tokens for users that are present in this entry controller
function getTIDs($asset_id) {
    if ($asset_id > 0) {
        $query = "
        SELECT hid.people_id, t.id as token_id
        FROM hid
        JOIN token as t ON (hid.people_id = t.people_id)
        WHERE asset_id = $asset_id
        ";

        $r = sql_query($query);
        $tids = array();
        while ($row = mysql_fetch_assoc($r)) {
            extract ($row);
            $tids[$people_id] = $token_id;
        }

        return $tids;
    }
    else
        return null;
}

function addName($loc, $data) {
    global $host, $username, $password;

    $people_id = $data['people_id'];
    if (empty($people_id))
        return;

    $query = "
    SELECT id as asset_id,
        ip_address as host,
        admin_user as username,
        admin_pass as password
    FROM asset
    WHERE loc = $loc
        and active = 1
    ";

    $r = sql_query($query);

    while ($row = mysql_fetch_assoc($r)) {
        extract($row);


        $pid = getPID($asset_id, $people_id);


        // If this PID in the list then update
        if ($pid > 0) {
            echo "Updating $people_id ($pid) for $host";
            $vars = hidEditPerson($pid, $data);
            if ($vars[0]['tag'] == 'hid:Error') {
                echo " Error: ",$vars[0]['attributes']['errorMessage'];
            }
            echo "<br />\n";
        }
        else {
            echo "Adding $people_id ($pid) for $host";
            $vars = hidAddPerson($data);
            if ($vars[0]['tag'] == 'hid:Error') {
                echo " Error: ",$vars[0]['attributes']['errorMessage'],"<br />\n";
            }

            $pid = $vars[0]['attributes']['cardholderID'];

            // Update lookup for CardHolderID
            if ($asset_id > 0 and $people_id > 0 and $pid > 0) {
                $query = "INSERT INTO hid (asset_id, people_id, pid) VALUES($asset_id, $people_id, $pid)";
                $r3 = sql_query($query);
            }
        }

    }
}

function getDoorState($hid, $states = array()) {
    global $host, $username, $password;

    $query = "
    SELECT ip_address, admin_user, admin_pass
    FROM asset
    WHERE id = $hid
    ";
    $r = sql_query($query);
    $row = mysql_fetch_assoc($r);
    
    $host = $row['ip_address'];
    $username = $row['admin_user'];
    $password = $row['admin_pass'];

    $vars = hidStatus();

    switch ($vars[0]['attributes']['relayState']) {
    case 'set':
        $s = '0';
        break;
    case 'unset':
        $s = '1';
        break;
    default:
        $s = '-1';
        break;
    }

    $query = "UPDATE asset SET locked = $s WHERE id = $hid";
    sql_query($query);

    if (count($states))
        return $states[$s];
    else
        return $s;
}

function hidAction($param) {
    global $host, $username, $password;

    $hid = $param['hid'];
    $action = $param['action'];
    if ($hid) {
        $query = "SELECT * FROM asset WHERE id = $hid";
        $r = sql_query($query);
        $row = mysql_fetch_assoc($r);
        
        $host = $row['ip_address'];
        $username = $row['admin_user'];
        $password = $row['admin_pass'];

        switch ($action) {
        case 'grant':
            echo "<h3>Grant</h3>\n";
            hidDoorGrant();
        case 'lock':
            echo "<h3>Lock</h3>\n";
            hidDoorLock();
            break;
        case 'unlock':
            echo "<h3>Unlock</h3>\n";
            hidDoorUnlock();
            break;
        }
        getDoorState($hid);
    }
}

function hidLog($d) {
    extract($d);

    $flist = ' ,hid_id,timestamp,event_type,event_data,token_id,loc_id,asset_id,people_id,';
    $vlist = ' ,hid_id,token_id,loc_id,asset_id,people_id,';
    $done = false;
    $query = "
    SELECT timestamp
    FROM asset_log
    WHERE asset_id = $asset_id
        and timestamp = '$timestamp'
        and loc_id = $loc_id
        and event_type = '$event_type'
    ";
    $r = sql_query($query);
    $row = mysql_fetch_assoc($r);
    if (is_array($row) 
      && array_key_exists('timestamp', $row) 
      && $row['timestamp'] == $timestamp) {
        $done = true;
    }
    else {
        $flds = $vals = '';
        foreach ($d as $k => $v) {
            if (strpos($flist, $k)) {
                $flds .= "$k,";
                if (strpos($vlist, $k)) {
                    if ($v == '')
                        $vals .= "null,";
                    else
                        $vals .= "$v,";
                }
                else {
                    $vals .= "'$v',";
                }
            }
        }
        $flds = substr($flds, 0, -1);
        $vals = substr($vals, 0, -1);
        $query = "INSERT INTO asset_log ($flds) VALUES($vals)";

        sql_query($query);
    }
    return $done;
}

function hidNotify($data) {
    extract ($data);
    $query = "
    SELECT b.notify,
        b.apt as unit,
        c.notify_email as email,
        c.notify_sms as sms,
        c.notify_smsprovider as carrier,
        c.name as loc_name,
        CONCAT(a.fname,' ',a.lname) as tenant
    FROM people a
    LEFT OUTER JOIN apt b ON (a.apt = b.id)
    LEFT OUTER JOIN loc c ON (b.loc = c.id)
    WHERE a.id = $people_id
    ";

    $r = sql_query($query);
    $row = mysql_fetch_assoc($r);
    if ($row['notify']) {
        $q2 = "SELECT description as rfid_name FROM asset WHERE id = $asset_id";
        $r2 = sql_query($q2);
        $row2 = mysql_fetch_assoc($r2);
        
        $flds = array_merge($row, $row2, $data);

        if ($flds['email'] != '')
            emailNotify($flds);

        if ($flds['sms'] != '')
            smsNotify($flds);
    }
    
}

function hidUpdLastUse($data) {
    extract($data);

    // Only update if valid
    if ($event_type == '2020') {
        $query = "
        SELECT hid_timestamp
        FROM people
        WHERE id = $people_id
            and hid_timestamp > '$timestamp'
        ORDER BY hid_timestamp desc
        LIMIT 1
        ";
        $r = sql_query($query);
        $row = mysql_fetch_assoc($r);

        // New notification
        if ( $row['hid_timestamp'] < $timestamp ) {
            $query = "
            UPDATE people
            SET hid_timestamp = '$timestamp'
            WHERE id = $people_id
            ";

            sql_query($query);
            // Now check for notification
            // hidNotify($data);
            
        }
    }
    return;
}

function hidTokenLookup($raw) {
    $query = "
    SELECT id
    FROM token
    WHERE token = '$raw'
    ";

    $r = sql_query($query);

    $row = mysql_fetch_row($r);

    return $row[0]+0;
}

function hidLastEvent($token_id) {
    $query = "
    SELECT timestamp
    FROM asset_log
    WHERE token_id = $token_id
    ORDER BY timestamp DESC
    LIMIT 1
    ";

    $r = sql_query($query);

    $row = mysql_fetch_row($r);

    return $row[0] ? date('m/d/y h:i a', strtotime($row[0])) : '';
}

?>
