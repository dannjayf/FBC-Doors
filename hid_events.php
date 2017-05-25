<?php
include 'config.php';
include 'config_hid.php';
include 'email.inc.php';

date_default_timezone_set ('America/Chicago');

$query = "
    SELECT
        a.id,
        a.loc,
        a.description,
        a.ip_address,
        a.admin_user,
        a.admin_pass,
        b.name
    FROM asset a
    LEFT OUTER JOIN loc b ON (a.loc = b.id)
    WHERE a.active=1
    ";
$r = sql_query($query);

while( $row = mysql_fetch_assoc($r)) {
    echo "\n{$row['description']}\n";
    $host = $row['ip_address'];
    $username = $row['admin_user'];
    $password = $row['admin_pass'];
    $asset_id = $row['id'];
    $loc = $row['loc'];
    $peoples = getPIDs($asset_id);
    $tids = getTIDs($asset_id);
    // print_r($peoples);
 
    $num = hidEventNum(); echo "$num\n";
    $done = false;
    // for ($i = $num; $i > 0; $i--) {
    for ($i = 0; $i <= $num; $i++) {
        $vars = hidListEvents(1, $i, true);
        echo "$i\r";
        foreach ($vars as $var) {
            $info = $var['attributes'];
            switch ($var['tag']) {
            case 'hid:EventMessage':
                extract($info);
                $d['loc_id'] = $loc;
                $d['asset_id'] = $asset_id;
                $d['timestamp'] = date('Y-m-d H:i:s', strtotime($timestamp));
                $d['event_type'] = $eventType;

                switch ($eventType) {
                case '2020':
                case '2024':  // Card without a schedule
		    
                    $people_id = array_key_exists($cardholderID, $peoples) ? $peoples[$cardholderID] : 0;
                    $d['people_id'] = $people_id;
                    if (!empty($rawCardNumber)) 
                        $d['token_id'] = hidTokenLookup($rawCardNumber);
                    elseif (array_key_exists($people_id, $tids))
                        $d['token_id'] = $tids[$people_id];
                    else
                        $d['token_id'] = 'null';
                    $d['hid_id'] = $cardholderID;
                    $d['event_data'] = serialize($info);
                    hidUpdLastUse($d);
                    $done = hidLog($d);
                    break;
                case '1022':  // Invalid Card
                case '2043':  // Unassigned Card
                case '7020':  // Change Time
                case '12031':  // Grant Access
                case '12032':  // Unlock Door
                case '12033':  // Lock Door
                case '4034':  // ?
                case '4041':  // ?
                case '4042':  // ?
                case '4043':  // ?
                case '4044':  // ?
                case '4045':  // ?
                    $d['event_data'] = serialize($info);
                    $done = hidLog($d);
                    break;
                default:
                    print_r($info);
                    break;
                }
                break;
            default:
                print_r($var);
                break;
            }
            // Remove $d so it's fresh
            $d = array();
            if ($done) break;
        }
        // Skip rest if entry exists
        if ($done) break;
    }
}



?>
