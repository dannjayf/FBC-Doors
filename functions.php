<?php

function h_header($title, $css = '', $onload = '') {
    global $pgm;
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <html>
    <head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <?php
    /*
    <SCRIPT TYPE="text/javascript" SRC="backlink.js"></SCRIPT>
    <style type="text/css">@import url(calendar.css);</style>
    <script type="text/javascript" src="calendar/calendar.js"></script>
    <script type="text/javascript" src="calendar/lang/calendar-en.js"></script>
    <script type="text/javascript" src="calendar/calendar-setup.js"></script>
    <script type="text/javascript" src="overlib/overlib.js"></script>
    */
    ?>
    <link rel='stylesheet' href='page.css'>
    <link rel='stylesheet' media=print href='print.css'>
    <?php
        // If there is a secondary style sheet load it.
        if ($css != '')
            echo "<link rel='stylesheet' href='$css'>\n";
    ?>
    </head>
    <?php

    // if there is an onload event put it here
    if ($onload != '') {
        echo "<body OnLoad=\"$onload;\">\n";
    }
    else
        echo "<body>\n";

}

function head($title, $tmenu = NULL) {
    h_header($title);
    echo '<img alt="FirstB" src="logo.jpg"><br>';
    if ($tmenu === false) 
	;
    else {
	echo "<a href='.'>Menu</a> | ";
	echo "<a href='setup.php'>Setup</a>";
	if ($tmenu != '') echo " | $tmenu";
    }
    if (strpos($title,"|"))
	list($title, $subtitle) = explode("|", $title);
    else
	$subtitle = '';

    echo "<h1>$title</h1>\n";
    if ($subtitle != '') echo "<h2>$subtitle</h2>\n";
}

function editrow($title, $key, $val, $lenrows = '', $max = '', $help = '', $hidden = '') {
    if (strstr($lenrows, ":"))
        list ($len, $rows, $editor) = explode(":", $lenrows);
    else {
        $len = $lenrows;
        $rows = '';
    }
    if ($len < 1) $len = 30;
    if ($max > 0) 
        $max = "maxlength='$max'";
    else 
        $max = '';
    echo "<tr>\n";
    echo "<td class='title'>$title";
    if ($help != '')
        echo '<br />', help(-2, $help);
    echo "</td>\n";
    if ($hidden != '')
        echo "<input type='hidden' name='$hidden$key' value='$val' />\n";
    if ($rows != "") {
        echo "<td>\n";
        if ($editor)
            include 'tinymce.php';
        echo "<textarea cols='$len' rows='$rows' name='$key' id='$key'>$val";
        echo "</textarea>\n";
        echo "</td>\n";

    }
    else {
        //
        // Needed to take care of single quotes in the value
        //
        $val = htmlspecialchars($val);
        echo "<td><input type='text' size='$len' $max name='$key' value=\"$val\" id='$key'>";
        echo "</td>\n";
    }
    echo "</tr>\n";
}

function sql_date($date,$end = 0) {
    if ($date) 
        if ($end)
            $date = "'".strftime('%Y-%m-%d 23:59:59', strtotime($date))."'";
        else
            $date = "'".strftime('%Y-%m-%d %H:%M:%S', strtotime($date))."'";

    else
        $date = 'null';

    return $date;
}

function sql_time($time) {
    if ($time) 
        $time = "'".strftime('%H:%M', strtotime('2014-01-01 '.$time))."'";
    else
        $time = 'null';

    return $time;
}

function dsp_date($date) {
    if ($date) 
        $date = strftime('%m/%d/%Y', strtotime($date));
    else
        $date = '';

    return $date;
}

function dsp_time($time) {
    if ($time) 
        $time = strftime('%H:%M', strtotime($time));
    else
        $time = '';

    return $time;
}

function sel_loc($val, $key, $p1) {

    $query = "
    SELECT id, name
    FROM loc
    ORDER BY name
    ";

    $r = sql_query($query);
    
    echo "<select name='$key'>\n";
    $check = '' == $val ? 'selected' : '';
    echo "<option value='' $check ></option>\n";
    while ($row = mysql_fetch_row($r)) { 

        $check = ($row[0] == $val) ? 'selected' : '';

        echo "<option value='$row[0]' $check >$row[1]</option>\n";
    }
    echo "</select>\n";
}

function sel_location($val, $key, $p1) {
    global $pgm;

    $query = "
    SELECT id, name
    FROM loc
    ORDER BY name
    ";

    $r = sql_query($query);
    
    $click = "onClick=\"this.form.action='$pgm';this.form.submit()\"";
    while ($row = mysql_fetch_row($r)) { 

        $check = ($row[0] == $val) ? 'checked' : '';

        echo "<input type='radio' name='$key' value='$row[0]' $check $click /> $row[1]<br />\n";
    }
}

// Select camera model
function sel_camera($val, $key, $p1) {

    $query = "
    SELECT id, model
    FROM cameras
    ORDER BY model
    ";

    $r = sql_query($query);
    
    echo "<select name='$key'>\n";
    $check = '' == $val ? 'selected' : '';
    echo "<option value='' $check ></option>\n";
    while ($row = mysql_fetch_row($r)) { 

        $check = ($row[0] == $val) ? 'selected' : '';

        echo "<option value='$row[0]' $check >$row[1]</option>\n";
    }
    echo "</select>\n";
}

// Select swtich model
function sel_switch($val, $key, $p1) {

    $query = "
    SELECT id, model
    FROM switches
    ORDER BY model
    ";

    $r = sql_query($query);
    
    echo "<select name='$key'>\n";
    $check = '' == $val ? 'selected' : '';
    echo "<option value='' $check ></option>\n";
    while ($row = mysql_fetch_row($r)) { 

        $check = ($row[0] == $val) ? 'selected' : '';

        echo "<option value='$row[0]' $check >$row[1]</option>\n";
    }
    echo "</select>\n";
}

// Select ip power switch
function sel_power($val, $key, $p1) {

    $query = "
    SELECT id, model
    FROM powers
    ORDER BY model
    ";

    $r = sql_query($query);
    
    echo "<select name='$key'>\n";
    $check = '' == $val ? 'selected' : '';
    echo "<option value='' $check ></option>\n";
    while ($row = mysql_fetch_row($r)) { 

        $check = ($row[0] == $val) ? 'selected' : '';

        echo "<option value='$row[0]' $check >$row[1]</option>\n";
    }
    echo "</select>\n";
}

function sel_asset($val, $key, $p1) {
    $loc = $_SESSION['loc'];

    $query = "
    SELECT id, assetid
    FROM asset
    WHERE loc =  $loc
    ORDER BY assetid
    ";

    $r = sql_query($query);
    
    echo "<select name='$key'>\n";
    $check = '' == $val ? 'selected' : '';
    echo "<option value='' $check ></option>\n";
    while ($row = mysql_fetch_row($r)) { 

        $check = ($row[0] == $val) ? 'selected' : '';

        echo "<option value='$row[0]' $check >$row[1]</option>\n";
    }
    echo "</select>\n";
}

function sel_asset_type($val, $key, $p1) {
    $loc = $_SESSION['loc'];

    $query = "
    SELECT id, name
    FROM assets
    ORDER BY name
    ";

    $r = sql_query($query);
    
    echo "<select name='$key'>\n";
    $check = '' == $val ? 'selected' : '';
    echo "<option value='' $check ></option>\n";
    while ($row = mysql_fetch_row($r)) { 

        $check = ($row[0] == $val) ? 'selected' : '';

        echo "<option value='$row[0]' $check >$row[1]</option>\n";
    }
    echo "</select>\n";
}

function sel_helios($val, $key, $p1) {
    $loc = $_SESSION['loc'];

    $query = "
    SELECT id, name
    FROM helios
    WHERE loc =  $loc
    ORDER BY name
    ";

    $r = sql_query($query);
    
    echo "<select name='$key'>\n";
    $check = '' == $val ? 'selected' : '';
    echo "<option value='' $check ></option>\n";
    while ($row = mysql_fetch_row($r)) { 

        $check = ($row[0] == $val) ? 'selected' : '';

        echo "<option value='$row[0]' $check >$row[1]</option>\n";
    }
    echo "</select>\n";
}

function sel_dow($val, $key, $p1) {
    global $dows;

    $loc = $_SESSION['loc'];

    $vals = explode(',', $val);

    foreach ($dows as $dow) {
	if ($vals == '')
	    $chk = '';
	elseif (array_search($dow, $vals) === false)
	    $chk = '';
	else
	    $chk = 'checked';

	echo "<input type='checkbox' name='{$key}[]' value='$dow' $chk /> $dow&nbsp;&nbsp;&nbsp;";
    }

}


// Select WAP model
function sel_wap($val, $key, $p1) {

    $query = "
    SELECT id, model
    FROM waps
    ORDER BY model
    ";

    $r = sql_query($query);
    
    echo "<select name='$key'>\n";
    $check = '' == $val ? 'selected' : '';
    echo "<option value='' $check ></option>\n";
    while ($row = mysql_fetch_row($r)) { 

        $check = ($row[0] == $val) ? 'selected' : '';

        echo "<option value='$row[0]' $check >$row[1]</option>\n";
    }
    echo "</select>\n";
}

// Select Unit
function sel_apt($val, $key, $p1) {

    $loc = $_SESSION['loc'];
    $query = "
    SELECT id, apt
    FROM apt
    WHERE loc = $loc
    ORDER BY apt
    ";

    $r = sql_query($query);
    
    echo "<select name='$key'>\n";
    $check = '' == $val ? 'selected' : '';
    echo "<option value='' $check ></option>\n";
    while ($row = mysql_fetch_row($r)) { 

        $check = ($row[0] == $val) ? 'selected' : '';

        echo "<option value='$row[0]' $check >$row[1]</option>\n";
    }
    echo "</select>\n";
}

// Select a person
function sel_people($val, $key, $p1) {
    $query = "
    SELECT id, CONCAT_WS(', ', lname, fname) as name
    FROM people
    ORDER BY lname, fname
    ";

    $r = sql_query($query);
    
    echo "<select name='$key'>\n";
    $check = '' == $val ? 'selected' : '';
    echo "<option value='' $check ></option>\n";
    while ($row = mysql_fetch_row($r)) { 

        $check = ($row[0] == $val) ? 'selected' : '';

        echo "<option value='$row[0]' $check >$row[1]</option>\n";
    }
    echo "</select>\n";
}

// Dropdown list
function editlist($title, $key, $val, $opt) {
    echo "<tr>\n";
    echo "<td align='right' class='title'><b>$title</b></td>\n";
    echo "<td>\n";

    echo "<select name='$key'>\n";
    foreach ($opt as $k => $v) {

        if ($k == $val) $check = 'selected'; else $check = '';

        echo "<option value='$k' $check >$v</option>\n";
    }
    echo "</select>\n";

    echo "</td>\n</tr>\n";
}

// Edit Helios
function edithelios($title, $tab, $loc, $apt = 0) {
    $apt = $apt + 0;
    echo "<tr>\n",
        "<td align='right' class='title'><b>$title</b></td>\n",
        "<input type='hidden' name='htab' value='$tab' />",
        "<td>\n<table>";
    $query = "
    SELECT 
        a.id,
        a.name,
        b.helios_id
    FROM helios a
    LEFT OUTER JOIN hacl b ON (b.helios_id = a.id and b.apt_id = $apt)
    WHERE a.loc = $loc
    ORDER BY a.name
    ";

    $r = sql_query($query);
    while ($row = mysql_fetch_array($r)) {
        extract($row);
        $checked = $helios_id ? 'checked' : '';

        echo "<tr>\n",
            "<td><input type='checkbox' $checked name=helios[$id] /></td>\n",
            "<td>$name</td>\n",
            "</tr>\n";
    }

    echo "</table>\n</td>\n</tr>\n";
}

// Edit RFID Devices
function editrfid($title, $loc, $tid = 0) {
    $tid = $tid + 0;

    echo "<tr>\n";
    echo "<td align='right' class='title'><b>$title</b></td>\n";
    echo "<td>\n<table>";
    $query = "
    SELECT 
        a.id,
        a.description,
        b.asset_id
    FROM asset a
    LEFT OUTER JOIN acl b ON (b.asset_id = a.id and b.token_id = $tid)
    WHERE loc = $loc
    ORDER BY description
    ";

    $r = sql_query($query);
    while ($row = mysql_fetch_array($r)) {
        extract($row);
        $checked = $asset_id ? 'checked' : '';

        echo "<tr>\n",
            "<td><input type='checkbox' $checked name='asset[$id]' value='1' /></td>\n",
            "<td>$description</td>\n",
            "</tr>\n";
    }

    echo "</table>\n</td>\n</tr>\n";
}

// Select RFID Devices
function sel_rfid($key, $val, $pid) {
    global $loc;

    $query = "
    SELECT 
        a.id,
        a.card_id,
        a.token,
        b.apt as bapt,
        a.people_id
    FROM token a
    LEFT OUTER JOIN people b ON (b.id = a.people_id)
    WHERE a.loc = $loc
    and a.card_id <> ''
    ORDER BY card_id
    ";

    $r = sql_query($query);

    $out = "<select name='$key'>\n";
    // If not here.
    if ($val == '')
        $out .= "<option selected value=''></option>\n";
    else
        $out .= "<option value=''></option>\n";

    while ($row = mysql_fetch_array($r)) {
        extract($row);
        // If this is the assiged card use it.
        if ($pid == $people_id)
            $out .= "<option selected value='$id'>$card_id <span style='font-size:8px;'>($token)</span></option>\n";
        // The only other option is a card that is not assigned.
        elseif (!$people_id)
            $out .= "<option value='$id'>$card_id <span syle='font-size:8px'>($token)</span></option>\n";

    }

    $out .= "</select>\n";

    return $out;
}

// Selectd RFID Devices for Apt
function edit_apt_rfid($title, $loc, $apt = 0) {
    $apt = $apt + 0;

    echo "<tr>\n";
    echo "<td align='right' class='title'><b>$title</b></td>\n";
    echo "<td>\n<table>";
    $query = "
    SELECT 
        a.id,
        a.description,
        b.asset_id
    FROM asset a
    LEFT OUTER JOIN apt_acl b ON (b.asset_id = a.id and b.apt_id = $apt)
    WHERE loc = $loc
    ORDER BY description
    ";

    $r = sql_query($query);
    while ($row = mysql_fetch_array($r)) {
        extract($row);
        $checked = $asset_id ? 'checked' : '';

        echo "<tr>\n",
            "<td><input type='checkbox' $checked name='asset[$id]' value='1' /></td>\n",
            "<td>$description</td>\n",
            "</tr>\n";
    }

    echo "</table>\n</td>\n</tr>\n";
}

// Selectd RFID Devices for Apt
function get_apt_rfid($loc, $apt = 0) {
    $apt = $apt + 0;

    $query = "
    SELECT 
        a.id,
        a.description,
        b.asset_id
    FROM 
        asset a,
        apt_acl b
    WHERE 
        b.asset_id = a.id 
        and b.apt_id = $apt
        and a.loc = $loc
    ORDER BY a.description
    ";

    $r = sql_query($query);
    $out = '';
    while ($row = mysql_fetch_array($r)) {
        extract($row);
        $out .= "$description, ";
    }

    return (substr($out,0,-2));

}

// Return the raw token from $tid
function getToken($tid, $hid) {
    if ($tid) {
        $query = "
        SELECT 
            a.token,
            b.pid
        FROM token a
        LEFT OUTER JOIN hid b ON (b.asset_id = $hid and b.people_id = a.people_id)
        WHERE a.id = $tid
        ";

        $r = sql_query($query);
        $row = mysql_fetch_array($r);
    }
    else
        $row = array();

    return $row;
}

// Update the Token Assignment
function updToken($tid, $pid) {
    if ($tid) {
        $query = "
        SELECT 
            id,
            people_id
        FROM token
        WHERE id = $tid
        ";

        $r = sql_query($query);
        $row = mysql_fetch_row($r);
        if (is_array($row)) {
            extract ($row);

            // If different the update
            if ($people_id != $pid) {
                // Delete old one.
                $query = "
                UPDATE token
                SET people_id = null
                WHERE people_id = $pid
                ";

                $r = sql_query($query);

                // Add New One
                $query = "
                UPDATE token
                SET people_id = $pid
                WHERE id = $tid
                ";

                $r = sql_query($query);
            }
        }
    }
}

/*
 * Upate the RFID controllers base on info
 * 
 * $tid = Token ID
 * $apt = Unit ID
 * $act = Active (if this Token should be activated)
 */
function updTokenACL($tid, $apt, $act) {
    global $host, $username, $password;

    if ($act) {
        foreach (getAllRFIDs($apt) as $rfid) {
            // Setup array of valid assets based on list
            $assets[$rfid['id']] = 1;
            $host = $rfid['ip_address'];
            $username = $rfid['admin_user'];
            $password = $rfid['admin_pass'];
            $tokens = getToken($tid, $rfid['id']);
            $token = $tokens['token'];
            if ($token != '') {
                // Update the ACL
                updACL($tid, $assets);
                $pid = $tokens['pid'];
                echo "using $username to activate $token for $pid ($host {$rfid['name']})<br>\n";
                $vars = hidAssignCard($pid, $token);
                echo $vars[0]['attributes']['errorMessage']," ";
                $vars = hidAssignSchedule($pid, 1);
                // echo $vars[0]['attributes']['errorMessage']," ";
                echo ".";
            }
            else {
                echo "Missing Token for $tid<br>\n";
            }
            ob_flush(); flush();
        }
    }
    // Inactive update each RFID with blank id.
    else {
        foreach (getAllRFIDs() as $rfid) {
            $host = $rfid['ip_address'];
            $username = $rfid['admin_user'];
            $password = $rfid['admin_pass'];
            $tokens = getToken($tid, $rfid['id']);
            $token = $tokens['token'];
            //  Remove Assignment
            // echo "deactivate $token for $host {$rfid['name']}<br>\n";
            $vars = hidAssignCard('', $token);
            // echo $vars[0]['attributes']['errorMessage']," ";
            echo ".";
            ob_flush(); flush();
        }
    }
}

// Update the ACL
function updACL($tid, $asset) {
    $loc = $_SESSION['loc'];

    $query = "
    SELECT 
        a.id,
        b.token_id
    FROM asset a
    LEFT OUTER JOIN acl b ON (b.asset_id = a.id and b.token_id = $tid)
    WHERE loc = $loc
    ";

    $r = sql_query($query);
    while ($row = mysql_fetch_array($r)) {
        $id = $row['id'];
        // Delete if no longer assigned to this asset
        if ($row['token_id'] == $tid and empty($asset[$id]))
            delACL($id, $tid);
        elseif (!empty($asset[$id]) and $row['token_id'] != $tid)
            addACL($id, $tid);
    }
}

// Delete entries for this pair
function delACL($id, $tid) {
    $query = "
    DELETE FROM acl
    WHERE asset_id = $id and token_id = $tid
    ";

    $r = sql_query($query);
}

// Add entries  for this pair
function addACL($id, $tid) {
    $query = "
    INSERT INTO acl (asset_id, token_id, timezone)
    VALUES ($id, $tid, 1)
    ";

    $r = sql_query($query);
}

// Update the HACL
function updHACL($apt, $helios) {
    $loc = $_SESSION['loc'];

    $query = "
    SELECT 
        a.id,
        b.apt_id
    FROM helios a
    LEFT OUTER JOIN hacl b ON (b.helios_id = a.id and b.apt_id = $apt)
    WHERE loc = $loc
    ";

    $r = sql_query($query);
    while ($row = mysql_fetch_array($r)) {
        $id = $row['id'];
        // Delete if no longer assigned to this asset
        if ($row['apt_id'] == $apt and empty($helios[$id]))
            delHACL($id, $apt);
        elseif (!empty($helios[$id]) and $row['apt_id'] != $apt)
            addHACL($id, $apt);
    }
}

// Delete entries for this pair
function delHACL($id, $apt) {
    $query = "
    DELETE FROM hacl
    WHERE helios_id = $id and apt_id = $apt
    ";

    $r = sql_query($query);
}

// Addd entries  for this pair
function addHACL($id, $apt) {
    $query = "
    INSERT INTO hacl (helios_id, apt_id)
    VALUES ($id, $apt)
    ";

    $r = sql_query($query);
}

// Update the AACL
function updAACL($apt, $asset) {
    $loc = $_SESSION['loc'];

    $query = "
    SELECT 
        a.id,
        b.apt_id
    FROM asset a
    LEFT OUTER JOIN apt_acl b ON (b.asset_id = a.id and b.apt_id = $apt)
    WHERE loc = $loc
    ";

    $r = sql_query($query);
    while ($row = mysql_fetch_array($r)) {
        $id = $row['id'];
        // Delete if no longer assigned to this asset
        if ($row['apt_id'] == $apt and empty($asset[$id]))
            delAACL($id, $apt);
        elseif (!empty($asset[$id]) and $row['apt_id'] != $apt)
            addAACL($id, $apt);
    }
}

// Delete entries for this pair
function delAACL($id, $apt) {
    $query = "
    DELETE FROM apt_acl
    WHERE asset_id = $id and apt_id = $apt
    ";

    $r = sql_query($query);
}

// Addd entries  for this pair
function addAACL($id, $apt) {
    $query = "
    INSERT INTO apt_acl (asset_id, apt_id)
    VALUES ($id, $apt)
    ";

    $r = sql_query($query);
}

function sel_list($key, $val, $opt) {

    $out = "<select name='$key'>\n";
    foreach ($opt as $k => $v) {

        if ($k == $val) $check = 'selected'; else $check = '';

        $out .= "<option value='$k' $check >$v</option>\n";
    }
    $out .= "</select>\n";

    return $out;
}


// Used for drop downs
function editfunc($title, $key, $val, $func, $p1='', $help = '') {
    echo "<tr>\n";
    echo "<td align=right class='title'>$title";
    if ($help != '')
        echo '<br />', help(-2, $help);
    echo "</td>\n";

    $val = htmlspecialchars($val); // Needed to take care of single quotes in the value
    echo "<td>\n";
    $func($val, $key, $p1);
    echo "</td>\n";
    echo "</tr>\n";
}

// Echeck Box
function editchk($title, $key, $val, $ck) {
    echo "<tr>\n";
    echo "<td align=right class='title'>$title</td>\n";
    //
    // Needed to take care of single quotes in the value
    //
    if ($val == $ck) 
        $chkd = 'checked';
    else
        $chkd = '';
    
    echo "<td>\n";
    echo "<input type='checkbox' $chkd name='$key' value='$ck' />\n";
    echo "</td>\n";
    echo "</tr>\n";
}

// Return name of asset
function getAssetName($id) {
    $query = "
    SELECT assetid
    FROM asset
    WHERE id = $id
    ";

    $r = sql_query($query);
    $row = mysql_fetch_row($r);

    return $row[0];
}

// returns Helios Info
function get_helios_info($id) {
    $query = "
    SELECT * 
    FROM helios
    WHERE id = $id
    ";

    $r = mysql_query($query) or die($query);

    return mysql_fetch_array($r);
}

function get_helios_pbook($id) {
    $query = "
    SELECT *
    FROM pbook
    WHERE device_id = $id
    ORDER BY entry_id
    ";

    $r = sql_query($query);

    while ($row = mysql_fetch_array($r)) {
        $info[$row['entry_id']] = $row;
    }

    return $info;
}

// returns ATA Info
function get_atainfo($id) {
    $query = "
    SELECT * 
    FROM ata
    WHERE id = $id
    ";

    $r = mysql_query($query) or die($query);

    return mysql_fetch_array($r);
}


function get_data($ata_id, $port) {
    $query = "
    SELECT *
    FROM data
    WHERE ata_id = $ata_id
    and port = $port
    ";

    $r = mysql_query($query) or die($query);
    $row = mysql_fetch_array($r);

    return $row;
}

function get_ata_status($ata_id) {
    $dt = strftime('%Y-%m-%d %H:%M', strtotime('now') - 400);
    $query = "
    SELECT *
    FROM data
    WHERE ata_id = $ata_id
    and ext <> ''
    and updts > '$dt'
    ";

    $r = mysql_query($query) or die($query);
    while ($row = mysql_fetch_array($r)) {
        $i++;
        if (substr($row['stat'],0,2) == 'OK')
            $n++;
    }


    return $i ? "$n of $i" : '';
    
}

function sql_escape($s) {
    return str_replace("'", "\\'", stripslashes($s));
}

function fix_quote($s) {
    return htmlentities($s, ENT_QUOTES);
}

// Merging function (update keywords in text)
function template_merge($text, $key, $value) {
    $d = explode('{'.$key.'}', $text);
    for ($i = 1; $i < count($d); $i++) {
        $d[$i] = $d[$i-1].$value.$d[$i];
    }
    $i--;

    return $d[$i];
}


// Create the config file
function create_cfg($id, $cfgname) {

    echo "file: $cfgname<br />\n";

    $info = get_atainfo($id);
    $domain = $info['domain'];
    $sip_port = $info['sip_port'];
    $gateway = $info['gateway'];
    $ip_addr = $info['ip_addr'];
    $ip_mask = $ip_addr == 'dhcp' ? '' : $info['ip_mask'];
    $dns     = $info['dns'];
    $syslog  = $info['syslog'];
    $snmp    = $info['snmp'];
    $timeoffset = "-05:00";

    $ports = $info['ports'];
    for ($i = 1; $i <= $ports; $i++) {
	$data = get_data($id, $i);
	$cinfo[$i]['ext'] = $data['ext'];
	$cinfo[$i]['name'] = $data['name'];
	$cinfo[$i]['secret'] = $data['secret'];
    }

    $cfg = file_get_contents('template_head.cfg');

    // Update info.
    $cfg = template_merge($cfg, 'timeoffset', $timeoffset);
    $cfg = template_merge($cfg, 'gateway', $gateway);
    $cfg = template_merge($cfg, 'domain', $domain);
    $cfg = template_merge($cfg, 'sip_port', $sip_port);
    $cfg = template_merge($cfg, 'ip_addr', $ip_addr);
    $cfg = template_merge($cfg, 'ip_mask', $ip_mask);
    $cfg = template_merge($cfg, 'dns', $dns);
    if ($syslog != '') {
        $syslog = "syslog-client\n  remote $syslog 514\n  facility sip severity debug";
    }
    $cfg = template_merge($cfg, 'syslog', $syslog);

    if ($snmp != '') {
        $snmp = "snmp community musadmin rw\n".
            "snmp community mustech ro\n".
            "snmp target $snmp security-name musadmin\n".
            "snmp host $snmp security-name musadmin\n".
            "snmp host $snmp security-name mustech";
    }
    $cfg = template_merge($cfg, 'snmp', $snmp);


    // First routing
    $cfg .= "routing-table called-e164 RT_TO_FXS\n";
    foreach ($cinfo as $i => $d) {
	$ext  = $d['ext'] != '' ? $d['ext'] : '" "';
        $cfg .= "    route $ext dest-interface IF_FXS_$i\n";
    }
    // Names
    $cfg .= "\nmapping-table calling-e164 to calling-name MT_EXT_TO_NAME\n";
    foreach ($cinfo as $i => $d) {
	$ext  = $d['ext'] != '' ? $d['ext'] : '" "';
	$name = $d['name'] != '' ? $d['name'] : ' ';
	    $cfg .= "    map $ext to \"$name\"\n";
    }

    $cfg .= "\ninterface sip IF_SIP_PBX\n".
            "       bind context sip-gateway GW_SIP_ALL_EXTENSIONS\n".
	    "       route call dest-table RT_TO_FXS\n".
            "       hold-method direction-attribute\n".
            "       remote $domain $sip_port\n\n";
    foreach ($cinfo as $i => $d) {
	$ext  = $d['ext'] != '' ? $d['ext'] : '" "';
        $cfg .= "interface fxs IF_FXS_$i\n".
            "    caller-id-presentation mid-ring\n".
            "         route call dest-table RT_DIGITCOLLECTION\n".
            "         message-waiting-indication stutter-dial-tone\n".
            "         message-waiting-indication frequency-shift-keying\n".
            "         call-transfer\n".
            "         subscriber-number $ext\n".
            "         service-pattern toggle !\n";
		      
    }

    $cfg .= "\ncontext cs switch\n  no shutdown\n\n".
	"authentication-service AS_ALL_EXTENSIONS\n\n";

    foreach ($cinfo as $i => $d) {
	$ext  = $d['ext'] != '' ? $d['ext'] : '" "';
	$secret  = $d['secret'] != '' ? $d['secret'] : '" "';
        $cfg .= "      username $ext password $secret\n";
    }

    $cfg .= "\nlocation-service LS_ALL_EXTENSIONS\n".
	    "    domain 1 $domain $sip_port\n".
	    "    identity-group default\n";
    foreach ($cinfo as $i => $d) {
        if ($d['ext'] != '') {
            $ext  = $d['ext'] != '' ? $d['ext'] : '" "';
            $cfg .= "   identity $ext\n".
                    "       authentication outbound\n".
                    "          authenticate 1 authentication-service AS_ALL_EXTENSIONS username $ext\n".
                    "       registration outbound\n".
                    "          registrar $domain $sip_port\n".
                    "          lifetime 10\n".
                    "          register auto\n".
                    "          retry-timeout on-system-error 10\n".
                    "          retry-timeout on-client-error 10\n".
                    "          retry-timeout on-server-error 10\n";
        }
    }

    $cfg .= "\ncontext sip-gateway GW_SIP_ALL_EXTENSIONS\n".
	    "     interface WAN\n".
	    "        bind interface IF_IP_LAN context router port $sip_port\n\n".
	    "context sip-gateway  GW_SIP_ALL_EXTENSIONS\n".
	    "      bind location-service LS_ALL_EXTENSIONS\n".
	    "      no shutdown\n\n".
	    "port ethernet 0 0\n".
	    "  medium auto\n".
	    "  encapsulation ip\n".
            "  bind interface IF_IP_LAN router\n".
	    "  no shutdown\n\n";

    foreach ($cinfo as $i => $d) {
	$p = $i-1;
	$cfg .= "port fxs 0 $p\n".
		"   caller-id format bell\n".
		"   use profile fxs us\n".
		"   encapsulation cc-fxs\n".
		"   bind interface IF_FXS_$i switch\n".
		"   no shutdown\n";

    }
    $cfg .= "################################################# END ##################################################\n";

    file_put_contents($cfgname, $cfg);
}

// Convers mac address to standard
function mac_addr($s) {
    $s = strtolower($s);
    $s = str_replace('-', ':', $s);
    $s = str_replace(' ', '', $s);

    // Check for no delimiters
    if (strlen($s) == 12) {
        $n = '';
        for ($i = 0; $i < 13 ; $i+=2)
            $n .= substr($s,$i,2).':';
        $s = substr($n, 0, 17);
    }

    return $s;
}

//Setup location as a session variable
function setLocation($pgm) {

    if (!empty($_POST['loc'])) {
        $_SESSION['loc'] = $_POST['loc'];
        header("Location: $pgm");
    }
    else {
        head("Select Location");

        // If new the unset the location so I can be set.
        if(!empty($_GET['loc']) and $_GET['loc'] == 'new') {
            unset($_SESSION['loc']);
        }

        ?>
        <form action="<?php echo $pgm;?>" method="post">
        <?php sel_location('', 'loc', '') ?>
        </form>
        <?php
        //<input type="Submit" name="Go" value="Go" />
    }
}

// Get the full name for the location
function getLocation($loc = false) {
    if ($loc === false)
        $loc = $_SESSION['loc'];

    if ($loc) {
        $query = "
        SELECT name
        FROM loc
        WHERE id = $loc";

        $r = sql_query($query);

        $row = mysql_fetch_array($r);
        $out = $row['name'];
    }
    else
        $out = '';

    return $out;
}

// Return list of doors for token
function getRFIDs($tid) {
    $loc = $_SESSION['loc'];

    $query = "
    SELECT 
        a.id,
        a.assetid,
        b.asset_id
    FROM asset a
    LEFT OUTER JOIN acl b ON (b.asset_id = a.id and b.token_id = $tid)
    WHERE loc = $loc
    ORDER BY assetid
    ";

    $r = sql_query($query);

    $out = '';
    while ($row = mysql_fetch_array($r)) {
        if ($row['asset_id'])
            $out .= $row['assetid'].', ';
    }

    return substr($out, 0, -2);
}

// Returns list of all RFIDs for this location
function getAllRFIDs($apt = null) {
    $loc = $_SESSION['loc'];

    // If apt is defined then only select apts
    if ($apt) {

        $query = "
        SELECT 
            a.id,
            a.ip_address,
            a.admin_user,
            a.admin_pass,
            a.description as name
        FROM 
            asset a,
            apt_acl b
        WHERE 
            a.loc = $loc and a.active = 1
            and b.apt_id = $apt
            and b.asset_id = a.id
        ";
    }
    else {
        $query = "
        SELECT *
        FROM asset
        WHERE loc = $loc and a.active = 1
        ";
    }

    $r = sql_query($query);
    while ($row = mysql_fetch_array($r)) {
        $out[] = $row;
    }

    return $out;
}

function is_live($ip, $port = 80) {
    $fp = fsockopen($ip, $port, $errno, $errstr, 1);
    if ($fp === false) {
        $rtn = false;
    }
    else {
        fclose($fp);
        $rtn = true;
    }

    return $rtn;
}

function xml2array($contents, $get_attributes = 1, $priority = 'tag') {
    if (!function_exists('xml_parser_create')) {
        return array ();
    }
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);
    if (!$xml_values)
        return;
    $xml_array = array ();
    $parents = array ();
    $opened_tags = array ();
    $arr = array ();
    $current = & $xml_array;
    $repeated_tag_index = array ();
    foreach ($xml_values as $data) {
        unset ($attributes, $value);
        extract($data);
        $result = array ();
        $attributes_data = array ();
        if (isset ($value)) {
            if ($priority == 'tag')
                $result = $value;
            else
                $result['value'] = $value;
        }
        if (isset ($attributes) and $get_attributes) {
            foreach ($attributes as $attr => $val) {
                if ($priority == 'tag')
                    $attributes_data[$attr] = $val;
                else
                    $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }
        if ($type == "open") {
            $parent[$level -1] = & $current;
            if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                $current[$tag] = $result;
                if ($attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                $current = & $current[$tag];
            }
            else {
                if (isset ($current[$tag][0])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else {
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                        );
                    $repeated_tag_index[$tag . '_' . $level] = 2;
                    if (isset ($current[$tag . '_attr'])) {
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset ($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = & $current[$tag][$last_item_index];
            }
        }
        elseif ($type == "complete") {
            if (!isset ($current[$tag])) {
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
            }
            else {
                if (isset ($current[$tag][0]) and is_array($current[$tag])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    if ($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else {
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                        );
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes) {
                        if (isset ($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset ($current[$tag . '_attr']);
                        }
                        if ($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }
        }
        elseif ($type == 'close') {
            $current = & $parent[$level -1];
        }
    }
    return ($xml_array);
}

function portOffset() {
    global $port_offset;

    $loc = $_SESSION['loc'];
    
    $query = "
    SELECT port_offset
    FROM loc
    WHERE id = $loc
    ";

    $r = sql_query($query);
    $row = mysql_fetch_array($r);
    $port_offset = $row['port_offset'];

}

// Retreieve list of names, inactive ones are gray
function get_names($apt) {
    $query = "
    SELECT *
    FROM people
    WHERE apt = $apt
    ORDER BY nickname
    ";
    
    $r = sql_query($query);

    $out = '';
    while ($row = mysql_fetch_array($r)) {
        extract ($row);

        if ($active) 
            $out .= $nickname.', ';
        else
            $out .= "<span class='done'>$nickname</span>, ";
    }

    return substr($out, 0, -2);
}

// Provide list of People for a specific unit
function get_name_info($apt) {
    $query = "
    SELECT
        a.id,
        a.nickname,
        b.card_id,
        a.active,
        b.token
    FROM 
        people a
    LEFT OUTER JOIN token b ON (b.people_id = a.id)
    WHERE 
        a.apt = $apt
    ORDER BY nickname
    ";
   
    $r = sql_query($query);

    while ($row = mysql_fetch_array($r)) {
        extract ($row);
        $chk = $active ? 'checked' : '';
        echo "<tr>\n",
            "<td><input type='hidden' name='pid[$id]' value='$id' /><a href='people.php?id=$id'>$nickname</a></td>\n",
            "<td>".sel_rfid("token[$id]", $token, $id)."</td>\n",
            "<td align='center'><input type='checkbox' name='active[$id]' $chk value='1' /></td>\n",
            "</tr>\n";
    }
}

function dd($data) {
    error_log(date('Y-m-d H:i:s ').print_r($data,true)."\n", 3, '/tmp/debug.log');
}

function getTokenList() {
    $query = "
    SELECT token
    FROM token
    ";

    $r = sql_query($query);
    while ($row = mysql_fetch_assoc($r)) {
        $assigned[$row['token']] = 1;
    }

    // Find tokens not in system
    $query = "
    SELECT *
    FROM asset_log
    WHERE event_type = '1022'
    ORDER BY timestamp DESC
    ";

    $r = sql_query($query);

    while ($row = mysql_fetch_assoc($r)) {
        $data = unserialize($row['event_data']);
        $tok = $data['rawCardNumber'];
        // If not assigned, and not found
        if (!array_key_exists($tok, $assigned) && !array_key_exists($tok, $tokens))
            $tokens[$tok] = $tok.' - '.date('m/d/Y h:i a', strtotime($row['timestamp']));
    }

    return $tokens;
}

?>
