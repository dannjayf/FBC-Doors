<?php

class HID {

    public $host = null;
    public $username = null;
    public $password = null;
    public $db = null;
    public $db_host = null;
    public $db_name = null;
    public $db_user = null;
    public $db_pass = null;
    public $building_id = null;
    public $verbose = false;
    public $pids = [];
    public $fob_user = [];
    public $run = false;
    public $all = false;
    public $event_types = [ '1' => 'Activate', '2' => 'Deactivate', '3' => 'Remove'];
    public $notifies = [];
    public $notifies2 = [];
    public $smsUrl = '';
    public $emailUrl = '';

    public $tzlist = array(
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

    public $xml1 = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><VertXMessage>";
    public $xml2 = "</VertXMessage>";

    public function __construct() {
        include 'config-local.php';
        $this->db_host = $db_host;
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->building_id = $building_id;


    }

    // Connect to Database
    public function sql_connect() {
        
        if (!$this->db = mysql_connect($this->db_host, $this->db_user, $this->db_pass, true)) {
           echo("Unable to connect to $this->db_host\n");
           return false;
        }
        else {
            mysql_select_db($this->db_name) or die("Unable to select $this->db_name\n");
            return true;
        }
    }

    function sql_query($query) {
        
        // Try four times
        if ($this->sql_connect()) 
            if ($r = mysql_query($query)) 
                return $r;

        sleep(4);

        if ($this->sql_connect())
            if ($r = mysql_query($query)) 
                return $r;

        sleep(4);

        if ($this->sql_connect())
            if ($r = mysql_query($query)) 
                return $r;

        sleep(4);

        if ($this->sql_connect())
            if ($r = mysql_query($query))
                return $r;
        
        // IF it still failed remove lock and die
        die("query $query failed(4)\n".mysql_error()."\n");
    }

    // Notify of Entry
    public function getNotifies() {
        $query = "
        SELECT 
            uu.user_id as user_id,
            units.id as unit_id, 
            bn.sms as n_sms, 
            bn.email as n_email,
            users.mobile as sms,
            users.email
        FROM units
        JOIN units_users as uu ON units.id = uu.unit_id
        JOIN buildings_notifyemails as bn ON units.building_id = bn.building_id
        JOIN users ON bn.user_id = users.id
        WHERE units.building_id = $this->building_id
            AND bn.kind = 1
            AND units.notify_at_entry = 1
        ";

        $r = $this->sql_query($query);
        $list = [];
        while ($row = mysql_fetch_assoc($r)) {
			extract($row);
            if ($n_sms) {
                if (!array_key_exists($unit_id, $list)) {
                    $list[$unit_id][$user_id] = [];
                    $list[$unit_id][$user_id]['sms'] = [];
                }
                $list[$unit_id][$user_id]['sms'][] = $sms;
            }

            if ($n_email) {
                if (!array_key_exists($unit_id, $list)) {
                    $list[$unit_id][$user_id] = [];
                    $list[$unit_id][$user_id]['email'] = [];
                }
                $list[$unit_id][$user_id]['email'][] = $row['email'];
            }
        }
        $this->notifies = $list;
    }
    
    // Admin Notify
    public function getNotifies2() {
        $query = "
        SELECT 
            bn.sms as n_sms, 
            bn.email as n_email,
            users.mobile as sms,
            users.email
        FROM buildings_notifyemails as bn
        JOIN users ON bn.user_id = users.id
        WHERE bn.building_id = $this->building_id
            AND bn.kind = 3
            AND (bn.sms = 1 OR bn.email = 1)
        ";

        $r = $this->sql_query($query);
        $list = [];
        while ($row = mysql_fetch_assoc($r)) {
            if ($row['n_sms']) {
                if (!array_key_exists('sms', $list)) {
                    $list['sms'] = [];
                }
                $list['sms'][] = $row['sms'];
            }

            if ($row['n_email']) {
                if (!array_key_exists('email', $list)) {
                    $list['email'] = [];
                }
                $list['email'][] = $row['email'];
            }
        }
        $this->notifies2 = $list;
    }
   
    public function getLines($url, $post = '', $timeout = 60) {

        $process = curl_init() or die ("Init Error");
        if ($post)  {
            curl_setopt($process, CURLOPT_POSTFIELDS, $post);
        }

        curl_setopt($process, CURLOPT_USERPWD, "$this->username:$this->password");
        curl_setopt($process, CURLOPT_URL, $url);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);

        $return = curl_exec($process) or curl_error($process);

        // If unauthorized, then redo with digest
        if (strpos($return, '401 - Unauthorized')) {
            curl_close($process);
            $process = curl_init() or die ("Init Error");
            if ($post)  {
                curl_setopt($process, CURLOPT_POSTFIELDS, $post);
            }

            curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
            curl_setopt($process, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($process, CURLOPT_URL, $url);
            curl_setopt($process, CURLOPT_HEADER, false);
            curl_setopt($process, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
            $return = curl_exec($process) or curl_error($process);
        }
        curl_close($process);

        $lines = str_replace(">",">\n", $return);

        return $lines;
    }

    // Parse XML
    public function xmlParse($lines) {
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

    public function hid($xml = '') {
        // if ($this->verbose) echo $xml;

        $xml = urlencode($this->xml1.$xml.$this->xml2);
        $url = "http://$this->host/cgi-bin/vertx_xml.cgi?XML=$xml";
		$rtn = $this->getLines($url);
        return $rtn;
    }

    public function hidAddCard($cid) {
        $xml = '<hid:Credentials action="AD">
        <hid:Credential cardNumber="'.$cid.'" isCard="true" endTime=""  />
        </hid:Credentials>
        ';

        return $this->xmlParse($this->hid($xml));
    }

    public function hidListCards($recs = 1000, $expand = false) {
        $expand = $expand === true ? 'responseFormat="expanded" ' : '';
        $xml = '<hid:Credentials action="LR" '.$expand.'recordOffset="0" recordCount="'.$recs.'"/>';

        return $this->xmlParse($this->hid($xml));
    }

    public function hidAssignCard($pid, $cid) {
        $xml = '<hid:Credentials action="UD" rawCardNumber="'.$cid.'" isCard="true">
        <hid:Credential cardholderID="'.$pid.'"/>
        </hid:Credentials>
        ';

        return $this->xmlParse($this->hid($xml));
    }

    public function hidAssignSchedule($pid, $sid) {
        $xml = '<hid:RoleSet action="UD" roleSetID="'.$pid.'">
        <hid:Roles><hid:Role roleID="'.$pid.'" scheduleID="'.$sid.'" resourceID="0"/>
        </hid:Roles></hid:RoleSet>
        ';

        return $this->xmlParse($this->hid($xml));
    }

    // Delete card
    public function hidDelCard($cid) {
        $xml = '<hid:Credentials action="DD" rawCardNumber="'.$cid.'" isCard="true" />';

        return $this->xmlParse($this->hid($xml));
    }


    public function hidAddPerson($data) {
        $fname = empty($data['fname']) ? '' : $data['fname'];
        $mname = empty($data['mname']) ? '' : $data['mname'];
        $lname = empty($data['lname']) ? '' : $data['lname'];
        $email = empty($data['email']) ? '' : $data['email'];
        $phone = empty($data['phone']) ? '' : $data['phone'];
        $user_id = empty($data['user_id']) ? '' : $data['user_id'];

        $d['forename'] = $fname;
        $d['middleName'] = $mname;
        $d['surname'] = $lname;
        $d['exemptFromPassback'] = 'true';
        $d['extendedAccess'] = 'false';
        $d['confirmingPin'] = '';
        $d['email'] = $email;
        $d['custom1'] = $user_id;
        $d['custom2'] = '';
        $d['phone'] = $phone;

        $xml = '<hid:Cardholders action="AD"><hid:Cardholder ';
        foreach ($d as $k => $v)
            $xml .= "$k=\"$v\" ";
        $xml .= ' /></hid:Cardholders>';

        return $this->xmlParse($this->hid($xml));
    }

    public function hidEditPerson($pid, $data) {
        $fname = empty($data['fname']) ? '' : $data['fname'];
        $mname = empty($data['mname']) ? '' : $data['mname'];
        $lname = empty($data['lname']) ? '' : $data['lname'];
        $email = empty($data['email']) ? '' : $data['email'];
        $phone = empty($data['phone']) ? '' : $data['phone'];
        $user_id = empty($data['user_id']) ? '' : $data['user_id'];

        $d['forename'] = $fname;
        $d['middleName'] = $mname;
        $d['surname'] = $lname;
        $d['RoleSetID'] = $pid;
        $d['exemptFromPassback'] = 'true';
        $d['extendedAccess'] = 'false';
        $d['confirmingPin'] = '';
        $d['email'] = $email;
        $d['custom1'] = $user_id;
        $d['custom2'] = '';
        $d['phone'] = $phone;

        $xml = '<hid:Cardholders action="UD" cardholderID="'.$pid.'\"><hid:Cardholder ';
        foreach ($d as $k => $v)
                $xml .= "$k=\"$v\" ";
        $xml .= ' /></hid:Cardholders>';


        return $this->xmlParse($this->hid($xml));
    }

    public function hidListPeople($recs = 1000, $expand = false) {
        $expand = $expand === true ? 'responseFormat="expanded" ' : '';
        $xml = '<hid:Cardholders action="LR" '.$expand.'recordOffset="0" recordCount="'.$recs.'"/>';

        return $this->xmlParse($this->hid($xml));
    }

    // Delete person from DB
    public function hidDelPerson($pid) {
        $xml = '<hid:Cardholders action="DD" cardholderID="'.$pid.'" />';

        return $this->xmlParse($this->hid($xml));
    }

    public function hidEventNum() {
        $xml = '<hid:EventMessages action="DR" />';
        $rtn = $this->xmlParse($this->hid($xml));
        $num =  0;
        if (array_key_exists(0, $rtn) && array_key_exists('attributes', $rtn[0])) {
            $var = $rtn[0]['attributes'];
            $num = array_key_exists('eventsInUse', $var) ? $var['eventsInUse'] : 0;
        }
        return $num;
    }

    public function hidListEvents($recs = 30, $offset = 0, $expand = false) {
        $expand = $expand === true ? 'responseFormat="expanded" ' : '';
        $xml = '<hid:EventMessages action="LR" '.$expand.'recordOffset="'.$offset.'" recordCount="'.$recs.'"/>';

        return $this->xmlParse($this->hid($xml));
    }

    public function hidListSchedules($rec = 10, $expand = false) {
        $expand = $expand === true ? 'responseFormat="expanded" ' : '';
        $xml = '<hid:Schedules action="LR" '.$expand.'recordOffset="0" recordCount="'.$recs.'"/>';

        return $this->xmlParse($this->hid($xml));
    }

    public function hidStatus($expand = false) {
        $expand = $expand === true ? 'responseFormat="expanded" ' : '';
        $xml = '<hid:Doors action="LR" responseFormat="status" />';

        return $this->xmlParse($this->hid($xml));
    }

    public function hidParam() {
        $xml = '<hid:EdgeSoloParameters action="DR"/>';

        return $this->xmlParse($this->hid($xml));
    }

    public function hidGetTime() {
        $xml = '<hid:Time action="DR"/>';

        return $this->xmlParse($this->hid($xml));
    }

    // Set time on device, retreive time to get timezone.
    public function hidSetTime() {
            global $tzlist;

            $time = hidGetTime();
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

            $rtn = $this->hid($xml);

        return $this->xmlParse($rtn);
    }

    public function hidNumEvents() {
        $info = $this->hid('<hid:EventMessages action="DR"/>');
        $vars = $this->xmlParse($info);

        return $vars[0]['attributes']['eventsInUse'];
    }

    public function hidNumCards() {
        $vars = $this->xmlParse($this->hid('<hid:Credentials action="DR"/>'));

        return $vars[0]['attributes']['credentialsInUse'];
    }

    public function hidNumPeople() {
        $vars = $this->xmlParse($this->hid('<hid:Cardholders action="DR"/>'));

        return $vars[0]['attributes']['cardholdersInUse'];
    }

    public function hidListParameters() {
        $vars = $this->xmlParse($this->hid('<hid:EdgeSoloParameters action="DR"/>'));

        return $vars[0]['attributes'];
    }

    public function hidDoorGrant() {
        $vars = $this->xmlParse($this->hid('<hid:Doors action="CM" command="grantAccess"/>'));

        return $vars[0];
    }

    public function hidDoorUnlock() {
        $vars = $this->xmlParse($this->hid('<hid:Doors action="CM" command="unlockDoor"/>'));

        return $vars[0];
    }

    public function hidDoorLock() {
        $vars = $this->xmlParse($this->hid('<hid:Doors action="CM" command="lockDoor"/>'));

        return $vars[0];
    }

    public function hidAlarmReset() {
        $vars = $this->xmlParse($this->hid('<hid:ControlVariables action="CM" index="101" value="false"/>'));
        return $vars[0];
    }


    public function getPID($asset_id, $user_id) {
        if ($asset_id > 0 and $user_id > 0) {
            $query = "
            SELECT pid
            FROM hid
            WHERE asset_id = $asset_id
                and user_id = $user_id
            ";

            $r = $this->sql_query($query);
            $row = mysql_fetch_row($r);

            return $row[0];
        }
        else
            return null;
    }

    // Get list of valid FOBs
    public function getFOBs() {

        $fobs = [];
        $this->user_fob = [];
        $query = "
        SELECT token, tokens.id as token_id, tokens.user_id
        FROM buildings_tokens
        JOIN tokens on buildings_tokens.token_id = tokens.id
        WHERE building_id = $this->building_id
        ";

        $r = $this->sql_query($query);
        while ($row = mysql_fetch_assoc($r)) {
            extract ($row);
            $fobs[$token] = $token_id;
            $this->fob_user[$token] = $user_id;
        }
        return $fobs;
    }

    // Remove old records from assets_hids and
    // Return list of PIDs from assets_hids
    public function getPIDs($asset_id) {
        // First remove records for users not assigned to building
        $query = "SELECT 
            ah.id as ah_id,
            ah.user_id,
            uu.unit_id,
            t.id as token_id,
            t.card_id 
        FROM assets_hids as ah 
        LEFT JOIN units_users as uu ON (ah.user_id = uu.user_id) 
        LEFT JOIN tokens as t ON (ah.user_id = t.user_id)
        WHERE ah.asset_id = $asset_id
            AND uu.unit_id IS NULL";
        $r = $this->sql_query($query);
        $rm = [];
        while ($row = mysql_fetch_assoc($r)) {
            extract ($row);
            if ($this->verbose) echo $user_id.':'.$unit_id.':'.$card_id."\n";
            $now = date('Y-m-d H:i:s');

            $q2 = "INSERT INTO system_logs (
                building_id,
                entity_id,
                entity_name,
                action_name,
                triggered_at,
                notes)
                VALUES (
                    $this->building_id,
                    $user_id,
                    'Resident',
                    'REMOVED FROM ENTRY',
                    '$now',
                    'Auto removed via hidchk')
                ";
            // echo "$q2\n";
            $this->sql_query($q2);

            // Mark as deleted so they can be added .
            $q2 = "UPDATE users SET status_delete = 1 WHERE id = $user_id";
            $this->sql_query($q2);


                
            if ($card_id) {
                // Add History record
                $q3 = "INSERT INTO tokens_histories (
                    building_id,
                    token_id,
                    user_id,
                    action,
                    action_date,
                    action_type)
                    VALUES (
                        $this->building_id,
                        $token_id,
                        $user_id,
                        'FOB $card_id Trashed',
                        '$now',
                        6
                    )";
                // echo "$q3\n";
                $this->sql_query($q3);
                $q3 = "UPDATE tokens SET user_id = -1,user_history=$user_id WHERE id = $token_id";
                // echo "$q3\n";
                $this->sql_query($q3);
            }

            // Remove record
            $q4 = "DELETE FROM assets_hids where id = $ah_id";
            // echo "$q4\n";
            $this->sql_query($q4);
            
        }

        $users = [];
        if ($asset_id > 0) {
            $query = "
            SELECT hid_id, user_id
            FROM assets_hids
            WHERE asset_id = $asset_id
            ";

            $r = $this->sql_query($query);
            while ($row = mysql_fetch_assoc($r)) {
                extract ($row);
                $users[$hid_id] = $user_id;
            }

        }
        return $users;
    }

    // Export the info from Edge Solo
    public function hidExportXML() {
        $xml = '<hid:System action="CM" command="exportXML" filename="export.xml" />';
        $data = null;

        // Allow longer wait
        $vars = $this->xmlParse($this->hid($xml,300));

        if (count($vars) && array_key_exists('attributes', $vars[0])) {
            $attr = $vars[0]['attributes'];
            if (array_key_exists('filePath', $attr)) {
                $data = $this->xmlParse($this->getLines("http://$this->host".$attr['filePath']));
            }
       }
       return $data;
    }

    // Compare Entry Controller database against portal
    public function chkUsers() {

        // Get the valid FOBs
        $fobs = $this->getFOBs();

        $query = "
        SELECT id as asset_id,
            ip_address,
            admin_user,
            admin_pass
        FROM assets
        WHERE building_id = $this->building_id
        ORDER BY ip_address
        ";
        $r = $this->sql_query($query);
        while ($row = mysql_fetch_assoc($r)) {
            extract($row);

            $this->pids = $this->getPIDs($asset_id);
            $this->host = $ip_address;
            $this->username = $admin_user;
            $this->password = $admin_pass;

            if ($this->verbose) echo "Checking $asset_id $ip_address\n";
            $vars = $this->hidExportXML();

            foreach($vars as $var) {
                if (array_key_exists('attributes', $var))
                    $attr = $var['attributes'];
                else
                    $attr = [];

                switch ($var['tag']) {
                case 'hid:Cardholder':
                    $hid_id = $attr['cardholderID'];
                    $user_id = array_key_exists('custom1', $attr) ? $attr['custom1'] : null;
                    // If the HID is missing or doesn't match then remove
                    if ($user_id) {
                        // If key exsits then done
                        if (array_key_exists($hid_id, $this->pids) && $this->pids[$hid_id] = $user_id) {
                            if ($this->verbose) echo "$hid_id matches $user_id\n";
                        }
                        else {
                            $this->delPID($asset_id, $hid_id);
                            if ($this->verbose) echo "$hid_id Deleted\n";
                            // $this->hidDelPerson($hid_id);
                        }
                    }
                    // If custom1 missing check to see if this user is in the assets_hids table
                    else {
                        if (!array_key_exists($hid_id, $this->pids)) {
                            if ($this->verbose) echo "$hid_id Deleted {$attr['forename']} {$attr['surname']}\n";
                            $this->hidDelPerson($hid_id);
                        }
                    }
                    if ($this->verbose) echo "$hid_id => $user_id\n";
                   
                    break;
                case 'hid:Credential':
                    $token = $attr['rawCardNumber'];
                    $hid_id = array_key_exists('cardholderID', $attr) ? $attr['cardholderID'] : null;

                    // check that FOB exists
                    if (!array_key_exists($token, $fobs)) {
                        if ($this->verbose) echo "$token Deleted\n";
                        $this->hidDelCard($token);
                    }
                    // Check the FOB belongs, if not unassign
                    elseif ($hid_id 
                      && array_key_exists($token, $this->fob_user)
                      && array_key_exists($hid_id, $this->pids)
                      && $this->fob_user[$token] != $this->pids[$hid_id]) {
                        if ($this->verbose) echo "Remove assignement for $token\n";
                        $this->hidAssignCard('', $token);

                    }

                    break;
                }
            }
        }
    }

    public function delPID($asset_id, $hid_id) {
        $query = "
        DELETE FROM assets_hids WHERE asset_id = $asset_id and hid_id = $hid_id
        ";

        $r = $this->sql_query($query);
    }



    public function getDoorState($hid, $states = array()) {
        global $host, $username, $password;

        $query = "
        SELECT ip_address, admin_user, admin_pass
        FROM asset
        WHERE id = $hid
        ";
        $r = $this->sql_query($query);
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
        $this->sql_query($query);

        if (count($states))
            return $states[$s];
        else
            return $s;
    }

    public function hidAction($param) {

        $hid = $param['hid'];
        $action = $param['action'];
        if ($hid) {
            $query = "SELECT * FROM asset WHERE id = $hid";
            $r = $this->sql_query($query);
            $row = mysql_fetch_assoc($r);
            
            $this->host = $row['ip_address'];
            $this->username = $row['admin_user'];
            $this->password = $row['admin_pass'];

            switch ($action) {
            case 'grant':
                echo "<h3>Grant</h3>\n";
                $this->hidDoorGrant();
            case 'lock':
                echo "<h3>Lock</h3>\n";
                $this->hidDoorLock();
                break;
            case 'unlock':
                echo "<h3>Unlock</h3>\n";
                $this->hidDoorUnlock();
                break;
            }
            $this->getDoorState($hid);
        }
    }

    // Query device for log entry
    // $timestamp
    // $building_id
    // $event_type
    // $token_id
    // $user_id
    // $hid_id
    // $type
    public function hidLog($d) {
        extract($d);
        if (!empty($type)) $event_type = $this->event_types[$type];
        if (empty($building_id)) $building_id = 'null';
        if (empty($asset_id)) $asset_id = 'null';
        if (empty($token_id)) $token_id = 'null';
        if (empty($hid_id)) $hid_id = 'null';
        if (empty($user_id)) $user_id = 'null';
        if (empty($unit_id)) $unit_id = 'null';
        if (empty($event_type)) $event_type = '';
        if (empty($event_data)) $event_data = '';

        $row = null;
        // If timestamp exists check to see if there is an entry
        if (!empty($timestamp)) {
            $query = "
            SELECT timestamp, event_type
            FROM assets_log
            WHERE asset_id = $asset_id
                and timestamp = '$timestamp'
                and event_type = '$event_type'
            ";
            $r = $this->sql_query($query);
            $row = mysql_fetch_assoc($r);
        }

        $done = false;

        // If there was a record and it matches the done
        if (is_array($row) 
          && array_key_exists('timestamp', $row) 
          && $row['timestamp'] == $timestamp
		  && !$this->all ) {
            if ($this->verbose) echo print_r($d,true),"\n",print_r($row,true),"\n";
            $done = true;
        }
        else {
            // If no timestamp use now.
            if (empty($timestamp)) $timestamp = date('Y-m-d H:i:s');

            $flds = 'building_id,asset_id,token_id,hid_id,user_id,unit_id,timestamp,event_type,event_data';
            $vals = "$building_id,$asset_id,$token_id,$hid_id,$user_id,$unit_id,'$timestamp','$event_type','$event_data'";
            // Create query
            $query = "INSERT INTO assets_log ($flds) VALUES($vals)";
			if ($this->verbose) echo "$query\n";

            // Insert into database
            $this->sql_query($query);
        }
        return $done;
    }

    public function emailNotify($flds = []) {
        print_r($flds);
    }

    public function smsNotify($flds = []) {
        print_r($flds);
    }

    public function hidNotify($data) {
        extract ($data);
        $query = "
        SELECT 
            d.id as unit_id,
            b.email as email,
            b.sms as sms,
            CONCAT(a.firstname,' ',a.lastname) as resident
        FROM users a
        LEFT OUTER JOIN units_users as c ON a.id = c.user_id
        LEFT OUTER JOIN units d ON (b.unit_id = c.id)
        LEFT OUTER JOIN buildings e ON (d.building_id = e.id)
        JOIN buildings_notifyemails as b ON (e.id = b.building_id)
        WHERE a.id = $user_id
        ";

        $r = $this->sql_query($query);
        $row = mysql_fetch_assoc($r);
        if ($row['notify']) {
            $q2 = "SELECT name as asset_name FROM assets WHERE id = $asset_id";
            $r2 = $this->sql_query($q2);
            $row2 = mysql_fetch_assoc($r2);
            
            $flds = array_merge($row, $row2, $data);

            if ($flds['email'] != '')
                $this->emailNotify($flds);

            if ($flds['sms'] != '')
                $this->smsNotify($flds);
        }
        
    }

    public function hidUpdLastUse($data) {
        extract($data);

        // Only update if valid
        if ($event_type == '2020') {
            $query = "
            SELECT hid_timestamp
            FROM users
            WHERE id = $user_id
                and hid_timestamp > '$timestamp'
            ORDER BY hid_timestamp desc
            LIMIT 1
            ";
            $r = $this->sql_query($query);
            $row = mysql_fetch_assoc($r);

            // New notification
            if ( $row['hid_timestamp'] < $timestamp ) {
                $query = "
                UPDATE users
                SET hid_timestamp = '$timestamp'
                WHERE id = $user_id
                ";

                $this->sql_query($query);
                // Now check for notification
                hidNotify($data);
                
            }
        }
        return;
    }

    public function getLastUpd($col = 'last_upd') {
        $dt = '2015-01-01';
        $query = "
        SELECT id,$col
        FROM assets
        WHERE status = 1
            AND building_id = $this->building_id
        ORDER BY last_upd ";

        $r = $this->sql_query($query);
        $cfg = mysql_fetch_assoc($r);
        if ($cfg && $cfg[$col]) {
            $dt = date('Y-m-d H:i:s', strtotime($cfg[$col]));
        }
        return $dt;
    }

    public function setLastUpd($dt = null, $col = 'last_upd') {
        // update timestamp
        if  ($dt) {
            $query = "UPDATE assets SET $col = '".date('Y-m-d H:i:s', strtotime($dt))."'
            WHERE building_id = $this->building_id AND status = 1";
            if ($this->run) $r = $this->sql_query($query);
        }
        return;
    }

    public function tokenClear($dt = null, $ip = null ) {

        if (!$dt) {
            $dt = $this->getLastUpd();
        }

        // clear recycled and empty tokens
        $query = "
        SELECT DISTINCT tokens.id as token_id, 
            tokens.token,
            assets.id as asset_id,
            assets.name,
            assets.building_id 
        FROM tokens
        INNER JOIN buildings_tokens on tokens.id = buildings_tokens.token_id
        INNER JOIN assets on buildings_tokens.building_id = assets.building_id
        WHERE assets.status = 1
            AND buildings_tokens.building_id = $this->building_id 
            AND tokens.user_id in (-2, -1, 0) 
            AND tokens.modified >= '$dt'";
        if ($ip) {
            $query .= " and assets.ip_address = '$ip'";
        }

        $r = $this->sql_query($query);

        $tot = mysql_num_rows($r);
        $n = 0;
        while ($row = mysql_fetch_assoc($r)) { extract($row);
            extract($row);
            $n++;
            if ($this->verbose) echo "$n of $tot\t$token_id\t$asset_id\t$name\n";
            $this->hidAssignCard('', $token);
            $d = [];
            $d['asset_id'] = $asset_id;
            $d['building_id'] = $building_id;
            $d['token_id'] = $token_id;
            $d['type'] = 3;

            $this->hidLog($d);
        }

    }

    public function getHid($asset_id, $user_id) {
        $query  = " 
        SELECT hid_id
        FROM assets_hids
        WHERE asset_id = $asset_id
            AND user_id = $user_id
        ";

        $r = $this->sql_query($query);
        $row = mysql_fetch_assoc($r);

        return $row ? $row['hid_id'] : null;
            
    }

    public function setHid($asset_id, $user_id, $hid_id) {
        $query = "
        INSERT INTO assets_hids
        (asset_id, user_id, hid_id)
        VALUES ($asset_id, $user_id, $hid_id)
        ";

        $r = $this->sql_query($query);
    }

    public function tokenUpd($dt = null, $user_id = null, $ip = null) {
        if (!$dt) {
            $dt = $this->getLastUpd();
        }

        //Look for tokens to update
        $query = "
        SELECT DISTINCT tokens.token,
            tokens.is_active,
            users.lastname,
            users.firstname,
            users.middlename,
            users.mobile,
            users.email,
            users.id as user_id,
            assets_units.asset_id,
            assets.name,
            units.id as unit_id,
            units.unit_no,
            units.fob,
            tokens.id as token_id,
            assets.building_id,
            assets.ip_address,
            assets.admin_user,
            assets.admin_pass
        FROM tokens 
        INNER JOIN buildings_tokens on tokens.id = buildings_tokens.token_id 
        INNER JOIN users on tokens.user_id = users.id 
        INNER JOIN units_users on tokens.user_id = units_users.user_id 
        INNER JOIN assets_units on units_users.unit_id = assets_units.unit_id 
        INNER JOIN assets on assets_units.asset_id = assets.id 
        INNER JOIN units on assets_units.unit_id = units.id 
        INNER JOIN buildings on units.building_id = buildings.id 
        WHERE assets.status = 1
            AND (tokens.model_id = buildings.fob_model_id or tokens.model_id = buildings.mfob_model_id)
            AND units.building_id = $this->building_id 
            AND tokens.user_id > 0
            AND (users.modified >= '$dt' or tokens.modified >= '$dt' or units.modified > '$dt')
        ";

        if ($user_id)
            $query .= " and tokens.user_id = $user_id ";
        else
            $query .= " and tokens.user_id > 0 ";
        if ($ip) 
            $query .= " and assets.ip_address = '$ip' ";

        $query .= "
            and (tokens.modified >= '$dt' or users.modified >= '$dt') 
        GROUP BY 
            assets.id,
            units.id,
            users.id,
            tokens.token
            ";

        $r = $this->sql_query($query);
        while ($row = mysql_fetch_assoc($r)) {
            extract($row);

            if (ip2long($ip_address)) {
                $this->host = $ip_address;
                $this->username = $admin_user;
                $this->password = $admin_pass;


                // Add Card
                if ($this->verbose) echo "Adding $token\n";
                $this->hidAddCard($token);

                $data = [
                    'lname' => $lastname,
                    'fname' => $firstname,
                    'mname' => $middlename,
                    'email' => $email,
                    'phone' => $mobile,
                    'user_id' => $user_id,
                    ];

                if ($hid_id = $this->getHid($asset_id, $user_id)) {
                    $this->hidEditPerson($hid_id, $data);
                }
                else {
                    $vars = $this->hidAddPerson($data);
                    if (array_key_exists(0,$vars)) {
                        $var = $vars[0];
                        if (array_key_exists('attributes', $var) 
                          && array_key_exists('cardholderID', $var['attributes'])) {
                            $hid_id = $var['attributes']['cardholderID'];
                            $this->setHid($asset_id, $user_id, $hid_id);
                        }
                        else  {
                            $hid_id = 0;
                        }
                    }
                }

                // If both are true then update with user info
                if  ($hid_id) {
                    $d = [];
                    $d['building_id'] = $building_id;
                    $d['asset_id'] = $asset_id;
                    $d['token_id'] = $token_id;
                    $d['hid_id'] = $hid_id;
                    $d['user_id'] = $user_id;
                    $d['unit_id'] = $unit_id;
                    if ($fob && $is_active) {
                        if ($this->verbose) echo "$token\t$user_id\t$firstname\t$lastname\n";
                        $this->hidAssignCard($hid_id, $token);
                        $this->hidAssignSchedule($hid_id, 1);
                        $d['type'] = 1;
                        $this->hidLog($d);
                    }
                    else {
                        if ($this->verbose) echo "$token\t$user_id\t$firstname\t$lastname\n";
                        $this->hidAssignCard('', $token);
                        $d['type'] = 2;
                        $this->hidLog($d);
                    }
                }
                if ($this->verbose) echo "$unit_no\t$fob\t$name\t$token\t$lastname, $firstname\n";
            }
        }

        return $dt;
    }

    public function getAssets() {
        $query = "
        SELECT id, name
        FROM assets
        WHERE status = 1
        AND building_id = $this->building_id
        ";

        $r = $this->sql_query($query);

        $assets = [];
        while ($row = mysql_fetch_assoc($r)) {
            $assets[$row['id']] = $row['name'];
        }
        return $assets;
    }

    public function getHids($asset_id) {
        $query = "
        SELECT hid_id, user_id
        FROM assets_hids
        WHERE asset_id = $asset_id
        ";
        $r = $this->sql_query($query);
        $hids = [];
        while ($row = mysql_fetch_assoc($r)) {
            $hids[$row['hid_id']] = $row['user_id'];
        }

        return $hids;
    }

    public function getUnitUser() {
        $query = "
        SELECT units_users.user_id, units_users.unit_id
        FROM units_users
        JOIN units ON units_users.unit_id = units.id
        WHERE building_id = $this->building_id
        ";
        $r = $this->sql_query($query);
        $units_users = [];
        while ($row = mysql_fetch_assoc($r)) {
            $units_users[$row['user_id']] = $row['unit_id'];
        }

        return $units_users;
    }

    public function getTokenUser($asset_id) {
        $query = "
        SELECT tokens.user_id, tokens.id
        FROM assets_hids
        LEFT OUTER JOIN tokens ON tokens.user_id = assets_hids.user_id
        WHERE asset_id = $asset_id
        ";

        $r = $this->sql_query($query);
        $tids = [];
        while ($row = mysql_fetch_assoc($r)) {
            $tids[$row['user_id']] = $row['id'];
        }

        return $tids;
    }

    public function chkStatus($locked = 1) {
        $vars = $this->hidStatus();
        if ($this->verbose
            && array_key_exists(0, $vars)
            && array_key_exists('attributes', $vars[0]))
                echo $vars[0]['attributes']['relayState'],"\n";

        // set State of asset to match database
        if (array_key_exists(0, $vars) && array_key_exists('attributes', $vars[0])) {
            switch ($vars[0]['attributes']['relayState']) {
            case 'set':
                if ($locked == 1) {
                    $this->hidDoorLock();
                }
                break;
            case 'unset':
                if ($locked == 0) {
                    $this->hidDoorUnLock();
                }
                break;
            default:
                break;
            }
        }
    }

    public function SMS($num, $msg) {
        $d['num'] = $num;
        $d['msg'] = $msg;
        
        $this->getLines($this->smsUrl, $d);
    }

    public function sendEmail($to, $subject, $msg) {
        $d['to'] = $to;
        $d['subject'] = $subject;
        $d['msg'] = $msg;
        
        $this->getLines($this->emailUrl, $d);
    }

    public function sendNotify($d = []) {
		extract($d);


		if (array_key_exists($unit_id, $this->notifies)
		  && array_key_exists($user_id, $this->notifies[$unit_id])) {
			$sms = array_key_exists('sms', $this->notifies[$unit_id][$user_id]) ? $this->notifies[$unit_id][$user_id]['sms'] : [];
			if ($this->verbose) echo "sendNotify: ".print_r($sms,true)."\n";

			$email = array_key_exists('email', $this->notifies[$unit_id][$user_id]) ? $this->notifies[$unit_id][$user_id]['email'] : [];
			if ($this->verbose) echo "sendNotify: ".print_r($email,true)."\n";

			$user = null;
			$query = "
			SELECT firstname, lastname
			FROM users
			WHERE id = $user_id";
			$r = $this->sql_query($query);
			$user = mysql_fetch_assoc($r);

			$asset = null;
			$query = "
			SELECT assets.name as asset_name, buildings.name as building_name
			FROM assets
			JOIN buildings ON assets.building_id = buildings.id
			WHERE assets.id = {$d['asset_id']}";
			$r = $this->sql_query($query);
			$asset = mysql_fetch_assoc($r);

			if (count($sms)+count($email)) {
				$msg = $user['firstname'].' '.$user['lastname'].' entered '.$asset['building_name'].' using '.$asset['asset_name'].' at '.date('m/d/Y g:i a', strtotime($timestamp));

				if(count($sms)) {
					foreach ($sms as $num) {
						$this->SMS($num, $msg);
					}
				}

				if (count($email)) {
					$subject = 'Entry Notification';
					foreach ($email as $to) {
						$this->sendEmail($to, $subject, $msg);
					}
				}

			}
		}
    }

    public function sendNotify2($d = [], $notify = []) {
        $sms = array_key_exists('sms', $notify) ? $notify['sms'] : [];
        if ($this->verbose) echo "sendNotify2: ".print_r($sms,true)."\n";

        $email = array_key_exists('email', $notify) ? $notify['email'] : [];
        if ($this->verbose) echo "sendNotify2: ".print_r($email,true)."\n";

        $asset = null;
        $query = "
        SELECT assets.name as asset_name, buildings.name as building_name
        FROM assets
        JOIN buildings ON assets.building_id = buildings.id
        WHERE assets.id = {$d['asset_id']}";
        $r = $this->sql_query($query);
        $asset = mysql_fetch_assoc($r);

        if (count($sms)+count($email)) {
            $msg = $asset['building_name'].' received invalid rawCardNumber on '.$asset['asset_name'].' at '.date('m/d/Y g:i a', strtotime($d['timestamp']));

            if(count($sms)) {
                foreach ($sms as $num) {
                    $this->SMS($num, $msg);
                }
            }

            if (count($email)) {
                $subject = 'Entry Notification';
                foreach ($email as $to) {
                    $this->sendEmail($to, $subject, $msg);
                }
            }

        }
    }

    public function getLogs() {
        $assets = $this->getAssets();

        $query = "
        SELECT assets.id as asset_id,
            name,
            ip_address,
            admin_user,
            admin_pass,
            locked
        FROM assets
        WHERE status = 1
            AND building_id = $this->building_id
        ";

        $r = $this->sql_query($query);
        while($row = mysql_fetch_assoc($r)) {
            extract($row);

            $this->host = $ip_address;
            $this->username = $admin_user;
            $this->password = $admin_pass;

            $hids = $this->getHids($asset_id);
            $users_units = $this->getUnitUser();
            $tids = $this->getTokenUser($asset_id);

            $num = $this->hidEventNum();

            if ($this->verbose) echo "$num events for  $name\n";
            $this->lastGrant = null;

			$done =  false;
            for ($i = 0; $i <= $num; $i++) {

                $vars = $this->hidListEvents(1, $i, true);
                foreach($vars as $var) {
                    if (array_key_exists('attributes', $var)) {
                        $notify = $notify2 = false;
                        $info = $var['attributes'];
                        switch ($var['tag']) {
                        case 'hid:EventMessage':
                            extract($info);
                            
                            $timestamp = strtotime($timestamp);
                            $d['asset_id'] = $asset_id;
                            $d['building_id'] = $this->building_id;
                            $d['timestamp'] = date('Y-m-d H:i:s', $timestamp);
                            $d['event_type'] = $eventType;
                            $d['event_data'] = '';
                            $d['user_id'] = 0;
                            $d['hid_id'] = 0;

                            switch($eventType) {
                            case '2020':   // FOB Grant
                                 $d['user_id'] = $user_id = array_key_exists($cardholderID, $hids) ? $hids[$cardholderID] : 0;
                                 $d['unit_id'] = $unit_id = array_key_exists($user_id, $users_units) ? $users_units[$user_id] : 0;
                                 $d['token_id'] = array_key_exists($d['user_id'], $tids) ? $tids[$d['user_id']] : 0;
                                 $d['hid_id'] = $cardholderID;
                                 $d['event_data'] = serialize($info);
                                 $done = $this->hidLog($d);

                                 // If this unit is in the notify list then set flag
                                 if (array_key_exists($unit_id, $this->notifies))
                                     $notify = true;

                                 if ($timestamp > $this->lastGrant)
                                    $this->lastGrant = $timestamp;
                                 break;
                            case '12031':  // Grant Access
                                $d['event_data'] = serialize($info); // Store additional Info
                                $d['token_id'] = null;
                                $d['hid_id'] = null;
                                $d['user_id'] = null;
                                $d['unit_id'] = null;

                                $done = $this->hidLog($d);
                                break;
                            case '4041':  // Forced Door Alarm
                            case '4042':  // Door Held
                            case '4043':  // Tamper Switch Alarm
                                // Send Alarm Acknowledge.
                                $this->hidAlarmReset();
                            case '2024':  // Card without a schedule
                            case '7020':  // Change Time
                            case '12032':  // Unlock Door
                            case '12033':  // Lock Door
                            case '4034':  // Alarm Acknowledged
                            case '4044':  // Input A Alarm
                            case '4045':  // Input B Alarm - Intercom trip
                                $d['event_data'] = serialize($info); // Store additional Info
                                $d['token_id'] = null;
                                $d['hid_id'] = null;
                                $d['user_id'] = null;
                                $d['unit_id'] = null;
                                $done = $this->hidLog($d);
                                break;
                            case '1022':  // Invalid Card
                            case '2043':  // Unassigned Card
                                $d['event_data'] = serialize($info); // Store additional Info
                                $d['token_id'] = null;
                                $d['hid_id'] = null;
                                $d['user_id'] = null;
                                $d['unit_id'] = null;
                                if ($rawCardNumber == '00') {
									if ($this->verbose) echo "notify2\n";
                                    $notify2 = true;
                                }
                                $done = $this->hidLog($d);
                                break;
                            default:
								echo "????\n";
                                print_r($info);
                                break;
                            }
                            break;
                        }
                        if ($done) break;
                        
                        // Only notify once
                        if ($notify) {
                            $this->sendNotify($d, $unit_id, $user_id);
                        }
                        if ($notify2) {
							if ($this->verbose) echo "notify2 Send\n";
                            $this->sendNotify2($d, $this->notifies2);
                        }

                        $d = [];
                    }
                }
                if ($done) break;
            }
            if ($this->lastGrant) {
                $this->lastGrant += 8;
                while ($this->lastGrant > time())
                    sleep(1);

                $this->chkStatus($locked);
            }
        }
    }
}
